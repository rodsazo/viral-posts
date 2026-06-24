<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Belief;
use App\Models\ContentPiece;
use App\Models\IdealFollower;
use App\Models\Question;
use App\Models\WinningIdea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiHopVisibilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * El Seguidor Ideal es el centro: de él salen preguntas (curadas por la idea) y
     * mitos/verdades (directos del seguidor).
     *
     * @return array{account: Account, follower: IdealFollower, idea: WinningIdea, myth: Belief, truth: Belief}
     */
    private function ideaForFollower(): array
    {
        $account = Account::factory()->create();
        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);

        $q1 = Question::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id]);
        $q2 = Question::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id]);

        // Creencias DIRECTAS del seguidor.
        $myth = Belief::factory()->myth()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id]);
        $truth = Belief::factory()->truth()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id]);

        $idea = WinningIdea::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id]);
        $idea->questions()->attach([$q1->id, $q2->id]);

        return compact('account', 'follower', 'idea', 'myth', 'truth');
    }

    public function test_winning_idea_derives_beliefs_from_its_follower(): void
    {
        ['idea' => $idea, 'myth' => $myth, 'truth' => $truth] = $this->ideaForFollower();

        $derived = $idea->derivedBeliefs();

        $this->assertCount(2, $derived);
        $this->assertEqualsCanonicalizing(
            [$myth->id, $truth->id],
            $derived->pluck('id')->all(),
        );
    }

    public function test_winning_idea_without_follower_has_empty_derived_beliefs(): void
    {
        $account = Account::factory()->create();
        $idea = WinningIdea::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => null]);

        $this->assertTrue($idea->derivedBeliefs()->isEmpty());
    }

    public function test_content_piece_cascades_questions_via_idea_and_beliefs_via_follower(): void
    {
        ['account' => $account, 'follower' => $follower, 'idea' => $idea] = $this->ideaForFollower();

        $piece = ContentPiece::factory()->create([
            'account_id' => $account->id,
            'ideal_follower_id' => $follower->id,
            'winning_idea_id' => $idea->id,
        ]);

        $this->assertCount(2, $piece->derivedQuestions()); // de la idea
        $this->assertCount(2, $piece->derivedBeliefs());    // del seguidor
    }

    public function test_loose_content_piece_without_idea_or_follower_has_empty_cascade(): void
    {
        $account = Account::factory()->create();

        $piece = ContentPiece::factory()->create([
            'account_id' => $account->id,
            'ideal_follower_id' => null,
            'winning_idea_id' => null,
        ]);

        $this->assertTrue($piece->derivedQuestions()->isEmpty());
        $this->assertTrue($piece->derivedBeliefs()->isEmpty());
    }
}
