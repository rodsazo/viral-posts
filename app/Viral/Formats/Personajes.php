<?php

namespace App\Viral\Formats;

use App\Viral\Format;
use App\Viral\Subformats\EscepticoConvencido;

class Personajes extends Format
{
    public function key(): string
    {
        return 'personajes';
    }

    public function instructions(): string
    {
        return <<<'TXT'
        FORMATO «Personajes»: una mini secuencia de teatro con dos o más personajes en pantalla, interpretados por personas distintas o por el mismo creador (cambiando ropa y encuadre) que conversan o debaten. Directrices:
        - Escribe el guion como DIÁLOGO por turnos numerados, uno por línea: «1 —» para el primer personaje, «2 —» para el segundo. Nunca como prosa corrida.
        - Cada personaje tiene voz y postura claras y consistentes; se distinguen a simple vista (vestuario/encuadre).
        - Ritmo de conversación real: turnos cortos, interrupciones, réplicas, cesiones a medias. Sin narrador.
        - Consistencia visual serial entre piezas (mismo fondo, mismo prop) para reconocimiento.
        - La estructura concreta la define el SUBFORMATO elegido.
        TXT;
    }

    public function subformats(): array
    {
        return [
            new EscepticoConvencido,
        ];
    }
}
