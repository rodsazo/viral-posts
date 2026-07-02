<?php

namespace App\Support\Ai;

use Anthropic\Lib\Attributes\Constrained;
use Anthropic\Lib\Concerns\StructuredOutputModelTrait;
use Anthropic\Lib\Contracts\StructuredOutputModel;

/**
 * Salida de un turno de refinamiento conversacional: una nota breve de qué se cambió
 * (para el hilo de chat) + la versión completa propuesta del guión. La propuesta solo
 * se aplica a la pieza si el usuario pulsa "Usar esta versión".
 */
class ScriptRefinement implements StructuredOutputModel
{
    use StructuredOutputModelTrait;

    #[Constrained(description: 'Nota breve (1-2 frases, en español) explicando qué cambiaste respecto a la petición del creador. Es el mensaje del chat, no repitas el guión aquí.')]
    public string $note;

    #[Constrained(description: 'Gancho reescrito: 1-2 frases que detienen el scroll y abren un bucle de curiosidad.')]
    public string $hook;

    #[Constrained(description: 'Historia reescrita: el desarrollo principal, concreto y en primera persona.')]
    public string $story;

    #[Constrained(description: 'Moraleja reescrita: la lección o reencuadre que refuerza las verdades y desmiente los mitos.')]
    public string $moral;

    #[Constrained(description: 'CTA reescrita: llamada a la acción clara y específica para el espectador.')]
    public string $cta;
}
