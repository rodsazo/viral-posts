<?php

namespace Tests\Feature;

use App\Enums\TeamRole;
use App\Models\Account;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->is_super_admin = true;
        $user->save();

        return $user;
    }

    private function member(Account $account, TeamRole $role = TeamRole::Editor): User
    {
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => $role->value]);

        return $user;
    }

    public function test_command_grants_and_revokes_super_admin(): void
    {
        $user = User::factory()->create();

        $this->artisan('super-admin', ['action' => 'grant', 'email' => $user->email])->assertSuccessful();
        $this->assertTrue($user->fresh()->isSuperAdmin());

        $this->artisan('super-admin', ['action' => 'revoke', 'email' => $user->email])->assertSuccessful();
        $this->assertFalse($user->fresh()->isSuperAdmin());
    }

    public function test_command_fails_for_unknown_email_on_revoke(): void
    {
        $this->artisan('super-admin', ['action' => 'revoke', 'email' => 'nadie@ejemplo.com'])->assertFailed();
    }

    public function test_super_admin_gate_allows_everything(): void
    {
        $this->assertTrue(Gate::forUser($this->superAdmin())->allows('cualquier-habilidad-no-definida'));
        $this->assertFalse(Gate::forUser(User::factory()->create())->allows('cualquier-habilidad-no-definida'));
    }

    public function test_super_admin_sees_all_brands_as_tenants(): void
    {
        Account::factory()->count(2)->create();
        $super = $this->superAdmin(); // no es miembro de ninguna

        $this->assertCount(2, $super->getTenants(Filament::getPanel('admin')));
    }

    public function test_accounts_resource_is_super_admin_only(): void
    {
        $account = Account::factory()->create();

        $this->actingAs($this->member($account))
            ->get("/admin/{$account->slug}/accounts")
            ->assertForbidden();

        $this->actingAs($this->superAdmin())
            ->get("/admin/{$account->slug}/accounts")
            ->assertOk();
    }

    public function test_users_resource_is_super_admin_only(): void
    {
        $account = Account::factory()->create();

        $this->actingAs($this->member($account))
            ->get("/admin/{$account->slug}/users")
            ->assertForbidden();

        $this->actingAs($this->superAdmin())
            ->get("/admin/{$account->slug}/users")
            ->assertOk();
    }

    public function test_suspended_brand_blocks_its_members(): void
    {
        $account = Account::factory()->create(['is_active' => false]);
        $member = $this->member($account);

        $this->assertFalse($member->canAccessTenant($account));
        $this->assertCount(0, $member->getTenants(Filament::getPanel('admin')));

        $this->actingAs($member)
            ->get("/studio/{$account->slug}/piezas")
            ->assertForbidden();
    }

    public function test_super_admin_still_reaches_a_suspended_brand_studio(): void
    {
        $account = Account::factory()->create(['is_active' => false]);

        $this->actingAs($this->superAdmin())
            ->get("/studio/{$account->slug}/piezas")
            ->assertOk();
    }

    public function test_deactivated_user_cannot_access_panel_or_studio(): void
    {
        $account = Account::factory()->create();
        $member = $this->member($account);
        $member->update(['is_active' => false]);

        $this->actingAs($member)->get("/admin/{$account->slug}")->assertForbidden();
        $this->actingAs($member)->get("/studio/{$account->slug}/piezas")->assertForbidden();
    }

    public function test_global_catalog_mutation_is_restricted_but_viewing_is_open(): void
    {
        $account = Account::factory()->create();
        $member = $this->member($account);

        // Cualquier miembro puede VER el catálogo global.
        $this->actingAs($member)->get("/admin/{$account->slug}/heras-templates")->assertOk();

        // Pero crear queda reservado al super admin.
        $this->actingAs($member)->get("/admin/{$account->slug}/heras-templates/create")->assertForbidden();
        $this->actingAs($this->superAdmin())->get("/admin/{$account->slug}/heras-templates/create")->assertOk();
    }
}
