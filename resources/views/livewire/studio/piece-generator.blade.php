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

                <flux:select wire:model.live="winning_idea_id" label="Idea ganadora" placeholder="Sin idea (pieza suelta)">
                    <flux:select.option value="">Sin idea (pieza suelta)</flux:select.option>
                    @foreach ($ideas as $idea)
                        <flux:select.option value="{{ $idea->id }}">{{ $idea->title }}</flux:select.option>
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

                {{-- Selección manual de contexto: Seguidor Ideal → preguntas y creencias --}}
                <div class="flex flex-col gap-2">
                    <flux:select wire:model.live="idealFollowerId" label="Seguidor ideal (opcional)" placeholder="Usar el contexto de la idea" description="Elígelo para escoger a mano qué preguntas y creencias enviar a la IA.">
                        <flux:select.option value="">Usar el contexto de la idea</flux:select.option>
                        @foreach ($followers as $follower)
                            <flux:select.option value="{{ $follower->id }}">{{ $follower->name }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    @if ($idealFollowerId)
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
                                    <flux:text class="text-zinc-500">Las preguntas de este seguidor aún no tienen mitos/verdades.</flux:text>
                                @endforelse
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Ideas Ganadoras Referenciales (Heras) + filtro por Referente --}}
                <div class="flex flex-col gap-2">
                    <flux:select wire:model.live="referentFilter" label="Ideas Ganadoras Referenciales (Heras)" placeholder="Todos los referentes" description="Filtra las plantillas por referente viral. Puedes elegir ninguna, una o varias.">
                        <flux:select.option value="">Todos los referentes</flux:select.option>
                        @foreach ($referents as $referent)
                            <flux:select.option value="{{ $referent->id }}">{{ $referent->name }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <div class="max-h-44 overflow-y-auto rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                        @forelse ($templates as $template)
                            <flux:checkbox
                                wire:model="herasTemplateIds"
                                value="{{ $template->id }}"
                                label="{{ $template->display_name }}"
                            />
                        @empty
                            <flux:text class="text-zinc-500">No hay plantillas para este referente.</flux:text>
                        @endforelse
                    </div>
                </div>

                {{-- Contexto en vivo de la idea (cuando no se elige seguidor manualmente) --}}
                @if (! $idealFollowerId && (count($this->contextQuestions) || count($this->contextBeliefs)))
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
</div>
