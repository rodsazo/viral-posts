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

    public function test_content_piece_cascades_questions_and_beliefs_from_its_follower(): void
    {
        $account = Account::factory()->create();
        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);

        Question::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id]);
        Question::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id]);
        Belief::factory()->myth()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id]);
        Belief::factory()->truth()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id]);

        // La idea ganadora es solo el formato; el contexto sale del SEGUIDOR de la pieza.
        $idea = WinningIdea::factory()->create(['account_id' => $account->id]);
        $piece = ContentPiece::factory()->create([
            'account_id' => $account->id,
            'ideal_follower_id' => $follower->id,
            'winning_idea_id' => $idea->id,
        ]);

        $this->assertCount(2, $piece->derivedQuestions());
        $this->assertCount(2, $piece->derivedBeliefs());
    }

    public function test_loose_content_piece_without_follower_has_empty_cascade(): void
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
