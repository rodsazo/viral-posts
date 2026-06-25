@php($tenant = \Filament\Facades\Filament::getTenant())

@if ($tenant)
    {{-- Acceso al Estudio desde la barra superior, a la izquierda del buscador
         (espeja el "Volver al admin" del Estudio). --}}
    <x-filament::button
        tag="a"
        href="{{ route('studio.home', $tenant) }}"
        icon="heroicon-m-sparkles"
        color="gray"
        size="sm"
    >
        Abrir Estudio
    </x-filament::button>
@endif
