@props([
    'title' => null,
    'description' => null,
    'icon' => null,
    'action' => null,
    'actionText' => null,
])

<div class="flex-grow-1 d-flex flex-column align-items-center justify-content-center p-5 text-center">
    @if($icon)
        <div class="mb-3 text-body-tertiary" style="font-size: 3rem;">
            {!! $icon !!}
        </div>
    @else
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" 
             class="mb-3 text-body-tertiary" viewBox="0 0 16 16" aria-hidden="true">
            <path d="M14 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
        </svg>
    @endif
    
    <h5 class="fw-semibold text-body mb-1">{{ $title ?? __('messages.no_records_found') }}</h5>
    <p class="small text-body-secondary mb-0">{{ $description ?? __('messages.try_adjust_search') }}</p>

    @if($action && $actionText)
        <a href="{{ $action }}" class="btn btn-sm btn-primary mt-3">
            {{ $actionText }}
        </a>
    @endif
</div>
