<?php

namespace Tests\Feature;

use App\Enums\AwarenessLevel;
use App\Enums\PainType;
use App\Enums\TeamRole;
use App\Livewire\Studio\AudienceHub;
use App\Models\Account;
use App\Models\IdealFollower;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AudienceHubTest extends TestCase
{
    use RefreshDatabase;

    private function member(Account $account): User
    {
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => TeamRole::Editor->value]);

        return $user;
    }

    public function test_non_member_cannot_open_the_audience_hub(): void
    {
        $account = Account::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get("/studio/{$account->slug}/audiencia")
            ->assertForbidden();
    }

    public function test_member_can_create_and_edit_a_follower(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));

        Livewire::test(AudienceHub::class, ['account' => $account])
            ->call('newFollower')
            ->set('followerName', 'AMA DE CASA OCUPADA')
            ->set('awareness', '2')
            ->set('followerDescription', 'Descripción de prueba');

        $follower = IdealFollower::where('account_id', $account->id)->first();
        $this->assertNotNull($follower);
        $this->assertSame('AMA DE CASA OCUPADA', $follower->name);
        $this->assertSame(AwarenessLevel::SolutionAware, $follower->awareness_level);
        $this->assertSame('Descripción de prueba', $follower->description);
    }

    public function test_member_can_add_and_delete_audience_items(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));
        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);

        $component = Livewire::test(AudienceHub::class, ['account' => $account])
            ->set('newQuestion', '¿Cómo duermo mejor?')
            ->call('addQuestion')
            ->set('newBelief.type', 'myth')
            ->set('newBelief.statement', 'Dormir poco es de exitosos')
            ->call('addBelief')
            ->set('newPain.type', PainType::Desire->value)
            ->set('newPain.body', 'Quiero descansar de verdad')
            ->call('addPain');

        $follower->refresh();
        $this->assertSame(1, $follower->questions()->count());
        $this->assertSame(1, $follower->beliefs()->count());
        $this->assertSame(1, $follower->pains()->count());
        $this->assertSame(PainType::Desire, $follower->pains()->first()->type);

        // Borrar la pregunta.
        $questionId = $follower->questions()->first()->id;
        $component->call('deleteQuestion', $questionId);
        $this->assertSame(0, $follower->questions()->count());
    }

    public function test_editing_a_child_item_autosaves(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));
        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);
        $question = Question::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id, 'body' => 'VIEJA']);

        Livewire::test(AudienceHub::class, ['account' => $account])
            ->set("questions.{$question->id}.body", 'NUEVA REDACCIÓN');

        $this->assertSame('NUEVA REDACCIÓN', $question->refresh()->body);
    }
}
