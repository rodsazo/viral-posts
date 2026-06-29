<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\HerasTemplate;
use App\Models\User;
use App\Models\WinningIdea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HerasTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_winning_idea_belongs_to_a_heras_template(): void
    {
        $account = Account::factory()->create();
        $template = HerasTemplate::factory()->create();

        $idea = WinningIdea::factory()->create([
            'account_id' => $account->id,
            'heras_template_id' => $template->id,
        ]);

        $this->assertTrue($idea->herasTemplate->is($template));
        $this->assertTrue($template->winningIdeas->contains($idea));
    }

    public function test_winning_idea_can_exist_without_a_template(): void
    {
        $account = Account::factory()->create();
        $idea = WinningIdea::factory()->create([
            'account_id' => $account->id,
            'heras_template_id' => null,
        ]);

        $this->assertNull($idea->herasTemplate);
    }

    public function test_heras_templates_are_a_global_catalog_visible_to_any_tenant(): void
    {
        $template = HerasTemplate::factory()->create([
            'name' => 'PLANTILLA GLOBAL DE PRUEBA',
        ]);

        $account = Account::factory()->create();
        $user = User::factory()->create();
        $user->accounts()->attach($account->id);

        // El catálogo es global: un miembro de cualquier marca lo ve íntegro.
        $response = $this->actingAs($user)->get("/admin/{$account->slug}/heras-templates");

        $response->assertOk();
        $response->assertSee('PLANTILLA GLOBAL DE PRUEBA');
    }
}
