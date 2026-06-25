<?php

namespace Tests\Feature;

use App\Enums\TeamRole;
use App\Livewire\Studio\HookManager;
use App\Livewire\Studio\PieceGenerator;
use App\Models\Account;
use App\Models\HookTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HookStudioTest extends TestCase
{
    use RefreshDatabase;

    private function member(Account $account, TeamRole $role = TeamRole::Editor): User
    {
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => $role->value]);

        return $user;
    }

    public function test_studio_hooks_requires_membership(): void
    {
        $account = Account::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get("/studio/{$account->slug}/ganchos")
            ->assertForbidden();
    }

    public function test_member_can_create_and_autosave_a_brand_hook(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));

        Livewire::test(HookManager::class, ['account' => $account])
            ->call('newHook')
            ->set('name', 'Confesión incómoda')
            ->set('objective', 'Abrir un bucle de curiosidad');

        $this->assertDatabaseHas('hook_templates', [
            'account_id' => $account->id,
            'name' => 'Confesión incómoda',
            'objective' => 'Abrir un bucle de curiosidad',
        ]);
    }

    public function test_manager_lists_only_this_brands_hooks(): void
    {
        $account = Account::factory()->create();
        $other = Account::factory()->create();
        $this->actingAs($this->member($account));

        HookTemplate::factory()->create(['account_id' => $account->id, 'name' => 'GANCHO PROPIO']);
        HookTemplate::factory()->create(['account_id' => null, 'name' => 'GANCHO GLOBAL']);
        HookTemplate::factory()->create(['account_id' => $other->id, 'name' => 'GANCHO AJENO']);

        Livewire::test(HookManager::class, ['account' => $account])
            ->assertSee('GANCHO PROPIO')
            ->assertDontSee('GANCHO GLOBAL')   // los globales se gestionan en el admin
            ->assertDontSee('GANCHO AJENO');
    }

    public function test_hook_deletion_is_reserved_to_brand_admins(): void
    {
        $account = Account::factory()->create();
        $hook = HookTemplate::factory()->create(['account_id' => $account->id]);

        $this->actingAs($this->member($account, TeamRole::Editor));
        Livewire::test(HookManager::class, ['account' => $account])->call('deleteHook', $hook->id);
        $this->assertDatabaseHas('hook_templates', ['id' => $hook->id]);

        $this->actingAs($this->member($account, TeamRole::Admin));
        Livewire::test(HookManager::class, ['account' => $account])->call('deleteHook', $hook->id);
        $this->assertDatabaseMissing('hook_templates', ['id' => $hook->id]);
    }

    public function test_generator_picker_shows_brand_and_global_hooks_not_other_brands(): void
    {
        $account = Account::factory()->create();
        $other = Account::factory()->create();
        $this->actingAs($this->member($account));

        HookTemplate::factory()->create(['account_id' => $account->id, 'name' => 'GANCHO DE MARCA']);
        HookTemplate::factory()->create(['account_id' => null, 'name' => 'GANCHO DE REFERENCIA']);
        HookTemplate::factory()->create(['account_id' => $other->id, 'name' => 'GANCHO DE OTRA MARCA']);

        Livewire::test(PieceGenerator::class, ['account' => $account])
            ->assertSee('GANCHO DE MARCA')
            ->assertSee('GANCHO DE REFERENCIA')
            ->assertDontSee('GANCHO DE OTRA MARCA');
    }
}
