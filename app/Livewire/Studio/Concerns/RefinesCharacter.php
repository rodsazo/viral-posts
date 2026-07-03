<?php

namespace App\Livewire\Studio\Concerns;

use App\Jobs\RefineCharacterJob;
use App\Models\AiGeneration;
use App\Models\CharacterRefinement;
use App\Support\Ai\ContentAssistant;
use App\Support\Ai\RefineCharacterContext;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;

/**
 * Chat de refinamiento conversacional de un Personaje de Marca, para componer en el editor
 * (BrandCharacterManager). El usuario pide ajustes y la IA propone una versión revisada del
 * documento; solo se aplica al elegirla. Refina sobre la versión GUARDADA del personaje.
 *
 * Requiere del componente anfitrión: `public Account $account`, `?int $selectedId`,
 * y el método `loadCharacter(BrandCharacter $c)` para recargar el formulario al aplicar.
 */
trait RefinesCharacter
{
    public string $refineInstruction = '';

    public ?int $refineGenerationId = null;

    public ?string $refineError = null;

    protected function resetRefineState(): void
    {
        $this->refineInstruction = '';
        $this->refineGenerationId = null;
        $this->refineError = null;
        unset($this->characterRefinements);
    }

    /**
     * @return Collection<int, CharacterRefinement>
     */
    #[Computed]
    public function characterRefinements(): Collection
    {
        if ($this->selectedId === null) {
            return collect();
        }

        return CharacterRefinement::where('brand_character_id', $this->selectedId)->orderBy('id')->get();
    }

    #[Computed]
    public function refining(): bool
    {
        return $this->refineGenerationId !== null;
    }

    #[Computed]
    public function aiEnabled(): bool
    {
        return app(ContentAssistant::class)->isConfigured();
    }

    public function sendCharacterRefinement(): void
    {
        $instruction = trim($this->refineInstruction);

        if ($instruction === '' || $this->selectedId === null || $this->refineGenerationId !== null) {
            return;
        }

        if (! app(ContentAssistant::class)->isConfigured()) {
            return;
        }

        $character = $this->account->brandCharacters()->find($this->selectedId);

        if ($character === null) {
            return;
        }

        $history = $this->characterRefinements
            ->map(fn (CharacterRefinement $r): array => ['role' => $r->role, 'body' => $r->body])
            ->all();

        $context = new RefineCharacterContext(
            instruction: $instruction,
            characterDocument: $character->toFullDocument(),
            history: $history,
        );

        CharacterRefinement::create([
            'brand_character_id' => $character->id,
            'user_id' => auth()->id(),
            'role' => CharacterRefinement::ROLE_USER,
            'body' => $instruction,
        ]);

        $generation = AiGeneration::create([
            'account_id' => $this->account->getKey(),
            'user_id' => auth()->id(),
            'kind' => 'character-refine',
            'status' => AiGeneration::STATUS_PROCESSING,
        ]);

        RefineCharacterJob::dispatch($generation->id, $context);

        $this->refineGenerationId = $generation->id;
        $this->refineInstruction = '';
        $this->refineError = null;
        unset($this->characterRefinements);
    }

    public function pollCharacterRefinement(): void
    {
        if ($this->refineGenerationId === null) {
            return;
        }

        $generation = AiGeneration::find($this->refineGenerationId);

        if ($generation === null || $generation->isProcessing()) {
            return;
        }

        if ($generation->isDone() && $this->selectedId !== null) {
            $result = $generation->result ?? [];

            CharacterRefinement::create([
                'brand_character_id' => $this->selectedId,
                'role' => CharacterRefinement::ROLE_ASSISTANT,
                'body' => $result['note'] ?? null,
                'proposal' => $result['fields'] ?? null,
            ]);
        } elseif (! $generation->isDone()) {
            $this->refineError = $generation->error ?: 'No se pudo refinar. Inténtalo de nuevo.';
        }

        $this->refineGenerationId = null;
        unset($this->characterRefinements);
        $this->dispatch('ai-generation-done');
    }

    /** Aplica la versión propuesta al personaje (conserva el nombre actual) y recarga el editor. */
    public function applyCharacterRefinement(int $id): void
    {
        $refinement = $this->characterRefinements->firstWhere('id', $id);

        if ($refinement === null || ! $refinement->isAssistant() || ! filled($refinement->proposal)) {
            return;
        }

        $character = $this->account->brandCharacters()->find($this->selectedId);

        if ($character === null) {
            return;
        }

        $fields = $refinement->proposal;
        unset($fields['name']); // no renombramos el personaje al aplicar

        $character->update($fields);
        $this->loadCharacter($character);
    }

    public function resetCharacterRefinements(): void
    {
        if ($this->selectedId === null) {
            return;
        }

        CharacterRefinement::where('brand_character_id', $this->selectedId)->delete();

        $this->refineGenerationId = null;
        $this->refineError = null;
        unset($this->characterRefinements);
    }
}
