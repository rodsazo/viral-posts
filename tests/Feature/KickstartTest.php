<?php

namespace Tests\Feature;

use App\Enums\AwarenessLevel;
use App\Enums\BeliefType;
use App\Enums\PainType;
use App\Enums\TeamRole;
use App\Jobs\GenerateSuggestionsJob;
use App\Livewire\Studio\IdealFollowerKickstart;
use App\Models\Account;
use App\Models\IdealFollower;
use App\Models\User;
use App\Support\Ai\BrandContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class KickstartTest extends TestCase
{
    use RefreshDatabase;

    private function member(Account $account): User
    {
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => TeamRole::Editor->value]);

        return $user;
    }

    public function test_kickstart_requires_membership(): void
    {
        $account = Account::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get("/studio/{$account->slug}/kickstart")
            ->assertForbidden();
    }

    public function test_brand_context_prompt_includes_brand_fields(): void
    {
        $account = Account::factory()->create([
            'name' => 'MARCA SALUD',
            'brand_promise' => 'CURAS RÁPIDAS',
            'main_offers' => 'PROGRAMA ONLINE',
        ]);

        $prompt = BrandContext::fromAccount($account)->toPrompt();

        $this->assertStringContainsString('MARCA SALUD', $prompt);
        $this->assertStringContainsString('CURAS RÁPIDAS', $prompt);
        $this->assertStringContainsString('PROGRAMA ONLINE', $prompt);
    }

    public function test_kickstart_enqueues_a_brand_job(): void
    {
        Queue::fake();
        config(['services.anthropic.key' => 'sk-ant-test']);

        $account = Account::factory()->create(['description' => 'una marca']);
        $this->actingAs($this->member($account));

        Livewire::test(IdealFollowerKickstart::class, ['account' => $account])
            ->set('instructions', 'enfoque salud')
            ->call('generate')
            ->assertSet('suggestions', []);

        Queue::assertPushed(GenerateSuggestionsJob::class, fn (GenerateSuggestionsJob $job): bool => $job->context instanceof BrandContext);
        $this->assertDatabaseHas('ai_generations', ['account_id' => $account->id, 'kind' => 'kickstart', 'status' => 'processing']);
    }

    public function test_kickstart_saves_selected_followers_with_their_audience(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));

        Livewire::test(IdealFollowerKickstart::class, ['account' => $account])
            ->set('suggestions', [
                [
                    'name' => 'AMAS DE CASA OCUPADAS',
                    'description' => 'Mujeres con poco tiempo',
                    'awareness_level' => 1,
                    'pains' => [['type' => 'desire', 'body' => 'Descansar de verdad']],
                    'questions' => ['¿Cómo duermo mejor?', '¿Qué como para tener energía?'],
                    'beliefs' => ['Dormir poco es de exitosos'],
                ],
                [
                    'name' => 'NO ELEGIDA',
                    'description' => 'x',
                    'awareness_level' => 0,
                    'pains' => [],
                    'questions' => [],
                    'beliefs' => [],
                ],
            ])
            ->set('selected', [0])
            ->call('createFollowers')
            ->assertRedirect(route('studio.audience', $account));

        $follower = IdealFollower::where('account_id', $account->id)->first();
        $this->assertNotNull($follower);
        $this->assertSame('AMAS DE CASA OCUPADAS', $follower->name);
        $this->assertSame(AwarenessLevel::ProblemAware, $follower->awareness_level);
        $this->assertSame(2, $follower->questions()->count());
        $this->assertSame(1, $follower->beliefs()->count());
        $this->assertSame(BeliefType::Myth, $follower->beliefs()->first()->type);
        $this->assertSame(1, $follower->pains()->count());
        $this->assertSame(PainType::Desire, $follower->pains()->first()->type);

        // Solo se creó la hipótesis elegida.
        $this->assertSame(1, IdealFollower::where('account_id', $account->id)->count());
    }
}
