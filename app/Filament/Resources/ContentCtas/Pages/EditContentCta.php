<?php

namespace App\Filament\Resources\ContentCtas\Pages;

use App\Filament\Resources\ContentCtas\ContentCtaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContentCta extends EditRecord
{
    protected static string $resource = ContentCtaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
