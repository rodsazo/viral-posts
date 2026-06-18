<?php

namespace App\Http\Middleware;

use App\Models\Account;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMembership
{
    public function handle(Request $request, Closure $next): Response
    {
        $account = $request->route('account');
        $user = $request->user();

        abort_unless($account instanceof Account && $user !== null, 403);

        // Usuario desactivado: sin acceso.
        abort_unless($user->is_active, 403);

        // El super admin entra a cualquier marca; el resto debe ser miembro de una marca activa.
        if (! $user->isSuperAdmin()) {
            abort_unless($account->is_active && $user->roleIn($account) !== null, 403);
        }

        // Marca activa disponible para vistas/componentes del estudio.
        view()->share('currentAccount', $account);

        return $next($request);
    }
}
