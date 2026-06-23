@props(['icon' => null, 'label' => ''])

{{-- Fila de menú "próximamente": destino del roadmap todavía no construido. No es clicable. --}}
<div class="flex items-center justify-between gap-3 rounded-md px-2 py-1.5 text-sm text-zinc-400 dark:text-zinc-500" aria-disabled="true">
    <span class="flex items-center gap-2">
        @if ($icon)
            <flux:icon :icon="$icon" variant="micro" class="size-4" />
        @endif
        {{ $label }}
    </span>
    <span class="rounded bg-zinc-100 px-1.5 py-0.5 text-[10px] font-medium uppercase tracking-wide text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500">próx.</span>
</div>
