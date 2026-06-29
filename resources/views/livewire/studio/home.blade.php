<div class="flex flex-col gap-8">
    {{-- Ideas Fijas (probadas) por explotar en el periodo activo --}}
    @if ($activePeriod && $fijaTotal > 0)
        @if ($fijaNeedingContent->isNotEmpty())
            <section class="rounded-xl border border-amber-300 bg-amber-50 p-4 dark:border-amber-500/30 dark:bg-amber-500/10">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <flux:heading size="lg" class="flex items-center gap-2">
                            <flux:icon.star variant="solid" class="size-5 text-amber-400" />
                            {{ $fijaNeedingContent->count() }}
                            {{ $fijaNeedingContent->count() === 1 ? 'idea Fija' : 'ideas Fijas' }} sin contenido en «{{ $activePeriod->name }}»
                        </flux:heading>
                        <flux:text class="mt-1 text-zinc-600 dark:text-zinc-300">Son tus conceptos probados: haz más contenido de ellos este periodo.</flux:text>
                    </div>
                    <flux:button :href="route('studio.generator', $account)" size="sm" variant="primary" icon="sparkles">Generar contenido</flux:button>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($fijaNeedingContent->take(8) as $idea)
                        <a href="{{ route('studio.winning-ideas', $account) }}" wire:navigate>
                            <flux:badge size="sm" color="amber" icon="star">{{ $idea->title }}</flux:badge>
                        </a>
                    @endforeach
                    @if ($fijaNeedingContent->count() > 8)
                        <flux:badge size="sm" color="zinc">+{{ $fijaNeedingContent->count() - 8 }} más</flux:badge>
                    @endif
                </div>
            </section>
        @else
            <section class="rounded-xl border border-green-300 bg-green-50 p-4 dark:border-green-500/30 dark:bg-green-500/10">
                <flux:heading size="sm" class="flex items-center gap-2">
                    <flux:icon.check-circle variant="solid" class="size-5 text-green-500" />
                    Todas tus ideas Fijas ya tienen contenido en «{{ $activePeriod->name }}» 🎉
                </flux:heading>
            </section>
        @endif
    @endif

    {{-- Totales --}}
    <section>
        <flux:heading size="lg" class="mb-3">Resumen de {{ $account->name }}</flux:heading>
        @php($statColors = ['text-violet-300', 'text-cyan-300', 'text-pink-300', 'text-amber-300'])
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ($totals as $label => $count)
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="text-2xl font-bold {{ $statColors[$loop->index % count($statColors)] }}">{{ $count }}</div>
                    <flux:text class="text-zinc-500">{{ $label }}</flux:text>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Pipeline --}}
    <section>
        <div class="mb-3 flex items-center justify-between">
            <flux:heading size="lg">Pipeline de producción</flux:heading>
            <flux:button :href="route('studio.pieces', $account)" size="sm" variant="primary" icon="pencil-square">
                Abrir composer
            </flux:button>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach ($pipeline as $stage)
                <div class="flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3 py-2 dark:border-zinc-800 dark:bg-zinc-900">
                    <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $stage['label'] }}</span>
                    <flux:badge size="sm" :color="$stage['count'] > 0 ? $stage['color'] : 'zinc'">{{ $stage['count'] }}</flux:badge>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Huecos por cubrir --}}
        <section>
            <flux:heading size="lg" class="mb-3">🕳️ Huecos por cubrir</flux:heading>
            <div class="flex flex-col gap-2 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                @foreach ($gaps as $label => $count)
                    <div class="flex items-center justify-between">
                        <flux:text>{{ $label }}</flux:text>
                        <flux:badge size="sm" :color="$count > 0 ? 'amber' : 'green'" :icon="$count > 0 ? 'exclamation-triangle' : 'check'">
                            {{ $count }}
                        </flux:badge>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Piezas recientes --}}
        <section>
            <flux:heading size="lg" class="mb-3">Piezas recientes</flux:heading>
            <div class="flex flex-col gap-1 rounded-xl border border-zinc-200 bg-white p-2 dark:border-zinc-800 dark:bg-zinc-900">
                @forelse ($recentPieces as $piece)
                    <a href="{{ route('studio.pieces', $account) }}" class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-800">
                        <span class="truncate text-sm">{{ $piece->title }}</span>
                        <flux:badge size="sm" :color="$piece->status->fluxColor()">{{ $piece->status->getLabel() }}</flux:badge>
                    </a>
                @empty
                    <flux:text class="px-3 py-2 text-zinc-500">Aún no hay piezas.</flux:text>
                @endforelse
            </div>
        </section>
    </div>
</div>
