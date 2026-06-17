<?php

namespace App\Livewire\Studio;

use App\Enums\ContentStatus;
use App\Models\Account;
use App\Models\ContentPiece;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.studio')]
class StudioKanban extends Component
{
    public Account $account;

    public function mount(Account $account): void
    {
        $this->account = $account;
    }

    /**
     * @return array<int, array{status: ContentStatus, pieces: Collection<int, ContentPiece>}>
     */
    public function columns(): array
    {
        $byStatus = $this->account->contentPieces()
            ->with('winningIdea')
            ->orderByDesc('updated_at')
            ->get()
            ->groupBy(fn (ContentPiece $piece) => $piece->status->value);

        return collect(ContentStatus::cases())
            ->map(fn (ContentStatus $status) => [
                'status' => $status,
                'pieces' => $byStatus->get($status->value, collect()),
            ])
            ->all();
    }

    /** @return array<string, int> */
    public function statusCounts(): array
    {
        $counts = [];

        foreach ($this->columns() as $column) {
            $counts[$column['status']->value] = $column['pieces']->count();
        }

        return $counts;
    }

    public function moveToStatus(int $pieceId, string $status): void
    {
        $statusEnum = ContentStatus::tryFrom($status);

        if ($statusEnum === null) {
            return;
        }

        $piece = $this->account->contentPieces()->whereKey($pieceId)->first();

        if ($piece === null || $piece->status === $statusEnum) {
            return;
        }

        $piece->update(['status' => $statusEnum]);
    }

    public function render(): View
    {
        return view('livewire.studio.kanban', [
            'columns' => $this->columns(),
        ]);
    }
}
