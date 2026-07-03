<?php

namespace App\Support\Ai;

/**
 * Contexto que alimenta la generación de ideas ganadoras: las preguntas de la
 * audiencia y los mitos/verdades en juego (más, opcionalmente, un borrador).
 */
class IdeaContext
{
    /**
     * @param  array<int, string>  $questions  preguntas de la audiencia
     * @param  array<int, string>  $beliefs  mitos/verdades ("[Tipo] enunciado")
     * @param  array<int, string>  $pains  dolores/problemas/deseos ("[Tipo] enunciado")
     */
    public function __construct(
        public array $questions = [],
        public array $beliefs = [],
        public array $pains = [],
        public ?string $draftTitle = null,
        public ?string $draftConcept = null,
        public ?string $brandPromise = null,
        public ?string $mainOffers = null,
        public ?string $extra = null,
        // Personaje de marca (ya renderizado con BrandCharacter::toPromptContext()).
        public ?string $characterContext = null,
    ) {}

    public function hasMaterial(): bool
    {
        return filled($this->questions) || filled($this->beliefs) || filled($this->pains) || filled($this->draftConcept);
    }

    public function toPrompt(): string
    {
        $lines = [];

        $lines[] = 'Propón ideas ganadoras de contenido a partir de este material.';

        $brand = array_filter([
            'Promesa de la marca' => $this->brandPromise,
            'Oferta(s) principal(es)' => $this->mainOffers,
        ], fn ($v) => filled($v));

        if (filled($brand)) {
            $lines[] = '';
            $lines[] = 'Contexto de la marca (las ideas deben encajar con ella):';
            foreach ($brand as $label => $value) {
                $lines[] = "- {$label}: {$value}";
            }
        }

        if (filled($this->characterContext)) {
            $lines[] = '';
            $lines[] = $this->characterContext;
        }

        if (filled($this->questions)) {
            $lines[] = '';
            $lines[] = 'Preguntas de la audiencia:';
            foreach ($this->questions as $q) {
                $lines[] = "- {$q}";
            }
        }

        if (filled($this->beliefs)) {
            $lines[] = '';
            $lines[] = 'Mitos a desmentir y verdades a reforzar:';
            foreach ($this->beliefs as $b) {
                $lines[] = "- {$b}";
            }
        }

        if (filled($this->pains)) {
            $lines[] = '';
            $lines[] = 'Dolores, problemas y deseos del seguidor:';
            foreach ($this->pains as $p) {
                $lines[] = "- {$p}";
            }
        }

        $draft = array_filter([
            'Título' => $this->draftTitle,
            'Concepto' => $this->draftConcept,
        ], fn ($v) => filled($v));

        if (filled($draft)) {
            $lines[] = '';
            $lines[] = 'Borrador actual del creador (mejóralo o propón alternativas):';
            foreach ($draft as $label => $value) {
                $lines[] = "{$label}: {$value}";
            }
        }

        if (filled($this->extra)) {
            $lines[] = '';
            $lines[] = 'Instrucciones adicionales del creador (priorízalas):';
            $lines[] = $this->extra;
        }

        return implode("\n", $lines);
    }
}
