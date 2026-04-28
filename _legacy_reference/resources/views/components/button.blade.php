@props([
    'variant' => 'secondary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'disabled' => false,
    'loading' => false,
    'icon' => null,
])

@php
    $variants = [
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        'success' => 'btn-success',
        'danger' => 'btn-danger',
        'warning' => 'btn-warning',
        'info' => 'btn-info',
        'light' => 'btn-light',
        'dark' => 'btn-dark',
        'link' => 'btn-link',
        'outline-primary' => 'btn-outline-primary',
    ];

    $sizes = [
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
    ];

    $buttonClass = 'btn ' . ($variants[$variant] ?? $variants['secondary']) . ' ' . ($sizes[$size] ?? '');

    if (!$href) {
        $tag = 'button';
    } else {
        $tag = 'a';
    }
@endphp

@if($tag === 'button')
    <button type="{{ $type }}" 
            class="{{ $buttonClass }}" 
            @if($disabled) disabled @endif
            @if($loading) data-loading="true" @endif>
        @if($icon)
            <span class="me-2">{{ $icon }}</span>
        @endif
        {{ $slot }}
    </button>
@else
    <a href="{{ $href }}" 
       class="{{ $buttonClass }}" 
       @if($disabled) style="pointer-events: none; opacity: 0.5;" @endif>
        @if($icon)
            <span class="me-2">{{ $icon }}</span>
        @endif
        {{ $slot }}
    </a>
@endif
