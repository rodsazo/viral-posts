<?php

namespace App\Livewire\Studio;

use App\Enums\IdeaStatus;
use App\Models\Account;
use App\Models\HerasTemplate;
use App\Models\Niche;
use App\Models\ViralReferent;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Ideas Referenciales: catálogo global de ideas ganadoras de referencia (plantillas Heras)
 * que el usuario puede filtrar por referente o nicho e IMPORTAR a su marca como Ideas
 * Ganadoras regulares (en estado Borrador, marcadas como importadas y con su referente y
 * todas sus URLs de referencia copiadas).
 */
#[Layout('components.layouts.studio')]
class ReferenceIdeaImporter extends Component
{
    public Account $account;

    // Filtros (sin tipar para tolerar el "" de los selects de Flux).
    public $referentFilter = null;

    public $nicheFilter = null;

    /** @var array<int, int|string> plantillas seleccionadas para importar */
    public array $selected = [];

    public function mount(Account $account): void
    {
        $this->account = $account;
    }

    public function import()
    {
        $templates = HerasTemplate::query()->whereKey($this->selected)->get();

        if ($templates->isEmpty()) {
            return null;
        }

        foreach ($templates as $template) {
            $urls = collect([$template->reference_url])
                ->merge($template->reference_urls ?? [])
                ->map(fn ($u): string => trim((string) $u))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $this->account->winningIdeas()->create([
                'title' => $template->name,
                'concept' => (string) $template->structure,
                'status' => IdeaStatus::Borrador->value,
                'imported_at' => now(),
                'viral_referent_id' => $template->viral_referent_id,
                'example_urls' => $urls,
            ]);
        }

        $count = $templates->count();
        session()->flash('studio.flash', $count === 1
            ? 'Se importó 1 idea referencial como idea ganadora (en Borrador).'
            : "Se importaron {$count} ideas referenciales como ideas ganadoras (en Borrador).");

        return $this->redirect(route('studio.winning-ideas', $this->account), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.studio.reference-idea-importer', [
            'templates' => HerasTemplate::query()
                ->with('viralReferent.niche')
                ->when($this->referentFilter, fn ($q, $id) => $q->where('viral_referent_id', $id))
                ->when($this->nicheFilter, fn ($q, $id) => $q->whereHas('viralReferent', fn ($r) => $r->where('niche_id', $id)))
                ->orderBy('name')
                ->get(),
            'referents' => ViralReferent::query()->whereHas('herasTemplates')->orderBy('name')->get(),
            'niches' => Niche::query()->orderBy('name')->get(),
        ]);
    }
}
