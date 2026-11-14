@props([
    'label' => null,
    'name',
    'id' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'required' => false,
])

@php
    $selectId = $id ?? $name;
    $hasError = $errors->has($name);
    $currentValue = old($name, $selected);
@endphp

<div>
    @if ($label)
        <label for="{{ $selectId }}" class="block text-sm font-medium text-app-heading">
            {{ $label }}
            @if ($required)
                <span class="text-status-danger">*</span>
            @endif
        </label>
    @endif

    <select
        id="{{ $selectId }}"
        name="{{ $name }}"
        @if ($required) required @endif
        {{ $attributes->merge([
            'class' => 'mt-1.5 block w-full rounded-lg border px-3 py-2 text-sm text-app-heading focus:outline-none focus:ring-2 focus:ring-brand-500 '.($hasError ? 'border-status-danger bg-red-50/40' : 'border-app-border bg-white'),
        ]) }}
    >
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) $currentValue === (string) $optionValue)>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>

    @error($name)
        <p class="mt-1 text-xs font-medium text-status-danger">{{ $message }}</p>
    @enderror
</div>
