<?php

namespace App\Livewire\Studio;

use App\Models\Account;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Paleta de comandos del Estudio (⌘K / Ctrl+K): saltar a una pieza o idea por nombre,
 * y atajos a las secciones (generadores, composer, kanban, etc.). Vive en la cabecera.
 */
class CommandPalette extends Component
{
    public Account $account;

    public string $query = '';

    public function mount(Account $account): void
    {
        $this->account = $account;
    }

    public function clear(): void
    {
        $this->query = '';
    }

    /** @return array<int, array{label: string, href: string, icon: string}> */
    private function shortcuts(): array
    {
        $a = $this->account;

        return [
            ['label' => 'Inicio', 'href' => route('studio.home', $a), 'icon' => 'home'],
            ['label' => 'Composer (piezas)', 'href' => route('studio.pieces', $a), 'icon' => 'document-text'],
            ['label' => 'Generador de piezas', 'href' => route('studio.generator', $a), 'icon' => 'sparkles'],
            ['label' => 'Generador de ideas', 'href' => route('studio.ideas', $a), 'icon' => 'sparkles'],
            ['label' => 'Ideas ganadoras', 'href' => route('studio.winning-ideas', $a), 'icon' => 'light-bulb'],
            ['label' => 'Ideas Referenciales', 'href' => route('studio.reference-ideas', $a), 'icon' => 'rectangle-stack'],
            ['label' => 'Kanban', 'href' => route('studio.kanban', $a), 'icon' => 'view-columns'],
            ['label' => 'Periodos', 'href' => route('studio.periods', $a), 'icon' => 'calendar-days'],
            ['label' => 'Audiencia (seguidores)', 'href' => route('studio.audience', $a), 'icon' => 'user-group'],
            ['label' => 'CTAs', 'href' => route('studio.ctas', $a), 'icon' => 'megaphone'],
            ['label' => 'Ganchos', 'href' => route('studio.hooks', $a), 'icon' => 'bolt'],
            ['label' => 'Kickstart', 'href' => route('studio.kickstart', $a), 'icon' => 'rocket-launch'],
            ['label' => 'Inbox', 'href' => route('studio.inbox', $a), 'icon' => 'inbox-arrow-down'],
        ];
    }

    public function render(): View
    {
        $q = trim($this->query);

        $shortcuts = collect($this->shortcuts())
            ->filter(fn (array $s): bool => $q === '' || Str::contains(Str::lower($s['label']), Str::lower($q)))
            ->values()
            ->all();

        $pieces = $q === '' ? collect() : $this->account->contentPieces()
            ->where('title', 'like', "%{$q}%")
            ->latest('updated_at')
            ->take(6)
            ->get(['id', 'title', 'status']);

        $ideas = $q === '' ? collect() : $this->account->winningIdeas()
            ->where('title', 'like', "%{$q}%")
            ->orderBy('title')
            ->take(6)
            ->get(['id', 'title', 'status']);

        return view('livewire.studio.command-palette', compact('shortcuts', 'pieces', 'ideas'));
    }
}
