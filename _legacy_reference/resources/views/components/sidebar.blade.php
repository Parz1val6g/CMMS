@php
    use App\Services\SidebarMenuService;
    $menuItems = SidebarMenuService::getMenuItems(auth()->user());
    $logoPath = appSetting('logo_path');
    $companyName = $companyName ?? 'SaaS Platform';
@endphp

<aside class="d-flex flex-column flex-shrink-0 bg-body border-end shadow-sm h-100" style="width: 260px; z-index: 10;" role="navigation" aria-label="{{ __('messages.main_navigation') }}">
    {{-- Logo / Branding Section --}}
    <a href="{{ route('home') }}" class="d-flex align-items-center p-4 text-decoration-none" title="{{ __('messages.go_to_dashboard') }}">
        <div class="d-inline-flex align-items-center justify-content-center text-white rounded-3 me-2" style="width: 36px; height: 36px; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); overflow: hidden;">
            @if($logoPath && file_exists(storage_path('app/public/' . $logoPath)))
                <img src="{{ asset('storage/' . $logoPath) }}" alt="{{ $companyName }} {{ __('logo') }}" style="width: 100%; height: 100%; object-fit: contain;">
            @else
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                    <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
                </svg>
            @endif
        </div>
        <span class="fs-5 fw-bold text-body">{{ $companyName }}</span>
    </a>

    <hr class="text-body-secondary opacity-25 m-0 mx-4" aria-hidden="true">

    {{-- Main Navigation Menu --}}
    <nav class="flex-grow-1">
        <ul class="nav flex-column p-3 gap-2">
            @forelse($menuItems as $item)
                <x-sidebar-option :item="$item" />
            @empty
                <li class="text-body-secondary small p-2">{{ __('messages.no_menu_items') }}</li>
            @endforelse
        </ul>
    </nav>

    <hr class="text-body-secondary opacity-25 m-0 mx-4" aria-hidden="true">

    {{-- Logout Section --}}
    <div class="p-3">
        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button type="submit" class="nav-link w-100 text-start d-flex align-items-center fw-medium rounded-3 border-0 bg-transparent text-body-secondary" title="{{ __('messages.logout') }}" style="transition: all 0.2s ease;">
                <svg class="me-3" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/>
                    <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
                </svg>
                <span>{{ __('Logout') }}</span>
            </button>
        </form>
    </div>
</aside>