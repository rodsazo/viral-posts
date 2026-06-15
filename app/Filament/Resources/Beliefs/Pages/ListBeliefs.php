<?php

namespace App\Filament\Resources\Beliefs\Pages;

use App\Filament\Pages\BulkBeliefs;
use App\Filament\Resources\Beliefs\BeliefResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBeliefs extends ListRecords
{
    protected static string $resource = BeliefResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('bulk')
                ->label('Crear en lote')
                ->icon('heroicon-o-queue-list')
                ->color('gray')
                ->url(fn (): string => BulkBeliefs::getUrl()),
            CreateAction::make(),
        ];
    }
}
