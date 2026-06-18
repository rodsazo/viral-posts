<?php

namespace App\Support\Ai;

use Anthropic\Core\Attributes\Required;
use Anthropic\Lib\Attributes\Constrained;
use Anthropic\Lib\Concerns\StructuredOutputModelTrait;
use Anthropic\Lib\Contracts\StructuredOutputModel;

/**
 * Una idea ganadora generada por la IA: título, concepto y mecanismo de viralidad.
 */
class IdeaVariant implements StructuredOutputModel
{
    use StructuredOutputModelTrait;

    #[Constrained(description: 'Título corto y atractivo de la idea de contenido.')]
    public string $title;

    #[Constrained(description: 'Concepto: en 2-4 frases, el ángulo de la idea y por qué conecta con la audiencia.')]
    public string $concept;

    // Los valores deben coincidir con App\Enums\ViralMechanism (se mapean a su `value`).
    #[Required(enum: ['curiosidad', 'sorpresa', 'controversia', 'identificacion', 'emocion', 'humor', 'utilidad', 'inspiracion'])]
    #[Constrained(description: 'Mecanismo de viralidad principal que activa la idea.')]
    public string $mechanism;
}
