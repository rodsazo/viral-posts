<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\HerasTemplate;
use App\Models\IdealFollower;
use App\Models\Question;
use App\Models\User;
use App\Models\WinningIdea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelTenancyTest extends TestCase
{
    use RefreshDatabase;

    private function questionFor(Account $account, string $body): Question
    {
        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);

        return Question::factory()->create([
            'account_id' => $account->id,
            'ideal_follower_id' => $follower->id,
            'body' => $body,
        ]);
    }

    public function test_listing_shows_only_active_tenant_records(): void
    {
        $accountA = Account::factory()->create();
        $accountB = Account::factory()->create();

        $qa = $this->questionFor($accountA, 'PREGUNTA DE LA MARCA A');
        $qb = $this->questionFor($accountB, 'PREGUNTA DE LA MARCA B');

        $user = User::factory()->create();
        $user->accounts()->attach([$accountA->id, $accountB->id]);

        $response = $this->actingAs($user)->get("/admin/{$accountA->slug}/questions");

        $response->assertOk();
        $response->assertSee($qa->body);
        $response->assertDontSee($qb->body);
    }

    public function test_user_cannot_access_a_tenant_they_do_not_belong_to(): void
    {
        $accountA = Account::factory()->create();
        $this->questionFor($accountA, 'PREGUNTA PRIVADA DE A');

        $outsider = User::factory()->create(); // no pertenece a ninguna marca

        $response = $this->actingAs($outsider)->get("/admin/{$accountA->slug}/questions");

        $this->assertContains(
            $response->status(),
            [302, 403, 404],
            'Un usuario ajeno no debería poder ver datos de la marca.',
        );
        $response->assertDontSee('PREGUNTA PRIVADA DE A');
    }

    public function test_resource_pages_render_for_a_member(): void
    {
        $account = Account::factory()->create();
        $user = User::factory()->create();
        $user->accounts()->attach($account->id);

        $idea = WinningIdea::factory()->create(['account_id' => $account->id]);
        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);
        $template = HerasTemplate::factory()->create(['number' => 1]);

        $urls = [
            "/admin/{$account->slug}",                                   // dashboard
            "/admin/{$account->slug}/questions",
            "/admin/{$account->slug}/questions/create",
            "/admin/{$account->slug}/beliefs/create",
            "/admin/{$account->slug}/ideal-followers/{$follower->id}/edit", // relation manager: preguntas
            "/admin/{$account->slug}/winning-ideas",
            "/admin/{$account->slug}/winning-ideas/create",
            "/admin/{$account->slug}/winning-ideas/{$idea->id}",         // infolist multi-salto
            "/admin/{$account->slug}/winning-ideas/{$idea->id}/edit",    // relation manager: piezas
            "/admin/{$account->slug}/content-pieces/create",
            "/admin/{$account->slug}/content-kanban",                    // tablero kanban
            "/admin/{$account->slug}/niches",                            // catálogo global
            "/admin/{$account->slug}/viral-referents",                   // catálogo global
            "/admin/{$account->slug}/heras-templates/{$template->id}",   // vista con imagen
        ];

        foreach ($urls as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }
}
