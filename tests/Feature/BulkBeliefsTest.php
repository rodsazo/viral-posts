<?php

namespace Tests\Feature;

use App\Enums\BeliefType;
use App\Filament\Pages\BulkBeliefs;
use App\Models\Account;
use App\Models\Belief;
use App\Models\IdealFollower;
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
        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);

        Livewire::test(BulkBeliefs::class)
            ->fillForm([
                'ideal_follower_id' => $follower->id,
                'batches' => [
                    [
                        'myths' => "El rol es dificilísimo\n\nNecesitas un experto",
                        'truths' => "Se aprende jugando\nHay GMs profesionales\n  ",
                    ],
                ],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(2, Belief::where('type', BeliefType::Myth)->count());
        $this->assertSame(2, Belief::where('type', BeliefType::Truth)->count());
        $this->assertSame(4, Belief::where('account_id', $account->id)->count());
    }

    public function test_all_beliefs_belong_to_the_chosen_follower(): void
    {
        $account = Account::factory()->create();
        $this->actInTenant($account);
        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);

        Livewire::test(BulkBeliefs::class)
            ->fillForm([
                'ideal_follower_id' => $follower->id,
                'batches' => [
                    ['myths' => 'Mito A', 'truths' => 'Verdad A'],
                    ['myths' => 'Mito B', 'truths' => ''],
                ],
            ])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(3, Belief::count());
        $this->assertSame(3, $follower->beliefs()->count());
    }

    public function test_follower_is_required(): void
    {
        $account = Account::factory()->create();
        $this->actInTenant($account);

        Livewire::test(BulkBeliefs::class)
            ->fillForm([
                'batches' => [
                    ['myths' => 'Mito sin seguidor', 'truths' => ''],
                ],
            ])
            ->call('save')
            ->assertHasErrors('data.ideal_follower_id');

        $this->assertSame(0, Belief::count());
    }

    public function test_nothing_is_created_when_groups_are_empty(): void
    {
        $account = Account::factory()->create();
        $this->actInTenant($account);
        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);

        Livewire::test(BulkBeliefs::class)
            ->fillForm([
                'ideal_follower_id' => $follower->id,
                'batches' => [
                    ['myths' => "  \n ", 'truths' => ''],
                ],
            ])
            ->call('save');

        $this->assertSame(0, Belief::count());
    }
}
