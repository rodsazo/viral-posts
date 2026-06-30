<?php

namespace Tests\Feature;

use App\Enums\BeliefType;
use App\Filament\Resources\ContentPieces\Pages\CreateContentPiece;
use App\Filament\Resources\Questions\Pages\CreateQuestion;
use App\Models\Account;
use App\Models\Belief;
use App\Models\IdealFollower;
use App\Models\Question;
use App\Models\User;
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

    public function test_question_form_shows_existing_questions_for_the_chosen_follower(): void
    {
        $follower = IdealFollower::factory()->create(['account_id' => $this->account->id]);
        Question::factory()->create(['account_id' => $this->account->id, 'ideal_follower_id' => $follower->id, 'body' => 'PREGUNTA YA EXISTENTE']);

        Livewire::test(CreateQuestion::class)
            ->assertDontSee('PREGUNTA YA EXISTENTE')
            ->fillForm(['ideal_follower_id' => $follower->id])
            ->assertSee('PREGUNTA YA EXISTENTE');
    }

    public function test_choosing_a_follower_reveals_its_questions_and_beliefs_in_the_piece_form(): void
    {
        $follower = IdealFollower::factory()->create(['account_id' => $this->account->id]);
        Question::factory()->create([
            'account_id' => $this->account->id,
            'ideal_follower_id' => $follower->id,
            'body' => 'PREGUNTA EN CASCADA',
        ]);
        Belief::factory()->create([
            'account_id' => $this->account->id,
            'ideal_follower_id' => $follower->id,
            'type' => BeliefType::Truth,
            'statement' => 'CREENCIA EN CASCADA',
        ]);

        // El contexto de la pieza sale del SEGUIDOR elegido (ya no de la idea).
        Livewire::test(CreateContentPiece::class)
            ->assertDontSee('PREGUNTA EN CASCADA')
            ->fillForm(['ideal_follower_id' => $follower->id])
            ->assertSee('PREGUNTA EN CASCADA')
            ->assertSee('CREENCIA EN CASCADA');
    }
}
