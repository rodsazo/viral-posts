<?php

namespace App\Filament\Resources\HerasTemplates\Pages;

use App\Filament\Resources\HerasTemplates\HerasTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHerasTemplate extends EditRecord
{
    protected static string $resource = HerasTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
