<?php

namespace App\Console\Commands;

use App\Enums\TeamRole;
use App\Models\Account;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * Crea el usuario administrador real de producción, de forma segura y por consola
 * (contraseña elegida en el momento, nunca fija). Opcionalmente crea la primera
 * marca y/o concede super admin. Pensado para el primer arranque en producción,
 * donde NO se ejecuta el DemoSeeder.
 *
 *   php artisan app:create-admin
 *   php artisan app:create-admin --email=correo@ejemplo.com --name="Nombre"
 */
class CreateAdminCommand extends Command
{
    protected $signature = 'app:create-admin
        {--email= : Email del usuario}
        {--name= : Nombre del usuario}';

    protected $description = 'Crea el usuario administrador real (por consola, contraseña segura).';

    public function handle(): int
    {
        $name = $this->option('name') ?: text('Nombre', required: true);
        $email = $this->option('email') ?: text('Email', required: true, validate: fn (string $v) => filter_var($v, FILTER_VALIDATE_EMAIL) ? null : 'Email no válido.');

        if (User::where('email', $email)->exists()) {
            $this->error("Ya existe un usuario con {$email}.");

            return self::FAILURE;
        }

        $plain = password('Contraseña', required: true, validate: function (string $v) {
            $validator = validator(['password' => $v], ['password' => [Password::min(8)->letters()->numbers()]]);

            return $validator->fails() ? 'Mínimo 8 caracteres, con letras y números.' : null;
        });
        // Confirmación a mano (los prompts no encadenan el campo password_confirmation).
        if ($plain !== password('Repite la contraseña', required: true)) {
            $this->error('Las contraseñas no coinciden.');

            return self::FAILURE;
        }

        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($plain);
        $user->email_verified_at = now();
        $user->save();

        $this->info("✓ Usuario {$user->email} creado.");

        // Primera marca (opcional): la app es multi-marca; el admin necesita al menos una.
        if (confirm('¿Crear una primera marca y asignar este usuario como Admin?', default: true)) {
            $brandName = text('Nombre de la marca', required: true);
            $account = Account::create(['name' => $brandName]);
            $account->users()->attach($user->id, ['role' => TeamRole::Admin->value]);
            $this->info("✓ Marca «{$account->name}» creada y asignada como Admin.");
        }

        // Super admin (opcional): gobierna catálogos globales, marcas y usuarios.
        if (confirm('¿Concederle el rol de super admin de plataforma?', default: true)) {
            $user->is_super_admin = true;
            $user->save();
            $this->info('✓ Ahora es super admin.');
        }

        $this->newLine();
        $this->info('Listo. Ya puedes iniciar sesión en /admin.');

        return self::SUCCESS;
    }
}
