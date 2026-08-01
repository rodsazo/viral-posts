<div>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Lista (mismo patrón que el Composer: sin contenedor, filtros arriba, cada idea es un card) --}}
        <aside class="lg:col-span-4">
            <div class="mb-3 flex items-center justify-between">
                <flux:heading size="lg">Ideas</flux:heading>
                <flux:button wire:click="newIdea" size="sm" variant="primary" icon="plus">Nueva</flux:button>
            </div>

            {{-- Filtro por estado --}}
            <flux:select wire:model.live="filterStatus" size="sm" class="mb-2">
                <flux:select.option value="">Activas (sin descartadas)</flux:select.option>
                @foreach ($ideaStatuses as $st)
                    <flux:select.option value="{{ $st->value }}">{{ $st->getLabel() }}</flux:select.option>
                @endforeach
                <flux:select.option value="todas">Todas (incl. descartadas)</flux:select.option>
            </flux:select>

            {{-- Filtro por piezas en el periodo activo --}}
            <flux:select wire:model.live="filterPieces" size="sm" class="mb-2">
                <flux:select.option value="todas">Con y sin piezas</flux:select.option>
                <flux:select.option value="con">Con piezas este periodo</flux:select.option>
                <flux:select.option value="sin">Sin piezas este periodo</flux:select.option>
            </flux:select>

            {{-- Periodo en el que se cuentan las piezas (el activo de la cabecera). --}}
            <p class="mb-3 flex items-center gap-1.5 text-xs text-zinc-500">
                <flux:icon.calendar-days variant="micro" class="size-3.5 text-amber-400" />
                Piezas contadas en:
                <span class="font-medium text-zinc-600 dark:text-zinc-300">{{ $activePeriod?->name ?? 'Sin periodo' }}</span>
            </p>

            <div class="flex flex-col gap-1">
                @forelse ($ideas as $idea)
                    <button
                        type="button"
                        wire:key="idea-{{ $idea->id }}"
                        wire:click="selectIdea({{ $idea->id }})"
                        @class([
                            'flex flex-col gap-1.5 rounded-lg border px-3 py-2 text-left transition',
                            'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:bg-zinc-800' => $selectedId !== $idea->id,
                            'border-amber-400 bg-amber-50 dark:border-amber-500/50 dark:bg-amber-500/10' => $selectedId === $idea->id,
                        ])
                    >
                        <span class="truncate text-sm font-medium">{{ $idea->title }}</span>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <flux:badge size="sm" :color="$idea->status->fluxColor()" :icon="$idea->status->icon()">{{ $idea->status->getLabel() }}</flux:badge>
                            @php($pieceCount = (int) ($pieceCounts[$idea->id] ?? 0))
                            <flux:badge size="sm" :color="$pieceCount > 0 ? 'violet' : 'zinc'" icon="film" :title="'Piezas en '.($activePeriod?->name ?? 'sin periodo')">{{ $pieceCount }}</flux:badge>
                            <flux:badge size="sm" :color="$idea->validationStatus()->fluxColor()" :icon="$idea->validationStatus()->icon()" :title="$idea->validationStatus()->getLabel()" />
                            @if ($idea->isImported())
                                <flux:badge size="sm" color="violet" icon="arrow-down-tray" :title="$idea->viralReferent?->name ? 'Importada de '.$idea->viralReferent->name : 'Importada'">Importada</flux:badge>
                            @endif
                        </div>
                    </button>
                @empty
                    <flux:text class="py-6 text-center text-zinc-500">Aún no hay ideas. Crea la primera.</flux:text>
                @endforelse
            </div>
        </aside>

        {{-- Editor --}}
        <section class="lg:col-span-8">
            @if ($selectedId)
                <div class="flex flex-col gap-5 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                    {{-- Cabecera: validación + guardado + borrar --}}
                    <div class="flex items-center justify-between gap-3">
                        <flux:badge :color="$this->validationStatus->fluxColor()" :icon="$this->validationStatus->icon()">
                            {{ $this->validationStatus->getLabel() }}
                        </flux:badge>
                        <div class="flex items-center gap-2">
                            @if ($saved)
                                <flux:badge size="sm" color="green" icon="check">Guardado</flux:badge>
                            @endif
                            <flux:button wire:click="createPieceFromIdea({{ $selectedId }})" size="sm" icon="film">Crear pieza</flux:button>
                            @if ($this->canDelete())
                                <flux:button wire:click="deleteIdea({{ $selectedId }})" wire:confirm="¿Eliminar esta idea ganadora?" variant="subtle" size="sm" icon="trash" />
                            @endif
                            {{-- Autoguarda al salir de cada campo; este botón guarda todo de una y da confirmación clara. --}}
                            <flux:button wire:click="save" variant="primary" size="sm" icon="check">Guardar</flux:button>
                        </div>
                    </div>

                    <flux:input wire:model.blur="title" label="Título" />

                    {{-- Estado del flujo: borrador → hipótesis → fija | descartada. --}}
                    <flux:select wire:model.live="status" label="Estado" description="Marca como Fija las ideas que ya te dan resultado (para hacer más contenido); Descartada las que no cuajan.">
                        @foreach ($ideaStatuses as $st)
                            <flux:select.option value="{{ $st->value }}">{{ $st->getLabel() }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    {{-- Ocultos por ahora (Mecanismo de viralidad y Plantilla Heras): se retomarán
                         cuando aprovechemos mejor esas relaciones.
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <flux:select wire:model.live="viral_mechanism" label="Mecanismo de viralidad" placeholder="Sin definir">
                            <flux:select.option value="">Sin definir</flux:select.option>
                            @foreach ($mechanisms as $mechanism)
                                <flux:select.option value="{{ $mechanism->value }}">{{ $mechanism->getLabel() }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:select wire:model.live="heras_template_id" label="Plantilla Heras" placeholder="Sin plantilla">
                            <flux:select.option value="">Sin plantilla</flux:select.option>
                            @foreach ($herasTemplates as $template)
                                <flux:select.option value="{{ $template->id }}">{{ $template->display_name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                    --}}

                    <flux:textarea wire:model.blur="concept" label="Concepto / estructura" rows="5" placeholder="Describe el FORMATO: estructura, condiciones y consideraciones para hacer el video (no el video en sí)." />

                    {{-- Ejemplos reales --}}
                    <div class="flex flex-col gap-2">
                        <flux:subheading>Ejemplos reales (URLs)</flux:subheading>
                        <flux:text class="text-xs text-zinc-400">Posts virales de otros creadores con este formato. Con al menos uno, la idea queda <span class="font-medium">Validada</span>.</flux:text>

                        @if (count($exampleUrls))
                            <div class="flex flex-col gap-2">
                                @foreach ($exampleUrls as $i => $url)
                                    <div class="flex items-center gap-2" wire:key="ex-{{ $i }}">
                                        <flux:input wire:model.blur="exampleUrls.{{ $i }}" class="flex-1" icon="link" />
                                        <flux:button wire:click="removeExampleUrl({{ $i }})" wire:confirm="¿Quitar este ejemplo?" variant="subtle" size="sm" icon="x-mark" />
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex items-center gap-2">
                            <flux:input
                                wire:model="newExampleUrl"
                                wire:keydown.enter.prevent="addExampleUrl"
                                class="flex-1"
                                icon="link"
                                placeholder="https://instagram.com/p/…  ó  https://tiktok.com/@…"
                            />
                            <flux:button wire:click="addExampleUrl" variant="filled" size="sm" icon="plus">Añadir</flux:button>
                        </div>
                    </div>
                </div>

                {{-- Piezas de esta idea en el periodo activo (enlaces al Composer). --}}
                <div class="mt-5 flex flex-col gap-3 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-between gap-2">
                        <flux:heading size="sm" class="flex items-center gap-1.5">
                            <flux:icon.film variant="micro" class="size-4 text-violet-400" />
                            Piezas de esta idea · {{ $activePeriod?->name ?? 'Sin periodo' }}
                        </flux:heading>
                        <flux:badge size="sm" :color="count($this->piecesForIdea) ? 'violet' : 'zinc'">{{ count($this->piecesForIdea) }}</flux:badge>
                    </div>

                    @forelse ($this->piecesForIdea as $piece)
                        <a
                            href="{{ route('studio.pieces', ['account' => $account, 'piece' => $piece->id]) }}"
                            wire:navigate
                            wire:key="piece-{{ $piece->id }}"
                            class="flex items-center justify-between gap-2 rounded-lg border border-zinc-200 px-3 py-2 transition hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800"
                        >
                            <span class="truncate text-sm font-medium">{{ $piece->title }}</span>
                            <flux:badge size="sm" :color="$piece->status->fluxColor()">{{ $piece->status->getLabel() }}</flux:badge>
                        </a>
                    @empty
                        <flux:text class="text-sm text-zinc-500">
                            No hay piezas de esta idea en este periodo.
                            <button type="button" wire:click="createPieceFromIdea({{ $selectedId }})" class="font-medium text-violet-500 hover:underline">Crea una</button>.
                        </flux:text>
                    @endforelse
                </div>
            @else
                <div class="rounded-xl border border-dashed border-zinc-300 p-10 text-center dark:border-zinc-700">
                    <flux:text class="text-zinc-500">Elige una idea de la lista o crea una nueva para empezar a editarla.</flux:text>
                </div>
            @endif
        </section>
    </div>
</div>
