<?php

namespace App\Viral\Formats;

use App\Viral\Format;

class HablandoACamara extends Format
{
    public function key(): string
    {
        return 'hablando_a_camara';
    }

    public function instructions(): string
    {
        return <<<'TXT'
        FORMATO «Hablando a cámara»: pieza a cámara, tono cercano y directo. Directrices:
        - Gancho con la afirmación polémica de frente.
        - Núcleo de "las 3 objeciones que siempre me hacen", enumeradas y respondidas (progresión lógica → práctica → personal).
        - Una analogía cotidiana central.
        - CTA híbrido (comentarios + enlace en bio) si encaja con el objetivo.
        TXT;
    }
}
