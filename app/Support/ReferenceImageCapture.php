<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Captura una copia PERSISTENTE de una imagen de referencia (miniatura de un reel de
 * Instagram / post de TikTok, etc.).
 *
 * Problema que resuelve: la URL que expone la red social (og:image / thumbnail) es
 * TEMPORAL y caduca, dejando las miniaturas rotas. Aquí resolvemos esa URL, descargamos
 * la imagen, la reducimos a un tamaño pequeño y la subimos a nuestro propio disco
 * (`brand_disk`: S3 en producción). Devolvemos una URL nuestra, que no caduca.
 */
class ReferenceImageCapture
{
    /** Ancho máximo de la copia almacenada (px). Las miniaturas no necesitan más. */
    private const MAX_WIDTH = 720;

    /** Límite de descarga para no traer archivos enormes (bytes). */
    private const MAX_DOWNLOAD = 15_000_000;

    public function __construct(private LinkPreview $preview) {}

    /**
     * Dado el enlace de un post (o una URL de imagen directa), guarda una copia reducida
     * en nuestro disco y devuelve su URL persistente. Best-effort: null si no se puede.
     */
    public function capture(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        try {
            // 1. Resolver la URL real de la imagen (og:image / thumbnail de TikTok).
            //    Si no se resuelve, quizá la propia URL ya es una imagen directa.
            $imageUrl = $this->preview->imageFor($url) ?? $url;

            // 2. Descargar los bytes.
            $bytes = $this->download($imageUrl);

            if ($bytes === null) {
                return null;
            }

            // 3. Reducir/reencodear a una miniatura pequeña.
            $optimized = $this->downscale($bytes);

            if ($optimized === null) {
                return null;
            }

            // 4. Guardar en nuestro disco y devolver la URL persistente.
            $disk = (string) config('filesystems.brand_disk', 'public');
            $path = 'reference-images/'.Str::random(40).'.jpg';

            Storage::disk($disk)->put($path, $optimized, 'public');

            return $this->publicUrl($disk, $path);
        } catch (Throwable) {
            return null;
        }
    }

    private function download(string $imageUrl): ?string
    {
        $response = Http::timeout(10)
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; ViralPostsBot/1.0)'])
            ->get($imageUrl);

        if (! $response->ok()) {
            return null;
        }

        $body = $response->body();

        return ($body !== '' && strlen($body) <= self::MAX_DOWNLOAD) ? $body : null;
    }

    /**
     * Reduce la imagen a un ancho máximo y la reencodea como JPEG. Devuelve los bytes o
     * null si los datos no son una imagen que GD entienda.
     */
    private function downscale(string $bytes): ?string
    {
        $image = @imagecreatefromstring($bytes);

        if ($image === false) {
            return null;
        }

        if (imagesx($image) > self::MAX_WIDTH) {
            $scaled = imagescale($image, self::MAX_WIDTH); // alto -1: mantiene proporción

            if ($scaled !== false) {
                imagedestroy($image);
                $image = $scaled;
            }
        }

        ob_start();
        imagejpeg($image, null, 82);
        $out = (string) ob_get_clean();
        imagedestroy($image);

        return $out !== '' ? $out : null;
    }

    /**
     * URL pública para el disco dado. En el disco público local devolvemos una URL
     * relativa a la raíz (evita el desajuste de host/puerto); en S3, la absoluta del
     * bucket. Mismo criterio que Account::logoUrl().
     */
    private function publicUrl(string $disk, string $path): string
    {
        $url = Storage::disk($disk)->url($path);

        if ($disk === 'public') {
            return preg_replace('#^https?://[^/]+#', '', $url) ?: $url;
        }

        return $url;
    }
}
