<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\ContentPiece;
use App\Models\WinningIdea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicPieceTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_public_token_is_generated_on_creation(): void
    {
        $piece = ContentPiece::factory()->create();

        $this->assertNotNull($piece->public_token);
        $this->assertSame(40, strlen($piece->public_token));
    }

    public function test_guest_can_view_a_piece_by_its_public_token(): void
    {
        $account = Account::factory()->create(['name' => 'El Rod']);
        $idea = WinningIdea::factory()->create([
            'account_id' => $account->id,
            'title' => 'EL GIRO INESPERADO',
            'concept' => 'Empezar por el final.',
            'example_urls' => ['https://www.tiktok.com/@alguien/video/123'],
        ]);
        $piece = ContentPiece::factory()->create([
            'account_id' => $account->id,
            'winning_idea_id' => $idea->id,
            'title' => 'MI PIEZA PÚBLICA',
            'hook' => 'GANCHO DE PRUEBA',
            'story' => 'HISTORIA DE PRUEBA',
        ]);

        // Sin sesión (invitado): la página pública es accesible.
        $this->get("/p/{$piece->public_token}")
            ->assertOk()
            ->assertSee('MI PIEZA PÚBLICA')
            ->assertSee('EL GIRO INESPERADO')
            ->assertSee('GANCHO DE PRUEBA')
            ->assertSee('HISTORIA DE PRUEBA')
            ->assertSee('https://www.tiktok.com/@alguien/video/123')
            ->assertSee('El Rod');
    }

    public function test_an_unknown_token_returns_404(): void
    {
        $this->get('/p/'.Str::random(40))->assertNotFound();
    }
}
