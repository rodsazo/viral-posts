<?php

namespace Tests\Feature;

use App\Support\ReferenceImageCapture;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Captura persistente de imágenes de referencia: en vez de guardar la URL temporal de la
 * red social (que caduca), descargamos, reducimos y subimos una copia a nuestro disco.
 */
class ReferenceImageCaptureTest extends TestCase
{
    private function jpegBytes(int $w = 1000, int $h = 600): string
    {
        $img = imagecreatetruecolor($w, $h);
        ob_start();
        imagejpeg($img);
        $bytes = (string) ob_get_clean();
        imagedestroy($img);

        return $bytes;
    }

    public function test_it_downloads_downscales_and_stores_a_persistent_copy(): void
    {
        Storage::fake('public');
        config(['filesystems.brand_disk' => 'public']);
        Http::fake(['*' => Http::response($this->jpegBytes(1000, 600), 200, ['Content-Type' => 'image/jpeg'])]);

        $url = app(ReferenceImageCapture::class)->capture('https://example.com/reel/pic.jpg');

        $this->assertNotNull($url);
        $this->assertStringContainsString('reference-images/', $url);

        $files = Storage::disk('public')->files('reference-images');
        $this->assertCount(1, $files);

        // La copia guardada está reducida a <= 720px de ancho.
        $stored = imagecreatefromstring(Storage::disk('public')->get($files[0]));
        $this->assertLessThanOrEqual(720, imagesx($stored));
        imagedestroy($stored);
    }

    public function test_it_returns_null_when_the_target_is_not_an_image(): void
    {
        Storage::fake('public');
        config(['filesystems.brand_disk' => 'public']);
        Http::fake(['*' => Http::response('<html>no image here</html>', 200)]);

        $url = app(ReferenceImageCapture::class)->capture('https://example.com/no-image');

        $this->assertNull($url);
        $this->assertEmpty(Storage::disk('public')->files('reference-images'));
    }

    public function test_empty_url_returns_null(): void
    {
        $this->assertNull(app(ReferenceImageCapture::class)->capture(''));
        $this->assertNull(app(ReferenceImageCapture::class)->capture(null));
    }
}
