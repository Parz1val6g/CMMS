@props([
    'type' => 'info',
    'title' => null,
    'icon' => true,
    'dismissible' => true,
])

@php
    $alertClasses = match ($type) {
        'success' => 'alert-success',
        'danger' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info',
        default => 'alert-info',
    };

    $icons = [
        'success' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M10.854 7.854a.5.5 0 0 0-.708-.708L7.5 9.793 6.354 8.646a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0l3-3z"/><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/></svg>',
        'danger' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0l-5.708 9.75a1.13 1.13 0 0 0 .98 1.684h11.456c.912 0 1.469-.921.98-1.684L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/></svg>',
        'warning' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0l-5.708 9.75a1.13 1.13 0 0 0 .98 1.684h11.456c.912 0 1.469-.921.98-1.684L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/></svg>',
        'info' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/></svg>',
    ];
@endphp

<div class="alert {{ $alertClasses }} d-flex align-items-start gap-3" role="alert">
    @if($icon && isset($icons[$type]))
        <div class="flex-shrink-0">
            {!! $icons[$type] !!}
        </div>
    @endif

    <div class="flex-grow-1">
        @if($title)
            <h5 class="alert-heading mb-1">{{ $title }}</h5>
        @endif
        
        <div class="alert-content">
            {{ $slot }}
        </div>
    </div>

    @if($dismissible)
        <button type="button" 
                class="btn-close" 
                data-bs-dismiss="alert" 
                aria-label="{{ __('Close alert') }}"></button>
    @endif
</div>
