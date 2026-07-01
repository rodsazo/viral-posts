<!DOCTYPE html>
<html lang="es" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Sal del anonimato digital</title>
    <meta name="description" content="Consultoría para negocios que quieren empezar su promoción online con el pie derecho. Procesos comprobados para crecer en el mundo digital.">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon-studio.svg') }}">
    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css'])
    @endif
</head>
<body class="min-h-full bg-white text-slate-800 antialiased">
    @php($booking = config('studio.booking_url'))
    @php($email = config('studio.contact_email'))
    @php($cta = filled($booking) ? $booking : (filled($email) ? 'mailto:'.$email.'?subject=Sesión de estrategia' : '#contacto'))

    {{-- Barra superior --}}
    <header class="sticky top-0 z-40 border-b border-slate-100 bg-white/80 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-5">
            <a href="/" class="flex items-center gap-2 text-lg font-black">
                <span class="grid size-8 place-items-center rounded-xl bg-gradient-to-br from-blue-900 to-cyan-500 text-white">T</span>
                <span class="bg-gradient-to-r from-blue-800 to-cyan-600 bg-clip-text text-transparent">Tracción Online</span>
            </a>
            <nav class="flex items-center gap-2 sm:gap-4">
                <a href="#proceso" class="hidden text-sm font-medium text-slate-500 hover:text-slate-800 sm:inline">Cómo trabajamos</a>
                <a href="/admin" class="hidden text-sm font-medium text-slate-500 hover:text-slate-800 sm:inline">Entrar</a>
                <a href="{{ $cta }}" @if (filled($booking)) target="_blank" rel="noopener" @endif
                   class="rounded-full bg-blue-900 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-blue-950">
                    Agenda tu sesión
                </a>
            </nav>
        </div>
    </header>

    {{-- Hero --}}
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-blue-50 via-white to-white"></div>
        <div class="relative mx-auto max-w-4xl px-5 py-20 text-center sm:py-28">
            <span class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-4 py-1.5 text-sm font-semibold text-blue-800">
                🚀 Consultoría de crecimiento digital
            </span>
            <h1 class="mt-6 text-4xl font-black leading-tight tracking-tight text-slate-900 sm:text-6xl">
                Sal del <span class="bg-gradient-to-r from-blue-800 to-cyan-600 bg-clip-text text-transparent">anonimato digital</span>.
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-slate-600 sm:text-xl">
                Ayudamos a negocios a empezar su recorrido de promoción online <strong>con el pie derecho</strong>:
                con procesos comprobados para crecer en el mundo digital y dejar de pasar desapercibido.
            </p>
            <div class="mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ $cta }}" @if (filled($booking)) target="_blank" rel="noopener" @endif
                   class="rounded-full bg-gradient-to-r from-blue-800 to-cyan-600 px-8 py-4 text-lg font-bold text-white shadow-lg shadow-blue-200 transition hover:-translate-y-0.5 hover:shadow-xl">
                    Agenda tu sesión de estrategia
                </a>
                <a href="#proceso" class="rounded-full px-6 py-4 text-lg font-semibold text-slate-600 ring-1 ring-slate-200 transition hover:bg-slate-50">
                    Ver cómo trabajamos
                </a>
            </div>
        </div>
    </section>

    {{-- Dolores --}}
    <section class="bg-slate-50 py-20">
        <div class="mx-auto max-w-5xl px-5">
            <div class="text-center">
                <h2 class="text-3xl font-black text-slate-900 sm:text-4xl">¿Te suena esto?</h2>
                <p class="mt-3 text-lg text-slate-500">Si te identificas, estás en el lugar correcto.</p>
            </div>
            <div class="mt-12 grid grid-cols-1 gap-5 md:grid-cols-3">
                @php($pains = [
                    ['😔', 'Sientes que haces, pero no te ven', 'Publicas, te esfuerzas… y el alcance no llega. El trabajo está, pero nadie lo nota.'],
                    ['💔', 'Perdiste la fe en las redes sociales', 'Probaste de todo y no funcionó. Ya no sabes si vale la pena seguir intentándolo.'],
                    ['😤', 'Ves que otros avanzan', 'Con productos incluso inferiores al tuyo, crecen y se posicionan. Y tú, no.'],
                ])
                @foreach ($pains as [$emoji, $title, $body])
                    <div class="rounded-3xl bg-white p-7 shadow-sm ring-1 ring-black/5">
                        <div class="text-4xl">{{ $emoji }}</div>
                        <h3 class="mt-4 text-xl font-bold text-slate-900">{{ $title }}</h3>
                        <p class="mt-2 leading-relaxed text-slate-600">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Proceso --}}
    <section id="proceso" class="py-20">
        <div class="mx-auto max-w-5xl px-5">
            <div class="text-center">
                <h2 class="text-3xl font-black text-slate-900 sm:text-4xl">Un proceso comprobado, paso a paso</h2>
                <p class="mx-auto mt-3 max-w-2xl text-lg text-slate-500">Nada de improvisar. Te acompañamos desde la estrategia hasta la ejecución.</p>
            </div>

            <div class="mt-14 space-y-8">
                @php($steps = [
                    ['1', 'Sesión inicial de estrategia', 'Nos sentamos contigo para entender tu negocio, tus objetivos y tu punto de partida. Salimos con un rumbo claro.'],
                    ['2', 'Diagnóstico de tus perfiles sociales', 'Analizamos tus redes actuales: qué funciona, qué frena tu crecimiento y dónde están las oportunidades.'],
                    ['3', 'Trabajo conjunto (2–3 meses)', 'Diseñamos y ejecutamos contigo una estrategia de contenidos a la medida de tus objetivos y de tu presupuesto operativo real: tiempo, recursos de grabación y de edición.'],
                ])
                @foreach ($steps as [$n, $title, $body])
                    <div class="flex flex-col gap-4 rounded-3xl bg-white p-7 shadow-sm ring-1 ring-black/5 sm:flex-row sm:items-start sm:gap-6 sm:p-8">
                        <div class="grid size-14 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-blue-900 to-cyan-500 text-2xl font-black text-white">{{ $n }}</div>
                        <div>
                            <h3 class="text-2xl font-bold text-slate-900">{{ $title }}</h3>
                            <p class="mt-2 text-lg leading-relaxed text-slate-600">{{ $body }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA final --}}
    <section id="contacto" class="bg-gradient-to-br from-blue-950 via-blue-900 to-cyan-800 py-20">
        <div class="mx-auto max-w-3xl px-5 text-center text-white">
            <h2 class="text-3xl font-black sm:text-4xl">Es hora de que te vean.</h2>
            <p class="mx-auto mt-4 max-w-xl text-lg text-blue-100">
                Agenda una sesión de estrategia y empecemos a construir tu tracción online con un plan que sí funciona.
            </p>
            <a href="{{ $cta }}" @if (filled($booking)) target="_blank" rel="noopener" @endif
               class="mt-8 inline-block rounded-full bg-white px-8 py-4 text-lg font-bold text-blue-900 shadow-lg transition hover:-translate-y-0.5">
                Agenda tu sesión de estrategia
            </a>
            @if (filled($email))
                <p class="mt-5 text-blue-100">o escríbenos a <a href="mailto:{{ $email }}" class="font-semibold underline">{{ $email }}</a></p>
            @endif
        </div>
    </section>

    <footer class="border-t border-slate-100 py-8">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-3 px-5 text-sm text-slate-400 sm:flex-row">
            <span>© {{ date('Y') }} {{ config('app.name') }}</span>
            <a href="/admin" class="hover:text-slate-600">Acceso al equipo</a>
        </div>
    </footer>
</body>
</html>
