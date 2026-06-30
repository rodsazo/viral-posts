<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Plan de contenido · {{ $brand->name }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-studio.svg') }}">
    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css'])
    @endif
</head>
<body class="min-h-full bg-gradient-to-b from-amber-50 via-rose-50 to-violet-100 text-zinc-800 antialiased">
    <div class="mx-auto max-w-7xl px-5 py-10 sm:py-14">

        {{-- Marca --}}
        <div class="flex flex-col items-center text-center">
            @if ($brand->logoUrl())
                <img src="{{ $brand->logoUrl() }}" alt="{{ $brand->name }}" class="mb-4 size-20 rounded-2xl object-cover shadow-md ring-1 ring-black/5">
            @endif
            <h1 class="text-4xl font-black leading-tight text-zinc-900 sm:text-5xl">{{ $brand->name }}</h1>
            <p class="mt-3 text-lg text-zinc-500">Plan de contenido</p>
        </div>

        @if (! $period)
            {{-- Sin periodo publicado --}}
            <section class="mx-auto mt-10 max-w-xl rounded-3xl bg-white p-10 text-center shadow-sm ring-1 ring-black/5">
                <p class="text-6xl">🗓️</p>
                <p class="mt-5 text-2xl font-bold text-zinc-800">No hay periodos de trabajo abiertos</p>
                <p class="mt-2 text-lg text-zinc-500">Todavía no hay un plan publicado. Vuelve pronto. 😊</p>
            </section>
        @else
            <div class="mt-6 flex flex-col items-center">
                <span class="rounded-full bg-white px-5 py-2 text-lg font-bold text-violet-700 shadow-sm ring-1 ring-violet-100">
                    📅 {{ $period->name }}
                </span>
                <p class="mt-3 text-base text-zinc-500">Así avanza el contenido de este periodo 👇</p>
            </div>

            {{-- Tablero (solo lectura). Clases de color literales para el scanner de Tailwind. --}}
            @php($accent = [
                'lista_para_grabacion' => ['emoji' => '🎬', 'head' => 'bg-cyan-100 text-cyan-900 ring-cyan-200', 'dot' => 'bg-cyan-400'],
                'grabada'              => ['emoji' => '🎥', 'head' => 'bg-amber-100 text-amber-900 ring-amber-200', 'dot' => 'bg-amber-400'],
                'editada'              => ['emoji' => '✂️', 'head' => 'bg-pink-100 text-pink-900 ring-pink-200', 'dot' => 'bg-pink-400'],
                'publicada'            => ['emoji' => '🚀', 'head' => 'bg-green-100 text-green-900 ring-green-200', 'dot' => 'bg-green-400'],
            ])

            <div class="mt-8 flex gap-4 overflow-x-auto pb-4">
                @foreach ($columns as $column)
                    @php($status = $column['status'])
                    @php($a = $accent[$status->value])
                    <div class="flex w-72 shrink-0 flex-col">
                        {{-- Cabecera de columna --}}
                        <div class="flex items-center justify-between gap-2 rounded-2xl px-4 py-3 text-base font-extrabold ring-1 {{ $a['head'] }}">
                            <span class="flex items-center gap-2">
                                <span class="text-xl">{{ $a['emoji'] }}</span>
                                {{ $status->getLabel() }}
                            </span>
                            <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-white/70 px-1.5 text-sm">{{ $column['pieces']->count() }}</span>
                        </div>

                        {{-- Tarjetas --}}
                        <div class="mt-3 flex flex-col gap-3">
                            @forelse ($column['pieces'] as $piece)
                                <a
                                    href="{{ route('piece.public', $piece->public_token) }}"
                                    class="block rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5 transition hover:-translate-y-0.5 hover:shadow-md"
                                >
                                    <p class="text-lg font-bold leading-snug text-zinc-900">{{ $piece->title ?: 'Pieza sin título' }}</p>
                                    @if ($piece->winningIdea)
                                        <p class="mt-1.5 truncate text-sm text-zinc-500">💡 {{ $piece->winningIdea->title }}</p>
                                    @endif
                                    @if ($piece->client_review_status?->value === 'approved')
                                        <p class="mt-2 inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-bold text-green-800">✅ Aprobada</p>
                                    @elseif ($piece->client_review_status?->value === 'changes_requested')
                                        <p class="mt-2 inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800">✏️ Cambios pedidos</p>
                                    @endif
                                    <p class="mt-3 flex items-center gap-1 text-sm font-semibold text-violet-600">
                                        Ver detalles <span aria-hidden="true">→</span>
                                    </p>
                                </a>
                            @empty
                                <div class="rounded-2xl border-2 border-dashed border-zinc-200 px-4 py-6 text-center text-sm text-zinc-400">
                                    Nada por aquí todavía
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <footer class="mt-12 text-center text-sm text-zinc-400">
            {{ $brand->name }} · Plan de contenido
        </footer>
    </div>
</body>
</html>
