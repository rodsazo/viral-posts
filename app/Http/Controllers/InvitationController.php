<?php

namespace App\Http\Controllers;

use App\Models\AccountInvitation;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class InvitationController extends Controller
{
    public function show(string $token): View|RedirectResponse
    {
        $invitation = $this->findPending($token);

        if ($invitation === null) {
            abort(404);
        }

        if ($invitation->isExpired()) {
            return $this->render($invitation, 'expired');
        }

        $existing = User::where('email', $invitation->email)->exists();

        if (! $existing) {
            return $this->render($invitation, 'register');
        }

        // Usuario existente: debe estar autenticado con ese mismo email.
        if (! Auth::check()) {
            // Volver aquí tras iniciar sesión (Filament respeta url.intended).
            redirect()->setIntendedUrl($invitation->acceptanceUrl());

            return redirect('/admin/login');
        }

        if (Auth::user()->email !== $invitation->email) {
            return $this->render($invitation, 'wrong_user');
        }

        return $this->render($invitation, 'accept');
    }

    public function accept(string $token, Request $request): RedirectResponse
    {
        $invitation = $this->findPending($token);

        if ($invitation === null || $invitation->isExpired()) {
            return redirect()->route('invitations.show', $token);
        }

        $user = User::where('email', $invitation->email)->first();

        if ($user !== null) {
            if (! Auth::check() || Auth::user()->email !== $invitation->email) {
                return back()->withErrors([
                    'auth' => "Inicia sesión con {$invitation->email} y vuelve a abrir el enlace para aceptar.",
                ]);
            }
        } else {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);

            $user = User::create([
                'name' => $data['name'],
                'email' => $invitation->email,
                'password' => Hash::make($data['password']),
                'email_verified_at' => now(),
            ]);

            Auth::login($user);
        }

        $invitation->account->users()->syncWithoutDetaching([
            $user->id => ['role' => $invitation->role->value],
        ]);

        $invitation->update(['accepted_at' => now()]);

        return redirect("/admin/{$invitation->account->slug}");
    }

    private function findPending(string $token): ?AccountInvitation
    {
        return AccountInvitation::with('account')
            ->where('token', $token)
            ->whereNull('accepted_at')
            ->first();
    }

    private function render(AccountInvitation $invitation, string $state): View
    {
        return view('invitations.show', [
            'invitation' => $invitation,
            'state' => $state,
        ]);
    }
}
