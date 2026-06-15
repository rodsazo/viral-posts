<?php

namespace Tests\Feature;

use App\Enums\BeliefType;
use App\Filament\Resources\Beliefs\Pages\CreateBelief;
use App\Filament\Resources\ContentPieces\Pages\CreateContentPiece;
use App\Filament\Resources\Questions\Pages\CreateQuestion;
use App\Filament\Resources\WinningIdeas\Pages\CreateWinningIdea;
use App\Models\Account;
use App\Models\Belief;
use App\Models\IdealFollower;
use App\Models\Question;
use App\Models\User;
use App\Models\WinningIdea;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FormContextPanelTest extends TestCase
{
    use RefreshDatabase;

    private Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()->create();
        $user = User::factory()->create();
        $user->accounts()->attach($this->account->id);

        $this->actingAs($user);
        Filament::setCurrentPanel('admin');
        Filament::setTenant($this->account);
    }

    public function test_choosing_questions_reveals_their_beliefs_live_in_the_idea_form(): void
    {
        $follower = IdealFollower::factory()->create(['account_id' => $this->account->id]);
        $q1 = Question::factory()->create(['account_id' => $this->account->id, 'ideal_follower_id' => $follower->id]);
        $q2 = Question::factory()->create(['account_id' => $this->account->id, 'ideal_follower_id' => $follower->id]);

        $myth = Belief::factory()->myth()->create(['account_id' => $this->account->id, 'statement' => 'MITO CONTEXTUAL VISIBLE']);
        $truth = Belief::factory()->truth()->create(['account_id' => $this->account->id, 'statement' => 'VERDAD CONTEXTUAL VISIBLE']);
        $q1->beliefs()->attach($myth->id);
        $q2->beliefs()->attach($truth->id);

        Livewire::test(CreateWinningIdea::class)
            ->assertDontSee('MITO CONTEXTUAL VISIBLE')
            ->fillForm(['questions' => [$q1->id, $q2->id]])
            ->assertSee('MITO CONTEXTUAL VISIBLE')
            ->assertSee('VERDAD CONTEXTUAL VISIBLE');
    }

    public function test_idea_form_flags_chosen_questions_without_beliefs(): void
    {
        $follower = IdealFollower::factory()->create(['account_id' => $this->account->id]);
        $withBelief = Question::factory()->create(['account_id' => $this->account->id, 'ideal_follower_id' => $follower->id, 'body' => 'PREGUNTA CON CREENCIA']);
        $withoutBelief = Question::factory()->create(['account_id' => $this->account->id, 'ideal_follower_id' => $follower->id, 'body' => 'PREGUNTA HUERFANA SIN CREENCIA']);

        $belief = Belief::factory()->create(['account_id' => $this->account->id]);
        $withBelief->beliefs()->attach($belief->id);

        Livewire::test(CreateWinningIdea::class)
            ->fillForm(['questions' => [$withBelief->id, $withoutBelief->id]])
            ->assertSee('PREGUNTA HUERFANA SIN CREENCIA');
    }

    public function test_question_form_shows_existing_questions_for_the_chosen_follower(): void
    {
        $follower = IdealFollower::factory()->create(['account_id' => $this->account->id]);
        Question::factory()->create(['account_id' => $this->account->id, 'ideal_follower_id' => $follower->id, 'body' => 'PREGUNTA YA EXISTENTE']);

        Livewire::test(CreateQuestion::class)
            ->assertDontSee('PREGUNTA YA EXISTENTE')
            ->fillForm(['ideal_follower_id' => $follower->id])
            ->assertSee('PREGUNTA YA EXISTENTE');
    }

    public function test_belief_form_shows_reach_of_chosen_questions(): void
    {
        $follower = IdealFollower::factory()->create(['account_id' => $this->account->id, 'name' => 'SEGUIDOR ALCANZADO']);
        $question = Question::factory()->create(['account_id' => $this->account->id, 'ideal_follower_id' => $follower->id]);

        Livewire::test(CreateBelief::class)
            ->assertDontSee('SEGUIDOR ALCANZADO')
            ->fillForm(['questions' => [$question->id]])
            ->assertSee('SEGUIDOR ALCANZADO');
    }

    public function test_choosing_an_idea_reveals_its_questions_and_beliefs_live_in_the_piece_form(): void
    {
        $follower = IdealFollower::factory()->create(['account_id' => $this->account->id]);
        $question = Question::factory()->create([
            'account_id' => $this->account->id,
            'ideal_follower_id' => $follower->id,
            'body' => 'PREGUNTA EN CASCADA',
        ]);
        $belief = Belief::factory()->create([
            'account_id' => $this->account->id,
            'type' => BeliefType::Truth,
            'statement' => 'CREENCIA EN CASCADA',
        ]);
        $question->beliefs()->attach($belief->id);

        $idea = WinningIdea::factory()->create(['account_id' => $this->account->id]);
        $idea->questions()->attach($question->id);

        Livewire::test(CreateContentPiece::class)
            ->assertDontSee('PREGUNTA EN CASCADA')
            ->fillForm(['winning_idea_id' => $idea->id])
            ->assertSee('PREGUNTA EN CASCADA')
            ->assertSee('CREENCIA EN CASCADA');
    }
}
