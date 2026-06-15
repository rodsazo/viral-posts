<?php

namespace Tests\Feature;

use App\Enums\BeliefType;
use App\Filament\Pages\BulkBeliefs;
use App\Models\Account;
use App\Models\Belief;
use App\Models\IdealFollower;
use App\Models\Question;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BulkBeliefsTest extends TestCase
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

    public function test_it_creates_myths_and_truths_skipping_blank_lines(): void
    {
        $account = Account::factory()->create();
        $this->actInTenant($account);

        Livewire::test(BulkBeliefs::class)
            ->fillForm([
                'batches' => [
                    [
                        'myths' => "El rol es dificilísimo\n\nNecesitas un experto",
                        'truths' => "Se aprende jugando\nHay GMs profesionales\n  ",
                        'question_ids' => [],
                    ],
                ],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(2, Belief::where('type', BeliefType::Myth)->count());
        $this->assertSame(2, Belief::where('type', BeliefType::Truth)->count());
        $this->assertSame(4, Belief::where('account_id', $account->id)->count());
    }

    public function test_each_group_links_to_its_own_questions(): void
    {
        $account = Account::factory()->create();
        $this->actInTenant($account);

        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);
        $q1 = Question::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id]);
        $q2 = Question::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id]);

        Livewire::test(BulkBeliefs::class)
            ->fillForm([
                'batches' => [
                    ['myths' => 'Mito A', 'truths' => 'Verdad A', 'question_ids' => [$q1->id]],
                    ['myths' => 'Mito B', 'truths' => '', 'question_ids' => [$q2->id]],
                ],
            ])
            ->call('save')
            ->assertHasNoErrors();

        // Grupo 1: 2 creencias → q1; Grupo 2: 1 creencia → q2.
        $this->assertSame(3, Belief::count());
        $this->assertSame(2, $q1->beliefs()->count());
        $this->assertSame(1, $q2->beliefs()->count());
    }

    public function test_nothing_is_created_when_groups_are_empty(): void
    {
        $account = Account::factory()->create();
        $this->actInTenant($account);

        Livewire::test(BulkBeliefs::class)
            ->fillForm([
                'batches' => [
                    ['myths' => "  \n ", 'truths' => '', 'question_ids' => []],
                ],
            ])
            ->call('save');

        $this->assertSame(0, Belief::count());
    }
}
