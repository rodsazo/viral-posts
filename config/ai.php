<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Asistente de IA (Anthropic / Claude)
    |--------------------------------------------------------------------------
    |
    | Configuración editable del asistente. La clave y el modelo viven en
    | config/services.php (anthropic.*); aquí va lo afinable del producto.
    |
    */

    'script' => [

        // Nº de variantes que pide el generador de piezas del Estudio.
        'suggestions' => 5,

        // Nº de variantes en las sugerencias en línea (composer / edición de pieza).
        'inline_suggestions' => 3,

        /*
        | Fórmula del guión (estructura). Editable en cualquier momento: el texto de
        | cada parte se inyecta en el prompt como la estructura que debe seguir la IA.
        | El orden define el orden en que se pide. Las claves coinciden con los campos
        | de la pieza (hook/story/moral/cta).
        */
        'formula' => [
            'hook' => 'Gancho — 1-2 frases que detienen el scroll y abren un bucle de curiosidad.',
            'story' => 'Historia — el desarrollo principal, concreto y en primera persona (3-6 frases); nada genérico.',
            'moral' => 'Moraleja — la lección o reencuadre que refuerza las verdades y desmiente los mitos.',
            'cta' => 'CTA — llamada a la acción clara y específica para el espectador.',
        ],

    ],

];
