@props([
    'label' => null,
    'name',
    'id' => null,
    'value' => null,
    'rows' => 5,
    'required' => false,
])

@php
    $textareaId = $id ?? $name;
    $hasError = $errors->has($name);
@endphp

<div>
    @if ($label)
        <label for="{{ $textareaId }}" class="block text-sm font-medium text-app-heading">
            {{ $label }}
            @if ($required)
                <span class="text-status-danger">*</span>
            @endif
        </label>
    @endif

    <textarea
        id="{{ $textareaId }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($required) required @endif
        {{ $attributes->merge([
            'class' => 'mt-1.5 block w-full rounded-lg border px-3 py-2 text-sm text-app-heading placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 '.($hasError ? 'border-status-danger bg-red-50/40' : 'border-app-border bg-white'),
        ]) }}
    >{{ old($name, $value) }}</textarea>

    @error($name)
        <p class="mt-1 text-xs font-medium text-status-danger">{{ $message }}</p>
    @enderror
</div>
