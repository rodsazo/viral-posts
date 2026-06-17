<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invitación · {{ $invitation->account->name }}</title>
    <style>
        :root { color-scheme: light dark; }
        body { font-family: ui-sans-serif, system-ui, sans-serif; background: #f3f4f6; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card { background: #fff; max-width: 28rem; width: calc(100% - 2rem); border-radius: 0.75rem; box-shadow: 0 10px 30px rgba(0,0,0,.08); padding: 2rem; }
        h1 { font-size: 1.25rem; margin: 0 0 .5rem; color: #111827; }
        p { color: #4b5563; line-height: 1.5; }
        .role { display: inline-block; background: #fef3c7; color: #92400e; padding: .1rem .5rem; border-radius: .375rem; font-size: .8rem; font-weight: 600; }
        label { display: block; font-size: .85rem; color: #374151; margin: .75rem 0 .25rem; font-weight: 600; }
        input { width: 100%; padding: .55rem .65rem; border: 1px solid #d1d5db; border-radius: .5rem; box-sizing: border-box; font-size: .95rem; }
        button { width: 100%; margin-top: 1.25rem; padding: .65rem; border: 0; border-radius: .5rem; background: #f59e0b; color: #111827; font-weight: 700; font-size: .95rem; cursor: pointer; }
        a.btn { display: block; text-align: center; margin-top: 1.25rem; padding: .65rem; border-radius: .5rem; background: #111827; color: #fff; text-decoration: none; font-weight: 600; }
        .err { background: #fee2e2; color: #991b1b; padding: .6rem .75rem; border-radius: .5rem; font-size: .85rem; margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Invitación a {{ $invitation->account->name }}</h1>

        @if ($state === 'expired')
            <p>Esta invitación ha <strong>caducado</strong>. Pide al administrador de la marca que te envíe una nueva.</p>
        @else
            <p>Te han invitado a colaborar con el rol de <span class="role">{{ $invitation->role->getLabel() }}</span>.</p>

            @if ($errors->any())
                <div class="err">{{ $errors->first() }}</div>
            @endif

            @switch($state)
                @case('register')
                    <p>Crea tu cuenta para <strong>{{ $invitation->email }}</strong> y unirte.</p>
                    <form method="POST" action="{{ route('invitations.accept', $invitation->token) }}">
                        @csrf
                        <label for="name">Tu nombre</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus>
                        <label for="password">Contraseña</label>
                        <input id="password" name="password" type="password" required>
                        <label for="password_confirmation">Repite la contraseña</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required>
                        <button type="submit">Crear cuenta y unirme</button>
                    </form>
                    @break

                @case('accept')
                    <p>Estás conectado como <strong>{{ $invitation->email }}</strong>.</p>
                    <form method="POST" action="{{ route('invitations.accept', $invitation->token) }}">
                        @csrf
                        <button type="submit">Aceptar invitación</button>
                    </form>
                    @break

                @case('wrong_user')
                    <p>Esta invitación es para <strong>{{ $invitation->email }}</strong>, pero tienes otra sesión abierta. Cierra sesión e inicia con ese correo para aceptar.</p>
                    <a class="btn" href="/admin/login">Ir al login</a>
                    @break
            @endswitch
        @endif
    </div>
</body>
</html>
