<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Revisión del cliente (aprobar / pedir cambios)
    |--------------------------------------------------------------------------
    |
    | Cuando está activo, la vista pública de la pieza permite al cliente aprobar
    | o pedir cambios (y avisa al equipo por correo). Desactivado por defecto: el
    | flujo de interacción con el cliente aún está por definir. Para activarlo,
    | define STUDIO_CLIENT_REVIEW=true en el entorno.
    |
    */

    'client_review' => env('STUDIO_CLIENT_REVIEW', false),

    /*
    |--------------------------------------------------------------------------
    | Landing público (home)
    |--------------------------------------------------------------------------
    |
    | URL de agenda (Calendly / Cal.com / etc.) a la que apunta el botón principal
    | del landing. Defínela en BOOKING_URL. Correo de contacto opcional para el pie.
    |
    */

    'booking_url' => env('BOOKING_URL', ''),

    'contact_email' => env('CONTACT_EMAIL', ''),

];
