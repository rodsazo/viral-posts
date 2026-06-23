<?php

namespace App\Filament\Resources\HookTemplates\Pages;

use App\Filament\Resources\HookTemplates\HookTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHookTemplate extends EditRecord
{
    protected static string $resource = HookTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
