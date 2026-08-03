<?php

namespace App\Viral\Formats;

use App\Viral\Format;

class HablandoACamaraVisual extends Format
{
    public function key(): string
    {
        return 'hablando_a_camara_visual';
    }

    public function instructions(): string
    {
        return <<<'TXT'
        FORMATO «Hablando a cámara (visual)»: como hablando a cámara, pero apoyado en un elemento visual (pizarra, objeto, texto en pantalla). Directrices:
        - El gancho se MUESTRA además de decirse (afirmación escrita/objeto).
        - La analogía se encarna en el objeto/visual: muéstralo en el momento del clímax.
        - Las objeciones pueden ir numeradas en pantalla mientras se responden.
        TXT;
    }
}
