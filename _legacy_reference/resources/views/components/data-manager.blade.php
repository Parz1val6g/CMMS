@props([
    'title',
    'items',
    'columns'      => [],
    'formSchema'   => [],
    'routes'       => [],
    'filterSchema' => [],
])

@php
    use App\Helpers\TemplateHelper;
@endphp

@if(isset($routes['index']))
    <form id="filter-form" action="{{ $routes['index'] }}" method="GET" class="card border-0 shadow-sm rounded-3 mb-3 bg-body flex-shrink-0">
        <div class="card-body p-3">
            <!-- Main Filter Row -->
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <!-- Search -->
                <div class="input-group input-group-sm flex-grow-1" style="max-width: 300px;">
                    <span class="input-group-text bg-body-tertiary border-end-0 text-body-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0 bg-transparent shadow-none" placeholder="{{ __('messages.search') }}" aria-label="{{ __('messages.search') }}">
                </div>

                <!-- Sort Order -->
                <select name="sort" class="form-select form-select-sm border bg-body-tertiary shadow-none text-body-secondary filter-sort-select" style="max-width: 140px;" aria-label="{{ __('messages.sort_by') }}">
                    <option value="desc" @selected(request('sort', 'desc') === 'desc')>{{ __('messages.newest') }}</option>
                    <option value="asc" @selected(request('sort') === 'asc')>{{ __('messages.oldest') }}</option>
                </select>

                <!-- Advanced Toggle -->
                <button type="button" class="btn btn-sm btn-outline-secondary shadow-none d-flex align-items-center" id="advanced-filter-toggle" title="{{ __('messages.toggle_advanced_filters') }}" style="transition: all 0.2s ease;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-1" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M1 11a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3zm5-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1V2z"/>
                    </svg>
                    {{ __('messages.advanced') }}
                </button>

                <!-- Right Side Actions -->
                <div class="ms-auto d-flex gap-2 align-items-center flex-wrap">
                    @if(isset($routes['index']) && \App\Helpers\FeatureFlags::isCsvExportEnabled())
                        <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center bg-body" onclick="exportTableToCSV()" title="{{ __('messages.export_table_to_csv') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-2" viewBox="0 0 16 16" aria-hidden="true">
                                <path fill-rule="evenodd" d="M14.5 1.5a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1h1.793L9.146 1.354a.5.5 0 0 1 .708-.708L14 4.793V2a.5.5 0 0 1 .5-.5zM1.5 14.5a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1H2.707l4.147 4.146a.5.5 0 0 1-.708.708L2 11.207V14a.5.5 0 0 1-.5.5z" />
                            </svg>
                            {{ __('messages.export_csv') }}
                        </button>
                    @endif

                    @if(isset($routes['store']))
                        <button type="button" class="btn btn-sm text-white fw-medium shadow-sm d-flex align-items-center" style="background-color: #4f46e5; border-color: #4f46e5;" data-bs-toggle="modal" data-bs-target="#createRecordModal" title="{{ __('messages.create_new_record') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-2" viewBox="0 0 16 16" aria-hidden="true">
                                <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                            </svg>
                            {{ __('messages.new') }}
                        </button>
                    @endif

                    <!-- Clear Filters -->
                    @if(request()->anyFilled(['search', 'date_from', 'date_to']))
                        <a href="{{ $routes['index'] }}" class="btn btn-sm btn-link text-muted text-decoration-none d-flex align-items-center" title="{{ __('messages.clear_all_filters') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-1" viewBox="0 0 16 16" aria-hidden="true">
                                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708l2.647-2.646-2.647-2.646a.5.5 0 0 1 0-.708z"/>
                            </svg>
                            {{ __('messages.clear') }}
                        </a>
                    @endif
                </div>
            </div>

            <!-- Active Filters Display -->
            @if(request()->anyFilled(['search', 'date_from', 'date_to']))
                <div class="d-flex gap-2 flex-wrap align-items-center mt-2">
                    @if(request('search'))
                        <span class="badge bg-primary-subtle text-primary small" style="font-weight: 500;">
                            🔍 {{ __('messages.search') }}: <strong>{{ request('search') }}</strong>
                        </span>
                    @endif
                    @if(request('date_from') || request('date_to'))
                        <span class="badge bg-warning-subtle text-warning small" style="font-weight: 500;">
                            📅 {{ __('messages.date') }}: <strong>{{ request('date_from') ?? '...' }} → {{ request('date_to') ?? '...' }}</strong>
                        </span>
                    @endif
                </div>
            @endif

            <!-- Advanced Filters (Hidden by default) -->
            <div id="advanced-filters" class="collapse mt-3" style="border-top: 1px solid var(--bs-border-color); padding-top: 1rem;">
                <div class="row g-3">

                    {{-- Dynamic select filters (priority, service_type, city, etc.) --}}
                    @foreach($filterSchema as $filter)
                        <div class="col-sm-6 col-md-3">
                            <label class="form-label small fw-bold text-body-secondary">{{ $filter['label'] }}</label>
                            <select name="{{ $filter['name'] }}"
                                    class="form-select form-select-sm bg-body-tertiary border-secondary-subtle js-filter-select"
                                    aria-label="{{ $filter['label'] }}">
                                @foreach($filter['options'] as $opt)
                                    <option value="{{ $opt['value'] }}"
                                        @selected(request($filter['name']) == $opt['value'])>
                                        {{ $opt['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach

                    {{-- Date From --}}
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label small fw-bold text-body-secondary">{{ __('messages.from_date') }}</label>
                        <input type="date" name="date_from" class="form-control form-control-sm bg-body-tertiary border-secondary-subtle" value="{{ request('date_from') }}" aria-label="{{ __('messages.filter_by_start_date') }}">
                    </div>

                    {{-- Date To --}}
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label small fw-bold text-body-secondary">{{ __('messages.to_date') }}</label>
                        <input type="date" name="date_to" class="form-control form-control-sm bg-body-tertiary border-secondary-subtle" value="{{ request('date_to') }}" aria-label="{{ __('messages.filter_by_end_date') }}">
                    </div>

                    {{-- Per Page --}}
                    <div class="col-sm-6 col-md-3">
                        <label class="form-label small fw-bold text-body-secondary">{{ __('messages.per_page') }}</label>
                        <select name="per_page" class="form-select form-select-sm bg-body-tertiary border-secondary-subtle filter-per-page-select">
                            <option value="10" @selected(request('per_page', 10) == 10)>10</option>
                            <option value="25" @selected(request('per_page') == 25)>25</option>
                            <option value="50" @selected(request('per_page') == 50)>50</option>
                            <option value="100" @selected(request('per_page') == 100)>100</option>
                        </select>
                    </div>

                    {{-- Apply Button --}}
                    <div class="col-sm-6 col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-sm text-white w-100" style="background-color: #4f46e5; border-color: #4f46e5;">
                            {{ __('messages.apply') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        // Helper function to safely submit form
        function submitFilterForm() {
            const form = document.getElementById('filter-form');
            if (form) {
                form.submit();
            }
        }

        // Auto-submit on sort change
        const sortSelect = document.querySelector('.filter-sort-select');
        if (sortSelect) {
            sortSelect.addEventListener('change', function(e) {
                e.preventDefault();
                e.stopPropagation();
                submitFilterForm();
            });
        }

        // Auto-submit on per_page change
        const perPageSelect = document.querySelector('.filter-per-page-select');
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function(e) {
                e.preventDefault();
                e.stopPropagation();
                submitFilterForm();
            });
        }

        // Auto-submit on any dynamic filter select change (priority, service_type, city...)
        document.querySelectorAll('.js-filter-select').forEach(function(sel) {
            sel.addEventListener('change', function(e) {
                e.preventDefault();
                submitFilterForm();
            });
        });

        // Toggle advanced filters
        const advancedToggle = document.getElementById('advanced-filter-toggle');
        const advancedFilters = document.getElementById('advanced-filters');

        if (advancedToggle) {
            advancedToggle.addEventListener('click', function(e) {
                e.preventDefault();
                advancedFilters.classList.toggle('show');
                advancedToggle.classList.toggle('active');

                // Store preference in localStorage
                if (advancedFilters.classList.contains('show')) {
                    localStorage.setItem('advancedFiltersOpen', 'true');
                    advancedToggle.style.backgroundColor = '#e0e7ff';
                } else {
                    localStorage.removeItem('advancedFiltersOpen');
                    advancedToggle.style.backgroundColor = '';
                }
            });
        }

        // Restore advanced filters state on page load
        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('advancedFiltersOpen') === 'true' && advancedToggle) {
                advancedToggle.click();
            }
        });

        // Event delegation for delete button (works with dynamic content)
        const tablePanel = document.getElementById('sm-table-panel');
        if (tablePanel) {
            tablePanel.addEventListener('click', function(e) {
                const deleteBtn = e.target.closest('.js-btn-delete');

                if (deleteBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const itemId = deleteBtn.dataset.itemId;
                    if (confirm('{{ __("messages.confirm_action") }}')) {
                        deleteItem(itemId);
                    }
                }
            });
        }

        // Handle search input - with debouncing to reduce requests (800ms delay)
        const searchInput = document.querySelector('input[name="search"]');
        let searchTimeout;

        if (searchInput) {
            // Submit on Enter key (immediate)
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    e.stopPropagation();
                    clearTimeout(searchTimeout);
                    submitFilterForm();
                }
            });

            // Debounce search on input (800ms delay)
            searchInput.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    submitFilterForm();
                }, 800);
            });
        }

        /**
         * Delete item via API
         */
        function deleteItem(itemId) {
            const form = document.getElementById('sm-edit-form');
            if (!form) return;

            const destroyRoute = form.dataset.destroyRoute || '';
            if (!destroyRoute) {
                console.error('Destroy route not configured');
                return;
            }

            const url = destroyRoute.replace(':id', itemId);

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => {
                if (response.ok) {
                    closeSmEditPanel();
                    submitFilterForm();
                } else {
                    alert('{{ __("messages.failed_to_delete_item") }}');
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                alert('{{ __("messages.error_deleting_item") }}');
            });
        }

        /**
         * Export table to CSV
         */
        function exportTableToCSV() {
            const pathname = window.location.pathname;
            const parts = pathname.split('/').filter(p => p);
            const model = parts[parts.length - 1];

            const url = new URL(window.location.href);
            url.pathname = `/api/${model}/export/csv`;

            const params = new URLSearchParams(window.location.search);
            for (const [key, value] of params) {
                url.searchParams.set(key, value);
            }

            fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => {
                if (response.ok) {
                    return response.blob();
                }
                throw new Error('CSV export failed');
            })
            .then(blob => {
                const blobUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = blobUrl;
                a.download = `export_${model}_${new Date().getTime()}.csv`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(blobUrl);
                document.body.removeChild(a);
            })
            .catch(error => {
                console.error('Export error:', error);
                alert('Failed to export CSV');
            });
        }
    </script>
