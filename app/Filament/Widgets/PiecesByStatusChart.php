<?php

namespace App\Filament\Widgets;

use App\Enums\ContentStatus;
use App\Models\Account;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class PiecesByStatusChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Piezas por estado';

    protected function getData(): array
    {
        /** @var Account $account */
        $account = Filament::getTenant();

        $counts = $account->contentPieces()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $labels = [];
        $data = [];

        foreach (ContentStatus::cases() as $status) {
            $labels[] = $status->getLabel();
            $data[] = (int) ($counts[$status->value] ?? 0);
        }

        return [
            'datasets' => [[
                'label' => 'Piezas',
                'data' => $data,
                'backgroundColor' => '#f59e0b',
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
