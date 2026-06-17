<?php

namespace App\Filament\Resources\HerasTemplates\Pages;

use App\Filament\Resources\HerasTemplates\HerasTemplateResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewHerasTemplate extends ViewRecord
{
    protected static string $resource = HerasTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
