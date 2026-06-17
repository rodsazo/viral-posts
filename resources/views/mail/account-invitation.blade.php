<x-mail::message>
# Invitación a {{ $accountName }}

Te han invitado a colaborar en **{{ $accountName }}** con el rol de **{{ $role }}**.

Pulsa el botón para aceptar la invitación. Si aún no tienes cuenta, podrás crearla en ese paso.

<x-mail::button :url="$url">
Aceptar invitación
</x-mail::button>

Si no esperabas esta invitación, puedes ignorar este correo.

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
