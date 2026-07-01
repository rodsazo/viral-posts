<?php

namespace Tests\Feature;

use App\Enums\ClientReviewStatus;
use App\Enums\ContentStatus;
use App\Models\Account;
use App\Models\ContentPiece;
use App\Models\Period;
use App\Models\User;
use App\Notifications\PieceReviewedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PublicPieceReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // La revisión del cliente está tras un flag; la activamos para estos tests.
        config(['studio.client_review' => true]);
    }

    private function visiblePiece(): ContentPiece
    {
        $account = Account::factory()->create();
        $period = Period::factory()->published()->create(['account_id' => $account->id]);

        return ContentPiece::factory()->create([
            'account_id' => $account->id,
            'period_id' => $period->id,
            'status' => ContentStatus::ListaParaGrabacion,
        ]);
    }

    public function test_client_can_approve_a_piece_without_login(): void
    {
        $piece = $this->visiblePiece();

        $this->post("/p/{$piece->public_token}/review", ['decision' => 'approved'])
            ->assertRedirect("/p/{$piece->public_token}")
            ->assertSessionHas('review.flash');

        $piece->refresh();
        $this->assertSame(ClientReviewStatus::Approved, $piece->client_review_status);
        $this->assertNotNull($piece->client_reviewed_at);
        $this->assertNull($piece->client_review_notes);
    }

    public function test_client_can_request_changes_with_a_note(): void
    {
        $piece = $this->visiblePiece();

        $this->post("/p/{$piece->public_token}/review", [
            'decision' => 'changes_requested',
            'notes' => 'EL GANCHO NO ME CONVENCE',
        ])->assertRedirect("/p/{$piece->public_token}");

        $piece->refresh();
        $this->assertSame(ClientReviewStatus::ChangesRequested, $piece->client_review_status);
        $this->assertSame('EL GANCHO NO ME CONVENCE', $piece->client_review_notes);
    }

    public function test_requesting_changes_requires_a_note(): void
    {
        $piece = $this->visiblePiece();

        $this->post("/p/{$piece->public_token}/review", ['decision' => 'changes_requested'])
            ->assertSessionHasErrors('notes');

        $this->assertSame(ClientReviewStatus::Pending, $piece->refresh()->client_review_status);
    }

    public function test_a_review_notifies_the_brand_team(): void
    {
        Notification::fake();

        $piece = $this->visiblePiece();
        $member = User::factory()->create();
        $piece->account->users()->attach($member->id, ['role' => 'editor']);

        $this->post("/p/{$piece->public_token}/review", [
            'decision' => 'changes_requested',
            'notes' => 'AJUSTA EL FINAL',
        ]);

        Notification::assertSentTo($member, PieceReviewedNotification::class);
    }

    public function test_review_is_disabled_by_the_feature_flag(): void
    {
        config(['studio.client_review' => false]);
        $piece = $this->visiblePiece();

        // La página pública no muestra los botones y el POST devuelve 404.
        $this->get("/p/{$piece->public_token}")->assertOk()->assertDontSee('¿Qué te parece?');
        $this->post("/p/{$piece->public_token}/review", ['decision' => 'approved'])->assertNotFound();
        $this->assertSame(ClientReviewStatus::Pending, $piece->refresh()->client_review_status);
    }

    public function test_cannot_review_a_non_public_piece(): void
    {
        $account = Account::factory()->create();
        // Sin periodo publicado → no es visible públicamente.
        $piece = ContentPiece::factory()->create([
            'account_id' => $account->id,
            'period_id' => null,
            'status' => ContentStatus::Borrador,
        ]);

        $this->post("/p/{$piece->public_token}/review", ['decision' => 'approved'])->assertNotFound();
        $this->assertSame(ClientReviewStatus::Pending, $piece->refresh()->client_review_status);
    }
}
