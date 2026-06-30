<?php

namespace App\Livewire\Studio;

use App\Jobs\GenerateSuggestionsJob;
use App\Models\Account;
use App\Models\AiGeneration;
use App\Models\Belief;
use App\Models\Pain;
use App\Models\Question;
use App\Support\Ai\ContentAssistant;
use App\Support\Ai\IdeaContext;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Generador de ideas ganadoras (solo Estudio): seguidor ideal → preguntas/creencias
 * elegidas → la IA propone ideas → el usuario guarda una o varias como WinningIdea
 * (enlazando las preguntas para conservar la visibilidad multi-salto).
 */
#[Layout('components.layouts.studio')]
class IdeaGenerator extends Component
{
    public Account $account;

    public ?int $idealFollowerId = null;

    /** @var array<int, int|string> */
    public array $questionIds = [];

    /** @var array<int, int|string> */
    public array $beliefIds = [];

    /** @var array<int, int|string> */
    public array $painIds = [];

    public ?string $instructions = null;

    /** @var array<int, array{label: string, fields: array<string, string>, preview: string}> */
    public array $suggestions = [];

    /** @var array<int, int|string> */
    public array $selected = [];

    public ?string $aiError = null;

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

    #[Computed]
    public function generating(): bool
    {
        return $this->generationId !== null;
    }

    public function updatedIdealFollowerId(): void
    {
        $this->questionIds = [];
        $this->beliefIds = [];
        $this->painIds = [];
    }

    /**
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
     * Dolores / problemas / deseos del seguidor.
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

    /** @return array<int, string> */
    #[Computed]
    public function contextQuestions(): array
    {
        if (! $this->idealFollowerId || empty($this->questionIds)) {
            return [];
        }

        $ids = array_map('intval', $this->questionIds);

        return $this->followerQuestions->whereIn('id', $ids)->pluck('body')->values()->all();
    }

    /** @return array<int, string> */
    #[Computed]
    public function contextBeliefs(): array
    {
        if (! $this->idealFollowerId || empty($this->beliefIds)) {
            return [];
        }

        $ids = array_map('intval', $this->beliefIds);

        return $this->followerBeliefs
            ->whereIn('id', $ids)
            ->map(fn (Belief $b): string => '['.$b->type->getLabel().'] '.$b->statement)
            ->values()
            ->all();
    }

    /** @return array<int, string> */
    #[Computed]
    public function contextPains(): array
    {
        if (! $this->idealFollowerId || empty($this->painIds)) {
            return [];
        }

        $ids = array_map('intval', $this->painIds);

        return $this->followerPains
            ->whereIn('id', $ids)
            ->map(fn (Pain $p): string => '['.$p->type->getLabel().'] '.$p->body)
            ->values()
            ->all();
    }

    #[Computed]
    public function canGenerate(): bool
    {
        return $this->aiEnabled
            && (filled($this->contextQuestions) || filled($this->contextBeliefs) || filled($this->contextPains) || filled($this->instructions));
    }

    /** Encola la generación de ideas. Puede regenerarse. */
    public function generate(): void
    {
        $this->aiError = null;
        $this->suggestions = [];
        $this->selected = [];

        if (! $this->canGenerate) {
            return;
        }

        $context = new IdeaContext(
            questions: $this->contextQuestions,
            beliefs: $this->contextBeliefs,
            pains: $this->contextPains,
            brandPromise: $this->account->brand_promise,
            mainOffers: $this->account->main_offers,
            extra: $this->instructions,
        );

        $generation = AiGeneration::create([
            'account_id' => $this->account->getKey(),
            'user_id' => auth()->id(),
            'kind' => 'idea',
            'status' => AiGeneration::STATUS_PROCESSING,
        ]);

        GenerateSuggestionsJob::dispatch($generation->id, $context, (int) config('ai.idea.suggestions', 3));

        $this->generationId = $generation->id;
    }

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

    /** Crea una WinningIdea por cada sugerencia elegida y la enlaza a las preguntas. */
    public function createIdeas()
    {
        $indices = array_values(array_intersect(array_keys($this->suggestions), array_map('intval', $this->selected)));

        if (empty($indices)) {
            return null;
        }

        $questionIds = array_map('intval', $this->questionIds);

        foreach ($indices as $i) {
            $fields = $this->suggestions[$i]['fields'];

            $idea = $this->account->winningIdeas()->create([
                'ideal_follower_id' => $this->idealFollowerId,
                'title' => $fields['title'] ?? 'Idea generada',
                'concept' => $fields['concept'] ?? '',
                'viral_mechanism' => $fields['viral_mechanism'] ?? null,
            ]);

            if (filled($questionIds)) {
                $idea->questions()->syncWithoutDetaching($questionIds);
            }
        }

        session()->flash('studio.flash', count($indices) === 1
            ? 'Se creó 1 idea ganadora.'
            : 'Se crearon '.count($indices).' ideas ganadoras.');

        // Llevamos al generador: las nuevas ideas ya están disponibles para crear piezas.
        return $this->redirect(route('studio.generator', $this->account), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.studio.idea-generator', [
            'followers' => $this->account->idealFollowers()->orderBy('name')->get(),
        ]);
    }
}