@endif

<div class="d-flex flex-grow-1 gap-3 overflow-hidden mb-3" style="min-height: 0;">

    <div id="sm-table-panel" class="card border border-body-tertiary shadow-none rounded-3 overflow-hidden bg-body d-flex flex-column panel-transition w-100 h-100">
        @if($items->isEmpty())
            <x-empty-state title="{{ __('messages.no_records_found') }}"
                           description="{{ __('messages.try_adjust_search') }}" />
        @else
            <div class="sm-table-responsive custom-scrollbar">
                <table class="table table-sm table-hover align-middle mb-0 text-nowrap" style="font-size: 0.85rem;">
                    <thead class="bg-body-tertiary sticky-top" style="z-index: 1;">
                        <tr>
                            @foreach($columns as $col)
                                <th class="px-3 py-1 text-body-secondary fw-semibold border-bottom" scope="col">{{ $col['label'] ?? '' }}</th>
                            @endforeach
                            @if(isset($routes['update']))
                                <th class="px-3 py-1 border-bottom text-end" scope="col">{{ __('messages.actions') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @foreach($items as $item)
                            <tr data-row-id="{{ $item->id ?? '' }}" class="row-item-{{ $item->id ?? '' }}">
                                @foreach($columns as $col)
                                    <td class="px-3 py-1">
                                        @if(isset($col['render']) && is_callable($col['render']))
                                            {!! TemplateHelper::formatColumnValue($item, $col) !!}
                                        @else
                                            {{ TemplateHelper::resolveValue($item, $col['key'] ?? '') }}
                                        @endif
                                    </td>
                                @endforeach

                                                <!-- @php error_log(print_r($routes, true)); @endphp -->
                                @if(isset($routes['update']))
                                    <td class="px-3 py-1 text-end">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary shadow-none js-btn-edit" data-item-id="{{ $item->id ?? '' }}" data-payload='@json($item)' title="{{ __('messages.edit') }}">
                                                {{ __('messages.edit') }}
                                            </button>
                                            @if(isset($routes['destroy']))
                                                <button type="button" class="btn btn-outline-danger shadow-none js-btn-delete" data-item-id="{{ $item->id }}" title="{{ __('messages.delete') }}">
                                                    {{ __('messages.delete') }}
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if(method_exists($items, 'links'))
            <div class="card-footer bg-body border-top p-2 mt-auto">
                {{ $items->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    @if(isset($routes['update']))
        <div id="sm-form-panel" class="card border border-body-tertiary shadow-none rounded-3 overflow-hidden bg-body d-flex flex-column panel-transition form-panel-collapsed">

            <div class="card-header bg-body border-bottom p-3 d-flex justify-content-between align-items-center flex-shrink-0">
                <h6 class="mb-0 fw-bold text-body">
                    {{ __('messages.edit_item', ['item' => rtrim($title, 's')]) }}
                </h6>
                <button type="button" class="btn-close shadow-none" aria-label="{{ __('messages.close') }}" onclick="closeSmEditPanel()"></button>
            </div>

            <div class="card-body overflow-auto p-4 flex-grow-1 custom-scrollbar d-flex flex-column">
                <div id="sm-form-error-container" class="flex-shrink-0"></div>

                <form id="sm-edit-form" method="POST" action data-route-template="{{ $routes['update'] ?? '' }}" data-destroy-route="{{ $routes['destroy'] ?? '' }}" enctype="multipart/form-data" novalidate class="d-flex flex-column flex-grow-1 h-100">
                    @csrf
                    @method('PUT')

                    <div class="flex-grow-1">
                        @foreach($formSchema as $field)
                            <div class="mb-4">
                                <x-form-field :field="$field" />
                            </div>
                        @endforeach
                    </div>

                    <div class="d-grid gap-2 mt-auto pt-4 flex-shrink-0">
                        <button type="submit" class="btn btn-sm text-white fw-medium shadow-sm" style="background-color: #4f46e5; border-color: #4f46e5;">
                            {{ __('messages.save_changes') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-body border text-body-secondary shadow-none" onclick="closeSmEditPanel()">
                            {{ __('messages.cancel') }}
                        </button>
                        @if(isset($routes['destroy']))
                            <button type="button" class="btn btn-sm btn-outline-danger shadow-none" onclick="softDeleteItem()">
                                {{ __('messages.remove') }}
                            </button>
                        @endif
                    </div>
                </form>
            </div>

        </div>
    @endif

</div>