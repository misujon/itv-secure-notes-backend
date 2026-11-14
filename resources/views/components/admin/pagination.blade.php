@props([
    'paginator',
])

@if ($paginator->hasPages())
    <div class="border-t border-app-border px-6 py-4">
        {{ $paginator->withQueryString()->links() }}
    </div>
@endif
