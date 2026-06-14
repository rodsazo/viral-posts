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
     * @return array{account: Account, idea: WinningIdea, myth: Belief, truth: Belief}
     */
    private function ideaWithSharedBeliefs(): array
    {
        $account = Account::factory()->create();
        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);

        $q1 = Question::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id]);
        $q2 = Question::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id]);

        $myth = Belief::factory()->myth()->create(['account_id' => $account->id]);
        $truth = Belief::factory()->truth()->create(['account_id' => $account->id]);

        // El mito está en ambas preguntas: debe aparecer una sola vez (sin duplicados).
        $q1->beliefs()->attach([$myth->id, $truth->id]);
        $q2->beliefs()->attach([$myth->id]);

        $idea = WinningIdea::factory()->create(['account_id' => $account->id]);
        $idea->questions()->attach([$q1->id, $q2->id]);

        return compact('account', 'idea', 'myth', 'truth');
    }

    public function test_winning_idea_derives_unique_beliefs_through_questions(): void
    {
        ['idea' => $idea, 'myth' => $myth, 'truth' => $truth] = $this->ideaWithSharedBeliefs();

        $derived = $idea->derivedBeliefs();

        $this->assertCount(2, $derived, 'El mito compartido no debe duplicarse.');
        $this->assertEqualsCanonicalizing(
            [$myth->id, $truth->id],
            $derived->pluck('id')->all(),
        );
    }

    public function test_winning_idea_without_questions_has_empty_derived_beliefs(): void
    {
        $account = Account::factory()->create();
        $idea = WinningIdea::factory()->create(['account_id' => $account->id]);

        $this->assertTrue($idea->derivedBeliefs()->isEmpty());
    }

    public function test_content_piece_cascades_questions_and_beliefs_through_its_idea(): void
    {
        ['account' => $account, 'idea' => $idea] = $this->ideaWithSharedBeliefs();

        $piece = ContentPiece::factory()->create([
            'account_id' => $account->id,
            'winning_idea_id' => $idea->id,
        ]);

        $this->assertCount(2, $piece->derivedQuestions());
        $this->assertCount(2, $piece->derivedBeliefs());
    }

    public function test_loose_content_piece_without_idea_has_empty_cascade(): void
    {
        $account = Account::factory()->create();

        $piece = ContentPiece::factory()->create([
            'account_id' => $account->id,
            'winning_idea_id' => null,
        ]);

        $this->assertTrue($piece->derivedQuestions()->isEmpty());
        $this->assertTrue($piece->derivedBeliefs()->isEmpty());
    }
}
