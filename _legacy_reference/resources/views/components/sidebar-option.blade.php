@props(['item' => []])

@php
    $href    = $item['href'] ?? '/';
    $path    = trim($href, '/');          // '' for root, 'clients' for /clients, etc.

    $isActive = $path === ''
        // Dashboard: only match the exact root
        ? request()->is('/')
        // Everything else: match the path and any sub-pages
        : request()->is($path) || request()->is($path . '/*');
@endphp

<li class="nav-item">
    <a href="{{ $item['href'] ?? '#' }}" 
       class="nav-link d-flex align-items-center gap-2 fw-medium rounded-3 sidebar-link px-3 py-2 {{ $isActive ? 'active' : 'text-body-secondary' }}" 
       @if($isActive) aria-current="page" style="background-color: #eef2ff; color: #4f46e5;" @endif
       title="{{ $item['label'] ?? '' }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
            @if($isActive && isset($item['icon_filled']))
                {!! $item['icon_filled'] !!}
            @elseif(isset($item['icon']))
                {!! $item['icon'] !!}
            @endif
        </svg>
        <span>{{ $item['label'] ?? '' }}</span>
    </a>
</li>

@push('styles')
    <style>
        .sidebar-link:not(.active) {
            transition: all 0.2s ease;
        }

        .sidebar-link:not(.active):hover,
        .sidebar-link:not(.active):focus {
            background-color: #f3f4f6;
            color: #374151;
        }
    </style>
@endpush