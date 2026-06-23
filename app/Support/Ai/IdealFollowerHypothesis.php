<?php

namespace App\Support\Ai;

use Anthropic\Core\Attributes\Required;
use Anthropic\Lib\Attributes\Constrained;
use Anthropic\Lib\Concerns\StructuredOutputModelTrait;
use Anthropic\Lib\Contracts\StructuredOutputModel;

/**
 * Una hipótesis de seguidor ideal generada por la IA, con sus dolores, preguntas y mitos.
 */
class IdealFollowerHypothesis implements StructuredOutputModel
{
    use StructuredOutputModelTrait;

    #[Constrained(description: 'Nombre corto del seguidor ideal.')]
    public string $name;

    #[Constrained(description: 'Descripción de la población: amplia, no un nicho.')]
    public string $description;

    #[Required(enum: [0, 1, 2, 3, 4])]
    #[Constrained(description: 'Nivel de conciencia (0-4) de esta población.')]
    public int $awareness_level;

    /**
     * @var PainItem[]
     */
    #[Constrained(description: '4 dolores, problemas o deseos comunes de esta población.', itemClass: PainItem::class, minItems: 1)]
    public array $pains;

    /**
     * @var string[]
     */
    #[Constrained(description: '4 preguntas frecuentes que esta persona se hace sobre el problema.', minItems: 1)]
    public array $questions;

    /**
     * @var string[]
     */
    #[Constrained(description: '4 mitos comunes en la industria que esta población suele creer.', minItems: 1)]
    public array $beliefs;
}
