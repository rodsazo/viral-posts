<?php

namespace App\Filament\Resources\HerasTemplates\Schemas;

use App\Models\ViralReferent;
use App\Support\ReferenceImageCapture;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class HerasTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('viral_referent_id')
                    ->label('Referente viral')
                    ->relationship('viralReferent', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Sin referente')
                    ->createOptionForm([
                        TextInput::make('name')->label('Nombre')->required()->maxLength(255),
                        Select::make('niche_id')->label('Nicho')->relationship('niche', 'name')->searchable()->preload(),
                        TextInput::make('instagram_url')->label('URL de Instagram')->url(),
                    ])
                    ->createOptionUsing(fn (array $data): int => ViralReferent::create($data)->getKey()),
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                TextInput::make('suggested_format')
                    ->label('Formato sugerido')
                    ->maxLength(255),
                TextInput::make('viral_mechanism')
                    ->label('Mecanismo de viralidad')
                    ->maxLength(255),
                TextInput::make('reference_url')
                    ->label('URL del post de referencia')
                    ->url()
                    ->maxLength(2048)
                    ->prefixIcon('heroicon-m-link')
                    ->placeholder('https://www.tiktok.com/@.../video/...')
                    ->suffixAction(
                        Action::make('fetchPreview')
                            ->label('Obtener vista previa')
                            ->icon('heroicon-m-photo')
                            ->action(function (Get $get, Set $set): void {
                                $image = app(ReferenceImageCapture::class)->capture($get('reference_url'));

                                if ($image !== null) {
                                    $set('preview_image_url', $image);
                                    Notification::make()->title('Vista previa obtenida')->success()->send();

                                    return;
                                }

                                Notification::make()
                                    ->title('No se pudo obtener la imagen automáticamente')
                                    ->body('Pega la URL de la imagen a mano (Instagram suele requerirlo).')
                                    ->warning()
                                    ->send();
                            }),
                    ),
                TextInput::make('preview_image_url')
                    ->label('URL de imagen de previsualización')
                    ->url()
                    ->maxLength(2048)
                    ->prefixIcon('heroicon-m-photo')
                    ->helperText('Se rellena con el botón "Obtener vista previa", o pégala manualmente.'),
                Repeater::make('reference_urls')
                    ->label('Más URLs de referencia')
                    ->helperText('Otros posts que ilustran esta plantilla (además del principal de arriba).')
                    ->simple(
                        TextInput::make('url')
                            ->url()
                            ->required()
                            ->placeholder('https://www.instagram.com/p/...'),
                    )
                    ->addActionLabel('Añadir URL')
                    ->reorderable(false)
                    ->defaultItems(0)
                    ->columnSpanFull(),
                Textarea::make('structure')
                    ->label('Estructura')
                    ->rows(5)
                    ->columnSpanFull(),
            ]);
    }
}
