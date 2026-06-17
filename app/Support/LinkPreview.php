<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Throwable;

class LinkPreview
{
    /**
     * Intenta obtener la URL de la imagen de previsualización de un post.
     *
     * Best-effort: TikTok vía oEmbed (sin auth) y el resto leyendo la
     * metaetiqueta og:image. Instagram/Facebook suelen requerir token de app,
     * así que pueden devolver null (se rellena a mano en ese caso).
     */
    public function imageFor(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        try {
            if (str_contains($url, 'tiktok.com')) {
                $thumb = $this->fromTikTokOembed($url);

                if ($thumb !== null) {
                    return $thumb;
                }
            }

            return $this->fromOpenGraph($url);
        } catch (Throwable) {
            return null;
        }
    }

    private function fromTikTokOembed(string $url): ?string
    {
        $response = Http::timeout(6)->get('https://www.tiktok.com/oembed', ['url' => $url]);

        return $response->ok() ? ($response->json('thumbnail_url') ?: null) : null;
    }

    private function fromOpenGraph(string $url): ?string
    {
        $response = Http::timeout(6)
            ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; ViralPostsBot/1.0)'])
            ->get($url);

        if (! $response->ok()) {
            return null;
        }

        if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $response->body(), $matches)) {
            return html_entity_decode($matches[1]);
        }

        // Variante con content antes de property.
        if (preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\']/i', $response->body(), $matches)) {
            return html_entity_decode($matches[1]);
        }

        return null;
    }
}
