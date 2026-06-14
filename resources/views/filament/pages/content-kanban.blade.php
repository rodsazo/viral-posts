<x-filament-panels::page>
    <div
        x-data="kanbanBoard(@js($this->getStatusCounts()))"
        wire:ignore
        class="flex gap-4 overflow-x-auto pb-4"
    >
        @foreach ($this->getColumns() as $column)
            @php($status = $column['status'])
            <div class="flex w-72 shrink-0 flex-col rounded-xl bg-gray-100 dark:bg-white/5">
                <header class="flex items-center justify-between gap-2 px-3 py-2">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                        {{ $status->getLabel() }}
                    </span>
                    <span
                        class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-gray-200 px-1.5 text-xs font-medium text-gray-700 dark:bg-white/10 dark:text-gray-300"
                        x-text="counts['{{ $status->value }}']"
                    ></span>
                </header>

                <div
                    class="flex min-h-24 flex-1 flex-col gap-2 px-2 pb-3"
                    data-status="{{ $status->value }}"
                    x-init="initColumn($el)"
                >
                    @foreach ($column['pieces'] as $piece)
                        <div
                            data-id="{{ $piece->getKey() }}"
                            class="cursor-grab rounded-lg border border-gray-200 bg-white p-3 shadow-sm active:cursor-grabbing dark:border-white/10 dark:bg-gray-900"
                        >
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $piece->title }}
                            </p>

                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                @if ($piece->format)
                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600 dark:bg-white/10 dark:text-gray-300">
                                        {{ $piece->format->getLabel() }}
                                    </span>
                                @endif

                                @if ($piece->rating)
                                    <span class="inline-flex items-center rounded-md bg-amber-100 px-1.5 py-0.5 text-xs text-amber-700 dark:bg-amber-400/10 dark:text-amber-400">
                                        {{ $piece->rating->getLabel() }}
                                    </span>
                                @endif
                            </div>

                            @if ($piece->winningIdea)
                                <p class="mt-2 truncate text-xs text-gray-500 dark:text-gray-400" title="{{ $piece->winningIdea->title }}">
                                    💡 {{ $piece->winningIdea->title }}
                                </p>
                            @else
                                <p class="mt-2 text-xs italic text-gray-400 dark:text-gray-500">
                                    Pieza suelta
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <script>
        function kanbanBoard(initialCounts) {
            return {
                counts: initialCounts,

                initColumn(el) {
                    this.withSortable(() => {
                        window.Sortable.create(el, {
                            group: 'content-kanban',
                            animation: 150,
                            ghostClass: 'opacity-40',
                            onEnd: (evt) => {
                                const from = evt.from.dataset.status;
                                const to = evt.to.dataset.status;

                                if (from === to) {
                                    return;
                                }

                                this.counts[from] = Math.max(0, (this.counts[from] ?? 1) - 1);
                                this.counts[to] = (this.counts[to] ?? 0) + 1;

                                const id = parseInt(evt.item.dataset.id, 10);
                                this.$wire.moveToStatus(id, to);
                            },
                        });
                    });
                },

                // Carga SortableJS bajo demanda (robusto ante navegación SPA del panel).
                withSortable(callback) {
                    if (window.Sortable) {
                        callback();
                        return;
                    }

                    document.addEventListener('sortablejs:ready', callback, { once: true });

                    if (window.__sortableLoading) {
                        return;
                    }

                    window.__sortableLoading = true;
                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js';
                    script.onload = () => document.dispatchEvent(new Event('sortablejs:ready'));
                    document.head.appendChild(script);
                },
            };
        }
    </script>
</x-filament-panels::page>
