<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * Única forma de crear un super admin o de otorgar/revocar el rol. No hay UI para esto.
 *
 *   php artisan super-admin grant correo@ejemplo.com
 *   php artisan super-admin revoke correo@ejemplo.com
 *   php artisan super-admin list
 */
class SuperAdminCommand extends Command
{
    protected $signature = 'super-admin
        {action : grant | revoke | list}
        {email? : Email del usuario (para grant/revoke)}';

    protected $description = 'Gestiona el rol super admin de plataforma (solo por línea de comando).';

    public function handle(): int
    {
        return match ($this->argument('action')) {
            'grant' => $this->grant(),
            'revoke' => $this->revoke(),
            'list' => $this->list(),
            default => $this->invalidAction(),
        };
    }

    private function grant(): int
    {
        $email = $this->requireEmail();

        if ($email === null) {
            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($user === null) {
            if (! confirm("No existe un usuario con {$email}. ¿Crearlo ahora?", default: true)) {
                $this->warn('Cancelado.');

                return self::FAILURE;
            }

            $user = new User;
            $user->name = text('Nombre', required: true);
            $user->email = $email;
            $user->password = password('Contraseña', required: true);
            $user->email_verified_at = now();
        }

        $user->is_super_admin = true;
        $user->save();

        $this->info("✓ {$user->email} ahora es super admin.");

        return self::SUCCESS;
    }

    private function revoke(): int
    {
        $email = $this->requireEmail();

        if ($email === null) {
            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($user === null) {
            $this->error("No existe un usuario con {$email}.");

            return self::FAILURE;
        }

        $user->is_super_admin = false;
        $user->save();

        $this->info("✓ {$user->email} ya no es super admin.");

        return self::SUCCESS;
    }

    private function list(): int
    {
        $admins = User::where('is_super_admin', true)->orderBy('email')->get(['name', 'email']);

        if ($admins->isEmpty()) {
            $this->warn('No hay super admins. Crea uno con: php artisan super-admin grant correo@ejemplo.com');

            return self::SUCCESS;
        }

        $this->table(['Nombre', 'Email'], $admins->map(fn (User $u): array => [$u->name, $u->email])->all());

        return self::SUCCESS;
    }

    private function requireEmail(): ?string
    {
        $email = $this->argument('email');

        if (blank($email)) {
            $this->error('Falta el email. Uso: php artisan super-admin '.$this->argument('action').' correo@ejemplo.com');

            return null;
        }

        return $email;
    }

    private function invalidAction(): int
    {
        $this->error('Acción no válida. Usa: grant | revoke | list.');

        return self::FAILURE;
    }
}
