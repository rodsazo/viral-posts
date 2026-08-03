<?php

namespace App\Viral\Formats;

use App\Viral\Format;

class Podcast extends Format
{
    public function key(): string
    {
        return 'podcast';
    }

    public function instructions(): string
    {
        return <<<'TXT'
        FORMATO «Podcast» (entrevista simulada): se recrea un clip de podcast entre anfitrión e invitado (pueden ser el mismo creador). Directrices:
        - El anfitrión pregunta exactamente lo que la audiencia se pregunta; el invitado encarna a la marca/experto.
        - Escalera de preguntas cada vez más personales; respuestas cortas y firmes (nada de monólogos).
        - Arranca por el momento más fuerte (clip-gancho), no por presentaciones.
        - Setup reconocible (micrófono, dos sillas) para credibilidad de "conversación real".
        TXT;
    }
}
