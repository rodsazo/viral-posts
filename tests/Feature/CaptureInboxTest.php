<?php

namespace Tests\Feature;

use App\Enums\TeamRole;
use App\Livewire\Studio\CaptureInbox;
use App\Models\Account;
use App\Models\Belief;
use App\Models\Capture;
use App\Models\IdealFollower;
use App\Models\Question;
use App\Models\User;
use App\Models\WinningIdea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CaptureInboxTest extends TestCase
{
    use RefreshDatabase;

    private function member(Account $account): User
    {
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => TeamRole::Editor->value]);

        return $user;
    }

    public function test_member_can_open_the_inbox(): void
    {
        $account = Account::factory()->create();
        $user = $this->member($account);

        $this->actingAs($user)
            ->get("/studio/{$account->slug}/inbox")
            ->assertOk()
            ->assertSee('Inbox de captura');
    }

    public function test_capture_creates_one_record_per_line(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));

        Livewire::test(CaptureInbox::class, ['account' => $account])
            ->set('note', "Idea uno\n\nIdea dos")
            ->call('capture')
            ->assertSet('note', '');

        $this->assertSame(2, Capture::where('account_id', $account->id)->count());
    }

    public function test_convert_capture_to_belief_idea_and_question(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));
        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);

        $toMyth = Capture::factory()->create(['account_id' => $account->id, 'body' => 'El rol es difícil']);
        $toIdea = Capture::factory()->create(['account_id' => $account->id, 'body' => 'Partida en 10 min']);
        $toQuestion = Capture::factory()->create(['account_id' => $account->id, 'body' => '¿Por dónde empiezo?']);

        Livewire::test(CaptureInbox::class, ['account' => $account])
            ->call('toBelief', $toMyth->id, 'myth')
            ->call('toIdea', $toIdea->id)
            ->call('toQuestion', $toQuestion->id);

        $this->assertSame(1, Belief::where('account_id', $account->id)->count());
        $this->assertSame(1, WinningIdea::where('account_id', $account->id)->count());
        $this->assertSame(1, Question::where('account_id', $account->id)->where('ideal_follower_id', $follower->id)->count());

        // Las capturas convertidas se retiran de la bandeja.
        $this->assertSame(0, Capture::where('account_id', $account->id)->count());
    }

    public function test_discard_removes_a_capture(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));
        $capture = Capture::factory()->create(['account_id' => $account->id]);

        Livewire::test(CaptureInbox::class, ['account' => $account])
            ->call('discard', $capture->id);

        $this->assertNull(Capture::find($capture->id));
    }
}
