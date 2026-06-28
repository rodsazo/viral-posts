<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_verified_admin_with_a_chosen_password(): void
    {
        $this->artisan('app:create-admin', ['--email' => 'real@admin.test', '--name' => 'Admin Real'])
            ->expectsQuestion('Contraseña', 'Secret123')
            ->expectsQuestion('Repite la contraseña', 'Secret123')
            ->expectsConfirmation('¿Crear una primera marca y asignar este usuario como Admin?', 'yes')
            ->expectsQuestion('Nombre de la marca', 'Mi Marca')
            ->expectsConfirmation('¿Concederle el rol de super admin de plataforma?', 'yes')
            ->assertSuccessful();

        $user = User::where('email', 'real@admin.test')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->is_super_admin);
        $this->assertTrue(Hash::check('Secret123', $user->password));

        $account = Account::where('name', 'Mi Marca')->first();
        $this->assertNotNull($account);
        $this->assertTrue($user->isAdminOf($account));
    }

    public function test_it_runs_non_interactively_with_options(): void
    {
        // Simula el command runner de Laravel Cloud (sin TTY): todo por opciones.
        $this->artisan('app:create-admin', [
            '--email' => 'cli@admin.test',
            '--name' => 'CLI Admin',
            '--password' => 'Secret123',
            '--brand' => 'Marca CLI',
            '--super-admin' => true,
            '--no-interaction' => true,
        ])->assertSuccessful();

        $user = User::where('email', 'cli@admin.test')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->is_super_admin);
        $this->assertTrue(Account::where('name', 'Marca CLI')->exists());
    }

    public function test_it_fails_non_interactively_without_a_password(): void
    {
        $this->artisan('app:create-admin', [
            '--email' => 'nopass@admin.test',
            '--name' => 'No Pass',
            '--no-interaction' => true,
        ])->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'nopass@admin.test']);
    }

    public function test_it_refuses_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@admin.test']);

        $this->artisan('app:create-admin', ['--email' => 'taken@admin.test', '--name' => 'X'])
            ->assertFailed();
    }

    public function test_it_rejects_mismatched_password_confirmation(): void
    {
        $this->artisan('app:create-admin', ['--email' => 'mismatch@admin.test', '--name' => 'X'])
            ->expectsQuestion('Contraseña', 'Secret123')
            ->expectsQuestion('Repite la contraseña', 'Otra9999')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'mismatch@admin.test']);
    }
}
