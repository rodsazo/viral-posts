<div>
    <flux:heading size="lg" class="mb-4">Pipeline de producción</flux:heading>

    <div
        x-data="studioKanban(@js($this->statusCounts()))"
        wire:ignore
        class="flex gap-4 overflow-x-auto pb-4"
    >
        {{-- Franja superior por estado: pinta el tablero como una progresión de color. --}}
        @php($columnAccent = [
            'borrador' => 'bg-zinc-400',
            'planificacion' => 'bg-violet-400',
            'guion_listo' => 'bg-blue-400',
            'lista_para_grabacion' => 'bg-cyan-400',
            'grabada' => 'bg-amber-400',
            'editada' => 'bg-pink-400',
            'publicada' => 'bg-green-400',
        ])
        @foreach ($columns as $column)
            @php($status = $column['status'])
            <div class="flex w-72 shrink-0 flex-col overflow-hidden rounded-xl bg-zinc-100 dark:bg-white/5">
                <div class="h-1 {{ $columnAccent[$status->value] ?? 'bg-zinc-400' }}"></div>
                <header class="flex items-center justify-between px-3 py-2">
                    <span class="flex items-center gap-2 text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                        <span class="size-2 rounded-full {{ $columnAccent[$status->value] ?? 'bg-zinc-400' }}"></span>
                        {{ $status->getLabel() }}
                    </span>
                    <span
                        class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-zinc-200 px-1.5 text-xs font-medium text-zinc-700 dark:bg-white/10 dark:text-zinc-300"
                        x-text="counts['{{ $status->value }}']"
                    ></span>
                </header>

                <div class="flex min-h-24 flex-1 flex-col gap-2 px-2 pb-3" data-status="{{ $status->value }}" x-init="initColumn($el)">
                    @foreach ($column['pieces'] as $piece)
                        <div
                            data-id="{{ $piece->id }}"
                            class="cursor-grab rounded-lg border border-zinc-200 bg-white p-3 shadow-sm active:cursor-grabbing dark:border-white/10 dark:bg-zinc-900"
                        >
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $piece->title }}</p>

                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                @if ($piece->objective)
                                    <flux:badge size="sm" color="blue">{{ $piece->objective->getLabel() }}</flux:badge>
                                @endif
                                @if ($piece->format)
                                    <flux:badge size="sm" color="zinc">{{ $piece->format->getLabel() }}</flux:badge>
                                @endif
                            </div>

                            @if ($piece->winningIdea)
                                <p class="mt-2 truncate text-xs text-zinc-500 dark:text-zinc-400" title="{{ $piece->winningIdea->title }}">💡 {{ $piece->winningIdea->title }}</p>
                            @else
                                <p class="mt-2 text-xs italic text-zinc-400 dark:text-zinc-500">Pieza suelta</p>
                            @endif

                            <a href="{{ route('studio.pieces', [$account, 'piece' => $piece->id]) }}" class="mt-2 inline-block text-xs font-medium text-amber-600 hover:underline dark:text-amber-400">
                                Abrir en composer →
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <script>
        function studioKanban(initialCounts) {
            return {
                counts: initialCounts,
                initColumn(el) {
                    this.withSortable(() => {
                        window.Sortable.create(el, {
                            group: 'studio-kanban',
                            animation: 150,
                            ghostClass: 'opacity-40',
                            draggable: '[data-id]',
                            onEnd: (evt) => {
                                const from = evt.from.dataset.status;
                                const to = evt.to.dataset.status;
                                if (from === to) return;
                                this.counts[from] = Math.max(0, (this.counts[from] ?? 1) - 1);
                                this.counts[to] = (this.counts[to] ?? 0) + 1;
                                this.$wire.moveToStatus(parseInt(evt.item.dataset.id, 10), to);
                            },
                        });
                    });
                },
                withSortable(cb) {
                    if (window.Sortable) return cb();
                    document.addEventListener('sortablejs:ready', cb, { once: true });
                    if (window.__sortableLoading) return;
                    window.__sortableLoading = true;
                    const s = document.createElement('script');
                    s.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js';
                    s.onload = () => document.dispatchEvent(new Event('sortablejs:ready'));
                    document.head.appendChild(s);
                },
            };
        }
    </script>
</div>
