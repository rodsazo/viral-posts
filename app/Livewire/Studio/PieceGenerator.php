<?php

namespace App\Livewire\Studio;

use App\Enums\ContentFormat;
use App\Enums\ContentObjective;
use App\Enums\ContentStatus;
use App\Jobs\GenerateSuggestionsJob;
use App\Models\Account;
use App\Models\AiGeneration;
use App\Models\Belief;
use App\Models\HerasTemplate;
use App\Models\HookTemplate;
use App\Models\Pain;
use App\Models\Question;
use App\Models\ViralReferent;
use App\Models\WinningIdea;
use App\Support\Ai\ContentAssistant;
use App\Support\Ai\ScriptContext;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Generador de piezas (solo Estudio): un flujo fuera de la edición que, a partir de
 * los parámetros de una pieza + fórmulas Heras de referencia, pide N guiones a la IA
 * y crea una pieza por cada variante elegida.
 */
#[Layout('components.layouts.studio')]
class PieceGenerator extends Component
{
    public Account $account;

    // Paso 1 — parámetros (los mismos que una pieza).
    public $winning_idea_id = null;

    public ?string $objective = null;

    public ?string $format = null;

    public ?string $instructions = null;

    // Selección manual de contexto: Seguidor Ideal → preguntas y creencias a enviar.
    public ?int $idealFollowerId = null;

    /** @var array<int, int|string> */
    public array $questionIds = [];

    /** @var array<int, int|string> */
    public array $beliefIds = [];

    /** @var array<int, int|string> */
    public array $painIds = [];

    // Ideas Ganadoras Referenciales (HerasTemplate) + filtro por Referente.
    public ?int $referentFilter = null;

    /** @var array<int, int|string> */
    public array $herasTemplateIds = [];

    // Plantillas de gancho seleccionadas (hasta 5) + estado del modal selector.
    /** @var array<int, int|string> */
    public array $selectedHookIds = [];

    /** @var array<int, int|string> */
    public array $modalHookIds = [];

    public ?string $hookSearch = null;

    public ?int $hookReferentFilter = null;

    // CTA hacia la que debe fluir el guión. Por ahora una sola; el contexto/prompt ya
    // soportan varias (lista), así que pasar a multi-selección luego es solo cambiar la UI.
    public ?int $selectedCtaId = null;

    // Paso 2-3 — sugerencias y selección.
    /** @var array<int, array{label: string, fields: array<string, string>, preview: string}> */
    public array $suggestions = [];

    /** @var array<int, int|string> */
    public array $selected = [];

    public ?string $aiError = null;

    // Generación en curso (id del registro AiGeneration) mientras se procesa en la cola.
    public ?int $generationId = null;

    public function mount(Account $account): void
    {
        $this->account = $account;
    }

    #[Computed]
    public function aiEnabled(): bool
    {
        return app(ContentAssistant::class)->isConfigured();
    }

    /** Al cambiar de seguidor, reinicia la idea (sus ideas se filtran) y el contexto manual. */
    public function updatedIdealFollowerId(): void
    {
        $this->winning_idea_id = null;
        $this->questionIds = [];
        $this->beliefIds = [];
        $this->painIds = [];
    }

    /**
     * Preguntas del seguidor elegido (para que el usuario marque las que enviar).
     *
     * @return Collection<int, Question>
     */
    #[Computed]
    public function followerQuestions(): Collection
    {
        if (! $this->idealFollowerId) {
            return collect();
        }

        return $this->account->questions()
            ->where('ideal_follower_id', $this->idealFollowerId)
            ->orderBy('body')
            ->get();
    }

    /**
     * Creencias (mitos/verdades) del seguidor: directas, ahora que el seguidor es el centro.
     *
     * @return Collection<int, Belief>
     */
    #[Computed]
    public function followerBeliefs(): Collection
    {
        if (! $this->idealFollowerId) {
            return collect();
        }

        return $this->account->beliefs()
            ->where('ideal_follower_id', $this->idealFollowerId)
            ->orderBy('statement')
            ->get();
    }

    /**
     * Dolores / problemas / deseos del seguidor elegido.
     *
     * @return Collection<int, Pain>
     */
    #[Computed]
    public function followerPains(): Collection
    {
        if (! $this->idealFollowerId) {
            return collect();
        }

        return $this->account->pains()
            ->where('ideal_follower_id', $this->idealFollowerId)
            ->orderBy('type')
            ->orderBy('body')
            ->get();
    }

    /** ¿Hay una generación en curso (encolada)? La vista la usa para hacer polling. */
    #[Computed]
    public function generating(): bool
    {
        return $this->generationId !== null;
    }

