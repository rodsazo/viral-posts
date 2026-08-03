<?php

namespace App\Support\Ai;

/**
 * Contexto de un turno de refinamiento conversacional de una pieza. Encapsula lo que
 * necesita la IA para "seguir trabajando" sobre el mismo guión:
 *
 *  - un bloque de sistema ESTABLE (rol + reglas + marca + idea + audiencia + borrador base)
 *    que se marca como cacheable (prompt caching): no cambia entre "más cálido" / "más corto",
 *    así que en cada vuelta se paga solo una fracción de esos tokens.
 *  - el HISTORIAL de la conversación (instrucciones del creador + propuestas de la IA) como
 *    mensajes user/assistant, y la nueva instrucción como último mensaje del usuario.
 *
 * Desacoplado del modelo persistido para poder construirlo desde el estado del Composer
 * y serializarlo dentro del Job de cola.
 */
class RefineContext
{
    /**
     * @param  array<int, string>  $questions  preguntas del seguidor que el guión responde
     * @param  array<int, string>  $beliefs  mitos/verdades a tratar ("[Tipo] enunciado")
     * @param  array<int, string>  $pains  dolores/deseos a tocar ("[Tipo] enunciado")
     * @param  array<int, array{role: string, body: ?string, proposal: ?array<string, string>}>  $history
     *                                                                                                     Turnos previos del hilo, en orden. Excluye la nueva instrucción (va en $instruction).
     */
    public function __construct(
        public string $instruction,
        public ?string $brandPromise = null,
        public ?string $mainOffers = null,
        public ?string $ideaTitle = null,
        public ?string $ideaConcept = null,
        public ?string $objective = null,
        public ?string $format = null,
        public array $questions = [],
        public array $beliefs = [],
        public array $pains = [],
        public ?string $baseHook = null,
        public ?string $baseStory = null,
        public ?string $baseMoral = null,
        public ?string $baseCta = null,
        public array $history = [],
        // Personaje de marca (ya renderizado con BrandCharacter::toPromptContext()).
        public ?string $characterContext = null,
        // Principios rectores (guía elegida) y guía del formato/subformato.
        public ?string $principlesInstructions = null,
        public ?string $formatGuide = null,
    ) {}

    /**
     * Bloque de sistema estable del hilo (rol + reglas + marca + idea + audiencia + borrador
     * base). Es la parte que se cachea; cámbiala lo menos posible entre turnos.
     */
    public function toSystem(): string
    {
        $role = (string) config('ai.refine.system.role', 'Eres un guionista experto en contenido viral de redes sociales.');
        $rules = collect((array) config('ai.refine.system.rules', []))
            ->map(fn (string $r): string => "- {$r}")->implode("\n");

        $lines = [];
        $lines[] = $role;
        $lines[] = '';
        $lines[] = 'Trabajas en una conversación de refinamiento: el creador te pedirá ajustes '
            .'("más cálido", "más corto", "otro gancho"…) y tú devuelves SIEMPRE la versión COMPLETA '
            .'del guión ya ajustada (gancho, historia, moraleja y CTA), más una nota breve de qué cambiaste. '
            .'Parte de la última versión vigente y aplica solo el cambio pedido, conservando lo que funciona.';
        $lines[] = '';
        $lines[] = 'Reglas:';
        $lines[] = $rules;

        $brand = array_filter([
            'Promesa de la marca' => $this->brandPromise,
            'Oferta(s) principal(es)' => $this->mainOffers,
        ], fn ($v) => filled($v));

        if (filled($brand)) {
            $lines[] = '';
            $lines[] = 'Contexto de la marca (tenlo presente en TODO el contenido):';
            foreach ($brand as $label => $value) {
                $lines[] = "- {$label}: {$value}";
            }
        }

        if (filled($this->characterContext)) {
            $lines[] = '';
            $lines[] = $this->characterContext;
        }

        if (filled($this->principlesInstructions)) {
            $lines[] = '';
            $lines[] = $this->principlesInstructions;
        }

        if (filled($this->formatGuide)) {
            $lines[] = '';
            $lines[] = 'FORMATO A REPLICAR (estructura y recomendaciones — síguelas):';
            $lines[] = $this->formatGuide;
        }

        if (filled($this->ideaTitle) || filled($this->ideaConcept)) {
            $lines[] = '';
            $lines[] = 'Idea ganadora (directriz principal — formato viral a replicar):';
            if (filled($this->ideaTitle)) {
                $lines[] = "- Título: {$this->ideaTitle}";
            }
            if (filled($this->ideaConcept)) {
                $lines[] = "- Estructura: {$this->ideaConcept}";
            }
        }

        if (filled($this->objective)) {
            $lines[] = "Objetivo: {$this->objective}";
        }
        if (filled($this->format)) {
            $lines[] = "Formato: {$this->format}";
        }

        if (filled($this->questions)) {
            $lines[] = '';
            $lines[] = 'Preguntas de la audiencia que el guión responde:';
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
            $lines[] = 'Dolores, problemas y deseos del seguidor que el guión debe tocar:';
            foreach ($this->pains as $p) {
                $lines[] = "- {$p}";
            }
        }

        $lines[] = '';
        $lines[] = 'BORRADOR DE PARTIDA de la pieza (punto de arranque de la conversación):';
        $lines[] = $this->renderScript($this->baseHook, $this->baseStory, $this->baseMoral, $this->baseCta);

        return implode("\n", $lines);
    }

    /**
     * La conversación como array de mensajes user/assistant, terminando en la nueva
     * instrucción del creador.
     *
     * @return array<int, array{role: string, content: string}>
     */
    public function toMessages(): array
    {
        $messages = [];

        foreach ($this->history as $turn) {
            $role = ($turn['role'] ?? '') === 'assistant' ? 'assistant' : 'user';

            if ($role === 'assistant') {
                $proposal = $turn['proposal'] ?? [];
                $content = trim((string) ($turn['body'] ?? ''));
                $script = $this->renderScript(
                    $proposal['hook'] ?? null,
                    $proposal['story'] ?? null,
                    $proposal['moral'] ?? null,
                    $proposal['cta'] ?? null,
                );
                $content = $content !== '' ? $content."\n\n".$script : $script;
            } else {
                $content = trim((string) ($turn['body'] ?? ''));
            }

            if ($content !== '') {
                $messages[] = ['role' => $role, 'content' => $content];
            }
        }

        $messages[] = ['role' => 'user', 'content' => $this->instruction];

        return $messages;
    }

    private function renderScript(?string $hook, ?string $story, ?string $moral, ?string $cta): string
    {
        return implode("\n\n", [
            'GANCHO: '.trim((string) $hook),
            'HISTORIA: '.trim((string) $story),
            'MORALEJA: '.trim((string) $moral),
            'CTA: '.trim((string) $cta),
        ]);
    }
}
