<?php

namespace App\Support\Ai;

use App\Models\Belief;
use App\Models\ContentPiece;
use App\Models\HerasTemplate;
use App\Models\Pain;
use App\Models\WinningIdea;

/**
 * Contexto que alimenta la generación de guiones. Desacoplado del modelo persistido
 * para poder construirlo desde una pieza guardada (Estudio) o desde el estado del
 * formulario sin guardar (admin Filament).
 */
class ScriptContext
{
    /**
     * @param  array<int, string>  $questions  preguntas que responde la pieza
     * @param  array<int, string>  $beliefs  mitos/verdades a tratar ("[Tipo] enunciado")
     * @param  array<int, string>  $pains  dolores/problemas/deseos a tocar ("[Tipo] enunciado")
     * @param  array<int, string>  $templates  fórmulas Heras de referencia (texto ya formateado)
     * @param  array<int, string>  $hooks  plantillas de gancho a usar (texto ya formateado)
     * @param  array<int, string>  $ctas  CTAs hacia las que debe fluir cada variante (texto ya formateado)
     */
    public function __construct(
        public ?string $title = null,
        public ?string $ideaTitle = null,
        public ?string $ideaConcept = null,
        public ?string $objective = null,
        public ?string $format = null,
        public array $questions = [],
        public array $beliefs = [],
        public array $pains = [],
        public ?string $currentHook = null,
        public ?string $currentStory = null,
        public ?string $currentMoral = null,
        public ?string $currentCta = null,
        public ?string $extra = null,
        public array $templates = [],
        public array $hooks = [],
        public array $ctas = [],
    ) {}

    public static function fromPiece(ContentPiece $piece): self
    {
        $idea = $piece->winningIdea;

        return new self(
            title: $piece->title,
            ideaTitle: $idea?->title,
            ideaConcept: $idea?->concept,
            objective: $piece->objective?->getLabel(),
            format: $piece->format?->getLabel(),
            questions: $piece->derivedQuestions()->pluck('body')->all(),
            beliefs: static::beliefLabels($piece->derivedBeliefs()->all()),
            pains: static::painLabels($piece->derivedPains()->all()),
            currentHook: $piece->hook,
            currentStory: $piece->story,
            currentMoral: $piece->moral,
            currentCta: $piece->cta,
            templates: $idea?->herasTemplate ? static::templateLines([$idea->herasTemplate]) : [],
        );
    }

    public static function fromIdea(?WinningIdea $idea): self
    {
        if ($idea === null) {
            return new self;
        }

        return new self(
            ideaTitle: $idea->title,
            ideaConcept: $idea->concept,
            questions: $idea->questions->pluck('body')->all(),
            beliefs: static::beliefLabels($idea->derivedBeliefs()->all()),
            pains: static::painLabels($idea->derivedPains()->all()),
            templates: $idea->herasTemplate ? static::templateLines([$idea->herasTemplate]) : [],
        );
    }

    /**
     * Formatea plantillas Heras como líneas de contexto, omitiendo campos vacíos
     * (las plantillas placeholder sin estructura no ensucian el prompt).
     *
     * @param  iterable<int, HerasTemplate>  $templates
     * @return array<int, string>
     */
    public static function templateLines(iterable $templates): array
    {
        return collect($templates)
            ->map(function (HerasTemplate $t): ?string {
                $meta = collect([
                    filled($t->suggested_format) ? "formato sugerido: {$t->suggested_format}" : null,
                    filled($t->viral_mechanism) ? "mecanismo: {$t->viral_mechanism}" : null,
                ])->filter()->implode('; ');

                $line = 'Fórmula «'.$t->name.'»';
                $line .= $meta !== '' ? " ({$meta})" : '';
                $line .= filled($t->structure) ? ". Estructura: {$t->structure}" : '';

                return $line;
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, Belief>  $beliefs
     * @return array<int, string>
     */
    private static function beliefLabels(array $beliefs): array
    {
        return array_map(
            fn (Belief $belief): string => '['.$belief->type->getLabel().'] '.$belief->statement,
            $beliefs,
        );
    }

    /**
     * @param  array<int, Pain>  $pains
     * @return array<int, string>
     */
    private static function painLabels(array $pains): array
    {
        return array_map(
            fn (Pain $pain): string => '['.$pain->type->getLabel().'] '.$pain->body,
            $pains,
        );
    }

    /**
     * Indica si hay material suficiente para pedir un guión con sentido.
     */
    public function hasMaterial(): bool
    {
        return filled($this->ideaConcept) || filled($this->questions) || filled($this->beliefs) || filled($this->pains);
    }

    /**
     * Renderiza el contexto como el mensaje de usuario que recibe el modelo.
     */
    public function toPrompt(): string
    {
        $lines = [];

        $lines[] = 'Crea el guión para esta pieza de contenido.';
        $lines[] = '';

        if (filled($this->title)) {
            $lines[] = "Título de trabajo: {$this->title}";
        }
        if (filled($this->ideaTitle)) {
            $lines[] = "Idea ganadora: {$this->ideaTitle}";
        }
        if (filled($this->ideaConcept)) {
            $lines[] = "Concepto: {$this->ideaConcept}";
        }
        if (filled($this->objective)) {
            $lines[] = "Objetivo: {$this->objective}";
        }
        if (filled($this->format)) {
            $lines[] = "Formato: {$this->format}";
        }

        if (filled($this->questions)) {
            $lines[] = '';
            $lines[] = 'Preguntas de la audiencia que el guión debe responder. Para cada guión puedes elegir una o varias. Trata de repartirlas equitativamente:';
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
            $lines[] = 'Dolores, problemas y deseos del seguidor que el guión debe tocar (conecta con ellos):';
            foreach ($this->pains as $p) {
                $lines[] = "- {$p}";
            }
        }

        if (filled($this->templates)) {
            $lines[] = '';
            $lines[] = 'Fórmulas virales de referencia a seguir (Ideas Ganadoras Referenciales — Heras):';
            foreach ($this->templates as $t) {
                $lines[] = "- {$t}";
            }
        }

        if (filled($this->hooks)) {
            $lines[] = '';
            $lines[] = 'Plantillas de gancho a usar: usa CADA una en una variante distinta para construir su gancho. '
                .'Si hay menos ganchos que variantes pedidas, inventa los ganchos restantes acordes al contenido.';
            foreach ($this->hooks as $h) {
                $lines[] = "- {$h}";
            }
        }

        if (filled($this->ctas)) {
            $lines[] = '';
            $lines[] = count($this->ctas) === 1
                ? 'Llamada a la acción (CTA) obligatoria: TODAS las variantes deben terminar dirigiendo al espectador hacia esta CTA. '
                    .'El contenido debe fluir de forma natural hacia ella (sin forzarla) y rematar con ella en el campo CTA:'
                : 'Llamadas a la acción (CTA) a usar: cada variante debe fluir de forma natural hacia una de estas CTAs y rematar con ella en el campo CTA:';
            foreach ($this->ctas as $cta) {
                $lines[] = "- {$cta}";
            }
        }

        $current = array_filter([
            'Gancho' => $this->currentHook,
            'Historia' => $this->currentStory,
            'Moraleja' => $this->currentMoral,
            'CTA' => $this->currentCta,
        ], fn ($v) => filled($v));

        if (filled($current)) {
            $lines[] = '';
            $lines[] = 'Borrador actual del creador (mejóralo o propón ángulos alternativos):';
            foreach ($current as $label => $value) {
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
