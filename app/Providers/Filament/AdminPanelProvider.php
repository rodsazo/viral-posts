<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Tenancy\EditAccountProfile;
use App\Models\Account;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Contracts\View\View;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->favicon(asset('favicon-admin.svg'))
            ->colors([
                'primary' => Color::Amber,
            ])
            // FontAwesome (Kit .js o CSS .css) para los íconos de las plantillas de gancho.
            ->renderHook('panels::head.end', fn (): string => static::fontAwesomeTag())
            // Acceso al Estudio en la barra superior, a la izquierda del buscador.
            ->renderHook('panels::global-search.before', fn (): View => view('filament.topbar-studio-link'))
            ->tenant(Account::class, slugAttribute: 'slug')
            // Sin auto-registro de marcas: las crea el super admin (recurso "Marcas").
            ->tenantProfile(EditAccountProfile::class)
            ->navigationGroups([
                'Audiencia',
                'Producción',
                'Referencia',
                'Equipo',
                'Plataforma',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    /** Etiqueta para cargar FontAwesome: <script> si es un Kit (.js), <link> si es CSS. */
    protected static function fontAwesomeTag(): string
    {
        $url = config('services.fontawesome.url');

        if (blank($url)) {
            return '';
        }

        return str_ends_with($url, '.css')
            ? '<link rel="stylesheet" href="'.e($url).'" crossorigin="anonymous">'
            : '<script src="'.e($url).'" crossorigin="anonymous"></script>';
    }
}
