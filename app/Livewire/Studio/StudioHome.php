<?php

namespace App\Livewire\Studio;

use App\Enums\ContentStatus;
use App\Models\Account;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.studio')]
class StudioHome extends Component
{
    public Account $account;

    public function mount(Account $account): void
    {
        $this->account = $account;
    }

    public function render(): View
    {
        $account = $this->account;

        $byStatus = $account->contentPieces()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $pipeline = collect(ContentStatus::cases())->map(fn (ContentStatus $status) => [
            'label' => $status->getLabel(),
            'color' => $status->fluxColor(),
            'count' => (int) ($byStatus[$status->value] ?? 0),
        ])->all();

        return view('livewire.studio.home', [
            'totals' => [
                'Piezas' => $account->contentPieces()->count(),
                'Ideas' => $account->winningIdeas()->count(),
                'Preguntas' => $account->questions()->count(),
                'Creencias' => $account->beliefs()->count(),
            ],
            'pipeline' => $pipeline,
            'gaps' => [
                'Seguidores sin creencias' => $account->idealFollowers()->doesntHave('beliefs')->count(),
                'Ideas sin piezas' => $account->winningIdeas()->doesntHave('contentPieces')->count(),
                'Preguntas sin idea' => $account->questions()->doesntHave('winningIdeas')->count(),
            ],
            'recentPieces' => $account->contentPieces()->latest('updated_at')->take(6)->get(),
        ]);
    }
}
