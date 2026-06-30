<?php

namespace App\Livewire\Studio;

use App\Enums\ContentStatus;
use App\Enums\IdeaStatus;
use App\Models\Account;
use App\Support\StudioPeriod;
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

        // Ideas FIJAS (las probadas) que aún no tienen pieza en el periodo activo:
        // recordatorio para producir más contenido de lo que ya funciona.
        $activePeriod = StudioPeriod::get($account);
        $fijaTotal = $account->winningIdeas()->where('status', IdeaStatus::Fija->value)->count();
        $fijaNeedingContent = $activePeriod
            ? $account->winningIdeas()
                ->where('status', IdeaStatus::Fija->value)
                ->whereDoesntHave('contentPieces', fn ($q) => $q->where('period_id', $activePeriod->id))
                ->orderBy('title')
                ->get()
            : collect();

        return view('livewire.studio.home', [
            'activePeriod' => $activePeriod,
            'fijaTotal' => $fijaTotal,
            'fijaNeedingContent' => $fijaNeedingContent,
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
                'Seguidores sin preguntas' => $account->idealFollowers()->doesntHave('questions')->count(),
            ],
            'recentPieces' => $account->contentPieces()->latest('updated_at')->take(6)->get(),
        ]);
    }
}
