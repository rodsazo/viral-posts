<?php

namespace Tests\Feature;

use App\Enums\BeliefType;
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

    public function test_choosing_a_follower_reveals_its_beliefs_live_in_the_idea_form(): void
    {
        // El seguidor es el centro: al elegirlo, el panel muestra sus mitos/verdades.
        $follower = IdealFollower::factory()->create(['account_id' => $this->account->id]);
        Belief::factory()->myth()->create(['account_id' => $this->account->id, 'ideal_follower_id' => $follower->id, 'statement' => 'MITO CONTEXTUAL VISIBLE']);
        Belief::factory()->truth()->create(['account_id' => $this->account->id, 'ideal_follower_id' => $follower->id, 'statement' => 'VERDAD CONTEXTUAL VISIBLE']);

        Livewire::test(CreateWinningIdea::class)
            ->assertDontSee('MITO CONTEXTUAL VISIBLE')
            ->fillForm(['ideal_follower_id' => $follower->id])
            ->assertSee('MITO CONTEXTUAL VISIBLE')
            ->assertSee('VERDAD CONTEXTUAL VISIBLE');
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

    public function test_choosing_an_idea_reveals_its_questions_and_follower_beliefs_in_the_piece_form(): void
    {
        $follower = IdealFollower::factory()->create(['account_id' => $this->account->id]);
        $question = Question::factory()->create([
            'account_id' => $this->account->id,
            'ideal_follower_id' => $follower->id,
            'body' => 'PREGUNTA EN CASCADA',
        ]);
        // Creencia DIRECTA del seguidor (no vía pregunta).
        Belief::factory()->create([
            'account_id' => $this->account->id,
            'ideal_follower_id' => $follower->id,
            'type' => BeliefType::Truth,
            'statement' => 'CREENCIA EN CASCADA',
        ]);

        $idea = WinningIdea::factory()->create(['account_id' => $this->account->id, 'ideal_follower_id' => $follower->id]);
        $idea->questions()->attach($question->id);

        // Al elegir la idea, sus preguntas y los mitos/verdades de su seguidor son visibles.
        Livewire::test(CreateContentPiece::class)
            ->assertDontSee('PREGUNTA EN CASCADA')
            ->fillForm(['winning_idea_id' => $idea->id])
            ->assertSee('PREGUNTA EN CASCADA')
            ->assertSee('CREENCIA EN CASCADA');
    }
}
