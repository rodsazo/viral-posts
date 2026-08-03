<?php

namespace App\Viral\Formats;

use App\Viral\Format;

class Rankings extends Format
{
    public function key(): string
    {
        return 'rankings';
    }

    public function instructions(): string
    {
        return <<<'TXT'
        FORMATO «Rankings»: una lista ordenada (top N) sobre un criterio claro y polémico. Directrices:
        - Gancho con el número o la promesa del ranking ("Los 3 errores que…", "El nº1 te va a sorprender").
        - Orden ascendente hacia un clímax: reserva el elemento más fuerte/inesperado para el final.
        - Cada ítem breve y con un porqué; incluye al menos un giro o elección contraintuitiva que genere debate.
        - CTA a comentarios pidiendo lo que falta o el desacuerdo ("¿cuál agregarías?", "¿en qué puesto lo pondrías?").
        TXT;
    }
}
