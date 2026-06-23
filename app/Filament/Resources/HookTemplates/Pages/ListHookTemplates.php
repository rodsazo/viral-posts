<?php

namespace App\Filament\Resources\HookTemplates\Pages;

use App\Filament\Resources\HookTemplates\HookTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHookTemplates extends ListRecords
{
    protected static string $resource = HookTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