    /** Encola la generación de guiones (paso 2). Puede regenerarse. */
    public function generate(): void
    {
        $this->aiError = null;
        $this->suggestions = [];
        $this->selected = [];

        if (! app(ContentAssistant::class)->isConfigured()) {
            return;
        }

        $context = ScriptContext::fromIdea($this->selectedIdea());
        $context->objective = $this->objective ? ContentObjective::tryFrom($this->objective)?->getLabel() : null;
        $context->format = $this->format ? ContentFormat::tryFrom($this->format)?->getLabel() : null;
        $context->extra = $this->instructions;
        // Si el usuario eligió seguidor + preguntas/creencias/dolores manualmente, manda esos.
        $context->questions = $this->contextQuestions;
        $context->beliefs = $this->contextBeliefs;
        $context->pains = $this->contextPains;
        $context->templates = collect($context->templates)
            ->merge(ScriptContext::templateLines($this->selectedTemplates()))
            ->unique()
            ->values()
            ->all();
        $context->hooks = $this->hookLines();
        $context->ctas = $this->ctaLines();

        $generation = AiGeneration::create([
            'account_id' => $this->account->getKey(),
            'user_id' => auth()->id(),
            'kind' => 'script',
            'status' => AiGeneration::STATUS_PROCESSING,
        ]);

        GenerateSuggestionsJob::dispatch($generation->id, $context, (int) config('ai.script.suggestions', 5));

        $this->generationId = $generation->id;
    }

    /** Polling: cuando el job termina, carga las sugerencias (o el error) y deja de sondear. */
    public function pollGeneration(): void
    {
        if ($this->generationId === null) {
            return;
        }

        $generation = AiGeneration::find($this->generationId);

        if ($generation === null || $generation->isProcessing()) {
            return;
        }

        if ($generation->isDone()) {
            $this->suggestions = $generation->result ?? [];
        } else {
            $this->aiError = $generation->error ?: 'No se pudo generar. Inténtalo de nuevo.';
        }

        $this->generationId = null;
        $this->dispatch('ai-generation-done');
    }

    /** Crea una pieza por cada variante seleccionada (paso 3) y lleva al composer. */
    public function createPieces()
    {
        $indices = array_values(array_intersect(array_keys($this->suggestions), array_map('intval', $this->selected)));

        if (empty($indices)) {
            return null;
        }

        $idea = $this->selectedIdea();
        $base = $idea?->title ?: 'Pieza generada';
        $position = 0;

        foreach ($indices as $i) {
            $fields = $this->suggestions[$i]['fields'];
            $position++;

            $this->account->contentPieces()->create([
                'winning_idea_id' => $this->winning_idea_id ?: null,
                'ideal_follower_id' => $this->idealFollowerId ?: $idea?->ideal_follower_id,
                'title' => count($indices) > 1 ? "{$base} — variante {$position}" : $base,
                'objective' => $this->objective ?: null,
                'format' => $this->format ?: null,
                'status' => ContentStatus::Borrador->value,
                'hook' => $fields['hook'] ?? null,
                'story' => $fields['story'] ?? null,
                'moral' => $fields['moral'] ?? null,
                'cta' => $fields['cta'] ?? null,
            ]);
        }

        session()->flash('studio.flash', count($indices) === 1
            ? 'Se creó 1 pieza desde el generador.'
            : 'Se crearon '.count($indices).' piezas desde el generador.');

        return $this->redirect(route('studio.pieces', $this->account), navigate: true);
    }

    private function selectedIdea(): ?WinningIdea
    {
        if (! $this->winning_idea_id) {
            return null;
        }

        return $this->account->winningIdeas()
            ->with(['questions', 'herasTemplate'])
            ->find($this->winning_idea_id);
    }

    /**
     * @return Collection<int, HerasTemplate>
     */
    private function selectedTemplates()
    {
        if (empty($this->herasTemplateIds)) {
            return collect();
        }

        return HerasTemplate::query()->whereKey($this->herasTemplateIds)->get();
    }

    /** Abre el modal selector de ganchos, sembrando la selección actual. */
    public function openHookPicker(): void
    {
        $this->modalHookIds = $this->selectedHookIds;
        $this->modal('hook-picker')->show();
    }

    /** Confirma la selección del modal (máx. 5) y lo cierra. */
    public function confirmHooks(): void
    {
        $this->selectedHookIds = array_slice(array_values($this->modalHookIds), 0, 5);
        $this->modal('hook-picker')->close();
    }

    /** Quita un gancho de la selección (botón ✕ de la mini-tarjeta). */
    public function removeHook(int $id): void
    {
        $this->selectedHookIds = array_values(array_filter(
            $this->selectedHookIds,
            fn ($x): bool => (int) $x !== $id,
        ));
    }

    /**
     * Ganchos seleccionados (modelos), en el orden de selección.
     *
     * @return Collection<int, HookTemplate>
     */
    #[Computed]
    public function selectedHooks(): Collection
    {
        if (empty($this->selectedHookIds)) {
            return collect();
        }

        $ids = array_map('intval', $this->selectedHookIds);

        return HookTemplate::query()->whereKey($ids)->get()
            ->sortBy(fn (HookTemplate $h): int => array_search($h->id, $ids, true))
            ->values();
    }

