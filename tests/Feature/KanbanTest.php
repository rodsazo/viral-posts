<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Filament\Pages\ContentKanban;
use App\Models\Account;
use App\Models\ContentPiece;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KanbanTest extends TestCase
{
    use RefreshDatabase;

    private function actingInTenant(Account $account): User
    {
        $user = User::factory()->create();
        $user->accounts()->attach($account->id);

        $this->actingAs($user);
        Filament::setCurrentPanel('admin');
        Filament::setTenant($account);

        return $user;
    }

    public function test_moving_a_card_persists_the_new_status(): void
    {
        $account = Account::factory()->create();
        $this->actingInTenant($account);

        $piece = ContentPiece::factory()->create([
            'account_id' => $account->id,
            'status' => ContentStatus::Planificacion,
        ]);

        Livewire::test(ContentKanban::class)
            ->call('moveToStatus', $piece->id, ContentStatus::Grabada->value);

        $this->assertSame(ContentStatus::Grabada, $piece->refresh()->status);
    }

    public function test_invalid_status_is_ignored(): void
    {
        $account = Account::factory()->create();
        $this->actingInTenant($account);

        $piece = ContentPiece::factory()->create([
            'account_id' => $account->id,
            'status' => ContentStatus::Planificacion,
        ]);

        Livewire::test(ContentKanban::class)
            ->call('moveToStatus', $piece->id, 'estado_inexistente');

        $this->assertSame(ContentStatus::Planificacion, $piece->refresh()->status);
    }

    public function test_cannot_move_a_piece_from_another_tenant(): void
    {
        $mine = Account::factory()->create();
        $other = Account::factory()->create();
        $this->actingInTenant($mine);

        $foreignPiece = ContentPiece::factory()->create([
            'account_id' => $other->id,
            'status' => ContentStatus::Planificacion,
        ]);

        Livewire::test(ContentKanban::class)
            ->call('moveToStatus', $foreignPiece->id, ContentStatus::Publicada->value);

        $this->assertSame(ContentStatus::Planificacion, $foreignPiece->refresh()->status);
    }
}
