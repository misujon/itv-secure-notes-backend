@props([
    'type' => 'info',
    'title' => null,
])

@php
    $styles = [
        'success' => 'border-green-200 bg-green-50 text-green-800',
        'error' => 'border-red-200 bg-red-50 text-red-800',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
        'info' => 'border-brand-100 bg-brand-50 text-brand-700',
    ];
    $classes = 'rounded-xl border px-4 py-3 text-sm '.($styles[$type] ?? $styles['info']);
@endphp

<div role="alert" {{ $attributes->merge(['class' => $classes]) }}>
    @if ($title)
        <p class="font-semibold">{{ $title }}</p>
    @endif
    <div>{{ $slot }}</div>
</div>
