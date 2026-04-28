<div class="scrollable-content d-flex flex-column h-100 w-100">
    {{ $slot }}
</div>

<style>
    .scrollable-content {
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-width: thin;
        scrollbar-color: var(--bs-secondary-bg) transparent;
    }

    .scrollable-content::-webkit-scrollbar {
        width: 6px;
    }

    .scrollable-content::-webkit-scrollbar-thumb {
        background: var(--bs-secondary-bg);
        border-radius: 3px;
    }

    .scrollable-content::-webkit-scrollbar-thumb:hover {
        background: var(--bs-secondary);
    }
</style>