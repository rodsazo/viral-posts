<?php

namespace Tests\Feature;

use App\Enums\ViralMechanism;
use App\Filament\Pages\BulkIdeas;
use App\Models\Account;
use App\Models\IdealFollower;
use App\Models\Question;
use App\Models\User;
use App\Models\WinningIdea;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BulkIdeasTest extends TestCase
{
    use RefreshDatabase;

    private function actInTenant(Account $account): void
    {
        $user = User::factory()->create();
        $user->accounts()->attach($account->id);

        $this->actingAs($user);
        Filament::setCurrentPanel('admin');
        Filament::setTenant($account);
    }

    public function test_it_creates_multiple_ideas_with_mechanism_and_question_links(): void
    {
        $account = Account::factory()->create();
        $this->actInTenant($account);

        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);
        $question = Question::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id]);

        Livewire::test(BulkIdeas::class)
            ->fillForm([
                'ideas' => [
                    [
                        'title' => 'Tu primera partida en 10 minutos',
                        'concept' => 'Desmontar el mito de la dificultad.',
                        'viral_mechanism' => ViralMechanism::Sorpresa->value,
                        'question_ids' => [$question->id],
                    ],
                    [
                        'title' => 'Solicita un GM',
                        'concept' => 'Para grupos sin máster.',
                        'viral_mechanism' => null,
                        'question_ids' => [],
                    ],
                ],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(2, WinningIdea::where('account_id', $account->id)->count());

        $first = WinningIdea::where('title', 'Tu primera partida en 10 minutos')->first();
        $this->assertSame(ViralMechanism::Sorpresa, $first->viral_mechanism);
        $this->assertSame(1, $first->questions()->count());
    }

    public function test_title_and_concept_are_required(): void
    {
        $account = Account::factory()->create();
        $this->actInTenant($account);

        Livewire::test(BulkIdeas::class)
            ->fillForm([
                'ideas' => [
                    ['title' => '', 'concept' => '', 'viral_mechanism' => null, 'question_ids' => []],
                ],
            ])
            ->call('save')
            ->assertHasErrors();

        $this->assertSame(0, WinningIdea::count());
    }
}
