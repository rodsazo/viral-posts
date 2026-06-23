<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Estudio' }}</title>
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
    @fluxAppearance
</head>
<body class="min-h-full bg-zinc-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
    <header class="border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div class="mx-auto flex h-14 max-w-7xl items-center justify-between px-4">
            <div class="flex items-center gap-4 text-sm">
                <span class="font-semibold">🎬 Estudio</span>
                @isset($currentAccount)
                    <nav class="flex items-center gap-1">
                        @php($navLink = fn (bool $active) => $active
                            ? 'rounded-md px-2 py-1 bg-zinc-200 font-medium text-zinc-900 dark:bg-zinc-700 dark:text-white'
                            : 'rounded-md px-2 py-1 text-zinc-600 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800')
                        <a href="{{ route('studio.home', $currentAccount) }}" class="{{ $navLink(request()->routeIs('studio.home')) }}">Inicio</a>
                        <a href="{{ route('studio.inbox', $currentAccount) }}" class="{{ $navLink(request()->routeIs('studio.inbox')) }}">Inbox</a>
                        <a href="{{ route('studio.audience', $currentAccount) }}" class="{{ $navLink(request()->routeIs('studio.audience')) }}">👥 Audiencia</a>
                        <a href="{{ route('studio.kickstart', $currentAccount) }}" class="{{ $navLink(request()->routeIs('studio.kickstart')) }}">🚀 Kickstart</a>
                        <a href="{{ route('studio.kanban', $currentAccount) }}" class="{{ $navLink(request()->routeIs('studio.kanban')) }}">Kanban</a>
                        <a href="{{ route('studio.ideas', $currentAccount) }}" class="{{ $navLink(request()->routeIs('studio.ideas')) }}">💡 Ideas</a>
                        <a href="{{ route('studio.generator', $currentAccount) }}" class="{{ $navLink(request()->routeIs('studio.generator')) }}">✨ Generador</a>
                        <a href="{{ route('studio.ctas', $currentAccount) }}" class="{{ $navLink(request()->routeIs('studio.ctas')) }}">📣 CTAs</a>
                        <a href="{{ route('studio.pieces', $currentAccount) }}" class="{{ $navLink(request()->routeIs('studio.pieces')) }}">Composer</a>
                    </nav>
                    <span class="text-zinc-400">·</span>
                    <flux:dropdown>
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
                @endisset
            </div>
            <flux:button href="/admin" variant="ghost" size="sm" icon="arrow-left">
                Volver al admin
            </flux:button>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-6">
        @if (session('studio.flash'))
            <flux:callout variant="success" icon="check-circle" class="mb-4">{{ session('studio.flash') }}</flux:callout>
        @endif

        {{ $slot }}
    </main>

    @fluxScripts
</body>
</html>
