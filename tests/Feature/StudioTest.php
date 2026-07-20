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
use Illuminate\Support\Facades\Storage;
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
        // Relativa a la raíz (sin esquema/host) para evitar el desajuste de origen con APP_URL.
        $this->assertSame('/storage/brand-logos/foo.png', $account->logoUrl());
        $this->assertStringStartsWith('/', (string) $account->logoUrl());
        $this->assertStringNotContainsString('http', (string) $account->logoUrl());
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

    public function test_composer_shows_idea_reference_reels_numbered(): void
    {
        $account = Account::factory()->create();
        $idea = WinningIdea::factory()->create([
            'account_id' => $account->id,
            'example_urls' => ['https://www.tiktok.com/@a/video/1', 'https://www.instagram.com/reel/2'],
        ]);
        $piece = ContentPiece::factory()->create(['account_id' => $account->id, 'winning_idea_id' => $idea->id]);
        $this->actingAs($this->member($account));

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->call('selectPiece', $piece->id)
            ->assertSee('Reels de referencia')
            ->assertSee('Referencia 1')
            ->assertSee('Referencia 2')
            ->assertSee('https://www.tiktok.com/@a/video/1');
    }

    public function test_choosing_a_follower_shows_context_and_autosaves(): void
    {
        $account = Account::factory()->create();
        $user = $this->member($account);

        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);
        // El contexto (preguntas/mitos) sale del SEGUIDOR de la pieza, no de la idea.
        Question::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id, 'body' => 'PREGUNTA DEL ESTUDIO']);
        Belief::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id, 'type' => BeliefType::Truth, 'statement' => 'CREENCIA DEL ESTUDIO']);

        $idea = WinningIdea::factory()->create(['account_id' => $account->id]);
        $piece = ContentPiece::factory()->create(['account_id' => $account->id, 'winning_idea_id' => null, 'ideal_follower_id' => null]);

        $this->actingAs($user);

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->call('selectPiece', $piece->id)
            ->set('winning_idea_id', $idea->id)
            ->set('idealFollowerId', $follower->id)
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
        // Toda pieza nueva arranca en "Borrador".
        $this->assertSame(ContentStatus::Borrador, ContentPiece::where('account_id', $account->id)->first()->status);
    }

    public function test_pieces_list_can_be_filtered_by_status(): void
    {
        $account = Account::factory()->create();
        ContentPiece::factory()->create(['account_id' => $account->id, 'title' => 'PIEZA BORRADOR', 'status' => ContentStatus::Borrador]);
        ContentPiece::factory()->create(['account_id' => $account->id, 'title' => 'PIEZA PLANIFICADA', 'status' => ContentStatus::Planificacion]);
        $this->actingAs($this->member($account));

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->assertSee('PIEZA BORRADOR')
            ->assertSee('PIEZA PLANIFICADA')
            ->set('statusFilter', ContentStatus::Borrador->value)
            ->assertSee('PIEZA BORRADOR')
            ->assertDontSee('PIEZA PLANIFICADA');
    }

    public function test_script_fields_autosave_even_with_empty_follower_select(): void
    {
        $account = Account::factory()->create();
        $piece = ContentPiece::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => null]);
        $this->actingAs($this->member($account));

        // El selector "Sin seguidor" manda "" (string) desde Flux: no debe romper el autoguardado.
        Livewire::test(PieceComposer::class, ['account' => $account])
            ->call('selectPiece', $piece->id)
            ->set('idealFollowerId', '')
            ->set('hookText', 'GANCHO AUTOGUARDADO')
            ->set('story', 'HISTORIA AUTOGUARDADA');

        $piece->refresh();
        $this->assertSame('GANCHO AUTOGUARDADO', $piece->hook);
        $this->assertSame('HISTORIA AUTOGUARDADA', $piece->story);
    }

    public function test_production_fields_autosave_in_the_composer(): void
    {
        $account = Account::factory()->create();
        $piece = ContentPiece::factory()->create(['account_id' => $account->id]);
        $this->actingAs($this->member($account));

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->call('selectPiece', $piece->id)
            ->set('location', 'Estudio casero')
            ->set('equipment', 'Cámara + micrófono')
            ->set('people', 'Yo y dos amigos')
            ->set('clientNotes', 'Grabamos el sábado');

        $piece->refresh();
        $this->assertSame('Estudio casero', $piece->location);
        $this->assertSame('Cámara + micrófono', $piece->equipment);
        $this->assertSame('Yo y dos amigos', $piece->people);
        $this->assertSame('Grabamos el sábado', $piece->client_notes);
    }

    public function test_deleting_a_piece_is_reserved_to_brand_admins(): void
    {
        $account = Account::factory()->create();
        $piece = ContentPiece::factory()->create(['account_id' => $account->id]);

        // Editor: no puede borrar.
        $this->actingAs($this->member($account));
        Livewire::test(PieceComposer::class, ['account' => $account])
            ->call('deletePiece', $piece->id);
        $this->assertDatabaseHas('content_pieces', ['id' => $piece->id]);

        // Admin: sí.
        $admin = User::factory()->create();
        $account->users()->attach($admin->id, ['role' => TeamRole::Admin->value]);
        $this->actingAs($admin);
        Livewire::test(PieceComposer::class, ['account' => $account])
            ->call('deletePiece', $piece->id);
        $this->assertDatabaseMissing('content_pieces', ['id' => $piece->id]);
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
            ->call('selectPiece', $piece->id)
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
            ->call('selectPiece', $piece->id)
            ->set('rumFactors.amplitud', '1.5848')
            ->set('rumFactors.intensidad', '1.5848')
            ->set('rumFactors.universalidad', '1.5848')
            ->set('rumFactors.inmediatez', '1.5848')
            ->set('rumFactors.independencia', '1.5848');

        $this->assertSame(10.0, $piece->refresh()->rum);
    }

    public function test_fetch_preview_stores_a_persistent_copy_of_the_image(): void
    {
        // La URL remota (og:image) caduca; guardamos una copia propia (reference-images/).
        Storage::fake('public');
        config(['filesystems.brand_disk' => 'public']);

        $jpeg = (function () {
            $img = imagecreatetruecolor(800, 500);
            ob_start();
            imagejpeg($img);
            $bytes = (string) ob_get_clean();
            imagedestroy($img);

            return $bytes;
        })();

        Http::fake([
            'example.com/post/*' => Http::response('<meta property="og:image" content="https://cdn.example.com/og.jpg">'),
            'cdn.example.com/*' => Http::response($jpeg, 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $account = Account::factory()->create();
        $this->actingAs($this->member($account));
        $piece = ContentPiece::factory()->create(['account_id' => $account->id]);

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->call('selectPiece', $piece->id)
            ->set('postUrl', 'https://example.com/post/1')
            ->call('fetchPreview');

        $stored = $piece->refresh()->preview_image_url;
        $this->assertNotNull($stored);
        $this->assertStringContainsString('reference-images/', $stored);
        $this->assertCount(1, Storage::disk('public')->files('reference-images'));
    }
}
