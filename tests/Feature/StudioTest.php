<?php

namespace Tests\Feature;

use App\Enums\BeliefType;
use App\Enums\ContentStatus;
use App\Enums\TeamRole;
use App\Livewire\Studio\PieceComposer;
use App\Models\Account;
use App\Models\Belief;
use App\Models\ContentPiece;
use App\Models\IdealFollower;
use App\Models\Question;
use App\Models\User;
use App\Models\WinningIdea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class StudioTest extends TestCase
{
    use RefreshDatabase;

    private function member(Account $account): User
    {
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => TeamRole::Editor->value]);

        return $user;
    }

    public function test_non_member_cannot_open_the_studio(): void
    {
        $account = Account::factory()->create();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->get("/studio/{$account->slug}/piezas")
            ->assertForbidden();
    }

    public function test_member_can_open_the_studio_and_see_pieces(): void
    {
        $account = Account::factory()->create();
        $user = $this->member($account);
        ContentPiece::factory()->create(['account_id' => $account->id, 'title' => 'PIEZA EN EL ESTUDIO']);

        $this->actingAs($user)
            ->get("/studio/{$account->slug}/piezas")
            ->assertOk()
            ->assertSee('PIEZA EN EL ESTUDIO');
    }

    public function test_account_logo_url_reflects_the_stored_path(): void
    {
        $account = Account::factory()->create(['logo_path' => null]);
        $this->assertNull($account->logoUrl());

        $account->update(['logo_path' => 'brand-logos/foo.png']);
        $this->assertStringContainsString('brand-logos/foo.png', (string) $account->logoUrl());
        $this->assertSame($account->logoUrl(), $account->getFilamentAvatarUrl());
    }

    public function test_brand_switcher_lists_the_users_brands(): void
    {
        $brandA = Account::factory()->create(['name' => 'MARCA ALFA']);
        $brandB = Account::factory()->create(['name' => 'MARCA BETA']);
        $user = User::factory()->create();
        $user->accounts()->attach([$brandA->id => ['role' => TeamRole::Editor->value], $brandB->id => ['role' => TeamRole::Editor->value]]);

        $this->actingAs($user)
            ->get("/studio/{$brandA->slug}")
            ->assertOk()
            ->assertSee('MARCA ALFA')
            ->assertSee('MARCA BETA');
    }

    public function test_member_can_open_the_studio_home(): void
    {
        $account = Account::factory()->create();
        $user = $this->member($account);
        ContentPiece::factory()->create(['account_id' => $account->id, 'title' => 'PIEZA RECIENTE']);

        $this->actingAs($user)
            ->get("/studio/{$account->slug}")
            ->assertOk()
            ->assertSee('Pipeline de producción')
            ->assertSee('PIEZA RECIENTE');
    }

    public function test_choosing_an_idea_shows_context_and_autosaves(): void
    {
        $account = Account::factory()->create();
        $user = $this->member($account);

        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);
        $question = Question::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id, 'body' => 'PREGUNTA DEL ESTUDIO']);
        $belief = Belief::factory()->create(['account_id' => $account->id, 'type' => BeliefType::Truth, 'statement' => 'CREENCIA DEL ESTUDIO']);
        $question->beliefs()->attach($belief->id);

        $idea = WinningIdea::factory()->create(['account_id' => $account->id]);
        $idea->questions()->attach($question->id);

        $piece = ContentPiece::factory()->create(['account_id' => $account->id, 'winning_idea_id' => null]);

        $this->actingAs($user);

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->set('winning_idea_id', $idea->id)
            ->assertSee('PREGUNTA DEL ESTUDIO')
            ->assertSee('CREENCIA DEL ESTUDIO')
            ->set('hookText', 'Gancho autoguardado');

        $piece->refresh();
        $this->assertSame($idea->id, $piece->winning_idea_id);
        $this->assertSame('Gancho autoguardado', $piece->hook);
    }

    public function test_composer_deeplink_preselects_a_piece(): void
    {
        $account = Account::factory()->create();
        $user = $this->member($account);
        ContentPiece::factory()->create(['account_id' => $account->id, 'title' => 'OTRA PIEZA']);
        $target = ContentPiece::factory()->create(['account_id' => $account->id, 'title' => 'PIEZA DEEPLINK']);

        $this->actingAs($user)
            ->get("/studio/{$account->slug}/piezas?piece={$target->id}")
            ->assertOk()
            ->assertSee('PIEZA DEEPLINK');
    }

    public function test_new_piece_creates_a_draft_in_the_account(): void
    {
        $account = Account::factory()->create();
        $user = $this->member($account);

        $this->actingAs($user);

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->call('newPiece');

        $this->assertSame(1, ContentPiece::where('account_id', $account->id)->count());
    }

    public function test_mark_published_sets_status_and_date(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));
        $piece = ContentPiece::factory()->create([
            'account_id' => $account->id,
            'status' => ContentStatus::GuionListo,
            'published_at' => null,
        ]);

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->call('markPublished');

        $piece->refresh();
        $this->assertSame(ContentStatus::Publicada, $piece->status);
        $this->assertNotNull($piece->published_at);
    }

    public function test_rum_factors_are_saved_and_rum_is_computed(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));
        $piece = ContentPiece::factory()->create(['account_id' => $account->id, 'rum_factors' => null]);

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->set('rumFactors.amplitud', '1.5848')
            ->set('rumFactors.intensidad', '1.5848')
            ->set('rumFactors.universalidad', '1.5848')
            ->set('rumFactors.inmediatez', '1.5848')
            ->set('rumFactors.independencia', '1.5848');

        $this->assertSame(10.0, $piece->refresh()->rum);
    }

    public function test_fetch_preview_stores_the_image_url(): void
    {
        Http::fake([
            'example.com/*' => Http::response('<meta property="og:image" content="https://example.com/og.jpg">'),
        ]);

        $account = Account::factory()->create();
        $this->actingAs($this->member($account));
        $piece = ContentPiece::factory()->create(['account_id' => $account->id]);

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->set('postUrl', 'https://example.com/post/1')
            ->call('fetchPreview')
            ->assertSet('previewImageUrl', 'https://example.com/og.jpg');

        $this->assertSame('https://example.com/og.jpg', $piece->refresh()->preview_image_url);
    }
}
