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

        abort_unless($account instanceof Account && $user !== null && $user->roleIn($account) !== null, 403);

        // Marca activa disponible para vistas/componentes del estudio.
        view()->share('currentAccount', $account);

        return $next($request);
    }
}
