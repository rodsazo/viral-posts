<?php

namespace App\Viral\Formats;

use App\Viral\Format;

class Puv extends Format
{
    public function key(): string
    {
        return 'puv';
    }

    public function instructions(): string
    {
        return <<<'TXT'
        FORMATO «PUV / Entrevista en calle»: el escéptico deja de ser actuado — es gente real. Directrices:
        - La pregunta-gancho se le hace al entrevistado ("¿Pagarías por…?").
        - Las objeciones las producen los entrevistados; el creador responde en cortes o en la segunda mitad.
        - PROHIBIDO editar para ridiculizar respuestas (rompe la protección del escéptico/novato).
        - Cierra reencuadrando con la analogía y una acción concreta.
        TXT;
    }
}
