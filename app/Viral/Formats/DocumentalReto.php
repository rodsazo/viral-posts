<?php

namespace App\Viral\Formats;

use App\Viral\Format;

class DocumentalReto extends Format
{
    public function key(): string
    {
        return 'documental_reto';
    }

    public function instructions(): string
    {
        return <<<'TXT'
        FORMATO «Documental / Reto»: versión ampliada del arco narrativo, con estructura de reto ("X días haciendo…", "¿puede un principiante…?"). Directrices:
        - Planteamiento del reto como gancho + apuesta emocional clara.
        - Hitos y obstáculos (cada uno resuelve una objeción); tensión creciente hacia el desenlace.
        - Resolución con prueba social grabada y un solo CTA.
        - Alto costo: solo para ideas doblemente validadas.
        TXT;
    }
}
