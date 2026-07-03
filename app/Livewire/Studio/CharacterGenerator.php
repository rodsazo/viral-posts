<?php

namespace App\Livewire\Studio;

use App\Jobs\GenerateCharacterJob;
use App\Models\Account;
use App\Models\AiGeneration;
use App\Models\IdealFollower;
use App\Support\Ai\CharacterContext;
use App\Support\Ai\ContentAssistant;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Generador de Personajes de Marca (Estudio): un formulario que precarga lo que el app ya
 * sabe de la marca (promesa, ofertas, cliente ideal, audiencia desde los seguidores ideales)
 * y pide los insumos NUEVOS que exige el framework (destino/CTAs reales, hechos de la historia
 * de origen, arco de origen). La IA devuelve un personaje 100% construido que se guarda como
 * entidad y se abre en el editor para pulirlo.
 */
#[Layout('components.layouts.studio')]
class CharacterGenerator extends Component
{
    public Account $account;

    public ?string $desiredName = null;

    public ?string $brandPromise = null;

    public ?string $mainOffers = null;

    public ?string $idealCustomerProfile = null;

    public ?string $audienceNotes = null;

    public ?string $conversionDestination = null;

    public ?string $validActions = null;

    public bool $isTopOfFunnel = false;

    public ?string $parentBrand = null;

    public ?string $originFacts = null;

    // Arco de origen: "sufria" (víctima) | "causaba" (converso) | "" (sin definir).
    public string $convertArc = '';

    public ?string $extra = null;

    public ?string $aiError = null;

    public ?int $generationId = null;

    public function mount(Account $account): void
    {
        $this->account = $account;
        // Precarga desde la marca: no volvemos a preguntar lo que ya sabemos.
        $this->brandPromise = $account->brand_promise;
        $this->mainOffers = $account->main_offers;
        $this->idealCustomerProfile = $account->ideal_customer_profile;
        $this->audienceNotes = $this->deriveAudience();
    }

    /** Compone la audiencia a partir de los seguidores ideales de la marca y sus dolores. */
    private function deriveAudience(): ?string
    {
        $lines = $this->account->idealFollowers()->orderBy('name')->get()
            ->map(function (IdealFollower $f): string {
                $line = '- '.$f->name.(filled($f->description) ? ': '.$f->description : '');
                $pains = $f->pains()->orderBy('type')->pluck('body')->take(4)->all();

                return filled($pains) ? $line.' (motivos: '.implode('; ', $pains).')' : $line;
            })->all();

        return filled($lines) ? implode("\n", $lines) : null;
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

    #[Computed]
    public function canGenerate(): bool
    {
        return $this->aiEnabled && filled($this->brandPromise);
    }

    public function generate(): void
    {
        $this->aiError = null;

        if (! $this->canGenerate || $this->generationId !== null) {
            return;
        }

        $context = new CharacterContext(
            desiredName: $this->desiredName,
            brandName: $this->account->name,
            brandDescription: $this->account->description,
            brandPromise: $this->brandPromise,
            mainOffers: $this->mainOffers,
            idealCustomerProfile: $this->idealCustomerProfile,
            audience: $this->audienceLines(),
            conversionDestination: $this->conversionDestination,
            validActions: $this->validActions,
            isTopOfFunnel: $this->isTopOfFunnel,
            parentBrand: $this->parentBrand,
            originFacts: $this->originFacts,
            convertArc: $this->arcLabel(),
            extra: $this->extra,
        );

        $generation = AiGeneration::create([
            'account_id' => $this->account->getKey(),
            'user_id' => auth()->id(),
            'kind' => 'character',
            'status' => AiGeneration::STATUS_PROCESSING,
        ]);

        GenerateCharacterJob::dispatch($generation->id, $context);

        $this->generationId = $generation->id;
    }

    public function pollGeneration()
    {
        if ($this->generationId === null) {
            return null;
        }

        $generation = AiGeneration::find($this->generationId);

        if ($generation === null || $generation->isProcessing()) {
            return null;
        }

        if (! $generation->isDone()) {
            $this->aiError = $generation->error ?: 'No se pudo generar. Inténtalo de nuevo.';
            $this->generationId = null;

            return null;
        }

        // Crea la entidad con el resultado + un nombre (el deseado gana) + snapshot de insumos.
        $data = $generation->result ?? [];
        $data['name'] = trim((string) $this->desiredName) ?: ($data['name'] ?? 'Personaje');
        $data['generation_inputs'] = [
            'brand_promise' => $this->brandPromise,
            'main_offers' => $this->mainOffers,
            'ideal_customer_profile' => $this->idealCustomerProfile,
            'audience' => $this->audienceNotes,
            'conversion_destination' => $this->conversionDestination,
            'valid_actions' => $this->validActions,
            'is_top_of_funnel' => $this->isTopOfFunnel,
            'parent_brand' => $this->parentBrand,
            'origin_facts' => $this->originFacts,
            'convert_arc' => $this->arcLabel(),
            'extra' => $this->extra,
        ];

        $character = $this->account->brandCharacters()->create($data);

        $this->generationId = null;
        $this->dispatch('ai-generation-done');

        session()->flash('studio.flash', 'Personaje generado. Revísalo y púlelo aquí.');

        return $this->redirect(route('studio.brand-characters', ['account' => $this->account, 'character' => $character->id]), navigate: true);
    }

    /** @return array<int, string> */
    private function audienceLines(): array
    {
        return collect(preg_split('/\r?\n/', (string) $this->audienceNotes))
            ->map(fn (string $l): string => trim(ltrim($l, "-• \t")))
            ->filter()
            ->values()
            ->all();
    }

    private function arcLabel(): ?string
    {
        return match ($this->convertArc) {
            'sufria' => 'Estaba del lado que SUFRÍA el problema (arco de víctima)',
            'causaba' => 'Estaba del lado que CAUSABA el problema sin saberlo (arco de converso)',
            default => null,
        };
    }

    public function render(): View
    {
        return view('livewire.studio.character-generator');
    }
}
