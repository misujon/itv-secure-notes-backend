@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer';
    $variants = [
        'primary' => 'bg-brand-500 text-white hover:bg-brand-600 shadow-xs',
        'secondary' => 'bg-white text-slate-700 border border-app-border hover:bg-slate-50 shadow-xs',
        'danger' => 'bg-status-danger text-white hover:bg-red-700 shadow-xs',
        'ghost' => 'text-slate-600 hover:bg-slate-100 hover:text-app-heading',
    ];
    $classes = $base.' '.($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
