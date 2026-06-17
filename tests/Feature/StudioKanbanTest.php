<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Enums\TeamRole;
use App\Livewire\Studio\StudioKanban;
use App\Models\Account;
use App\Models\ContentPiece;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StudioKanbanTest extends TestCase
{
    use RefreshDatabase;

    private function member(Account $account): User
    {
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => TeamRole::Editor->value]);

        return $user;
    }

    public function test_member_can_open_the_kanban(): void
    {
        $account = Account::factory()->create();
        $user = $this->member($account);
        ContentPiece::factory()->create(['account_id' => $account->id, 'title' => 'TARJETA KANBAN']);

        $this->actingAs($user)
            ->get("/studio/{$account->slug}/kanban")
            ->assertOk()
            ->assertSee('Pipeline de producción')
            ->assertSee('TARJETA KANBAN');
    }

    public function test_moving_a_card_persists_the_status(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));
        $piece = ContentPiece::factory()->create(['account_id' => $account->id, 'status' => ContentStatus::Planificacion]);

        Livewire::test(StudioKanban::class, ['account' => $account])
            ->call('moveToStatus', $piece->id, ContentStatus::Grabada->value);

        $this->assertSame(ContentStatus::Grabada, $piece->refresh()->status);
    }

    public function test_cannot_move_a_piece_from_another_account(): void
    {
        $mine = Account::factory()->create();
        $other = Account::factory()->create();
        $this->actingAs($this->member($mine));
        $foreign = ContentPiece::factory()->create(['account_id' => $other->id, 'status' => ContentStatus::Planificacion]);

        Livewire::test(StudioKanban::class, ['account' => $mine])
            ->call('moveToStatus', $foreign->id, ContentStatus::Publicada->value);

        $this->assertSame(ContentStatus::Planificacion, $foreign->refresh()->status);
    }
}
