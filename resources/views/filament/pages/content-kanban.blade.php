<x-filament-panels::page>
    <div
        x-data="kanbanBoard(@js($this->getStatusCounts()))"
        wire:ignore
        class="vp-kanban-board"
    >
        @foreach ($this->getColumns() as $column)
            @php($status = $column['status'])
            <div class="vp-kanban-col">
                <header class="vp-kanban-col-head">
                    <span>{{ $status->getLabel() }}</span>
                    <span class="vp-kanban-count" x-text="counts['{{ $status->value }}']"></span>
                </header>

                <div class="vp-kanban-list" data-status="{{ $status->value }}" x-init="initColumn($el)">
                    @foreach ($column['pieces'] as $piece)
                        <div class="vp-kanban-card" data-id="{{ $piece->getKey() }}">
                            <p class="vp-kanban-card-title">{{ $piece->title }}</p>

                            <div class="vp-kanban-tags">
                                @if ($piece->format)
                                    <span class="vp-kanban-tag">{{ $piece->format->getLabel() }}</span>
                                @endif
                                @if ($piece->rating)
                                    <span class="vp-kanban-tag vp-kanban-tag--rating">{{ $piece->rating->getLabel() }}</span>
                                @endif
                            </div>

                            @if ($piece->winningIdea)
                                <p class="vp-kanban-meta" title="{{ $piece->winningIdea->title }}">
                                    💡 {{ \Illuminate\Support\Str::limit($piece->winningIdea->title, 40) }}
                                </p>
                            @else
                                <p class="vp-kanban-meta vp-kanban-meta--empty">Pieza suelta</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <style>
        .vp-kanban-board {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding-bottom: 1rem;
            align-items: flex-start;
        }
        .vp-kanban-col {
            flex: 0 0 18rem;
            width: 18rem;
            display: flex;
            flex-direction: column;
            background: #f3f4f6;
            border-radius: 0.75rem;
        }
        .dark .vp-kanban-col { background: rgba(255, 255, 255, 0.05); }
        .vp-kanban-col-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            padding: 0.625rem 0.75rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #374151;
        }
        .dark .vp-kanban-col-head { color: #e5e7eb; }
        .vp-kanban-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.25rem;
            height: 1.25rem;
            padding: 0 0.375rem;
            border-radius: 9999px;
            background: rgba(0, 0, 0, 0.08);
            font-size: 0.7rem;
            font-weight: 600;
        }
        .dark .vp-kanban-count { background: rgba(255, 255, 255, 0.1); }
        .vp-kanban-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            padding: 0 0.5rem 0.75rem;
            min-height: 4rem;
            flex: 1;
        }
        .vp-kanban-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 0.75rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            cursor: grab;
        }
        .vp-kanban-card:active { cursor: grabbing; }
        .dark .vp-kanban-card {
            background: #18181b;
            border-color: rgba(255, 255, 255, 0.1);
        }
        .vp-kanban-card-title {
            margin: 0;
            font-size: 0.875rem;
            font-weight: 500;
            color: #111827;
        }
        .dark .vp-kanban-card-title { color: #f4f4f5; }
        .vp-kanban-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.375rem;
            margin-top: 0.5rem;
        }
        .vp-kanban-tag {
            font-size: 0.7rem;
            padding: 0.1rem 0.4rem;
            border-radius: 0.375rem;
            background: rgba(0, 0, 0, 0.06);
            color: #4b5563;
        }
        .dark .vp-kanban-tag { background: rgba(255, 255, 255, 0.1); color: #d4d4d8; }
        .vp-kanban-tag--rating { background: rgba(245, 158, 11, 0.15); color: #b45309; }
        .dark .vp-kanban-tag--rating { color: #fbbf24; }
        .vp-kanban-meta {
            margin: 0.5rem 0 0;
            font-size: 0.72rem;
            color: #6b7280;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .dark .vp-kanban-meta { color: #9ca3af; }
        .vp-kanban-meta--empty { font-style: italic; color: #9ca3af; }
        .vp-kanban-ghost { opacity: 0.4; }
    </style>

    <script>
        function kanbanBoard(initialCounts) {
            return {
                counts: initialCounts,

                initColumn(el) {
                    this.withSortable(() => {
                        window.Sortable.create(el, {
                            group: 'content-kanban',
                            animation: 150,
                            ghostClass: 'vp-kanban-ghost',
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
