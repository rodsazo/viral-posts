<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Enums\IdeaStatus;
use App\Enums\TeamRole;
use App\Enums\ValidationStatus;
use App\Livewire\Studio\WinningIdeaManager;
use App\Models\Account;
use App\Models\ContentPiece;
use App\Models\Period;
use App\Models\User;
use App\Models\WinningIdea;
use App\Support\StudioPeriod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WinningIdeaStudioTest extends TestCase
{
    use RefreshDatabase;

    private function member(Account $account, TeamRole $role = TeamRole::Editor): User
    {
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => $role->value]);

        return $user;
    }

    public function test_validation_status_is_derived_from_example_urls(): void
    {
        $pending = WinningIdea::factory()->create(['example_urls' => null]);
        $validated = WinningIdea::factory()->create(['example_urls' => ['https://tiktok.com/@x/video/1']]);

        $this->assertFalse($pending->isValidated());
        $this->assertSame(ValidationStatus::Pending, $pending->validationStatus());

        $this->assertTrue($validated->isValidated());
        $this->assertSame(ValidationStatus::Validated, $validated->validationStatus());
    }

    public function test_studio_crud_requires_membership(): void
    {
        $account = Account::factory()->create();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->get("/studio/{$account->slug}/ideas-ganadoras")
            ->assertForbidden();
    }

    public function test_new_idea_starts_as_draft(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));

        Livewire::test(WinningIdeaManager::class, ['account' => $account])->call('newIdea');

        $this->assertSame(IdeaStatus::Borrador, $account->winningIdeas()->first()->status);
    }

    public function test_list_hides_discarded_ideas_unless_filtered(): void
    {
        $account = Account::factory()->create();
        WinningIdea::factory()->create(['account_id' => $account->id, 'title' => 'IDEA ACTIVA', 'status' => IdeaStatus::Hipotesis]);
        WinningIdea::factory()->create(['account_id' => $account->id, 'title' => 'IDEA DESCARTADA', 'status' => IdeaStatus::Descartada]);
        $this->actingAs($this->member($account));

        Livewire::test(WinningIdeaManager::class, ['account' => $account])
            ->assertSee('IDEA ACTIVA')
            ->assertDontSee('IDEA DESCARTADA')          // por defecto, ocultas
            ->set('filterStatus', 'descartada')
            ->assertSee('IDEA DESCARTADA')
            ->assertDontSee('IDEA ACTIVA')
            ->set('filterStatus', 'todas')
            ->assertSee('IDEA ACTIVA')
            ->assertSee('IDEA DESCARTADA');
    }

    public function test_list_is_ordered_fija_then_hipotesis_then_borrador(): void
    {
        $account = Account::factory()->create();
        // Títulos elegidos para que el orden por estado domine al alfabético.
        WinningIdea::factory()->create(['account_id' => $account->id, 'title' => 'CRUDO AAA', 'status' => IdeaStatus::Borrador]);
        WinningIdea::factory()->create(['account_id' => $account->id, 'title' => 'PROBADA ZZZ', 'status' => IdeaStatus::Fija]);
        WinningIdea::factory()->create(['account_id' => $account->id, 'title' => 'TEORIA MMM', 'status' => IdeaStatus::Hipotesis]);
        $this->actingAs($this->member($account));

        Livewire::test(WinningIdeaManager::class, ['account' => $account])
            ->assertSeeInOrder(['PROBADA ZZZ', 'TEORIA MMM', 'CRUDO AAA']);
    }

    public function test_status_autosaves_in_the_editor(): void
    {
        $account = Account::factory()->create();
        $idea = WinningIdea::factory()->create(['account_id' => $account->id, 'status' => IdeaStatus::Borrador]);
        $this->actingAs($this->member($account));

        Livewire::test(WinningIdeaManager::class, ['account' => $account])
            ->call('selectIdea', $idea->id)
            ->set('status', IdeaStatus::Fija->value);

        $this->assertSame(IdeaStatus::Fija, $idea->refresh()->status);
    }

    public function test_member_can_create_and_autosave_an_idea(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));

        Livewire::test(WinningIdeaManager::class, ['account' => $account])
            ->call('newIdea')
            ->set('title', 'Mi idea ganadora')
            ->set('concept', 'Un ángulo concreto.');

        $this->assertDatabaseHas('winning_ideas', [
            'account_id' => $account->id,
            'title' => 'Mi idea ganadora',
            'concept' => 'Un ángulo concreto.',
        ]);
    }

    public function test_adding_an_example_url_validates_the_idea(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));

        $component = Livewire::test(WinningIdeaManager::class, ['account' => $account])
            ->call('newIdea')
            ->set('newExampleUrl', 'https://www.instagram.com/reel/abc/')
            ->call('addExampleUrl');

        $idea = WinningIdea::where('account_id', $account->id)->firstOrFail();
        $this->assertSame(['https://www.instagram.com/reel/abc/'], $idea->fresh()->example_urls);
        $this->assertTrue($idea->fresh()->isValidated());
        $component->assertSet('validationStatus', ValidationStatus::Validated);

        // Quitar el ejemplo vuelve a dejarla pendiente.
        $component->call('removeExampleUrl', 0);
        $this->assertSame([], $idea->fresh()->example_urls);
        $this->assertFalse($idea->fresh()->isValidated());
    }

    public function test_create_piece_from_idea_makes_a_draft_and_redirects_to_composer(): void
    {
        $account = Account::factory()->create();
        $idea = WinningIdea::factory()->create(['account_id' => $account->id, 'title' => 'FORMATO VIRAL']);
        $this->actingAs($this->member($account));

        Livewire::test(WinningIdeaManager::class, ['account' => $account])
            ->call('createPieceFromIdea', $idea->id)
            ->assertRedirect("/studio/{$account->slug}/piezas?piece=".$account->contentPieces()->first()->id);

        $piece = $account->contentPieces()->first();
        $this->assertSame('FORMATO VIRAL', $piece->title);
        $this->assertSame($idea->id, $piece->winning_idea_id);
        $this->assertSame(ContentStatus::Borrador, $piece->status);
    }

    public function test_deep_link_preselects_an_idea(): void
    {
        $account = Account::factory()->create();
        $idea = WinningIdea::factory()->create(['account_id' => $account->id, 'title' => 'IDEA DEEP LINK']);
        $this->actingAs($this->member($account));

        Livewire::withQueryParams(['idea' => $idea->id])
            ->test(WinningIdeaManager::class, ['account' => $account])
            ->assertSet('selectedId', $idea->id)
            ->assertSet('title', 'IDEA DEEP LINK');
    }

    public function test_deletion_is_reserved_to_brand_admins(): void
    {
        $account = Account::factory()->create();
        $idea = WinningIdea::factory()->create(['account_id' => $account->id]);

        // Editor: no puede borrar.
        $this->actingAs($this->member($account, TeamRole::Editor));
        Livewire::test(WinningIdeaManager::class, ['account' => $account])
            ->call('deleteIdea', $idea->id);
        $this->assertDatabaseHas('winning_ideas', ['id' => $idea->id]);

        // Admin: sí.
        $this->actingAs($this->member($account, TeamRole::Admin));
        Livewire::test(WinningIdeaManager::class, ['account' => $account])
            ->call('deleteIdea', $idea->id);
        $this->assertDatabaseMissing('winning_ideas', ['id' => $idea->id]);
    }

    public function test_counts_pieces_of_the_active_period_and_filters_by_them(): void
    {
        $account = Account::factory()->create();
        $period = Period::factory()->create(['account_id' => $account->id]);
        $otherPeriod = Period::factory()->create(['account_id' => $account->id]);

        $withPieces = WinningIdea::factory()->create(['account_id' => $account->id, 'title' => 'IDEA CON PIEZAS']);
        $withoutPieces = WinningIdea::factory()->create(['account_id' => $account->id, 'title' => 'IDEA SIN PIEZAS']);

        // 2 piezas de la idea en el periodo activo; 1 en otro periodo (no debe contar).
        ContentPiece::factory()->count(2)->create(['account_id' => $account->id, 'winning_idea_id' => $withPieces->id, 'period_id' => $period->id]);
        ContentPiece::factory()->create(['account_id' => $account->id, 'winning_idea_id' => $withPieces->id, 'period_id' => $otherPeriod->id]);

        $this->actingAs($this->member($account));
        StudioPeriod::set($account, $period->id);

        Livewire::test(WinningIdeaManager::class, ['account' => $account])
            ->assertViewHas('pieceCounts', fn ($counts) => (int) ($counts[$withPieces->id] ?? 0) === 2 && (int) ($counts[$withoutPieces->id] ?? 0) === 0)
            ->set('filterPieces', 'con')
            ->assertSee('IDEA CON PIEZAS')
            ->assertDontSee('IDEA SIN PIEZAS')
            ->set('filterPieces', 'sin')
            ->assertSee('IDEA SIN PIEZAS')
            ->assertDontSee('IDEA CON PIEZAS');
    }

    public function test_manager_only_lists_ideas_from_the_active_brand(): void
    {
        $account = Account::factory()->create();
        $other = Account::factory()->create();
        $this->actingAs($this->member($account));

        WinningIdea::factory()->create(['account_id' => $account->id, 'title' => 'IDEA PROPIA']);
        WinningIdea::factory()->create(['account_id' => $other->id, 'title' => 'IDEA AJENA']);

        Livewire::test(WinningIdeaManager::class, ['account' => $account])
            ->assertSee('IDEA PROPIA')
            ->assertDontSee('IDEA AJENA');
    }
}
