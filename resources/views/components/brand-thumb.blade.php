@props(['brand', 'size' => 'size-6'])

@php($url = $brand->logoUrl())

@if ($url)
    <img src="{{ $url }}" alt="{{ $brand->name }}" {{ $attributes->merge(['class' => $size.' shrink-0 rounded-md object-cover']) }} />
@else
    <span {{ $attributes->merge(['class' => $size.' flex shrink-0 items-center justify-center rounded-md bg-zinc-200 text-xs font-semibold text-zinc-600 dark:bg-zinc-700 dark:text-zinc-200']) }}>
        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($brand->name, 0, 1)) }}
    </span>
@endif
