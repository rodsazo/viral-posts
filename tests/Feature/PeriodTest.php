<?php

namespace Tests\Feature;

use App\Enums\IdeaStatus;
use App\Enums\PeriodStatus;
use App\Enums\TeamRole;
use App\Livewire\Studio\PeriodManager;
use App\Livewire\Studio\PeriodSwitcher;
use App\Livewire\Studio\PieceComposer;
use App\Livewire\Studio\StudioHome;
use App\Livewire\Studio\StudioKanban;
use App\Models\Account;
use App\Models\ContentPiece;
use App\Models\Period;
use App\Models\User;
use App\Models\WinningIdea;
use App\Support\StudioPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PeriodTest extends TestCase
{
    use RefreshDatabase;

    private function member(Account $account, TeamRole $role = TeamRole::Editor): User
    {
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => $role->value]);

        return $user;
    }

    public function test_periods_screen_renders_for_a_member(): void
    {
        $account = Account::factory()->create();
        Period::factory()->create(['account_id' => $account->id, 'name' => 'PERIODO VISIBLE']);

        $this->actingAs($this->member($account))
            ->get("/studio/{$account->slug}/periodos")
            ->assertOk()
            ->assertSee('PERIODO VISIBLE');
    }

    public function test_switcher_creates_a_period_and_makes_it_active(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));

        Livewire::test(PeriodSwitcher::class, ['account' => $account])
            ->set('newName', 'Julio 2026')
            ->call('create')
            ->assertDispatched('period-changed');

        $period = $account->periods()->first();
        $this->assertSame('Julio 2026', $period->name);
        $this->assertSame(PeriodStatus::Borrador, $period->status);
        $this->assertSame($period->id, StudioPeriod::id($account->refresh()));
    }

    public function test_new_pieces_are_assigned_to_the_active_period(): void
    {
        $account = Account::factory()->create();
        $period = Period::factory()->create(['account_id' => $account->id]);
        $this->actingAs($this->member($account));

        StudioPeriod::set($account, $period->id);

        Livewire::test(PieceComposer::class, ['account' => $account])->call('newPiece');

        $this->assertSame($period->id, $account->contentPieces()->latest('id')->first()->period_id);
    }

    public function test_composer_can_move_a_piece_into_a_period(): void
    {
        $account = Account::factory()->create();
        $period = Period::factory()->create(['account_id' => $account->id]);
        $piece = ContentPiece::factory()->create(['account_id' => $account->id, 'period_id' => null]);
        $this->actingAs($this->member($account));

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->call('selectPiece', $piece->id)
            ->set('periodId', $period->id);

        $this->assertSame($period->id, $piece->refresh()->period_id);
    }

    public function test_none_mode_shows_unassigned_pieces_even_when_a_period_exists(): void
    {
        $account = Account::factory()->create();
        $period = Period::factory()->create(['account_id' => $account->id]);
        ContentPiece::factory()->create(['account_id' => $account->id, 'period_id' => $period->id, 'title' => 'CON PERIODO']);
        ContentPiece::factory()->create(['account_id' => $account->id, 'period_id' => null, 'title' => 'SIN PERIODO']);
        $this->actingAs($this->member($account));

        StudioPeriod::setNone($account);

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->assertSee('SIN PERIODO')
            ->assertDontSee('CON PERIODO');
    }

    public function test_switcher_can_enter_none_mode(): void
    {
        $account = Account::factory()->create();
        Period::factory()->create(['account_id' => $account->id]);
        $this->actingAs($this->member($account));

        Livewire::test(PeriodSwitcher::class, ['account' => $account])
            ->call('selectNone')
            ->assertDispatched('period-changed');

        $this->assertTrue(StudioPeriod::isNone($account));
    }

    public function test_composer_only_lists_pieces_of_the_active_period(): void
    {
        $account = Account::factory()->create();
        $july = Period::factory()->create(['account_id' => $account->id, 'name' => 'Julio']);
        $august = Period::factory()->create(['account_id' => $account->id, 'name' => 'Agosto']);
        ContentPiece::factory()->create(['account_id' => $account->id, 'period_id' => $july->id, 'title' => 'PIEZA DE JULIO']);
        ContentPiece::factory()->create(['account_id' => $account->id, 'period_id' => $august->id, 'title' => 'PIEZA DE AGOSTO']);
        $this->actingAs($this->member($account));

        StudioPeriod::set($account, $july->id);

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->assertSee('PIEZA DE JULIO')
            ->assertDontSee('PIEZA DE AGOSTO');
    }

    public function test_kanban_only_shows_pieces_of_the_active_period(): void
    {
        $account = Account::factory()->create();
        $july = Period::factory()->create(['account_id' => $account->id]);
        $august = Period::factory()->create(['account_id' => $account->id]);
        ContentPiece::factory()->create(['account_id' => $account->id, 'period_id' => $july->id, 'title' => 'KANBAN JULIO']);
        ContentPiece::factory()->create(['account_id' => $account->id, 'period_id' => $august->id, 'title' => 'KANBAN AGOSTO']);
        $this->actingAs($this->member($account));

        StudioPeriod::set($account, $july->id);

        Livewire::test(StudioKanban::class, ['account' => $account])
            ->assertSee('KANBAN JULIO')
            ->assertDontSee('KANBAN AGOSTO');
    }

    public function test_manager_autosaves_status_and_deletion_is_admin_only(): void
    {
        $account = Account::factory()->create();
        $period = Period::factory()->create(['account_id' => $account->id]);

        // Editor: edita estado (autoguarda) pero NO puede borrar.
        $this->actingAs($this->member($account, TeamRole::Editor));
        Livewire::test(PeriodManager::class, ['account' => $account])
            ->set("periods.{$period->id}.status", PeriodStatus::Publicado->value)
            ->call('deletePeriod', $period->id);

        $this->assertSame(PeriodStatus::Publicado, $period->refresh()->status);
        $this->assertDatabaseHas('periods', ['id' => $period->id]);

        // Admin: sí puede borrar.
        $this->actingAs($this->member($account, TeamRole::Admin));
        Livewire::test(PeriodManager::class, ['account' => $account])->call('deletePeriod', $period->id);
        $this->assertDatabaseMissing('periods', ['id' => $period->id]);
    }

    public function test_home_flags_fija_ideas_without_content_in_the_active_period(): void
    {
        $account = Account::factory()->create();
        $period = Period::factory()->published()->create(['account_id' => $account->id]);
        StudioPeriod::set($account, $period->id);

        WinningIdea::factory()->create(['account_id' => $account->id, 'title' => 'FIJA SIN PIEZA', 'status' => IdeaStatus::Fija]);
        $covered = WinningIdea::factory()->create(['account_id' => $account->id, 'title' => 'FIJA CON PIEZA', 'status' => IdeaStatus::Fija]);
        ContentPiece::factory()->create(['account_id' => $account->id, 'period_id' => $period->id, 'winning_idea_id' => $covered->id]);
        $this->actingAs($this->member($account));

        Livewire::test(StudioHome::class, ['account' => $account])
            ->assertSee('FIJA SIN PIEZA')
            ->assertDontSee('FIJA CON PIEZA');
    }

    public function test_deleting_a_period_leaves_its_pieces_without_period(): void
    {
        $account = Account::factory()->create();
        $period = Period::factory()->create(['account_id' => $account->id]);
        $piece = ContentPiece::factory()->create(['account_id' => $account->id, 'period_id' => $period->id]);
        $this->actingAs($this->member($account, TeamRole::Admin));

        Livewire::test(PeriodManager::class, ['account' => $account])->call('deletePeriod', $period->id);

        $this->assertDatabaseHas('content_pieces', ['id' => $piece->id, 'period_id' => null]);
    }
}
