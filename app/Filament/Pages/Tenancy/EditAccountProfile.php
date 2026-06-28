<?php

namespace App\Filament\Pages\Tenancy;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Schema;

class EditAccountProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Datos de la marca';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre de la marca')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('logo_path')
                    ->label('Logo / imagen')
                    ->image()
                    ->avatar()
                    ->disk(config('filesystems.brand_disk'))
                    ->visibility('public')
                    ->directory('brand-logos')
                    ->imageEditor()
                    ->helperText('Se muestra como miniatura en el Estudio y el selector de marca.'),
                Textarea::make('description')
                    ->label('Descripción')
                    ->rows(3),
                Textarea::make('brand_promise')
                    ->label('Promesa de la marca')
                    ->helperText('Qué le promete la marca a sus seguidores; su oferta central.')
                    ->rows(3),
                Textarea::make('main_offers')
                    ->label('Oferta(s) principal(es)')
                    ->helperText('Qué vende la marca, en última instancia.')
                    ->rows(3),
                Textarea::make('ideal_customer_profile')
                    ->label('Perfil del cliente ideal')
                    ->helperText('Qué tipo de personas encajan mejor con la oferta de la marca.')
                    ->rows(3),
            ]);
    }
}
