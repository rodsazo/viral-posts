<?php

namespace App\Viral\Formats;

use App\Viral\Format;

class Pov extends Format
{
    public function key(): string
    {
        return 'pov';
    }

    public function instructions(): string
    {
        return <<<'TXT'
        FORMATO «POV» (primera persona): la cámara vive la situación; el espectador ES el protagonista/escéptico (p. ej. quien filma sostiene el celular como entrevistador y puede mostrar su propia mano al preguntar; el creador responde). Directrices:
        - Sitúa al espectador dentro de la escena desde el segundo 0 (texto en pantalla que define el POV).
        - La escalera de objeciones se convierte en una escalera de MICRO-ESCENAS emocionales: miedo → sorpresa → comodidad.
        - La analogía puede ir como texto en pantalla.
        - El producto aparece al final como el ORIGEN de la buena experiencia mostrada, no como anuncio.
        TXT;
    }
}
