<?php

namespace App\Livewire\Studio;

use App\Models\Account;
use App\Models\HookTemplate;
use App\Models\ViralReferent;
use App\Support\FontAwesomeIcons;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Gestor de ganchos propios de la marca en el Estudio (account_id = marca activa).
 * Los ganchos globales de referencia (account_id nulo) se gestionan en el admin y
 * aquí no aparecen; sí están disponibles en el Generador de piezas.
 */
#[Layout('components.layouts.studio')]
class HookManager extends Component
{
    public Account $account;

    public ?int $selectedId = null;

    // Campos del gancho seleccionado (autoguardado).
    public string $name = '';

    public ?string $objective = null;

    public ?string $notes = null;

    public ?string $icon = null;

    public ?string $example = null; // se guarda en example_generic

    // Referente viral opcional (sin tipar para tolerar el "" de Flux).
    public $viral_referent_id = null;

    /** @var array<int, string> ejemplos reales (URLs) */
    public array $exampleUrls = [];

    public string $newExampleUrl = '';

    public bool $saved = false;

    public function mount(Account $account): void
    {
        // Sin selección por defecto (el usuario elige un gancho de la lista).
        $this->account = $account;
    }

    public function newHook(): void
    {
        $hook = $this->account->hookTemplates()->create(['name' => 'Nuevo gancho']);
        $this->loadHook($hook);
    }

    public function selectHook(int $id): void
    {
        $hook = $this->account->hookTemplates()->find($id);

        if ($hook !== null) {
            $this->loadHook($hook);
        }
    }

    public function deleteHook(int $id): void
    {
        // Borrado reservado a administradores de la marca (como en el resto del Estudio).
        if (! $this->canDelete()) {
            return;
        }

        $this->account->hookTemplates()->whereKey($id)->delete();

        if ($this->selectedId === $id) {
            $this->reset(['selectedId', 'name', 'objective', 'notes', 'icon', 'example', 'viral_referent_id', 'exampleUrls']);
        }
    }

    private function loadHook(HookTemplate $hook): void
    {
        $this->selectedId = $hook->id;
        $this->name = $hook->name ?? '';
        $this->objective = $hook->objective;
        $this->notes = $hook->notes;
        $this->icon = $hook->icon;
        $this->example = $hook->example_generic;
        $this->viral_referent_id = $hook->viral_referent_id;
        $this->exampleUrls = array_values($hook->real_examples ?? []);
        $this->newExampleUrl = '';
        $this->saved = false;
    }

    public function updated(string $name): void
    {
        if ($this->currentHook() === null) {
            return;
        }

        if (in_array($name, ['name', 'objective', 'notes', 'icon', 'example', 'viral_referent_id'], true)) {
            $this->saveHook();

            return;
        }

        if (str_starts_with($name, 'exampleUrls')) {
            $this->persistExamples();
        }
    }

    private function saveHook(): void
    {
        $this->currentHook()?->update([
            'name' => trim($this->name) ?: 'Sin nombre',
            'objective' => $this->objective ?: null,
            'notes' => $this->notes ?: null,
            'icon' => $this->icon ?: null,
            'example_generic' => $this->example ?: null,
            'viral_referent_id' => $this->viral_referent_id ?: null,
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

        $this->currentHook()?->update(['real_examples' => $urls]);
        $this->saved = true;
    }

    private function currentHook(): ?HookTemplate
    {
        return $this->selectedId === null
            ? null
            : $this->account->hookTemplates()->find($this->selectedId);
    }

    public function canDelete(): bool
    {
        return auth()->user()->isAdminOf($this->account);
    }

    public function render(): View
    {
        return view('livewire.studio.hook-manager', [
            'hooks' => $this->account->hookTemplates()->orderBy('name')->get(),
            'referents' => ViralReferent::query()->orderBy('name')->get(),
            'icons' => FontAwesomeIcons::ICONS,
        ]);
    }
}
