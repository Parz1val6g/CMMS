@props([
    'active' => 'dashboard',
    'breadcrumbs' => [
        ['name' => 'Dashboard', 'url' => '/']
    ]
])

<header class="bg-body border-bottom px-4 py-3 d-flex justify-content-between align-items-center sticky-top shadow-sm" style="z-index: 5;">
    {{-- Breadcrumb Navigation --}}
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0 small fw-medium text-nowrap">
            @foreach($breadcrumbs as $breadcrumb)
                @php
                    $isDashboard = strtolower($breadcrumb['name'] ?? '') === 'dashboard';
                    $isActive = $loop->last;
                @endphp

                <li class="breadcrumb-item {{ $isActive ? 'active text-body fw-bold' : '' }}" 
                    @if($isActive) aria-current="page" @endif>

                    @if(!$isActive)
                        <a href="{{ $breadcrumb['url'] ?? '#' }}" 
                           class="text-decoration-none text-body hover-text-primary" 
                           style="transition: color 0.2s;">
                    @endif

                    @if($isDashboard)
                        <svg class="me-1" style="vertical-align: -0.125em;" 
                             xmlns="http://www.w3.org/2000/svg" width="15" height="15" 
                             fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                            <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L8 2.207l6.646 6.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5Z"/>
                            <path d="m8 3.293 6 6V13.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5V9.293l6-6Z"/>
                        </svg>
                    @else
                        {{ $breadcrumb['name'] ?? '' }}
                    @endif

                    @if(!$isActive)
                        </a>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>

    {{-- Header Actions --}}
    <div class="d-flex align-items-center gap-3">
        {{-- Theme Toggle --}}
        <button id="theme-toggle" 
                class="btn btn-body rounded-circle p-2 d-flex align-items-center justify-content-center text-body-secondary border-0 transition-all" 
                style="width: 40px; height: 40px;" 
                title="{{ __('messages.toggle_theme') }}"
                aria-label="{{ __('messages.toggle_theme') }}">
            
            <svg id="icon-sun" xmlns="http://www.w3.org/2000/svg" width="18" height="18" 
                 fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6zm0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
            </svg>
            
            <svg id="icon-moon" class="d-none" xmlns="http://www.w3.org/2000/svg" width="18" height="18" 
                 fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"/>
            </svg>
        </button>

        <div class="vr text-body-secondary opacity-25 mx-1" style="height: 24px;"></div>

        {{-- User Menu --}}
        <div class="d-flex align-items-center cursor-pointer">
            <span class="text-body-secondary small me-3 d-none d-md-block">
                {{ __('messages.hello') }}, <strong class="text-body">{{ auth()->user()->first_name ?? '' }}</strong>
            </span>
            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                 {{ strtoupper(substr(Auth()->user()->first_name ?? '?', 0, 1)) }}
            </div>
        </div>

    </div>
</header>