<?php

namespace App\Filament\Widgets;

use App\Enums\ContentStatus;
use App\Models\Account;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContentStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        /** @var Account $account */
        $account = Filament::getTenant();

        $pieces = $account->contentPieces();
        $publicadas = (clone $pieces)->where('status', ContentStatus::Publicada->value)->count();
        $totalPiezas = $pieces->count();

        return [
            Stat::make('Seguidores ideales', $account->idealFollowers()->count())
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            Stat::make('Preguntas', $account->questions()->count())
                ->descriptionIcon('heroicon-m-question-mark-circle'),
            Stat::make('Creencias', $account->beliefs()->count())
                ->descriptionIcon('heroicon-m-scale'),
            Stat::make('Ideas ganadoras', $account->winningIdeas()->count())
                ->descriptionIcon('heroicon-m-light-bulb')
                ->color('warning'),
            Stat::make('Piezas de contenido', $totalPiezas)
                ->description($publicadas.' publicadas')
                ->descriptionIcon('heroicon-m-film')
                ->color('success'),
        ];
    }
}
