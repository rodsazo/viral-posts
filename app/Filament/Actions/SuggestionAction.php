<?php

namespace App\Filament\Actions;

use App\Support\Ai\Suggestion;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions as ActionsComponent;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\HtmlString;
use Throwable;

/**
 * Patrón de UX reutilizable para sugerencias de IA en el admin (Filament).
 *
 * Flujo de dos pasos: (1) el usuario puede escribir instrucciones adicionales y pulsa
 * "Generar" (puede **regenerar** las veces que quiera); (2) se muestran **hasta 3
 * alternativas** y, **solo al elegir** una, se reescribe su contenido (vía `Set`).
 * Una sugerencia puede afectar **uno o varios** campos (su mapa `fields`).
 *
 * Reutilizable para cualquier caso: el `$generator` recibe el estado del formulario y
 * las instrucciones libres, y devuelve `Suggestion[]`.
 */
class SuggestionAction
{
    /**
     * @param  callable(Get, ?string): array<int, Suggestion>  $generator
     */
    public static function make(string $name, callable $generator): Action
    {
        return Action::make($name)
            ->icon('heroicon-m-sparkles')
            ->color('info')
            ->modalHeading('Sugerencias de IA')
            ->modalDescription('Da instrucciones (opcional), genera y elige una. Solo al elegir se reescribe tu contenido.')
            ->modalSubmitActionLabel('Usar la seleccionada')
            ->fillForm(fn (): array => ['brief' => null, 'suggestions' => [], 'choice' => null])
            ->schema([
                Textarea::make('brief')
                    ->label('Instrucciones adicionales (opcional)')
                    ->placeholder('Describe lo que quieres: ángulo, tono, detalles del post…')
                    ->rows(2),
                ActionsComponent::make([
                    Action::make('generate')
                        ->label(fn (Get $get): string => filled($get('suggestions')) ? 'Regenerar' : 'Generar sugerencias')
                        ->icon('heroicon-m-sparkles')
                        ->action(function (Get $get, Set $set) use ($generator): void {
                            try {
                                $suggestions = $generator($get, $get('brief'));
                            } catch (Throwable $e) {
                                Notification::make()->title($e->getMessage())->danger()->send();

                                return;
                            }

                            $set('suggestions', array_map(fn (Suggestion $s): array => $s->toArray(), $suggestions));
                            $set('choice', null);
                        }),
                ]),
                Hidden::make('suggestions'),
                Radio::make('choice')
                    ->hiddenLabel()
                    ->visible(fn (Get $get): bool => filled($get('suggestions')))
                    ->options(fn (Get $get): array => collect($get('suggestions') ?? [])
                        ->mapWithKeys(fn (array $s, int $i): array => [$i => $s['label']])
                        ->all())
                    ->descriptions(fn (Get $get): array => collect($get('suggestions') ?? [])
                        ->mapWithKeys(fn (array $s, int $i): array => [$i => new HtmlString(nl2br(e($s['preview'])))])
                        ->all())
                    ->required(),
            ])
            ->action(function (array $data, Set $set): void {
                $chosen = ($data['suggestions'] ?? [])[$data['choice'] ?? ''] ?? null;

                if ($chosen === null) {
                    return;
                }

                foreach ($chosen['fields'] as $field => $value) {
                    $set($field, $value);
                }

                Notification::make()->title('Sugerencia aplicada')->success()->send();
            });
    }
}
