<?php

namespace Tests\Feature;

use App\Enums\CtaCategory;
use App\Enums\TeamRole;
use App\Livewire\Studio\ContentCtaManager;
use App\Models\Account;
use App\Models\ContentCta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContentCtaTest extends TestCase
{
    use RefreshDatabase;

    private function member(Account $account, TeamRole $role = TeamRole::Editor): User
    {
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => $role->value]);

        return $user;
    }

    public function test_category_is_cast_to_enum(): void
    {
        $cta = ContentCta::factory()->create(['category' => CtaCategory::Keyword]);

        $this->assertSame(CtaCategory::Keyword, $cta->fresh()->category);
    }

    public function test_studio_manager_requires_membership(): void
    {
        $account = Account::factory()->create();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->get("/studio/{$account->slug}/ctas")
            ->assertForbidden();
    }

    public function test_member_can_add_edit_and_delete_ctas_in_studio(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));

        $component = Livewire::test(ContentCtaManager::class, ['account' => $account])
            ->set('newCta', ['category' => 'keyword', 'text' => 'Comenta RECURSO'])
            ->call('addCta');

        $this->assertDatabaseHas('content_ctas', [
            'account_id' => $account->id,
            'category' => 'keyword',
            'text' => 'Comenta RECURSO',
        ]);

        $cta = ContentCta::where('account_id', $account->id)->firstOrFail();

        // Editar persiste al vuelo (autoguardado).
        $component->set("ctas.{$cta->id}.text", 'Comenta RECURSO actualizado');
        $this->assertSame('Comenta RECURSO actualizado', $cta->fresh()->text);

        // Borrar.
        $component->call('deleteCta', $cta->id);
        $this->assertDatabaseMissing('content_ctas', ['id' => $cta->id]);
    }

    public function test_studio_manager_only_shows_own_brand_ctas(): void
    {
        $account = Account::factory()->create();
        $other = Account::factory()->create();
        $this->actingAs($this->member($account));

        $own = ContentCta::factory()->create(['account_id' => $account->id, 'text' => 'CTA PROPIA']);
        $alien = ContentCta::factory()->create(['account_id' => $other->id, 'text' => 'CTA AJENA']);

        $ctas = Livewire::test(ContentCtaManager::class, ['account' => $account])->get('ctas');

        $this->assertArrayHasKey($own->id, $ctas);
        $this->assertArrayNotHasKey($alien->id, $ctas);
    }

    public function test_admin_resource_is_visible_to_members(): void
    {
        $account = Account::factory()->create();

        $this->actingAs($this->member($account))
            ->get("/admin/{$account->slug}/content-ctas")
            ->assertOk();
    }
}
