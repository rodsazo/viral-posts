<?php

namespace App\Filament\Pages;

use App\Enums\ContentStatus;
use App\Models\Account;
use App\Models\ContentPiece;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class ContentKanban extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedViewColumns;

    protected static string|UnitEnum|null $navigationGroup = 'Producción';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Kanban';

    protected static ?string $title = 'Kanban de producción';

    protected string $view = 'filament.pages.content-kanban';

    /**
     * Columnas del tablero: una por estado, con sus piezas (de la marca activa).
     *
     * @return array<int, array{status: ContentStatus, pieces: Collection<int, ContentPiece>}>
     */
    public function getColumns(): array
    {
        /** @var Account $account */
        $account = Filament::getTenant();

        $byStatus = $account->contentPieces()
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

    /**
     * Conteo inicial por estado, para los contadores reactivos del tablero.
     *
     * @return array<string, int>
     */
    public function getStatusCounts(): array
    {
        $counts = [];

        foreach ($this->getColumns() as $column) {
            $counts[$column['status']->value] = $column['pieces']->count();
        }

        return $counts;
    }

    /**
     * Persiste el cambio de estado al soltar una tarjeta en otra columna.
     */
    public function moveToStatus(int $pieceId, string $status): void
    {
        $statusEnum = ContentStatus::tryFrom($status);

        if ($statusEnum === null) {
            return;
        }

        /** @var Account $account */
        $account = Filament::getTenant();

        $piece = $account->contentPieces()->whereKey($pieceId)->first();

        if ($piece === null || $piece->status === $statusEnum) {
            return;
        }

        $piece->update(['status' => $statusEnum]);

        Notification::make()
            ->title("«{$piece->title}» → {$statusEnum->getLabel()}")
            ->success()
            ->send();
    }
}
