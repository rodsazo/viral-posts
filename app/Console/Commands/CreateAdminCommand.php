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
 * (contraseña elegida, nunca fija). Opcionalmente crea la primera marca y/o concede
 * super admin. Pensado para el primer arranque en producción (donde NO corre el DemoSeeder).
 *
 * Funciona de DOS formas:
 *  - Interactivo (terminal con TTY): pregunta lo que falte.
 *  - No interactivo (p. ej. el command runner de Laravel Cloud, sin TTY): toma todo de
 *    opciones/entorno. La contraseña, por seguridad, mejor por la variable ADMIN_PASSWORD
 *    (así no queda en el registro del comando) que por --password.
 *
 *   php artisan app:create-admin                                  # interactivo
 *   ADMIN_PASSWORD=... php artisan app:create-admin --email=tu@correo.com --name="Tu Nombre" \
 *       --brand="Mi Marca" --super-admin --no-interaction         # no interactivo
 */
class CreateAdminCommand extends Command
{
    protected $signature = 'app:create-admin
        {--email= : Email del usuario}
        {--name= : Nombre del usuario}
        {--password= : Contraseña (mejor usar la variable de entorno ADMIN_PASSWORD)}
        {--brand= : Crea una marca con este nombre y asigna al usuario como Admin}
        {--super-admin : Concede el rol de super admin de plataforma}';

    protected $description = 'Crea el usuario administrador real (por consola, contraseña segura).';

    public function handle(): int
    {
        $interactive = $this->input->isInteractive();

        $name = $this->option('name') ?: ($interactive ? text('Nombre', required: true) : null);
        $email = $this->option('email') ?: ($interactive
            ? text('Email', required: true, validate: fn (string $v) => filter_var($v, FILTER_VALIDATE_EMAIL) ? null : 'Email no válido.')
            : null);

        if (blank($name) || blank($email)) {
            $this->error('Faltan datos. En modo no interactivo: app:create-admin --email= --name= (contraseña en ADMIN_PASSWORD o --password).');

            return self::FAILURE;
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("Email no válido: {$email}");

            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error("Ya existe un usuario con {$email}.");

            return self::FAILURE;
        }

        $plain = $this->resolvePassword($interactive);

        if ($plain === null) {
            return self::FAILURE;
        }

        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($plain);
        $user->email_verified_at = now();
        $user->save();

        $this->info("✓ Usuario {$user->email} creado.");

        // Primera marca: por --brand, o preguntando si estamos en una terminal interactiva.
        $brandName = $this->option('brand');
        if (blank($brandName) && $interactive && confirm('¿Crear una primera marca y asignar este usuario como Admin?', default: true)) {
            $brandName = text('Nombre de la marca', required: true);
        }
        if (filled($brandName)) {
            $account = Account::create(['name' => $brandName]);
            $account->users()->attach($user->id, ['role' => TeamRole::Admin->value]);
            $this->info("✓ Marca «{$account->name}» creada y asignada como Admin.");
        }

        // Super admin: por --super-admin, o preguntando si es interactivo.
        $super = (bool) $this->option('super-admin');
        if (! $super && $interactive) {
            $super = confirm('¿Concederle el rol de super admin de plataforma?', default: true);
        }
        if ($super) {
            $user->is_super_admin = true;
            $user->save();
            $this->info('✓ Ahora es super admin.');
        }

        $this->newLine();
        $this->info('Listo. Ya puedes iniciar sesión en /admin.');

        return self::SUCCESS;
    }

    /**
     * Resuelve la contraseña: --password, variable ADMIN_PASSWORD o, si es interactivo,
     * la pregunta (con confirmación). Devuelve null si no es válida (ya avisa del error).
     */
    private function resolvePassword(bool $interactive): ?string
    {
        $plain = $this->option('password') ?: (env('ADMIN_PASSWORD') ?: null);

        if (blank($plain) && $interactive) {
            $plain = password('Contraseña', required: true);
            if ($plain !== password('Repite la contraseña', required: true)) {
                $this->error('Las contraseñas no coinciden.');

                return null;
            }
        }

        if (blank($plain)) {
            $this->error('Falta la contraseña: define la variable de entorno ADMIN_PASSWORD o pásala con --password.');

            return null;
        }

        $validator = validator(['password' => $plain], ['password' => [Password::min(8)->letters()->numbers()]]);
        if ($validator->fails()) {
            $this->error('La contraseña debe tener mínimo 8 caracteres, con letras y números.');

            return null;
        }

        return $plain;
    }
}
