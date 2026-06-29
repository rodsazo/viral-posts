<?php

namespace Tests\Feature;

use App\Models\HerasTemplate;
use App\Models\Niche;
use App\Models\ViralReferent;
use App\Support\LinkPreview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ViralReferentTest extends TestCase
{
    use RefreshDatabase;

    public function test_template_belongs_to_referent_which_belongs_to_niche(): void
    {
        $niche = Niche::factory()->create(['name' => 'Rol de mesa']);
        $referent = ViralReferent::factory()->create(['niche_id' => $niche->id, 'name' => 'Víctor Heras']);
        $template = HerasTemplate::factory()->create([
            'viral_referent_id' => $referent->id,
        ]);

        $this->assertTrue($template->viralReferent->is($referent));
        $this->assertTrue($referent->niche->is($niche));
        $this->assertTrue($referent->herasTemplates->contains($template));
    }

    public function test_link_preview_uses_tiktok_oembed(): void
    {
        Http::fake([
            'www.tiktok.com/oembed*' => Http::response(['thumbnail_url' => 'https://cdn.tiktok.com/thumb.jpg']),
        ]);

        $image = app(LinkPreview::class)->imageFor('https://www.tiktok.com/@user/video/123');

        $this->assertSame('https://cdn.tiktok.com/thumb.jpg', $image);
    }

    public function test_link_preview_parses_open_graph_image(): void
    {
        Http::fake([
            'example.com/*' => Http::response('<html><head><meta property="og:image" content="https://example.com/og.jpg"></head></html>'),
        ]);

        $image = app(LinkPreview::class)->imageFor('https://example.com/post/1');

        $this->assertSame('https://example.com/og.jpg', $image);
    }

    public function test_link_preview_returns_null_for_blank_url(): void
    {
        $this->assertNull(app(LinkPreview::class)->imageFor(null));
        $this->assertNull(app(LinkPreview::class)->imageFor('   '));
    }
}