    /**
     * Cada gancho seleccionado formateado para el prompt (nombre + objetivo + ejemplos).
     *
     * @return array<int, string>
     */
    private function hookLines(): array
    {
        return $this->selectedHooks->map(function (HookTemplate $h): string {
            $examples = collect([
                'Genérico' => $h->example_generic,
                'Salud' => $h->example_health,
                'Sexo' => $h->example_sex,
                'Dinero' => $h->example_money,
                'Desarrollo Personal' => $h->example_personal_dev,
            ])->filter()->map(fn (string $t, string $l): string => "[{$l}] {$t}")->implode(' | ');

            $line = 'Gancho «'.$h->name.'»';
            $line .= filled($h->objective) ? ' — objetivo: '.$h->objective : '';
            $line .= $examples !== '' ? '. Ejemplos: '.$examples : '';

            return $line;
        })->values()->all();
    }

    /**
     * CTA(s) seleccionada(s) formateada(s) para el prompt. Devuelve una lista aunque
     * de momento solo se elija una, para que añadir multi-selección sea trivial.
     *
     * @return array<int, string>
     */
    private function ctaLines(): array
    {
        if (! $this->selectedCtaId) {
            return [];
        }

        $cta = $this->account->contentCtas()->find($this->selectedCtaId);

        if ($cta === null) {
            return [];
        }

        return ['CTA ['.$cta->category->getLabel().'] «'.$cta->text.'» — objetivo: '.$cta->category->promptIntent()];
    }

    /**
     * Preguntas que se enviarán: las marcadas manualmente si hay seguidor elegido;
     * en caso contrario, las derivadas de la idea ganadora.
     *
     * @return array<int, string>
     */
    #[Computed]
    public function contextQuestions(): array
    {
        if ($this->idealFollowerId) {
            $ids = array_map('intval', $this->questionIds);

            return $this->followerQuestions
                ->whereIn('id', $ids)
                ->pluck('body')
                ->values()
                ->all();
        }

        return $this->selectedIdea()?->questions->pluck('body')->all() ?? [];
    }

    /**
     * Creencias que se enviarán: las marcadas manualmente si hay seguidor elegido;
     * en caso contrario, las derivadas de la idea ganadora.
     *
     * @return array<int, string>
     */
    #[Computed]
    public function contextBeliefs(): array
    {
        $label = fn (Belief $belief): string => '['.$belief->type->getLabel().'] '.$belief->statement;

        if ($this->idealFollowerId) {
            $ids = array_map('intval', $this->beliefIds);

            return $this->followerBeliefs
                ->whereIn('id', $ids)
                ->map($label)
                ->values()
                ->all();
        }

        $idea = $this->selectedIdea();

        return $idea ? $idea->derivedBeliefs()->map($label)->all() : [];
    }

    /**
     * Dolores/problemas/deseos que se enviarán: los marcados manualmente si hay
     * seguidor elegido; en caso contrario, los del seguidor de la idea ganadora.
     *
     * @return array<int, string>
     */
    #[Computed]
    public function contextPains(): array
    {
        $label = fn (Pain $pain): string => '['.$pain->type->getLabel().'] '.$pain->body;

        if ($this->idealFollowerId) {
            $ids = array_map('intval', $this->painIds);

            return $this->followerPains
                ->whereIn('id', $ids)
                ->map($label)
                ->values()
                ->all();
        }

        $idea = $this->selectedIdea();

        return $idea ? $idea->derivedPains()->map($label)->all() : [];
    }

    public function render(): View
    {
        return view('livewire.studio.piece-generator', [
            // Solo las ideas del seguidor elegido, con el nº de piezas ya creadas por idea.
            'ideas' => $this->idealFollowerId
                ? $this->account->winningIdeas()
                    ->where('ideal_follower_id', $this->idealFollowerId)
                    ->withCount('contentPieces')
                    ->orderBy('title')
                    ->get()
                : collect(),
            'followers' => $this->account->idealFollowers()->orderBy('name')->get(),
            'referents' => ViralReferent::query()->orderBy('name')->get(),
            'templates' => HerasTemplate::query()
                ->when($this->referentFilter, fn ($q) => $q->where('viral_referent_id', $this->referentFilter))
                ->orderBy('number')
                ->get(),
            // Ganchos de la marca + los globales de referencia (account_id nulo).
            'hooks' => HookTemplate::query()
                ->with('viralReferent')
                ->where(fn ($q) => $q->whereNull('account_id')->orWhere('account_id', $this->account->id))
                ->when($this->hookReferentFilter, fn ($q) => $q->where('viral_referent_id', $this->hookReferentFilter))
                ->when(filled($this->hookSearch), fn ($q) => $q->where(fn ($w) => $w
                    ->where('name', 'like', '%'.$this->hookSearch.'%')
                    ->orWhere('objective', 'like', '%'.$this->hookSearch.'%')))
                ->orderBy('name')
                ->get(),
            'hookReferents' => ViralReferent::query()->whereHas('hookTemplates')->orderBy('name')->get(),
            'ctas' => $this->account->contentCtas()->orderBy('category')->orderBy('id')->get(),
            'excerpt' => fn (?string $text): string => Str::limit((string) $text, 90),
        ]);
    }
}
