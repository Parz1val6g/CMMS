@props([
    'variant' => 'secondary',
    'pill' => false,
    'icon' => null,
])

@php
    $classes = 'badge ' . (match ($variant) {
        'primary' => 'bg-primary',
        'success' => 'bg-success',
        'danger' => 'bg-danger',
        'warning' => 'bg-warning text-dark',
        'info' => 'bg-info',
        'secondary' => 'bg-secondary',
        'light' => 'bg-light text-dark',
        'dark' => 'bg-dark',
        default => 'bg-secondary',
    });

    if ($pill) {
        $classes .= ' rounded-pill';
    }
@endphp

<span class="{{ $classes }}" @if($icon) title="{{ $slot }}" @endif>
    @if($icon)
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
            {!! $icon !!}
        </svg>
    @else
        {{ $slot }}
    @endif
</span>
