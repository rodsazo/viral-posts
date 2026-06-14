<?php

namespace App\Filament\Resources\HerasTemplates\Pages;

use App\Filament\Resources\HerasTemplates\HerasTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHerasTemplates extends ListRecords
{
    protected static string $resource = HerasTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
