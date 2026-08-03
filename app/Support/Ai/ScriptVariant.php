<?php

namespace App\Support\Ai;

use Anthropic\Lib\Attributes\Constrained;
use Anthropic\Lib\Concerns\StructuredOutputModelTrait;
use Anthropic\Lib\Contracts\StructuredOutputModel;

/**
 * Una variante de guión generada por la IA (estructura gancho → historia → moraleja → CTA).
 * El SDK de Anthropic infiere el JSON schema desde estos tipos y rellena la respuesta.
 */
class ScriptVariant implements StructuredOutputModel
{
    use StructuredOutputModelTrait;

    #[Constrained(description: 'Gancho: la apertura que detiene el scroll en 0-3 s. En formatos de diálogo: el primer turno del escéptico + la primera respuesta firme (turnos «1 —» / «2 —», uno por línea).')]
    public string $hook;

    #[Constrained(description: 'Historia: el desarrollo del guion. En formatos de diálogo: los turnos intermedios (la escalera de objeciones), escritos como «1 —» / «2 —», uno por línea, turnos cortos.')]
    public string $story;

    #[Constrained(description: 'Moraleja: el clímax que demuestra experticia. En formatos de diálogo: la analogía desbloqueadora y la resolución de la última barrera (turnos «1 —» / «2 —»).')]
    public string $moral;

    #[Constrained(description: 'CTA: cierre con UNA sola llamada a la acción. En formatos de diálogo: los turnos finales (la conversión del escéptico) y, en línea aparte, «CTA en pantalla: …».')]
    public string $cta;
}
