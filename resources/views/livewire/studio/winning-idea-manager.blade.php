<div>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Lista (mismo patrón que el Composer: sin contenedor, filtros arriba, cada idea es un card) --}}
        <aside class="lg:col-span-4">
            <div class="mb-3 flex items-center justify-between">
                <flux:heading size="lg">Ideas</flux:heading>
                <flux:button wire:click="newIdea" size="sm" variant="primary" icon="plus">Nueva</flux:button>
            </div>

            {{-- Filtro por estado --}}
            <flux:select wire:model.live="filterStatus" size="sm" class="mb-3">
                <flux:select.option value="">Activas (sin descartadas)</flux:select.option>
                @foreach ($ideaStatuses as $st)
                    <flux:select.option value="{{ $st->value }}">{{ $st->getLabel() }}</flux:select.option>
                @endforeach
                <flux:select.option value="todas">Todas (incl. descartadas)</flux:select.option>
            </flux:select>

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
                            @if ($this->canDelete())
                                <flux:button wire:click="deleteIdea({{ $selectedId }})" wire:confirm="¿Eliminar esta idea ganadora?" variant="subtle" size="sm" icon="trash" />
                            @endif
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
            @else
                <div class="rounded-xl border border-dashed border-zinc-300 p-10 text-center dark:border-zinc-700">
                    <flux:text class="text-zinc-500">Elige una idea de la lista o crea una nueva para empezar a editarla.</flux:text>
                </div>
            @endif
        </section>
    </div>
</div>
