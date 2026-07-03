<div>
    <div class="mb-4">
        <flux:heading size="xl">Generador de ideas</flux:heading>
        <flux:subheading>Elige preguntas y mitos/verdades de un seguidor, deja que la IA proponga ideas ganadoras y guarda las que te gusten.</flux:subheading>
    </div>

    @unless ($this->aiEnabled)
        <flux:callout variant="warning" icon="key" class="mb-4">
            El asistente de IA no está configurado. Define <code>ANTHROPIC_API_KEY</code> en el entorno para usar el generador de ideas.
        </flux:callout>
    @endunless

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Paso 1: contexto --}}
        <section class="lg:col-span-5">
            <div class="flex flex-col gap-4 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <flux:heading size="lg">1 · Contexto</flux:heading>

                <flux:select wire:model.live="idealFollowerId" label="Seguidor ideal" placeholder="Elige un seguidor">
                    <flux:select.option value="">Elige un seguidor</flux:select.option>
                    @foreach ($followers as $follower)
                        <flux:select.option value="{{ $follower->id }}">{{ $follower->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                @if (count($characters))
                    <flux:select wire:model.live="brandCharacterId" label="Personaje de marca (opcional)" placeholder="Sin personaje" description="Si eliges uno, las ideas salen con su voz y posturas.">
                        <flux:select.option value="">Sin personaje</flux:select.option>
                        @foreach ($characters as $character)
                            <flux:select.option value="{{ $character->id }}">{{ $character->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                @endif

                @if ($idealFollowerId)
                    <div>
                        <flux:subheading class="mb-1">Preguntas a enviar</flux:subheading>
                        <div class="max-h-40 overflow-y-auto rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                            @forelse ($this->followerQuestions as $question)
                                <flux:checkbox wire:model.live="questionIds" value="{{ $question->id }}" label="{{ $question->body }}" />
                            @empty
                                <flux:text class="text-zinc-500">Este seguidor no tiene preguntas.</flux:text>
                            @endforelse
                        </div>
                        <flux:text class="mt-1 text-xs text-zinc-400">Las preguntas marcadas se enlazarán a las ideas que crees.</flux:text>
                    </div>

                    <div>
                        <flux:subheading class="mb-1">Mitos/verdades a enviar</flux:subheading>
                        <div class="max-h-40 overflow-y-auto rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                            @forelse ($this->followerBeliefs as $belief)
                                <flux:checkbox wire:model.live="beliefIds" value="{{ $belief->id }}" label="[{{ $belief->type->getLabel() }}] {{ $belief->statement }}" />
                            @empty
                                <flux:text class="text-zinc-500">Este seguidor aún no tiene mitos/verdades.</flux:text>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <flux:subheading class="mb-1">Dolores/deseos a enviar</flux:subheading>
                        <div class="max-h-40 overflow-y-auto rounded-lg border border-zinc-200 p-3 dark:border-zinc-800">
                            @forelse ($this->followerPains as $pain)
                                <flux:checkbox wire:model.live="painIds" value="{{ $pain->id }}" label="[{{ $pain->type->getLabel() }}] {{ $pain->body }}" />
                            @empty
                                <flux:text class="text-zinc-500">Este seguidor aún no tiene dolores/deseos.</flux:text>
                            @endforelse
                        </div>
                    </div>
                @else
                    <flux:text class="text-zinc-500">Elige un seguidor para escoger sus preguntas y mitos/verdades.</flux:text>
                @endif

                <flux:textarea wire:model="instructions" label="Instrucciones adicionales (opcional)" rows="2" placeholder="Ángulo, tono, enfoque deseado…" />

                <flux:button
                    wire:click="generate"
                    icon="sparkles"
                    variant="primary"
                    :disabled="! $this->canGenerate || $this->generating"
                >
                    {{ $this->generating ? 'Generando…' : (count($suggestions) ? 'Regenerar ideas' : 'Generar ideas') }}
                </flux:button>
            </div>
        </section>

        {{-- Paso 2-3: ideas + guardado --}}
        <section class="lg:col-span-7">
            @if ($aiError)
                <flux:callout variant="danger" icon="exclamation-triangle" class="mb-4">{{ $aiError }}</flux:callout>
            @endif

            @if ($this->generating)
                <div wire:poll.2s="pollGeneration" class="rounded-xl border border-dashed border-zinc-300 p-10 text-center dark:border-zinc-700">
                    <flux:icon.arrow-path class="mx-auto mb-3 size-6 animate-spin text-zinc-400" />
                    <flux:text class="text-zinc-500">Generando ideas en segundo plano… puede tardar un momento.</flux:text>
                </div>
            @elseif (count($suggestions))
                <div class="mb-3 flex items-center justify-between">
                    <flux:heading size="lg">2 · Elige las ideas</flux:heading>
                    <flux:button
                        wire:click="createIdeas"
                        icon="light-bulb"
                        variant="primary"
                        size="sm"
                        :disabled="! count($selected)"
                    >
                        Crear {{ count($selected) ?: '' }} {{ count($selected) === 1 ? 'idea' : 'ideas' }}
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
                    <flux:text class="text-zinc-500">Elige contexto a la izquierda y pulsa “Generar ideas”. Aparecerán aquí las sugerencias.</flux:text>
                </div>
            @endif
        </section>
    </div>
</div>
