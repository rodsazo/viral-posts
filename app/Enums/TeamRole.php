<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TeamRole: string implements HasColor, HasLabel
{
    case Admin = 'admin';
    case Editor = 'editor';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Editor => 'Editor',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Admin => 'warning',
            self::Editor => 'info',
        };
    }
}
