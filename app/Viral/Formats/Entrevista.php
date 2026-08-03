<?php

namespace App\Viral\Formats;

use App\Viral\Format;

class Entrevista extends Format
{
    public function key(): string
    {
        return 'entrevista';
    }

    public function instructions(): string
    {
        return <<<'TXT'
        FORMATO «Entrevista» (uno a uno): conversación con una persona real o experta. Directrices:
        - Preguntas que representan las dudas de la audiencia, en escalera.
        - Extrae 1–2 frases-clip potentes (gancho) y una analogía cotidiana.
        - Respeta al entrevistado; el conflicto es con la idea, no con la persona.
        TXT;
    }
}
