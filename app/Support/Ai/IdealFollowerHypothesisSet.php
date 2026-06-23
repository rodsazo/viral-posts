<?php

namespace App\Support\Ai;

use Anthropic\Lib\Attributes\Constrained;
use Anthropic\Lib\Concerns\StructuredOutputModelTrait;
use Anthropic\Lib\Contracts\StructuredOutputModel;

/**
 * Raíz del structured output del Kickstart: hasta 3 hipótesis de seguidor ideal.
 */
class IdealFollowerHypothesisSet implements StructuredOutputModel
{
    use StructuredOutputModelTrait;

    /**
     * @var IdealFollowerHypothesis[]
     */
    #[Constrained(
        description: 'Entre 1 y 3 hipótesis de seguidor ideal, distintas entre sí y repartidas por nivel de conciencia.',
        itemClass: IdealFollowerHypothesis::class,
        minItems: 1,
    )]
    public array $hypotheses;
}
