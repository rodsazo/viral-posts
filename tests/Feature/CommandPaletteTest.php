<?php

namespace Tests\Feature;

use App\Livewire\Studio\CommandPalette;
use App\Models\Account;
use App\Models\ContentPiece;
use App\Models\User;
use App\Models\WinningIdea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CommandPaletteTest extends TestCase
{
    use RefreshDatabase;

    private function member(Account $account): User
    {
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => 'editor']);

        return $user;
    }

    public function test_search_finds_pieces_and_ideas_by_title(): void
    {
        $account = Account::factory()->create();
        ContentPiece::factory()->create(['account_id' => $account->id, 'title' => 'PIEZA BUSCABLE']);
        WinningIdea::factory()->create(['account_id' => $account->id, 'title' => 'IDEA BUSCABLE']);
        ContentPiece::factory()->create(['account_id' => $account->id, 'title' => 'OTRA COSA']);
        $this->actingAs($this->member($account));

        Livewire::test(CommandPalette::class, ['account' => $account])
            ->set('query', 'BUSCABLE')
            ->assertSee('PIEZA BUSCABLE')
            ->assertSee('IDEA BUSCABLE')
            ->assertDontSee('OTRA COSA');
    }

    public function test_shortcuts_are_listed_and_filterable(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));

        Livewire::test(CommandPalette::class, ['account' => $account])
            ->assertSee('Generador de piezas')          // atajos visibles sin buscar
            ->assertSee('Kanban')
            ->set('query', 'kanban')
            ->assertSee('Kanban')
            ->assertDontSee('Generador de piezas');
    }
}
