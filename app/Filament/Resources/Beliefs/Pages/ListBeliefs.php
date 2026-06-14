<?php

namespace App\Filament\Resources\Beliefs\Pages;

use App\Filament\Resources\Beliefs\BeliefResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBeliefs extends ListRecords
{
    protected static string $resource = BeliefResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
