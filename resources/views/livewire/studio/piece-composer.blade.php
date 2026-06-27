<div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
    {{-- Lista de piezas --}}
    <aside class="lg:col-span-3">
        <div class="mb-3 flex items-center justify-between">
            <flux:heading size="lg">Piezas</flux:heading>
            <flux:button wire:click="newPiece" size="sm" variant="primary" icon="plus">Nueva</flux:button>
        </div>

        {{-- Filtro por estado: concentrarse en borradores, planificación, etc. --}}
        <flux:select wire:model.live="statusFilter" size="sm" class="mb-3">
            <flux:select.option value="">Todos los estados</flux:select.option>
            @foreach (\App\Enums\ContentStatus::cases() as $case)
                <flux:select.option value="{{ $case->value }}">{{ $case->getLabel() }}</flux:select.option>
            @endforeach
        </flux:select>

        <div class="flex flex-col gap-1">
            @forelse ($pieces as $piece)
                <button
                    type="button"
                    wire:click="selectPiece({{ $piece->id }})"
                    @class([
                        'rounded-lg border px-3 py-2 text-left transition',
                        'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:bg-zinc-800' => $piece->id !== $pieceId,
                        'border-amber-400 bg-amber-50 dark:border-amber-500/50 dark:bg-amber-500/10' => $piece->id === $pieceId,
                    ])
                >
                    <div class="truncate text-sm font-medium">{{ $piece->title }}</div>
                    <div class="mt-1 flex flex-wrap items-center gap-1">
                        <flux:badge size="sm" :color="$piece->status->fluxColor()">{{ $piece->status->getLabel() }}</flux:badge>
                        @if ($piece->rum !== null)
                            <flux:badge size="sm" :color="\App\Support\Rum::fluxColor($piece->rum)">RUM {{ number_format($piece->rum, 1) }}</flux:badge>
                        @else
                            <flux:badge size="sm" color="zinc">RUM —</flux:badge>
                        @endif
                    </div>
                </button>
            @empty
                @if (filled($statusFilter))
                    <flux:text class="text-zinc-500">No hay piezas en este estado.</flux:text>
                @else
                    <flux:text class="text-zinc-500">Aún no hay piezas. Crea la primera.</flux:text>
                @endif
            @endforelse
        </div>
    </aside>

    @if ($pieceId)
        {{-- Composer --}}
        <section class="lg:col-span-6">
            <div class="mb-3 flex items-center gap-2">
                <flux:heading size="lg">Composer</flux:heading>
                <flux:badge x-show="$wire.saved" x-cloak size="sm" color="green" icon="check">Guardado</flux:badge>
                <flux:spacer />
                @if ($this->canDelete())
                    <flux:button
                        wire:click="deletePiece({{ $pieceId }})"
                        wire:confirm="¿Eliminar esta pieza de contenido? Esta acción no se puede deshacer."
                        variant="subtle"
                        size="sm"
                        icon="trash"
                    >Eliminar</flux:button>
                @endif
                {{-- Compartir: copia el enlace público (vista de cliente) al portapapeles. --}}
                @if ($this->publicUrl)
                    <span x-data="{ url: @js($this->publicUrl), copied: false }">
                        <flux:button
                            size="sm"
                            icon="share"
                            x-on:click="navigator.clipboard.writeText(url); copied = true; setTimeout(() => copied = false, 1500)"
                        >
                            <span x-show="!copied">Compartir</span>
                            <span x-show="copied" x-cloak>¡Copiado!</span>
                        </flux:button>
                    </span>
                @endif
                {{-- Autoguarda al salir de cada campo; este botón guarda todo de una y da confirmación clara. --}}
                <flux:button wire:click="save" variant="primary" size="sm" icon="check">Guardar</flux:button>
            </div>

            @php($tabBtn = 'rounded-t-md border-b-2 px-3 py-2 text-sm transition -mb-px')
            @php($tabOn = 'border-violet-400 font-medium text-zinc-900 dark:text-white')
            @php($tabOff = 'border-transparent text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300')
            <div x-data="{ tab: 'basicos' }" class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                {{-- Siempre visibles, encima de las pestañas. Título 2/3, Estado 1/3. --}}
                <div class="grid grid-cols-1 gap-4 border-b border-zinc-200 p-5 dark:border-zinc-800 sm:grid-cols-3 sm:items-start">
                    <div class="sm:col-span-2">
                        <flux:textarea wire:model.blur="title" label="Título de trabajo" rows="2" class="text-lg! font-bold!" />
                    </div>
                    <div class="sm:col-span-1">
                        <flux:select wire:model.live="status" label="Estado">
                            @foreach (\App\Enums\ContentStatus::cases() as $case)
                                <flux:select.option value="{{ $case->value }}">{{ $case->getLabel() }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>

                {{-- Pestañas --}}
                <div class="flex flex-wrap gap-1 border-b border-zinc-200 px-3 pt-2 dark:border-zinc-800">
                    <button type="button" @click="tab='basicos'" :class="tab==='basicos' ? '{{ $tabOn }}' : '{{ $tabOff }}'" class="{{ $tabBtn }}"><i class="fa-solid fa-circle-info mr-1.5"></i>Datos básicos</button>
                    <button type="button" @click="tab='guion'" :class="tab==='guion' ? '{{ $tabOn }}' : '{{ $tabOff }}'" class="{{ $tabBtn }}"><i class="fa-solid fa-clapperboard mr-1.5"></i>Guión</button>
                    <button type="button" @click="tab='rum'" :class="tab==='rum' ? '{{ $tabOn }}' : '{{ $tabOff }}'" class="{{ $tabBtn }}"><i class="fa-solid fa-gauge-high mr-1.5"></i>RUM</button>
                    <button type="button" @click="tab='produccion'" :class="tab==='produccion' ? '{{ $tabOn }}' : '{{ $tabOff }}'" class="{{ $tabBtn }}"><i class="fa-solid fa-video mr-1.5"></i>Producción</button>
                </div>

                <div class="p-5">
                    {{-- Pestaña: Datos básicos --}}
                    <div x-show="tab==='basicos'" class="flex flex-col gap-4">
                        <flux:select wire:model.live="winning_idea_id" label="Idea ganadora" placeholder="Sin idea (pieza suelta)">
                            <flux:select.option value="">Sin idea (pieza suelta)</flux:select.option>
                            @foreach ($ideas as $idea)
                                <flux:select.option value="{{ $idea->id }}">{{ $idea->title }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        {{-- El seguidor ideal es el centro: de él salen los mitos/verdades a tratar. --}}
                        <flux:select wire:model.live="idealFollowerId" label="Seguidor ideal" placeholder="Sin seguidor" description="Suele coincidir con el de la idea; de él salen los mitos/verdades a tratar.">
                            <flux:select.option value="">Sin seguidor</flux:select.option>
                            @foreach ($followers as $follower)
                                <flux:select.option value="{{ $follower->id }}">{{ $follower->name }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <div class="grid grid-cols-2 gap-4">
                            <flux:select wire:model.live="objective" label="Objetivo" placeholder="Sin objetivo">
                                <flux:select.option value="">Sin objetivo</flux:select.option>
                                @foreach (\App\Enums\ContentObjective::cases() as $case)
                                    <flux:select.option value="{{ $case->value }}">{{ $case->getLabel() }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:select wire:model.live="format" label="Formato" placeholder="Sin formato">
                                <flux:select.option value="">Sin formato</flux:select.option>
                                @foreach (\App\Enums\ContentFormat::cases() as $case)
                                    <flux:select.option value="{{ $case->value }}">{{ $case->getLabel() }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:select wire:model.live="rating" label="Calificación" placeholder="Sin calificar">
                                <flux:select.option value="">Sin calificar</flux:select.option>
                                @foreach (\App\Enums\ContentRating::cases() as $case)
                                    <flux:select.option value="{{ $case->value }}">{{ $case->getLabel() }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                    </div>

                    {{-- Pestaña: Guión --}}
                    <div x-show="tab==='guion'" x-cloak class="flex flex-col gap-4">
                        @if ($this->aiEnabled)
                            <div class="flex justify-end">
                                <flux:button wire:click="suggestScript" icon="sparkles" variant="subtle" size="sm">
                                    Sugerir guión (IA)
                                </flux:button>
                            </div>
                        @endif

                        <flux:textarea wire:model.blur="hookText" label="Gancho" rows="2" />
                        <flux:textarea wire:model.blur="story" label="Historia" rows="12" />
                        <flux:textarea wire:model.blur="moral" label="Moraleja" rows="4" />
                        <flux:textarea wire:model.blur="cta" label="CTA" rows="2" />
                    </div>

                    {{-- Pestaña: RUM --}}
                    <div x-show="tab==='rum'" x-cloak class="flex flex-col gap-4">
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Relevancia Única de Mercado — busca un RUM alto para más chance de viralidad.</p>

                        @foreach (\App\Support\Rum::FACTORS as $key => $factor)
                            <flux:select wire:model.live="rumFactors.{{ $key }}" label="{{ $factor['label'] }}" placeholder="Sin evaluar" description="{{ $factor['help'] }}">
                                <flux:select.option value="">Sin evaluar</flux:select.option>
                                @foreach ($factor['options'] as $val => $optLabel)
                                    <flux:select.option value="{{ $val }}">{{ $optLabel }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        @endforeach

                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium">RUM:</span>
                            @if ($this->rum !== null)
                                <flux:badge size="lg" :color="\App\Support\Rum::fluxColor($this->rum)">{{ number_format($this->rum, 1) }}</flux:badge>
                            @else
                                <flux:badge size="lg" color="zinc">Sin evaluar</flux:badge>
                            @endif
                        </div>
                    </div>

                    {{-- Pestaña: Producción --}}
                    <div x-show="tab==='produccion'" x-cloak class="flex flex-col gap-4">
                        {{-- Enlace público para que el cliente vea y valide la pieza (sin login). --}}
                        @if ($this->publicUrl)
                            <div class="rounded-xl border border-violet-200 bg-violet-50 p-4 dark:border-violet-500/30 dark:bg-violet-500/10"
                                 x-data="{ url: @js($this->publicUrl), copied: false }">
                                <div class="flex items-center gap-2">
                                    <flux:icon.share variant="micro" class="size-4 text-violet-500" />
                                    <flux:heading size="sm">Enlace para el cliente</flux:heading>
                                </div>
                                <flux:text class="mt-1 text-zinc-500">Vista pública (sin login) para que la revise y valide el guión.</flux:text>
                                <div class="mt-3 flex items-center gap-2">
                                    <input type="text" readonly :value="url"
                                           class="flex-1 truncate rounded-lg border border-zinc-200 bg-white px-3 py-1.5 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300" />
                                    <flux:button size="sm" variant="primary" icon="clipboard-document"
                                                 x-on:click="navigator.clipboard.writeText(url); copied = true; setTimeout(() => copied = false, 1500)">
                                        <span x-show="!copied">Copiar</span>
                                        <span x-show="copied" x-cloak>¡Copiado!</span>
                                    </flux:button>
                                    <flux:button size="sm" variant="ghost" icon="arrow-top-right-on-square" :href="$this->publicUrl" target="_blank">Abrir</flux:button>
                                </div>
                            </div>
                        @endif

                        <flux:input wire:model.blur="location" label="Locación" placeholder="¿Dónde se graba?" />
                        <flux:textarea wire:model.blur="equipment" label="Equipo necesario" rows="3" placeholder="Cámara, micro, trípode, luces…" />
                        <flux:textarea wire:model.blur="people" label="Personas y personajes" rows="3" placeholder="Quién aparece / qué personajes hacen falta" />

                        <flux:separator text="Publicación" />

                        <div class="flex items-end gap-2">
                            <flux:input wire:model.blur="postUrl" label="URL publicada" placeholder="https://..." class="flex-1" />
                            <flux:button wire:click="fetchPreview" icon="photo" variant="subtle">Vista previa</flux:button>
                        </div>

                        @if ($previewImageUrl)
                            <img src="{{ $previewImageUrl }}" alt="Vista previa del post" class="max-h-48 rounded-lg border border-zinc-200 object-cover dark:border-zinc-800" />
                        @endif

                        <div class="flex items-center justify-between">
                            <flux:text class="text-zinc-500">
                                @if ($publishedAt)
                                    Publicada el {{ $publishedAt }}
                                @else
                                    Sin publicar
                                @endif
                            </flux:text>
                            @if ($status !== \App\Enums\ContentStatus::Publicada->value)
                                <flux:button wire:click="markPublished" icon="check-badge" variant="primary" size="sm">Marcar publicada</flux:button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Contexto en vivo --}}
        <aside class="lg:col-span-3">
            <flux:heading size="lg" class="mb-3">Contexto</flux:heading>

            <div class="flex flex-col gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <div>
                    <flux:subheading>Preguntas que responde</flux:subheading>
                    @if (count($this->contextQuestions))
                        <ul class="mt-1 list-disc space-y-1 pl-5 text-sm text-zinc-600 dark:text-zinc-300">
                            @foreach ($this->contextQuestions as $q)
                                <li>{{ $q }}</li>
                            @endforeach
                        </ul>
                    @else
                        <flux:text class="mt-1 text-zinc-400">Elige una idea para ver su contexto.</flux:text>
                    @endif
                </div>

                <flux:separator />

                <div>
                    <flux:subheading>💡 Mitos/verdades a tratar</flux:subheading>
                    @if (count($this->contextBeliefs))
                        <ul class="mt-1 list-disc space-y-1 pl-5 text-sm text-zinc-600 dark:text-zinc-300">
                            @foreach ($this->contextBeliefs as $b)
                                <li>{{ $b }}</li>
                            @endforeach
                        </ul>
                    @else
                        <flux:text class="mt-1 text-zinc-400">—</flux:text>
                    @endif
                </div>
            </div>
        </aside>
    @else
        <section class="lg:col-span-9">
            <div class="rounded-xl border border-dashed border-zinc-300 p-10 text-center dark:border-zinc-700">
                <flux:text class="text-zinc-500">Selecciona una pieza a la izquierda o crea una nueva para empezar.</flux:text>
            </div>
        </section>
    @endif

    {{-- Patrón de sugerencias IA: hasta 3 alternativas; aplicar solo al elegir. --}}
    <flux:modal name="script-suggestions" class="md:w-[42rem]">
        <div class="space-y-4">
            <div>
                <flux:heading size="lg">Sugerencias de guión</flux:heading>
                <flux:subheading>Da instrucciones (opcional), genera y elige una. Si cierras sin elegir, tu guión no cambia.</flux:subheading>
            </div>

            <flux:textarea wire:model="aiBrief" label="Instrucciones adicionales (opcional)" rows="2" placeholder="Describe el post que quieres: ángulo, tono, detalles…" />

            <div class="flex justify-end">
                <flux:button
                    wire:click="generateScriptSuggestions"
                    icon="sparkles"
                    variant="primary"
                    size="sm"
                    :disabled="$this->generatingScript"
                >
                    {{ $this->generatingScript ? 'Generando…' : (count($scriptSuggestions) ? 'Regenerar' : 'Generar sugerencias') }}
                </flux:button>
            </div>

            @if ($aiError)
                <flux:callout variant="danger" icon="exclamation-triangle">{{ $aiError }}</flux:callout>
            @endif

            @if ($this->generatingScript)
                <div wire:poll.2s="pollScript" class="flex items-center justify-center gap-2 rounded-lg border border-dashed border-zinc-300 p-6 text-center dark:border-zinc-700">
                    <flux:icon.arrow-path class="size-5 animate-spin text-zinc-400" />
                    <flux:text class="text-zinc-500">Generando en segundo plano…</flux:text>
                </div>
            @else
                <div class="flex flex-col gap-3">
                    @forelse ($scriptSuggestions as $i => $suggestion)
                        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-800">
                            <div class="mb-2 flex items-center justify-between">
                                <flux:badge size="sm" color="zinc">{{ $suggestion['label'] }}</flux:badge>
                                <flux:button wire:click="applyScriptSuggestion({{ $i }})" size="sm" variant="primary" icon="check">Usar esta</flux:button>
                            </div>
                            <p class="whitespace-pre-line text-sm text-zinc-600 dark:text-zinc-300">{{ $suggestion['preview'] }}</p>
                        </div>
                    @empty
                        <flux:text class="text-zinc-500">Escribe instrucciones (opcional) y pulsa “Generar sugerencias”.</flux:text>
                    @endforelse
                </div>
            @endif
        </div>
    </flux:modal>
</div>
