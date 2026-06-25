<?php

namespace App\Livewire\Studio;

use App\Jobs\GenerateSuggestionsJob;
use App\Models\Account;
use App\Models\AiGeneration;
use App\Support\Ai\BrandContext;
use App\Support\Ai\ContentAssistant;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * AI Kickstart (solo Estudio): a partir de la información de la marca, la IA propone
 * 3 hipótesis de seguidor ideal (cada una con dolores, preguntas y mitos + nivel de
 * conciencia). El usuario elige cuáles guardar; se crean como registros reales.
 */
#[Layout('components.layouts.studio')]
class IdealFollowerKickstart extends Component
{
    public Account $account;

    public ?string $instructions = null;

    /** @var array<int, array<string, mixed>> */
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
    public function brandReady(): bool
    {
        return BrandContext::fromAccount($this->account)->hasMaterial();
    }

    #[Computed]
    public function generating(): bool
    {
        return $this->generationId !== null;
    }

    /** Encola la generación de hipótesis de seguidor ideal. */
    public function generate(): void
    {
        $this->aiError = null;
        $this->suggestions = [];
        $this->selected = [];

        if (! $this->aiEnabled) {
            return;
        }

        $context = BrandContext::fromAccount($this->account);
        $context->extra = $this->instructions;

        $generation = AiGeneration::create([
            'account_id' => $this->account->getKey(),
            'user_id' => auth()->id(),
            'kind' => 'kickstart',
            'status' => AiGeneration::STATUS_PROCESSING,
        ]);

        GenerateSuggestionsJob::dispatch($generation->id, $context, (int) config('ai.kickstart.suggestions', 3));

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

    /** Crea un seguidor ideal (con sus preguntas/creencias/dolores) por cada hipótesis elegida. */
    public function createFollowers()
    {
        $indices = array_values(array_intersect(array_keys($this->suggestions), array_map('intval', $this->selected)));

        if (empty($indices)) {
            return null;
        }

        foreach ($indices as $i) {
            $h = $this->suggestions[$i];

            $follower = $this->account->idealFollowers()->create([
                'name' => $h['name'] ?? 'Seguidor generado',
                'description' => $h['description'] ?? null,
                'awareness_level' => $h['awareness_level'] ?? null,
            ]);

            foreach ($h['questions'] ?? [] as $body) {
                $follower->questions()->create(['account_id' => $this->account->getKey(), 'body' => $body]);
            }

            foreach ($h['beliefs'] ?? [] as $statement) {
                $follower->beliefs()->create(['account_id' => $this->account->getKey(), 'type' => 'myth', 'statement' => $statement]);
            }

            foreach ($h['pains'] ?? [] as $pain) {
                $follower->pains()->create([
                    'account_id' => $this->account->getKey(),
                    'type' => $pain['type'] ?? 'pain',
                    'body' => $pain['body'] ?? '',
                ]);
            }
        }

        session()->flash('studio.flash', count($indices) === 1
            ? 'Se creó 1 seguidor ideal con su audiencia.'
            : 'Se crearon '.count($indices).' seguidores ideales con su audiencia.');

        return $this->redirect(route('studio.audience', $this->account), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.studio.ideal-follower-kickstart');
    }
}
