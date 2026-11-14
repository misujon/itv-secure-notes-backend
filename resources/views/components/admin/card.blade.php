@props([
    'title' => null,
    'description' => null,
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-app-border bg-app-surface shadow-xs']) }}>
    @if ($title || isset($header))
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-app-border px-6 py-4">
            <div>
                @if ($title)
                    <h2 class="text-base font-semibold text-app-heading">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="mt-0.5 text-sm text-app-muted">{{ $description }}</p>
                @endif
            </div>
            @isset($header)
                <div class="flex items-center gap-2">
                    {{ $header }}
                </div>
            @endisset
        </div>
    @endif

    <div @class(['p-6' => $padding])>
        {{ $slot }}
    </div>
</div>
