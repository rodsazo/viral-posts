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

    #[Constrained(description: 'Gancho: 1-2 frases que detienen el scroll y abren un bucle de curiosidad.')]
    public string $hook;

    #[Constrained(description: 'Historia: el desarrollo principal, concreto y en primera persona. 3-6 frases.')]
    public string $story;

    #[Constrained(description: 'Moraleja: la lección o reencuadre que refuerza las verdades y desmiente los mitos.')]
    public string $moral;

    #[Constrained(description: 'CTA: llamada a la acción clara y específica para el espectador.')]
    public string $cta;
}
