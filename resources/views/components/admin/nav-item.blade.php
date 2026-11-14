@props([
    'href',
    'active' => false,
])

<a
    href="{{ $href }}"
    @if ($active) aria-current="page" @endif
    @class([
        'group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
        'bg-brand-50 text-brand-700' => $active,
        'text-slate-600 hover:bg-slate-100 hover:text-app-heading' => ! $active,
    ])
>
    @isset($icon)
        <span @class([
            'shrink-0',
            'text-brand-600' => $active,
            'text-slate-400 group-hover:text-slate-600' => ! $active,
        ])>
            {{ $icon }}
        </span>
    @endisset
    <span class="truncate">{{ $slot }}</span>
</a>
