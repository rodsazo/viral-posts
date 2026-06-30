<div class="flex flex-col gap-8">
    <div>
        <flux:heading size="xl">Uso de IA</flux:heading>
        <flux:subheading>Volumen de generaciones de esta marca. Cada generación es una llamada de pago a Anthropic; este panel ayuda a dimensionar el gasto <em>(no registramos tokens ni coste exacto)</em>.</flux:subheading>
    </div>

    {{-- Totales --}}
    <section class="grid grid-cols-2 gap-3 sm:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="text-2xl font-bold text-violet-300">{{ $total }}</div>
            <flux:text class="text-zinc-500">Generaciones (total)</flux:text>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="text-2xl font-bold text-cyan-300">{{ $thisMonth }}</div>
            <flux:text class="text-zinc-500">Este mes</flux:text>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="text-2xl font-bold {{ $failed > 0 ? 'text-amber-300' : 'text-emerald-300' }}">{{ $failed }}</div>
            <flux:text class="text-zinc-500">Fallidas</flux:text>
        </div>
    </section>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Por tipo --}}
        <section>
            <flux:heading size="lg" class="mb-3">Por tipo</flux:heading>
            <div class="flex flex-col gap-2 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                @forelse ($byKind as $label => $count)
                    <div class="flex items-center justify-between">
                        <flux:text>{{ $label }}</flux:text>
                        <flux:badge size="sm" color="violet">{{ $count }}</flux:badge>
                    </div>
                @empty
                    <flux:text class="text-zinc-500">Aún no hay generaciones.</flux:text>
                @endforelse
            </div>
        </section>

        {{-- Por usuario --}}
        <section>
            <flux:heading size="lg" class="mb-3">Por miembro</flux:heading>
            <div class="flex flex-col gap-2 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                @forelse ($byUser as $name => $count)
                    <div class="flex items-center justify-between">
                        <flux:text>{{ $name }}</flux:text>
                        <flux:badge size="sm" color="cyan">{{ $count }}</flux:badge>
                    </div>
                @empty
                    <flux:text class="text-zinc-500">—</flux:text>
                @endforelse
            </div>
        </section>
    </div>

    {{-- Recientes --}}
    <section>
        <flux:heading size="lg" class="mb-3">Actividad reciente</flux:heading>
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            @forelse ($recent as $gen)
                <div class="flex items-center justify-between gap-3 border-b border-zinc-100 px-4 py-2.5 last:border-0 dark:border-zinc-800">
                    <span class="flex items-center gap-2 text-sm">
                        @if ($gen->status === \App\Models\AiGeneration::STATUS_DONE)
                            <flux:badge size="sm" color="green" icon="check">Hecha</flux:badge>
                        @elseif ($gen->status === \App\Models\AiGeneration::STATUS_FAILED)
                            <flux:badge size="sm" color="red" icon="x-mark">Fallida</flux:badge>
                        @else
                            <flux:badge size="sm" color="zinc" icon="clock">En curso</flux:badge>
                        @endif
                        <span class="text-zinc-600 dark:text-zinc-300">{{ $kindLabel($gen->kind) }}</span>
                    </span>
                    <span class="flex items-center gap-2 text-xs text-zinc-400">
                        <span>{{ $gen->user?->name ?? 'Sistema' }}</span>
                        <span>·</span>
                        <span>{{ $gen->created_at->diffForHumans() }}</span>
                    </span>
                </div>
            @empty
                <p class="px-4 py-6 text-center text-sm text-zinc-500">Sin actividad de IA todavía.</p>
            @endforelse
        </div>
    </section>
</div>
