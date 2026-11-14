@props([
    'label' => null,
    'name',
    'id' => null,
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'required' => false,
])

@php
    $inputId = $id ?? $name;
    $hasError = $errors->has($name);
@endphp

<div>
    @if ($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-app-heading">
            {{ $label }}
            @if ($required)
                <span class="text-status-danger">*</span>
            @endif
        </label>
    @endif

    <input
        id="{{ $inputId }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        @if ($required) required @endif
        {{ $attributes->merge([
            'class' => 'mt-1.5 block w-full rounded-lg border px-3 py-2 text-sm text-app-heading placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 '.($hasError ? 'border-status-danger bg-red-50/40' : 'border-app-border bg-white'),
        ]) }}
    />

    @if ($hint && ! $hasError)
        <p class="mt-1 text-xs text-app-muted">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-xs font-medium text-status-danger">{{ $message }}</p>
    @enderror
</div>
