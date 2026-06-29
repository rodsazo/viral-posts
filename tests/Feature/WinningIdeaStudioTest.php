<?php

namespace Tests\Feature;

use App\Enums\IdeaStatus;
use App\Enums\TeamRole;
use App\Enums\ValidationStatus;
use App\Livewire\Studio\WinningIdeaManager;
use App\Models\Account;
use App\Models\IdealFollower;
use App\Models\Question;
use App\Models\User;
use App\Models\WinningIdea;
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

    public function test_linking_questions_syncs_the_pivot(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));

        $question = Question::factory()->create(['account_id' => $account->id, 'body' => 'PREGUNTA CLAVE']);

        Livewire::test(WinningIdeaManager::class, ['account' => $account])
            ->call('newIdea')
            ->set('questionIds', [$question->id]);

        $idea = WinningIdea::where('account_id', $account->id)->firstOrFail();
        $this->assertTrue($idea->questions()->whereKey($question->id)->exists());
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

    public function test_manager_list_filters_by_follower_and_shows_follower_name(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));

        $followerA = IdealFollower::factory()->create(['account_id' => $account->id, 'name' => 'SEGUIDOR A']);
        $followerB = IdealFollower::factory()->create(['account_id' => $account->id, 'name' => 'SEGUIDOR B']);
        WinningIdea::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $followerA->id, 'title' => 'IDEA DE A']);
        WinningIdea::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $followerB->id, 'title' => 'IDEA DE B']);

        // Sin filtro: ambas, con el nombre del seguidor visible en su tarjeta.
        Livewire::test(WinningIdeaManager::class, ['account' => $account])
            ->assertSee('IDEA DE A')
            ->assertSee('IDEA DE B')
            ->assertSee('SEGUIDOR A')
            // Al filtrar por A, solo aparece su idea.
            ->set('filterFollowerId', $followerA->id)
            ->assertSee('IDEA DE A')
            ->assertDontSee('IDEA DE B');
    }
}
