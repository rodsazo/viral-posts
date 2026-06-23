<?php

namespace App\Filament\Resources\Pains\Pages;

use App\Filament\Resources\Pains\PainResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPains extends ListRecords
{
    protected static string $resource = PainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
