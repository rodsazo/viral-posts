<div class="mx-auto flex max-w-3xl flex-col gap-6">
    <div>
        <flux:heading size="lg">Inbox de captura</flux:heading>
        <flux:text class="text-zinc-500">Vuelca ideas al vuelo; clasifícalas después como pregunta, creencia o idea.</flux:text>
    </div>

    {{-- Caja de captura --}}
    <div class="flex gap-2">
        <flux:input
            wire:model="note"
            wire:keydown.enter="capture"
            placeholder="Escribe algo y pulsa Enter…"
            autofocus
            class="flex-1"
        />
        <flux:button wire:click="capture" variant="primary" icon="plus">Capturar</flux:button>
    </div>

    {{-- Seguidor por defecto para convertir a pregunta --}}
    @if ($followers->isNotEmpty())
        <div class="flex items-center gap-2">
            <flux:text class="text-zinc-500">Convertir preguntas/creencias para:</flux:text>
            <flux:select wire:model.live="followerId" class="max-w-xs">
                @foreach ($followers as $follower)
                    <flux:select.option value="{{ $follower->id }}">{{ $follower->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    @endif

    {{-- Lista de capturas pendientes --}}
    <div class="flex flex-col gap-2">
        @forelse ($captures as $capture)
            <div wire:key="cap-{{ $capture->id }}" class="rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-900">
                <p class="text-sm">{{ $capture->body }}</p>
                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                    <flux:button wire:click="toQuestion({{ $capture->id }})" size="xs" variant="subtle" :disabled="$followers->isEmpty()">→ Pregunta</flux:button>
                    <flux:button wire:click="toBelief({{ $capture->id }}, 'myth')" size="xs" variant="subtle" :disabled="$followers->isEmpty()">→ Mito</flux:button>
                    <flux:button wire:click="toBelief({{ $capture->id }}, 'truth')" size="xs" variant="subtle" :disabled="$followers->isEmpty()">→ Verdad</flux:button>
                    <flux:button wire:click="toIdea({{ $capture->id }})" size="xs" variant="subtle">→ Idea</flux:button>
                    <flux:spacer />
                    <flux:button wire:click="discard({{ $capture->id }})" size="xs" variant="ghost" icon="trash" wire:confirm="¿Descartar esta captura?" />
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-700">
                <flux:text class="text-zinc-500">Bandeja vacía. Captura algo arriba para empezar.</flux:text>
            </div>
        @endforelse
    </div>
</div>
