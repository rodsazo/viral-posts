<?php

namespace Tests\Feature;

use App\Enums\TeamRole;
use App\Models\Account;
use App\Models\HookTemplate;
use App\Models\User;
use App\Models\ViralReferent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HookTemplateTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->is_super_admin = true;
        $user->save();

        return $user;
    }

    private function member(Account $account): User
    {
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => TeamRole::Editor->value]);

        return $user;
    }

    public function test_real_examples_is_cast_to_array(): void
    {
        $hook = HookTemplate::factory()->create([
            'real_examples' => ['https://a.test', 'https://b.test'],
        ]);

        $this->assertSame(['https://a.test', 'https://b.test'], $hook->fresh()->real_examples);
    }

    public function test_hook_template_mutation_is_super_admin_only_but_viewing_is_open(): void
    {
        $account = Account::factory()->create();
        $member = $this->member($account);

        // Cualquier miembro del admin puede VER el catálogo global de ganchos.
        $this->actingAs($member)->get("/admin/{$account->slug}/hook-templates")->assertOk();

        // Crear/editar queda reservado al super admin.
        $this->actingAs($member)->get("/admin/{$account->slug}/hook-templates/create")->assertForbidden();
        $this->actingAs($this->superAdmin())->get("/admin/{$account->slug}/hook-templates/create")->assertOk();
    }

    public function test_super_admin_can_open_the_edit_form(): void
    {
        $account = Account::factory()->create();
        $hook = HookTemplate::factory()->create(['viral_referent_id' => ViralReferent::factory()]);

        $this->actingAs($this->superAdmin())
            ->get("/admin/{$account->slug}/hook-templates/{$hook->id}/edit")
            ->assertOk();
    }
}
