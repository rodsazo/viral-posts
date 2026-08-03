<?php

namespace App\Viral\Formats;

use App\Viral\Format;

class Selfie extends Format
{
    public function key(): string
    {
        return 'selfie';
    }

    public function instructions(): string
    {
        return <<<'TXT'
        FORMATO «Selfie» (opinión directa a cámara, cámara en mano, energía íntima de "te lo cuento solo a ti"): el creador da su opinión de frente. Directrices:
        - Gancho de tabú en la primera frase ("El 90% de X se enoja cuando digo esto, pero lo voy a decir igual").
        - La tesis, dicha de frente justo tras el gancho.
        - El creador se ADELANTA a las objeciones él mismo ("Y ojo — no lo digo por…"), en vez de que las haga un escéptico.
        - Conserva la analogía cotidiana y el contraste irónico ("pagamos por no ver anuncios, pero…"): son el activo más valioso de la idea.
        - Defiende explícitamente al "bando atacado suave" ("y para que quede claro: no digo que todo deba ser…") para no alienar.
        - CTA a comentarios con pregunta binaria ("¿tú qué opinas: sí o no? Te leo."). El debate es el motor del alcance; la marca aparece como matiz de honestidad, no como CTA duro.
        TXT;
    }
}
