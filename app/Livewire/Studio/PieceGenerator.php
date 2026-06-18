<?php

namespace App\Livewire\Studio;

use App\Enums\ContentFormat;
use App\Enums\ContentObjective;
use App\Enums\ContentStatus;
use App\Models\Account;
use App\Models\Belief;
use App\Models\HerasTemplate;
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
use Throwable;

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

    // Ideas Ganadoras Referenciales (HerasTemplate) + filtro por Referente.
    public ?int $referentFilter = null;

    /** @var array<int, int|string> */
    public array $herasTemplateIds = [];

    // Paso 2-3 — sugerencias y selección.
    /** @var array<int, array{label: string, fields: array<string, string>, preview: string}> */
    public array $suggestions = [];

    /** @var array<int, int|string> */
    public array $selected = [];

    public ?string $aiError = null;

    public function mount(Account $account): void
    {
        $this->account = $account;
    }

    #[Computed]
    public function aiEnabled(): bool
    {
        return app(ContentAssistant::class)->isConfigured();
    }

    /** Al cambiar de seguidor, reinicia las preguntas/creencias elegidas. */
    public function updatedIdealFollowerId(): void
    {
        $this->questionIds = [];
        $this->beliefIds = [];
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
            ->with('beliefs')
            ->orderBy('body')
            ->get();
    }

    /**
     * Creencias (mitos/verdades) ligadas a las preguntas de ese seguidor.
     *
     * @return Collection<int, Belief>
     */
    #[Computed]
    public function followerBeliefs(): Collection
    {
        return $this->followerQuestions
            ->flatMap->beliefs
            ->unique('id')
            ->sortBy('statement')
            ->values();
    }

    /** Pide a la IA las variantes de guión (paso 2). Puede regenerarse. */
    public function generate(): void
    {
        $this->aiError = null;
        $this->suggestions = [];
        $this->selected = [];

        $assistant = app(ContentAssistant::class);

        if (! $assistant->isConfigured()) {
            return;
        }

        $context = ScriptContext::fromIdea($this->selectedIdea());
        $context->objective = $this->objective ? ContentObjective::tryFrom($this->objective)?->getLabel() : null;
        $context->format = $this->format ? ContentFormat::tryFrom($this->format)?->getLabel() : null;
        $context->extra = $this->instructions;
        // Si el usuario eligió seguidor + preguntas/creencias manualmente, manda esas.
        $context->questions = $this->contextQuestions;
        $context->beliefs = $this->contextBeliefs;
        $context->templates = collect($context->templates)
            ->merge(ScriptContext::templateLines($this->selectedTemplates()))
            ->unique()
            ->values()
            ->all();

        try {
            $suggestions = $assistant->suggestScripts($context, (int) config('ai.script.suggestions', 5));
            $this->suggestions = array_map(fn ($s): array => $s->toArray(), $suggestions);
        } catch (Throwable $e) {
            $this->aiError = $e->getMessage();
        }
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
                'title' => count($indices) > 1 ? "{$base} — variante {$position}" : $base,
                'objective' => $this->objective ?: null,
                'format' => $this->format ?: null,
                'status' => ContentStatus::Planificacion->value,
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
            ->with(['questions.beliefs', 'herasTemplate'])
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

    public function render(): View
    {
        return view('livewire.studio.piece-generator', [
            'ideas' => $this->account->winningIdeas()->orderBy('title')->get(),
            'followers' => $this->account->idealFollowers()->orderBy('name')->get(),
            'referents' => ViralReferent::query()->orderBy('name')->get(),
            'templates' => HerasTemplate::query()
                ->when($this->referentFilter, fn ($q) => $q->where('viral_referent_id', $this->referentFilter))
                ->orderBy('number')
                ->get(),
            'excerpt' => fn (?string $text): string => Str::limit((string) $text, 90),
        ]);
    }
}
