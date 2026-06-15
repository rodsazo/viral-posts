<?php

namespace App\Filament\Resources\WinningIdeas\Pages;

use App\Filament\Pages\BulkIdeas;
use App\Filament\Resources\WinningIdeas\WinningIdeaResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWinningIdeas extends ListRecords
{
    protected static string $resource = WinningIdeaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('bulk')
                ->label('Crear en lote')
                ->icon('heroicon-o-queue-list')
                ->color('gray')
                ->url(fn (): string => BulkIdeas::getUrl()),
            CreateAction::make(),
        ];
    }
}
