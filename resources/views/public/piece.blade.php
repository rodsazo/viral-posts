<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $piece->title ?: 'Propuesta de contenido' }}@isset($brand) · {{ $brand->name }}@endisset</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-studio.svg') }}">
    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css'])
    @endif
    <style>
        /* Texto del guión: respetamos los saltos de línea y damos aire entre líneas. */
        .script-body { white-space: pre-wrap; }
    </style>
</head>
<body class="min-h-full bg-gradient-to-b from-amber-50 via-rose-50 to-violet-100 text-zinc-800 antialiased">
    <div class="mx-auto max-w-3xl px-5 py-10 sm:py-16">

        {{-- Marca + intro --}}
        <div class="flex flex-col items-center text-center">
            @if ($brand?->logoUrl())
                <img src="{{ $brand->logoUrl() }}" alt="{{ $brand->name }}" class="mb-4 size-16 rounded-2xl object-cover shadow-md ring-1 ring-black/5">
            @endif
            @isset($brand)
                <p class="text-sm font-semibold uppercase tracking-widest text-violet-500">{{ $brand->name }}</p>
            @endisset
            <p class="mt-2 text-base text-zinc-500">Propuesta de contenido para tu revisión 👀</p>
        </div>

        @if (session('review.flash'))
            <div class="mt-6 rounded-2xl bg-green-50 px-5 py-4 text-center text-lg font-semibold text-green-800 ring-1 ring-green-200">
                {{ session('review.flash') }}
            </div>
        @endif

        {{-- Título grande --}}
        <h1 class="mt-5 text-center text-4xl font-black leading-tight text-zinc-900 sm:text-5xl">
            {{ $piece->title ?: 'Pieza sin título todavía' }}
        </h1>

        {{-- Chips de estado / objetivo / formato --}}
        @php($statusPill = [
            'borrador' => 'bg-zinc-100 text-zinc-700 ring-zinc-200',
            'planificacion' => 'bg-violet-100 text-violet-700 ring-violet-200',
            'guion_listo' => 'bg-blue-100 text-blue-700 ring-blue-200',
            'lista_para_grabacion' => 'bg-cyan-100 text-cyan-700 ring-cyan-200',
            'grabada' => 'bg-amber-100 text-amber-700 ring-amber-200',
            'editada' => 'bg-pink-100 text-pink-700 ring-pink-200',
            'publicada' => 'bg-green-100 text-green-700 ring-green-200',
        ])
        <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
            <span class="rounded-full px-4 py-1.5 text-sm font-semibold shadow-sm ring-1 {{ $statusPill[$piece->status->value] ?? 'bg-zinc-100 text-zinc-700 ring-zinc-200' }}">
                📍 {{ $piece->status->getLabel() }}
            </span>
            @if ($piece->objective)
                <span class="rounded-full bg-white px-4 py-1.5 text-sm font-semibold text-violet-700 shadow-sm ring-1 ring-violet-100">
                    🎯 {{ $piece->objective->getLabel() }}
                </span>
            @endif
            @if ($piece->format)
                <span class="rounded-full bg-white px-4 py-1.5 text-sm font-semibold text-rose-700 shadow-sm ring-1 ring-rose-100">
                    🎬 {{ $piece->format->getLabel() }}
                </span>
            @endif
        </div>

        <div class="mt-10 space-y-6">

            {{-- La idea --}}
            @if ($idea)
                <section class="rounded-3xl bg-white p-7 shadow-sm ring-1 ring-black/5 sm:p-9">
                    <h2 class="flex items-center gap-3 text-2xl font-extrabold text-zinc-900">
                        <span class="text-3xl">💡</span> La idea detrás
                    </h2>
                    <p class="mt-4 text-2xl font-bold leading-snug text-violet-700">{{ $idea->title }}</p>
                    @if (filled($idea->concept))
                        <p class="mt-4 text-lg leading-relaxed text-zinc-700">{{ $idea->concept }}</p>
                    @endif
                </section>
            @endif

            {{-- Ejemplos reales (la idea ya funcionó en la vida real) --}}
            @if ($idea && filled($idea->example_urls))
                <section class="rounded-3xl bg-emerald-50 p-7 shadow-sm ring-1 ring-emerald-100 sm:p-9">
                    <h2 class="flex items-center gap-3 text-2xl font-extrabold text-emerald-900">
                        <span class="text-3xl">🔥</span> Esto ya funcionó en la vida real
                    </h2>
                    <p class="mt-2 text-base text-emerald-700">Otros creadores se volvieron virales con esta misma idea:</p>
                    <ul class="mt-5 space-y-3">
                        @foreach ($idea->example_urls as $url)
                            <li>
                                <a href="{{ $url }}" target="_blank" rel="noopener nofollow"
                                   class="flex items-center gap-3 rounded-2xl bg-white px-5 py-4 text-lg font-semibold text-emerald-800 shadow-sm ring-1 ring-emerald-100 transition hover:ring-emerald-300">
                                    <span class="text-2xl">▶️</span>
                                    <span class="truncate">{{ $url }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- A quién le hablamos --}}
            @if ($follower)
                <section class="rounded-3xl bg-white p-7 shadow-sm ring-1 ring-black/5 sm:p-9">
                    <h2 class="flex items-center gap-3 text-2xl font-extrabold text-zinc-900">
                        <span class="text-3xl">🙋</span> ¿A quién le hablamos?
                    </h2>
                    <p class="mt-4 text-2xl font-bold leading-snug text-cyan-700">{{ $follower->name }}</p>
                    @if ($follower->awareness_level)
                        <p class="mt-2 text-base text-zinc-500">{{ $follower->awareness_level->getLabel() }}</p>
                    @endif
                    @if (filled($follower->description))
                        <p class="mt-4 text-lg leading-relaxed text-zinc-700">{{ $follower->description }}</p>
                    @endif
                </section>
            @endif

            {{-- El guión --}}
            @if (filled($piece->hook) || filled($piece->story) || filled($piece->moral) || filled($piece->cta))
                <section class="rounded-3xl bg-white p-7 shadow-sm ring-1 ring-black/5 sm:p-9">
                    <h2 class="flex items-center gap-3 text-2xl font-extrabold text-zinc-900">
                        <span class="text-3xl">🎬</span> El guión
                    </h2>
                    <p class="mt-2 text-base text-zinc-500">Esto es lo que se dirá, paso a paso. Léelo y dinos si te late. 💬</p>

                    <div class="mt-6 space-y-5">
                        {{-- Clases literales (no interpoladas) para que el scanner de Tailwind v4 las compile. --}}
                        @php($parts = [
                            ['🪝', 'El gancho', 'Lo primero que se dice para enganchar', $piece->hook,
                                'rounded-2xl bg-amber-50 p-5 ring-1 ring-amber-100 sm:p-6', 'text-lg font-extrabold text-amber-800', 'mt-1 text-sm text-amber-600/80'],
                            ['📖', 'La historia', 'El desarrollo, el corazón del video', $piece->story,
                                'rounded-2xl bg-rose-50 p-5 ring-1 ring-rose-100 sm:p-6', 'text-lg font-extrabold text-rose-800', 'mt-1 text-sm text-rose-600/80'],
                            ['💎', 'La moraleja', 'La idea que queremos que quede', $piece->moral,
                                'rounded-2xl bg-violet-50 p-5 ring-1 ring-violet-100 sm:p-6', 'text-lg font-extrabold text-violet-800', 'mt-1 text-sm text-violet-600/80'],
                            ['📣', 'La llamada a la acción', 'Qué le pedimos que haga al final', $piece->cta,
                                'rounded-2xl bg-cyan-50 p-5 ring-1 ring-cyan-100 sm:p-6', 'text-lg font-extrabold text-cyan-800', 'mt-1 text-sm text-cyan-600/80'],
                        ])
                        @foreach ($parts as [$emoji, $label, $hint, $text, $boxClass, $titleClass, $hintClass])
                            @if (filled($text))
                                <div class="{{ $boxClass }}">
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-xl">{{ $emoji }}</span>
                                        <h3 class="{{ $titleClass }}">{{ $label }}</h3>
                                    </div>
                                    <p class="{{ $hintClass }}">{{ $hint }}</p>
                                    <p class="script-body mt-3 text-xl leading-relaxed text-zinc-800">{{ $text }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Notas para el cliente --}}
            @if (filled($piece->client_notes))
                <section class="rounded-3xl bg-amber-50 p-7 shadow-sm ring-1 ring-amber-100 sm:p-9">
                    <h2 class="flex items-center gap-3 text-2xl font-extrabold text-amber-900">
                        <span class="text-3xl">📝</span> Notas para ti
                    </h2>
                    <p class="script-body mt-4 text-lg leading-relaxed text-amber-900/90">{{ $piece->client_notes }}</p>
                </section>
            @endif

            {{-- Cómo se grabará (detalles de producción) --}}
            @if (filled($piece->location) || filled($piece->equipment) || filled($piece->people))
                <section class="rounded-3xl bg-white p-7 shadow-sm ring-1 ring-black/5 sm:p-9">
                    <h2 class="flex items-center gap-3 text-2xl font-extrabold text-zinc-900">
                        <span class="text-3xl">🎥</span> Cómo se grabará
                    </h2>
                    <dl class="mt-5 space-y-5">
                        @php($prod = [
                            ['📍', 'Locación', $piece->location],
                            ['🎒', 'Equipo necesario', $piece->equipment],
                            ['🧑‍🤝‍🧑', 'Personas y personajes', $piece->people],
                        ])
                        @foreach ($prod as [$emoji, $label, $value])
                            @if (filled($value))
                                <div>
                                    <dt class="flex items-center gap-2 text-base font-bold text-zinc-500">
                                        <span class="text-lg">{{ $emoji }}</span> {{ $label }}
                                    </dt>
                                    <dd class="script-body mt-1 text-lg leading-relaxed text-zinc-800">{{ $value }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                </section>
            @endif

            {{-- Estado vacío amable: si no hay guión ni idea aún --}}
            @if (blank($piece->hook) && blank($piece->story) && blank($piece->moral) && blank($piece->cta) && ! $idea)
                <section class="rounded-3xl bg-white p-9 text-center shadow-sm ring-1 ring-black/5">
                    <p class="text-5xl">✏️</p>
                    <p class="mt-4 text-xl font-semibold text-zinc-700">Esta pieza todavía se está cocinando.</p>
                    <p class="mt-1 text-base text-zinc-500">Vuelve pronto: aquí aparecerán la idea y el guión completos.</p>
                </section>
            @endif
        </div>

        {{-- Tu respuesta (aprobar / pedir cambios) --}}
        <section class="mt-8 rounded-3xl bg-white p-7 shadow-sm ring-1 ring-black/5 sm:p-9">
            <h2 class="flex items-center gap-3 text-2xl font-extrabold text-zinc-900">
                <span class="text-3xl">🙌</span> ¿Qué te parece?
            </h2>

            @if ($piece->client_review_status?->value === 'approved')
                <p class="mt-4 flex items-center gap-2 text-xl font-bold text-green-700">✅ ¡La aprobaste! Gracias.</p>
                <p class="mt-1 text-base text-zinc-500">Puedes volver a responder si cambias de opinión.</p>
            @elseif ($piece->client_review_status?->value === 'changes_requested')
                <p class="mt-4 flex items-center gap-2 text-xl font-bold text-amber-700">✍️ Pediste cambios.</p>
                @if (filled($piece->client_review_notes))
                    <p class="mt-2 rounded-2xl bg-amber-50 px-4 py-3 text-lg text-amber-900 ring-1 ring-amber-100">“{{ $piece->client_review_notes }}”</p>
                @endif
            @else
                <p class="mt-2 text-lg text-zinc-500">Dinos si te gusta tal cual o, si necesitas cambios, escríbelos abajo. 👇</p>
            @endif

            <form method="POST" action="{{ route('piece.public.review', $piece->public_token) }}" class="mt-6">
                @csrf

                <div class="mb-4">
                    <label class="mb-1 block text-base font-semibold text-zinc-700">¿Quieres pedir cambios? Cuéntanos aquí</label>
                    <textarea name="notes" rows="4"
                              class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-lg text-zinc-800 focus:border-amber-400 focus:outline-none"
                              placeholder="Escríbelo con tus palabras… (solo si vas a pedir cambios)">{{ old('notes', $piece->client_review_notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-base font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <button type="submit" name="decision" value="approved"
                            class="flex-1 rounded-2xl bg-green-500 px-6 py-4 text-center text-xl font-extrabold text-white shadow-sm transition hover:bg-green-600">
                        ✅ Me gusta, aprobar
                    </button>
                    <button type="submit" name="decision" value="changes_requested"
                            class="flex-1 rounded-2xl bg-amber-100 px-6 py-4 text-center text-xl font-extrabold text-amber-800 ring-1 ring-amber-200 transition hover:bg-amber-200">
                        ✏️ Pedir cambios
                    </button>
                </div>
            </form>
        </section>

        <footer class="mt-12 text-center text-sm text-zinc-400">
            @isset($brand) Creado por {{ $brand->name }} · @endisset
            ¿Dudas o cambios? Usa los botones de arriba. 🤝
        </footer>
    </div>
</body>
</html>
