<?php

namespace App\Filament\Resources\ViralReferents\Pages;

use App\Filament\Resources\ViralReferents\ViralReferentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListViralReferents extends ListRecords
{
    protected static string $resource = ViralReferentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
