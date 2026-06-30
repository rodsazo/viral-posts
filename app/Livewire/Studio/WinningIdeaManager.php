<?php

namespace App\Livewire\Studio;

use App\Enums\ContentStatus;
use App\Enums\IdeaStatus;
use App\Enums\ValidationStatus;
use App\Enums\ViralMechanism;
use App\Models\Account;
use App\Models\HerasTemplate;
use App\Models\WinningIdea;
use App\Support\StudioPeriod;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * CRUD de Ideas Ganadoras en el Estudio: lista a la izquierda, editor con autoguardado a
 * la derecha. La idea ganadora es una descripción de FORMATO (título + estructura/concepto
 * + estado + ejemplos reales); ya NO se relaciona con seguidor, preguntas ni mitos (eso se
 * elige al generar la pieza). Una idea con al menos un ejemplo queda "Validada".
 */
#[Layout('components.layouts.studio')]
class WinningIdeaManager extends Component
{
    public Account $account;

    public ?int $selectedId = null;

    // Campos de la idea seleccionada (autoguardado).
    public string $title = '';

    public ?string $concept = null;

    // Estado del flujo de la idea (borrador/hipótesis/fija/descartada). Sin tipar por Flux.
    public $status = 'borrador';

    // Ocultos por ahora en el editor, pero conservados en BD.
    public ?string $viral_mechanism = null;

    public ?int $heras_template_id = null;

    /** @var array<int, string> ejemplos reales (URLs), lista plana */
    public array $exampleUrls = [];

    public string $newExampleUrl = '';

    // Filtro por estado: '' = Activas (oculta descartadas), 'todas' = todas, o un estado concreto.
    public string $filterStatus = '';

    public bool $saved = false;

    public function mount(Account $account): void
    {
        // Sin selección por defecto: el usuario elige una idea (evita confusión al abrir).
        $this->account = $account;
    }

    public function newIdea(): void
    {
        $idea = $this->account->winningIdeas()->create([
            'title' => 'Nueva idea ganadora',
            'concept' => '',
            'status' => IdeaStatus::Borrador->value,
        ]);

        $this->loadIdea($idea);
    }

    public function selectIdea(int $id): void
    {
        $idea = $this->account->winningIdeas()->find($id);

        if ($idea !== null) {
            $this->loadIdea($idea);
        }
    }

    public function deleteIdea(int $id): void
    {
        // El borrado queda reservado a administradores de la marca (igual que en el admin).
        if (! auth()->user()->isAdminOf($this->account)) {
            return;
        }

        $this->account->winningIdeas()->whereKey($id)->delete();

        if ($this->selectedId === $id) {
            $this->reset(['selectedId', 'title', 'concept', 'status', 'viral_mechanism', 'heras_template_id', 'exampleUrls']);
            $next = $this->account->winningIdeas()->orderBy('title')->first();

            if ($next !== null) {
                $this->loadIdea($next);
            }
        }
    }

    private function loadIdea(WinningIdea $idea): void
    {
        $this->selectedId = $idea->id;
        $this->title = $idea->title;
        $this->concept = $idea->concept;
        $this->status = $idea->status?->value ?? IdeaStatus::Borrador->value;
        $this->viral_mechanism = $idea->viral_mechanism?->value;
        $this->heras_template_id = $idea->heras_template_id;
        $this->exampleUrls = array_values($idea->example_urls ?? []);
        $this->newExampleUrl = '';
        $this->saved = false;
    }

    public function updated(string $name): void
    {
        if ($this->currentIdea() === null) {
            return;
        }

        if (in_array($name, ['title', 'concept', 'status', 'viral_mechanism', 'heras_template_id'], true)) {
            $this->saveIdea();

            return;
        }

        if (str_starts_with($name, 'exampleUrls')) {
            $this->persistExamples();
        }
    }

    private function saveIdea(): void
    {
        $this->currentIdea()?->update([
            'title' => trim($this->title) ?: 'Sin título',
            'concept' => (string) $this->concept,
            'status' => IdeaStatus::tryFrom($this->status)?->value ?? IdeaStatus::Borrador->value,
            'viral_mechanism' => $this->viral_mechanism ?: null,
            'heras_template_id' => $this->heras_template_id ?: null,
        ]);

        $this->saved = true;
    }

    public function addExampleUrl(): void
    {
        $url = trim($this->newExampleUrl);

        if ($url === '') {
            return;
        }

        $this->exampleUrls[] = $url;
        $this->newExampleUrl = '';
        $this->persistExamples();
    }

    public function removeExampleUrl(int $index): void
    {
        unset($this->exampleUrls[$index]);
        $this->exampleUrls = array_values($this->exampleUrls);
        $this->persistExamples();
    }

    private function persistExamples(): void
    {
        $urls = array_values(array_filter(array_map('trim', $this->exampleUrls), fn (string $u): bool => $u !== ''));

        $this->currentIdea()?->update(['example_urls' => $urls]);
        $this->saved = true;
    }

    private function currentIdea(): ?WinningIdea
    {
        return $this->selectedId === null
            ? null
            : $this->account->winningIdeas()->find($this->selectedId);
    }

    /** Estado de validación en vivo (según los ejemplos actuales en pantalla). */
    #[Computed]
    public function validationStatus(): ValidationStatus
    {
        $hasExample = filled(array_filter(array_map('trim', $this->exampleUrls), fn (string $u): bool => $u !== ''));

        return $hasExample ? ValidationStatus::Validated : ValidationStatus::Pending;
    }

    /** Atajo: crea una pieza con esta idea ya elegida y salta al Composer. */
    public function createPieceFromIdea(int $id)
    {
        $idea = $this->account->winningIdeas()->find($id);

        if ($idea === null) {
            return null;
        }

        $piece = $this->account->contentPieces()->create([
            'title' => $idea->title,
            'winning_idea_id' => $idea->id,
            'status' => ContentStatus::Borrador->value,
            'period_id' => StudioPeriod::id($this->account),
        ]);

        session()->flash('studio.flash', 'Pieza creada desde la idea. ¡A escribir el guión!');

        return $this->redirect(route('studio.pieces', ['account' => $this->account, 'piece' => $piece->id]), navigate: true);
    }

    public function canDelete(): bool
    {
        return auth()->user()->isAdminOf($this->account);
    }

    public function render(): View
    {
        return view('livewire.studio.winning-idea-manager', [
            'ideas' => $this->account->winningIdeas()
                // Por defecto ('') ocultamos las descartadas; 'todas' muestra todo; o un estado concreto.
                ->when($this->filterStatus === '', fn ($q) => $q->where('status', '!=', IdeaStatus::Descartada->value))
                ->when(! in_array($this->filterStatus, ['', 'todas'], true), fn ($q) => $q->where('status', $this->filterStatus))
                ->orderBy('title')
                ->get()
                // Orden por estado: Fija → Hipótesis → Borrador → Descartada (estable: respeta el título dentro de cada grupo).
                ->sortBy(fn ($idea) => $idea->status->sortPriority())
                ->values(),
            'ideaStatuses' => IdeaStatus::cases(),
            'mechanisms' => ViralMechanism::cases(),
            'herasTemplates' => HerasTemplate::query()->orderBy('name')->get(),
        ]);
    }
}
