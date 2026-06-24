<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GapsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Huecos por cubrir';

    protected ?string $description = 'Tu backlog de creación: lo que aún falta por conectar.';

    protected function getStats(): array
    {
        /** @var Account $account */
        $account = Filament::getTenant();

        $followersWithoutBeliefs = $account->idealFollowers()->doesntHave('beliefs')->count();
        $ideasWithoutPieces = $account->winningIdeas()->doesntHave('contentPieces')->count();
        $questionsWithoutIdea = $account->questions()->doesntHave('winningIdeas')->count();

        return [
            $this->gapStat('Seguidores sin creencias', $followersWithoutBeliefs, 'heroicon-m-scale'),
            $this->gapStat('Ideas sin piezas', $ideasWithoutPieces, 'heroicon-m-film'),
            $this->gapStat('Preguntas sin idea', $questionsWithoutIdea, 'heroicon-m-light-bulb'),
        ];
    }

    private function gapStat(string $label, int $count, string $icon): Stat
    {
        return Stat::make($label, $count)
            ->description($count === 0 ? 'Todo cubierto' : 'Pendiente de conectar')
            ->descriptionIcon($icon)
            ->color($count === 0 ? 'success' : 'warning');
    }
}
