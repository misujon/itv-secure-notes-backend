@props([
    'label' => null,
    'name',
    'value' => '',
    'placeholder' => 'Write your content here...',
    'required' => false,
])

@php
    $fieldId = $attributes->get('id', $name);
    $initialValue = old($name, $value ?? '');
    $hasError = $errors->has($name);
@endphp

<div>
    @if ($label)
        <label for="{{ $fieldId }}" class="mb-1.5 block text-sm font-medium text-app-heading">
            {{ $label }}
            @if ($required)
                <span class="text-status-danger">*</span>
            @endif
        </label>
    @endif

    <div
        data-rich-editor
        data-placeholder="{{ $placeholder }}"
        @class([
            'overflow-hidden rounded-lg border bg-white transition focus-within:ring-2',
            'border-status-danger focus-within:border-status-danger focus-within:ring-red-100' => $hasError,
            'border-app-border focus-within:border-brand-500 focus-within:ring-brand-100' => ! $hasError,
        ])
    >
        <div
            class="flex flex-wrap items-center gap-1 border-b border-app-border bg-slate-50/80 px-2 py-1.5"
            role="toolbar"
            aria-label="Text formatting"
        >
            @php
                $btnClass = 'inline-flex h-7 items-center justify-center rounded px-2 text-xs font-medium text-slate-600 transition hover:bg-slate-200/70 hover:text-app-heading data-[active=true]:bg-brand-100 data-[active=true]:text-brand-700';
            @endphp

            <button type="button" data-editor-action="bold" class="{{ $btnClass }}" title="Bold">
                <span class="font-bold">B</span>
            </button>
            <button type="button" data-editor-action="italic" class="{{ $btnClass }}" title="Italic">
                <span class="italic">I</span>
            </button>
            <button type="button" data-editor-action="strike" class="{{ $btnClass }}" title="Strikethrough">
                <span class="line-through">S</span>
            </button>

            <span class="mx-1 h-4 w-px bg-app-border" aria-hidden="true"></span>

            <button type="button" data-editor-action="h2" class="{{ $btnClass }}" title="Heading 2">H2</button>
            <button type="button" data-editor-action="h3" class="{{ $btnClass }}" title="Heading 3">H3</button>

            <span class="mx-1 h-4 w-px bg-app-border" aria-hidden="true"></span>

            <button type="button" data-editor-action="bulletList" class="{{ $btnClass }}" title="Bullet list">• List</button>
            <button type="button" data-editor-action="orderedList" class="{{ $btnClass }}" title="Numbered list">1. List</button>
            <button type="button" data-editor-action="blockquote" class="{{ $btnClass }}" title="Quote">Quote</button>
            <button type="button" data-editor-action="codeBlock" class="{{ $btnClass }}" title="Code block">Code</button>

            <span class="mx-1 h-4 w-px bg-app-border" aria-hidden="true"></span>

            <button type="button" data-editor-action="undo" class="{{ $btnClass }}" title="Undo">Undo</button>
            <button type="button" data-editor-action="redo" class="{{ $btnClass }}" title="Redo">Redo</button>
        </div>

        <div data-rich-editor-surface></div>

        <textarea
            id="{{ $fieldId }}"
            name="{{ $name }}"
            data-rich-editor-input
            class="sr-only"
            tabindex="-1"
            aria-hidden="true"
        >{{ $initialValue }}</textarea>
    </div>

    @error($name)
        <p class="mt-1.5 text-xs text-status-danger">{{ $message }}</p>
    @enderror
</div>
