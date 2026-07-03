<?php

namespace Tests\Feature;

use App\Enums\TeamRole;
use App\Livewire\Studio\BrandDesign;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Diseño de Marca en el Estudio: edición de la identidad de la marca activa.
 */
class BrandDesignTest extends TestCase
{
    use RefreshDatabase;

    private function member(Account $account, TeamRole $role = TeamRole::Editor): User
    {
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => $role->value]);

        return $user;
    }

    public function test_screen_renders_for_a_member(): void
    {
        $account = Account::factory()->create(['name' => 'MI MARCA']);

        $this->actingAs($this->member($account))
            ->get("/studio/{$account->slug}/marca/diseno")
            ->assertOk()
            ->assertSee('Diseño de Marca')
            ->assertSee('MI MARCA');
    }

    public function test_non_member_cannot_access(): void
    {
        $account = Account::factory()->create();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->get("/studio/{$account->slug}/marca/diseno")
            ->assertForbidden();
    }

    public function test_saving_persists_the_brand_fields(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));

        Livewire::test(BrandDesign::class, ['account' => $account])
            ->set('name', 'Nuevo Nombre')
            ->set('brandPromise', 'Ayudamos a X a lograr Y')
            ->set('mainOffers', 'Consultoría 1:1')
            ->set('idealCustomerProfile', 'Dueños de negocio')
            ->call('save');

        $account->refresh();
        $this->assertSame('Nuevo Nombre', $account->name);
        $this->assertSame('Ayudamos a X a lograr Y', $account->brand_promise);
        $this->assertSame('Consultoría 1:1', $account->main_offers);
        $this->assertSame('Dueños de negocio', $account->ideal_customer_profile);
    }

    public function test_uploading_a_logo_stores_it_and_sets_the_path(): void
    {
        $disk = config('filesystems.brand_disk', 'public');
        Storage::fake($disk);

        $account = Account::factory()->create(['logo_path' => null]);
        $this->actingAs($this->member($account));

        Livewire::test(BrandDesign::class, ['account' => $account])
            ->set('logo', UploadedFile::fake()->image('logo.png', 200, 200));

        $account->refresh();
        $this->assertNotNull($account->logo_path);
        Storage::disk($disk)->assertExists($account->logo_path);
    }

    public function test_removing_the_logo_clears_the_path(): void
    {
        $disk = config('filesystems.brand_disk', 'public');
        Storage::fake($disk);
        Storage::disk($disk)->put('brand-logos/old.png', 'x');

        $account = Account::factory()->create(['logo_path' => 'brand-logos/old.png']);
        $this->actingAs($this->member($account));

        Livewire::test(BrandDesign::class, ['account' => $account])
            ->call('removeLogo');

        $this->assertNull($account->refresh()->logo_path);
        Storage::disk($disk)->assertMissing('brand-logos/old.png');
    }
}
