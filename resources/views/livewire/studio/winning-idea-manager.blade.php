<div>
    <div class="mb-4">
        <flux:heading size="xl">Ideas ganadoras</flux:heading>
        <flux:subheading>Gestiona tus ideas ganadoras: concepto, mecanismo, preguntas y ejemplos reales de viralidad.</flux:subheading>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Lista --}}
        <section class="lg:col-span-4">
            <div class="flex flex-col gap-2 rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-900">
                <flux:button wire:click="newIdea" variant="primary" icon="plus" size="sm" class="w-full">Nueva idea</flux:button>

                {{-- Filtro de la lista por seguidor ideal --}}
                <flux:select wire:model.live="filterFollowerId" size="sm" placeholder="Todos los seguidores">
                    <flux:select.option value="">Todos los seguidores</flux:select.option>
                    @foreach ($followers as $follower)
                        <flux:select.option value="{{ $follower->id }}">{{ $follower->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                {{-- Filtro por estado: por defecto oculta las descartadas. --}}
                <flux:select wire:model.live="filterStatus" size="sm">
                    <flux:select.option value="">Activas (sin descartadas)</flux:select.option>
                    @foreach ($ideaStatuses as $st)
                        <flux:select.option value="{{ $st->value }}">{{ $st->getLabel() }}</flux:select.option>
                    @endforeach
                    <flux:select.option value="todas">Todas (incl. descartadas)</flux:select.option>
                </flux:select>

                <div class="mt-1 flex max-h-[32rem] flex-col gap-1 overflow-y-auto">
                    @forelse ($ideas as $idea)
                        <button
                            type="button"
                            wire:key="idea-{{ $idea->id }}"
                            wire:click="selectIdea({{ $idea->id }})"
                            @class([
                                'flex flex-col gap-1.5 rounded-lg border px-3 py-2 text-left transition',
                                'border-violet-300 bg-violet-50 dark:border-violet-500/40 dark:bg-violet-500/10' => $selectedId === $idea->id,
                                'border-transparent hover:bg-zinc-50 dark:hover:bg-zinc-800/50' => $selectedId !== $idea->id,
                            ])
                        >
                            <span class="truncate text-sm font-medium">{{ $idea->title }}</span>
                            <div class="flex flex-wrap items-center gap-1.5">
                                <flux:badge size="sm" :color="$idea->status->fluxColor()" :icon="$idea->status->icon()">{{ $idea->status->getLabel() }}</flux:badge>
                                @if ($idea->idealFollower)
                                    <flux:badge size="sm" color="zinc" icon="user">{{ $idea->idealFollower->name }}</flux:badge>
                                @else
                                    <flux:badge size="sm" color="zinc" variant="subtle">Sin seguidor</flux:badge>
                                @endif
                                <flux:badge size="sm" :color="$idea->validationStatus()->fluxColor()" :icon="$idea->validationStatus()->icon()" :title="$idea->validationStatus()->getLabel()" />
                            </div>
                        </button>
                    @empty
                        <flux:text class="px-3 py-6 text-center text-zinc-500">
                            {{ $filterFollowerId ? 'No hay ideas para este seguidor.' : 'Aún no hay ideas. Crea la primera.' }}
                        </flux:text>
                    @endforelse
                </div>
            </div>
        </section>

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

                    {{-- El seguidor ideal es el centro: de él salen preguntas y mitos/verdades. --}}
                    <flux:select wire:model.live="idealFollowerId" label="Seguidor ideal" placeholder="Elige un seguidor" description="De este seguidor salen sus preguntas y sus mitos/verdades.">
                        <flux:select.option value="">Sin seguidor</flux:select.option>
                        @foreach ($followers as $follower)
                            <flux:select.option value="{{ $follower->id }}">{{ $follower->name }}</flux:select.option>
                        @endforeach
                    </flux:select>

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

                    <flux:textarea wire:model.blur="concept" label="Concepto" rows="4" placeholder="Explica el ángulo de la idea en 2-4 frases." />

                    {{-- Ejemplos reales --}}
                    <div class="flex flex-col gap-2">
                        <flux:subheading>Ejemplos reales (URLs)</flux:subheading>
                        <flux:text class="text-xs text-zinc-400">Posts virales de otros creadores con una idea similar. Con al menos uno, la idea queda <span class="font-medium">Validada</span>.</flux:text>

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

                    {{-- Preguntas que resuelve --}}
                    <div class="flex flex-col gap-2">
                        <flux:subheading>Preguntas que resuelve</flux:subheading>
                        <flux:input wire:model.live.debounce.300ms="questionSearch" size="sm" icon="magnifying-glass" placeholder="Buscar preguntas…" />
                        <div class="max-h-52 overflow-y-auto rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                            @forelse ($questions as $question)
                                <flux:checkbox wire:model.live="questionIds" value="{{ $question->id }}" label="{{ $question->body }}" />
                            @empty
                                <flux:text class="text-zinc-500">No hay preguntas que coincidan.</flux:text>
                            @endforelse
                        </div>
                    </div>

                    {{-- Contexto multi-salto: mitos/verdades derivados --}}
                    @if (count($this->contextBeliefs))
                        <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/40">
                            <flux:subheading class="mb-1">Mitos y verdades en juego</flux:subheading>
                            <ul class="list-disc space-y-1 pl-5 text-sm text-zinc-600 dark:text-zinc-300">
                                @foreach ($this->contextBeliefs as $belief)
                                    <li>{{ $belief }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @else
                <div class="rounded-xl border border-dashed border-zinc-300 p-10 text-center dark:border-zinc-700">
                    <flux:text class="text-zinc-500">Elige una idea de la lista o crea una nueva para empezar a editarla.</flux:text>
                </div>
            @endif
        </section>
    </div>
</div>
