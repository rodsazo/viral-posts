<?php

namespace App\Viral\Formats;

use App\Viral\Format;

class Vlog extends Format
{
    public function key(): string
    {
        return 'vlog';
    }

    public function instructions(): string
    {
        return <<<'TXT'
        FORMATO «Vlog» (arco narrativo): la idea se estira a una historia documentada ("Voy a llevar a [escéptico real] a su primera vez con…"). Directrices:
        - Cada objeción del guion = un momento documentado: antes se dice a cámara, después se resuelve en la experiencia.
        - La conversión final del escéptico es literal y grabada: máxima prueba social.
        - Mayor costo de producción: reserva este formato para ideas ya validadas en formatos baratos.
        TXT;
    }
}
