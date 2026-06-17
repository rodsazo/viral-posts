<?php

namespace App\Filament\Resources\ViralReferents\Pages;

use App\Filament\Resources\ViralReferents\ViralReferentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditViralReferent extends EditRecord
{
    protected static string $resource = ViralReferentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
