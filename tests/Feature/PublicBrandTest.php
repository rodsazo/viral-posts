<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\Account;
use App\Models\ContentPiece;
use App\Models\Period;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicBrandTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_public_token_is_generated_for_each_account(): void
    {
        $account = Account::factory()->create();

        $this->assertNotNull($account->public_token);
        $this->assertSame(40, strlen($account->public_token));
    }

    public function test_board_shows_the_latest_published_period_and_only_ready_pieces(): void
    {
        $account = Account::factory()->create(['name' => 'El Rod']);

        // Un periodo publicado más antiguo y el más reciente (este es el que debe verse).
        Period::factory()->published()->create(['account_id' => $account->id, 'name' => 'JUNIO VIEJO']);
        $latest = Period::factory()->published()->create(['account_id' => $account->id, 'name' => 'JULIO ACTUAL']);

        $ready = ContentPiece::factory()->create([
            'account_id' => $account->id,
            'period_id' => $latest->id,
            'title' => 'PIEZA LISTA',
            'status' => ContentStatus::ListaParaGrabacion,
        ]);
        // Pieza del periodo correcto pero en estado anterior: NO debe aparecer.
        ContentPiece::factory()->create([
            'account_id' => $account->id,
            'period_id' => $latest->id,
            'title' => 'PIEZA EN BORRADOR',
            'status' => ContentStatus::Borrador,
        ]);

        $this->get("/m/{$account->public_token}")
            ->assertOk()
            ->assertSee('El Rod')
            ->assertSee('JULIO ACTUAL')
            ->assertDontSee('JUNIO VIEJO')
            ->assertSee('PIEZA LISTA')
            ->assertDontSee('PIEZA EN BORRADOR')
            ->assertSee(route('piece.public', $ready->public_token)); // la tarjeta enlaza a la pieza
    }

    public function test_board_shows_a_friendly_message_when_no_published_period(): void
    {
        $account = Account::factory()->create();
        // Solo un periodo en borrador: no cuenta como abierto.
        Period::factory()->create(['account_id' => $account->id]);

        $this->get("/m/{$account->public_token}")
            ->assertOk()
            ->assertSee('No hay periodos de trabajo abiertos');
    }

    public function test_an_unknown_brand_token_returns_404(): void
    {
        $this->get('/m/'.Str::random(40))->assertNotFound();
    }
}
