<div>
    <div class="mb-4">
        <flux:heading size="xl">Generador de piezas</flux:heading>
        <flux:subheading>Elige los parámetros, deja que la IA proponga guiones y crea una pieza por cada uno que te guste.</flux:subheading>
    </div>

    @unless ($this->aiEnabled)
        <flux:callout variant="warning" icon="key" class="mb-4">
            El asistente de IA no está configurado. Define <code>ANTHROPIC_API_KEY</code> en el entorno para usar el generador.
        </flux:callout>
    @endunless

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Paso 1: parámetros --}}
        <section class="lg:col-span-5">
            <div class="flex flex-col gap-4 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <flux:heading size="lg">1 · Parámetros</flux:heading>

                {{-- 1) Idea ganadora (formato a replicar); entre corchetes, nº de piezas ya creadas. --}}
                <flux:select wire:model.live="winning_idea_id" label="Idea ganadora" placeholder="Sin idea (pieza suelta)" description="El formato a replicar. Es independiente del seguidor: puedes usar la misma idea para distintos seguidores.">
                    <flux:select.option value="">Sin idea (pieza suelta)</flux:select.option>
                    @foreach ($ideas as $idea)
                        <flux:select.option value="{{ $idea->id }}">{{ $idea->title }} · {{ $idea->status->getLabel() }} [{{ $idea->content_pieces_count }} piezas]</flux:select.option>
                    @endforeach
                </flux:select>

                {{-- 2) Seguidor ideal: de él salen el contexto (preguntas/mitos/dolores). --}}
                <flux:select wire:model.live="idealFollowerId" label="Seguidor ideal" placeholder="Elige un seguidor ideal" description="A quién va dirigido. Sus opciones muestran el nivel de conciencia (0-4) y el nombre; de él salen las preguntas/mitos/dolores.">
                    <flux:select.option value="">Elige un seguidor ideal</flux:select.option>
                    @foreach ($followers as $follower)
                        <flux:select.option value="{{ $follower->id }}">@if ($follower->awareness_level) {{ $follower->awareness_level->value }} · @endif{{ $follower->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <div class="grid grid-cols-2 gap-4">
                    <flux:select wire:model="objective" label="Objetivo" placeholder="Sin objetivo">
                        <flux:select.option value="">Sin objetivo</flux:select.option>
                        @foreach (\App\Enums\ContentObjective::cases() as $case)
                            <flux:select.option value="{{ $case->value }}">{{ $case->getLabel() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model="format" label="Formato" placeholder="Sin formato">
                        <flux:select.option value="">Sin formato</flux:select.option>
                        @foreach (\App\Enums\ContentFormat::cases() as $case)
                            <flux:select.option value="{{ $case->value }}">{{ $case->getLabel() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:textarea wire:model="instructions" label="Instrucciones adicionales (opcional)" rows="2" placeholder="Ángulo, tono, detalles del post…" />

                {{-- Contexto del seguidor elegido: preguntas / mitos / dolores a enviar --}}
                @if ($idealFollowerId)
                    <div class="flex flex-col gap-2">
                        <div>
                            <flux:subheading class="mb-1">Preguntas a enviar</flux:subheading>
                            <div class="max-h-40 overflow-y-auto rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                                @forelse ($this->followerQuestions as $question)
                                    <flux:checkbox wire:model="questionIds" value="{{ $question->id }}" label="{{ $question->body }}" />
                                @empty
                                    <flux:text class="text-zinc-500">Este seguidor no tiene preguntas.</flux:text>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <flux:subheading class="mb-1">Mitos/verdades a enviar</flux:subheading>
                            <div class="max-h-40 overflow-y-auto rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                                @forelse ($this->followerBeliefs as $belief)
                                    <flux:checkbox wire:model="beliefIds" value="{{ $belief->id }}" label="[{{ $belief->type->getLabel() }}] {{ $belief->statement }}" />
                                @empty
                                    <flux:text class="text-zinc-500">Este seguidor aún no tiene mitos/verdades.</flux:text>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <flux:subheading class="mb-1">Dolores/problemas/deseos a enviar</flux:subheading>
                            <div class="max-h-40 overflow-y-auto rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                                @forelse ($this->followerPains as $pain)
                                    <flux:checkbox wire:model="painIds" value="{{ $pain->id }}" label="[{{ $pain->type->getLabel() }}] {{ $pain->body }}" />
                                @empty
                                    <flux:text class="text-zinc-500">Este seguidor aún no tiene dolores/deseos.</flux:text>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Plantillas de ganchos seleccionadas --}}
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <flux:subheading>Plantillas de ganchos <span class="text-zinc-400">({{ count($selectedHookIds) }}/5)</span></flux:subheading>
                        <flux:button wire:click="openHookPicker" size="xs" variant="subtle" icon="plus">Agregar gancho</flux:button>
                    </div>
                    @if (count($this->selectedHooks))
                        <div class="flex flex-wrap gap-2">
                            @foreach ($this->selectedHooks as $hook)
                                <span class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 py-1 pl-2 pr-1 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                                    @if ($hook->icon)<i class="{{ $hook->icon }}"></i>@endif
                                    {{ $hook->name }}
                                    <button type="button" wire:click="removeHook({{ $hook->id }})" class="rounded p-0.5 text-zinc-400 hover:bg-zinc-200 hover:text-zinc-700 dark:hover:bg-zinc-700">
                                        <flux:icon.x-mark variant="micro" />
                                    </button>
                                </span>
                            @endforeach
                        </div>
                    @else
                        <flux:text class="text-xs text-zinc-400">Sin ganchos. La IA inventará los ganchos de las 5 variantes.</flux:text>
                    @endif
                </div>

                {{-- CTA hacia la que debe fluir el guión (de momento, una sola) --}}
                <div class="flex flex-col gap-2">
                    <flux:subheading>Llamada a la acción (CTA)</flux:subheading>
                    @if (count($ctas))
                        <div class="max-h-44 overflow-y-auto rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                            <flux:radio.group wire:model.live="selectedCtaId">
                                <flux:radio value="" label="Sin CTA (la IA decide el cierre)" />
                                @foreach ($ctas as $cta)
                                    <flux:radio value="{{ $cta->id }}" label="[{{ $cta->category->getLabel() }}] {{ $cta->text }}" />
                                @endforeach
                            </flux:radio.group>
                        </div>
                        <flux:text class="text-xs text-zinc-400">Si eliges una, todas las variantes fluirán hacia esa CTA.</flux:text>
                    @else
                        <flux:text class="text-xs text-zinc-400">
                            No hay CTAs todavía. Créalas en <a href="{{ route('studio.ctas', $account) }}" class="underline" wire:navigate>📣 CTAs</a>.
                        </flux:text>
                    @endif
                </div>

                {{-- Contexto en vivo de la idea (cuando no se elige seguidor manualmente) --}}
                @if (! $idealFollowerId && (count($this->contextQuestions) || count($this->contextBeliefs) || count($this->contextPains)))
                    <flux:separator />
                    <div class="text-sm">
                        @if (count($this->contextQuestions))
                            <flux:subheading>Preguntas que responde</flux:subheading>
                            <ul class="mt-1 list-disc space-y-1 pl-5 text-zinc-600 dark:text-zinc-300">
                                @foreach ($this->contextQuestions as $q)
                                    <li>{{ $q }}</li>
                                @endforeach
                            </ul>
                        @endif
                        @if (count($this->contextBeliefs))
                            <flux:subheading class="mt-3">Mitos/verdades a tratar</flux:subheading>
                            <ul class="mt-1 list-disc space-y-1 pl-5 text-zinc-600 dark:text-zinc-300">
                                @foreach ($this->contextBeliefs as $b)
                                    <li>{{ $b }}</li>
                                @endforeach
                            </ul>
                        @endif
                        @if (count($this->contextPains))
                            <flux:subheading class="mt-3">Dolores/deseos a tocar</flux:subheading>
                            <ul class="mt-1 list-disc space-y-1 pl-5 text-zinc-600 dark:text-zinc-300">
                                @foreach ($this->contextPains as $p)
                                    <li>{{ $p }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif

                <flux:button
                    wire:click="generate"
                    icon="sparkles"
                    variant="primary"
                    :disabled="! $this->aiEnabled || $this->generating"
                >
                    {{ $this->generating ? 'Generando…' : (count($suggestions) ? 'Regenerar guiones' : 'Generar guiones') }}
                </flux:button>
            </div>
        </section>

        {{-- Paso 2-3: sugerencias + creación --}}
        <section class="lg:col-span-7">
            @if ($aiError)
                <flux:callout variant="danger" icon="exclamation-triangle" class="mb-4">{{ $aiError }}</flux:callout>
            @endif

            @if ($this->generating)
                {{-- Generación en cola: sondeamos hasta que el job termine. --}}
                <div wire:poll.2s="pollGeneration" class="rounded-xl border border-dashed border-zinc-300 p-10 text-center dark:border-zinc-700">
                    <flux:icon.arrow-path class="mx-auto mb-3 size-6 animate-spin text-zinc-400" />
                    <flux:text class="text-zinc-500">Generando guiones en segundo plano… puede tardar un momento.</flux:text>
                </div>
            @elseif (count($suggestions))
                <div class="mb-3 flex items-center justify-between">
                    <flux:heading size="lg">2 · Elige los guiones</flux:heading>
                    <flux:button
                        wire:click="createPieces"
                        icon="document-plus"
                        variant="primary"
                        size="sm"
                        :disabled="! count($selected)"
                    >
                        Crear {{ count($selected) ?: '' }} {{ count($selected) === 1 ? 'pieza' : 'piezas' }}
                    </flux:button>
                </div>

                <div class="flex flex-col gap-3">
                    @foreach ($suggestions as $i => $suggestion)
                        <label class="flex cursor-pointer gap-3 rounded-lg border border-zinc-200 p-4 transition hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800/50">
                            <flux:checkbox wire:model.live="selected" value="{{ $i }}" />
                            <div class="min-w-0 flex-1">
                                <flux:badge size="sm" color="zinc" class="mb-2">{{ $suggestion['label'] }}</flux:badge>
                                <p class="whitespace-pre-line text-sm text-zinc-600 dark:text-zinc-300">{{ $suggestion['preview'] }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            @else
                <div class="rounded-xl border border-dashed border-zinc-300 p-10 text-center dark:border-zinc-700">
                    <flux:text class="text-zinc-500">Configura los parámetros a la izquierda y pulsa “Generar guiones”. Aparecerán aquí las sugerencias.</flux:text>
                </div>
            @endif
        </section>
    </div>

    {{-- Modal: selector de plantillas de gancho --}}
    <flux:modal name="hook-picker" class="md:w-[52rem]">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Plantillas de ganchos</flux:heading>
                <flux:subheading>Elige hasta 5. La IA usará uno por variante; los que falten los inventará.</flux:subheading>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <flux:input wire:model.live.debounce.300ms="hookSearch" placeholder="Buscar por nombre u objetivo…" icon="magnifying-glass" />
                <flux:select wire:model.live="hookReferentFilter" placeholder="Todos los referentes">
                    <flux:select.option value="">Todos los referentes</flux:select.option>
                    @foreach ($hookReferents as $referent)
                        <flux:select.option value="{{ $referent->id }}">{{ $referent->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            @php($selectedCount = count($modalHookIds))
            <div class="grid max-h-[26rem] grid-cols-1 gap-3 overflow-y-auto pr-1 sm:grid-cols-2">
                @forelse ($hooks as $hook)
                    @php($checked = in_array((string) $hook->id, array_map('strval', $modalHookIds), true))
                    @php($examples = collect([
                        'Genérico' => $hook->example_generic,
                        'Salud' => $hook->example_health,
                        'Sexo' => $hook->example_sex,
                        'Dinero' => $hook->example_money,
                        'Desarrollo Personal' => $hook->example_personal_dev,
                    ])->filter()->map(fn ($t, $l) => ['label' => $l, 'text' => $t])->values())
                    <div @class([
                        'rounded-lg border p-3',
                        'border-amber-400 bg-amber-50 dark:border-amber-500/50 dark:bg-amber-500/10' => $checked,
                        'border-zinc-200 dark:border-zinc-800' => ! $checked,
                    ])>
                        <div class="flex items-start gap-3">
                            <flux:checkbox wire:model.live="modalHookIds" value="{{ $hook->id }}" :disabled="! $checked && $selectedCount >= 5" />
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($hook->icon)<i class="{{ $hook->icon }} text-zinc-500"></i>@endif
                                    <span class="font-medium">{{ $hook->name }}</span>
                                    <flux:badge size="sm" color="gray">{{ $hook->viralReferent?->name }}</flux:badge>
                                </div>
                                @if ($hook->objective)
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $hook->objective }}</p>
                                @endif

                                {{-- Carrusel de ejemplos --}}
                                <div x-data="{ i: 0, slides: @js($examples) }" class="mt-2 rounded-md bg-zinc-50 p-2 dark:bg-zinc-800/60">
                                    <template x-if="slides.length === 0">
                                        <p class="text-xs italic text-zinc-400">Sin ejemplos.</p>
                                    </template>
                                    <template x-if="slides.length">
                                        <div>
                                            <div class="mb-1 flex items-center justify-between">
                                                <span class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500" x-text="slides[i].label"></span>
                                                <span class="text-xs text-zinc-400" x-show="slides.length > 1" x-text="(i + 1) + '/' + slides.length"></span>
                                            </div>
                                            <p class="text-xs text-zinc-600 dark:text-zinc-300" x-text="slides[i].text"></p>
                                            <div class="mt-2 flex justify-end gap-1" x-show="slides.length > 1">
                                                <button type="button" @click="i = (i - 1 + slides.length) % slides.length" class="rounded px-1.5 text-zinc-500 hover:bg-zinc-200 dark:hover:bg-zinc-700">‹</button>
                                                <button type="button" @click="i = (i + 1) % slides.length" class="rounded px-1.5 text-zinc-500 hover:bg-zinc-200 dark:hover:bg-zinc-700">›</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <flux:text class="col-span-full text-zinc-500">No hay plantillas de gancho que coincidan.</flux:text>
                @endforelse
            </div>

            <div class="flex items-center justify-between">
                <flux:text class="text-sm text-zinc-500">{{ $selectedCount }}/5 seleccionados</flux:text>
                <flux:button wire:click="confirmHooks" variant="primary" icon="check">Confirmar</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
