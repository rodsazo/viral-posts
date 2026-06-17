<div class="flex flex-col gap-8">
    {{-- Totales --}}
    <section>
        <flux:heading size="lg" class="mb-3">Resumen de {{ $account->name }}</flux:heading>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ($totals as $label => $count)
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="text-2xl font-bold">{{ $count }}</div>
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
                    <flux:badge size="sm" :color="$stage['count'] > 0 ? 'amber' : 'zinc'">{{ $stage['count'] }}</flux:badge>
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
                        <flux:badge size="sm" color="zinc">{{ $piece->status->getLabel() }}</flux:badge>
                    </a>
                @empty
                    <flux:text class="px-3 py-2 text-zinc-500">Aún no hay piezas.</flux:text>
                @endforelse
            </div>
        </section>
    </div>
</div>
