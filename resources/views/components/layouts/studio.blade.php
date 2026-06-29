<!DOCTYPE html>
<html lang="es" class="h-full dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Estudio' }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-studio.svg') }}">
    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css'])
    @endif
    @php($faUrl = config('services.fontawesome.url'))
    @if (filled($faUrl))
        @if (str_ends_with($faUrl, '.css'))
            <link rel="stylesheet" href="{{ $faUrl }}" crossorigin="anonymous">
        @else
            <script src="{{ $faUrl }}" crossorigin="anonymous"></script>
        @endif
    @endif
    {{-- El Estudio fija su propio tema oscuro (clase `dark` en <html>); no usamos el
         conmutador de apariencia de Flux para que el tema no se revierta a claro. --}}
</head>
<body class="studio-shell min-h-full bg-zinc-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
    <header class="border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4">
            <div class="flex items-center gap-3 text-sm">
                @isset($currentAccount)
                    {{-- El rótulo "Estudio" es el enlace a la portada (ya no hay item "Inicio"). --}}
                    <a href="{{ route('studio.home', $currentAccount) }}" class="flex items-center gap-1.5 font-semibold transition hover:opacity-80">
                        <flux:icon.film variant="micro" class="size-4 text-violet-400" />
                        <span class="bg-gradient-to-r from-violet-300 to-pink-300 bg-clip-text text-transparent">Estudio</span>
                    </a>
                @else
                    <span class="flex items-center gap-1.5 font-semibold">
                        <flux:icon.film variant="micro" class="size-4 text-violet-400" />
                        <span class="bg-gradient-to-r from-violet-300 to-pink-300 bg-clip-text text-transparent">Estudio</span>
                    </span>
                @endisset
                @isset($currentAccount)
                    {{-- Estilo de píldora compartido por enlaces y disparadores de grupo (activo-aware). --}}
                    @php($navLink = fn (bool $active) => 'inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 '.($active
                        ? 'bg-zinc-200 font-medium text-zinc-900 dark:bg-zinc-700 dark:text-white'
                        : 'text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800'))
                    {{-- Un grupo se marca activo si la ruta actual es cualquiera de sus hijas (incluidas las próximas). --}}
                    @php($audienceActive = request()->routeIs('studio.audience', 'studio.kickstart', 'studio.ctas'))
                    @php($contentActive = request()->routeIs('studio.winning-ideas', 'studio.reference-ideas', 'studio.ideas', 'studio.generator', 'studio.pieces', 'studio.hooks'))
                    @php($planActive = request()->routeIs('studio.kanban', 'studio.periods', 'studio.calendar'))
                    @php($analyticsActive = request()->routeIs('studio.performance', 'studio.ai-usage'))

                    <nav class="flex items-center gap-1">

                        {{-- Audiencia --}}
                        <flux:dropdown position="bottom" align="start">
                            <button type="button" class="{{ $navLink($audienceActive) }}">
                                <flux:icon.users variant="micro" class="size-4 text-cyan-300" /> Audiencia
                                <flux:icon.chevron-down variant="micro" class="size-3.5 opacity-60" />
                            </button>
                            <flux:menu>
                                <flux:menu.item href="{{ route('studio.audience', $currentAccount) }}" icon="user-group">Seguidores ideales</flux:menu.item>
                                <flux:menu.item href="{{ route('studio.ctas', $currentAccount) }}" icon="megaphone">CTAs</flux:menu.item>
                                <flux:menu.item href="{{ route('studio.kickstart', $currentAccount) }}" icon="rocket-launch">Kickstart</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>

                        {{-- Contenido --}}
                        <flux:dropdown position="bottom" align="start">
                            <button type="button" class="{{ $navLink($contentActive) }}">
                                <flux:icon.pencil-square variant="micro" class="size-4 text-pink-300" /> Contenido
                                <flux:icon.chevron-down variant="micro" class="size-3.5 opacity-60" />
                            </button>
                            <flux:menu>
                                <flux:menu.heading>Ideas Ganadoras</flux:menu.heading>
                                <flux:menu.item href="{{ route('studio.winning-ideas', $currentAccount) }}" icon="light-bulb">Ideas ganadoras</flux:menu.item>
                                <flux:menu.item href="{{ route('studio.reference-ideas', $currentAccount) }}" icon="rectangle-stack">Ideas Referenciales</flux:menu.item>
                                <flux:menu.item href="{{ route('studio.ideas', $currentAccount) }}" icon="sparkles">Generador de ideas</flux:menu.item>
                                <flux:menu.separator></flux:menu.separator>
                                <flux:menu.heading>Contenido</flux:menu.heading>
                                <flux:menu.item href="{{ route('studio.pieces', $currentAccount) }}" icon="document-text">Contenido</flux:menu.item>
                                <flux:menu.item href="{{ route('studio.generator', $currentAccount) }}" icon="sparkles">Generador de contenido</flux:menu.item>
                                <flux:menu.item href="{{ route('studio.hooks', $currentAccount) }}" icon="bolt">Ganchos</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>

                        {{-- Planificación --}}
                        <flux:dropdown position="bottom" align="start">
                            <button type="button" class="{{ $navLink($planActive) }}">
                                <flux:icon.calendar-days variant="micro" class="size-4 text-amber-300" /> Planificación
                                <flux:icon.chevron-down variant="micro" class="size-3.5 opacity-60" />
                            </button>
                            <flux:menu>
                                <flux:menu.item href="{{ route('studio.kanban', $currentAccount) }}" icon="view-columns">Kanban</flux:menu.item>
                                <flux:menu.item href="{{ route('studio.periods', $currentAccount) }}" icon="calendar-days">Periodos</flux:menu.item>
                                <x-studio.menu-soon icon="calendar" label="Calendario" />
                            </flux:menu>
                        </flux:dropdown>

                        {{-- Análisis — oculto temporalmente (restaurar cuando esté listo).
                        <flux:dropdown position="bottom" align="start">
                            <button type="button" class="{{ $navLink($analyticsActive) }}">
                                <flux:icon.chart-bar variant="micro" class="size-4 text-emerald-300" /> Análisis
                                <flux:icon.chevron-down variant="micro" class="size-3.5 opacity-60" />
                            </button>
                            <flux:menu>
                                <x-studio.menu-soon icon="arrow-trending-up" label="Rendimiento" />
                                <x-studio.menu-soon icon="banknotes" label="Uso de IA" />
                            </flux:menu>
                        </flux:dropdown>
                        --}}
                    </nav>
                @endisset
            </div>

            @isset($currentAccount)
                <div class="flex items-center gap-2">
                    {{-- Captura rápida (Inbox): acción puntual, no una fase del flujo. --}}
                    <flux:button
                        :href="route('studio.inbox', $currentAccount)"
                        :variant="request()->routeIs('studio.inbox') ? 'filled' : 'ghost'"
                        size="sm"
                        icon="inbox-arrow-down"
                        aria-label="Captura rápida (Inbox)"
                    />

                    {{-- Selector de periodo activo (planificación): filtra Composer/Kanban y asigna piezas nuevas. --}}
                    <livewire:studio.period-switcher :account="$currentAccount" :key="'period-switcher-'.$currentAccount->id" />

                    <flux:dropdown position="bottom" align="end">
                        <flux:button variant="ghost" size="sm" icon:trailing="chevron-down">
                            <span class="flex items-center gap-2">
                                <x-brand-thumb :brand="$currentAccount" size="size-5" />
                                {{ $currentAccount->name }}
                            </span>
                        </flux:button>
                        <flux:menu>
                            @foreach (auth()->user()->accounts as $brand)
                                <flux:menu.item
                                    href="{{ route(request()->route()->getName(), $brand) }}"
                                    :checked="$brand->is($currentAccount)"
                                >
                                    <span class="flex items-center gap-2">
                                        <x-brand-thumb :brand="$brand" size="size-5" />
                                        {{ $brand->name }}
                                    </span>
                                </flux:menu.item>
                            @endforeach
                        </flux:menu>
                    </flux:dropdown>

                    <flux:button href="/admin" variant="ghost" size="sm" icon="arrow-left">
                        Volver al admin
                    </flux:button>
                </div>
            @else
                <flux:button href="/admin" variant="ghost" size="sm" icon="arrow-left">
                    Volver al admin
                </flux:button>
            @endisset
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-6">
        @if (session('studio.flash'))
            <flux:callout variant="success" icon="check-circle" class="mb-4">{{ session('studio.flash') }}</flux:callout>
        @endif

        {{ $slot }}
    </main>

    @fluxScripts

    {{-- Sonido al terminar una generación de IA (los componentes emiten 'ai-generation-done').
         Se sintetiza con Web Audio: un "ding" agradable, sin necesidad de un archivo de audio. --}}
    <script>
        window.playAiDoneSound = function () {
            try {
                const Ctx = window.AudioContext || window.webkitAudioContext;
                if (! Ctx) return;
                const ctx = new Ctx();
                if (ctx.state === 'suspended') ctx.resume();
                const now = ctx.currentTime;
                // Dos notas ascendentes (La5 → Mi6).
                [[880, 0], [1318.51, 0.13]].forEach(function ([freq, offset]) {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sine';
                    osc.frequency.value = freq;
                    const t = now + offset;
                    gain.gain.setValueAtTime(0.0001, t);
                    gain.gain.exponentialRampToValueAtTime(0.2, t + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.35);
                    osc.connect(gain).connect(ctx.destination);
                    osc.start(t);
                    osc.stop(t + 0.4);
                });
                setTimeout(function () { ctx.close(); }, 1000);
            } catch (e) { /* el navegador no permite audio: lo ignoramos */ }
        };
        window.addEventListener('ai-generation-done', function () { window.playAiDoneSound(); });

        // Cambio de periodo activo: recargamos para refiltrar Composer/Kanban de forma
        // consistente (incluye los contadores del Kanban, que viven en estado de Alpine).
        window.addEventListener('period-changed', function () { window.location.reload(); });
    </script>
</body>
</html>
