@extends('layouts.app')

@section('title', __('messages.settings'))

@php
    $breadcrumbs = [
        ['name' => __('messages.dashboard'), 'url' => route('home')],
        ['name' => __('messages.settings'), 'url' => route('settings.show')],
    ];
@endphp

@section('content')
    <style>
        .settings-nav {
            flex-wrap: nowrap;
            white-space: nowrap;
        }
        .settings-nav .nav-link {
            color: var(--bs-secondary-color);
            font-weight: 400;
            padding-bottom: 0.75rem;
            transition: all 0.2s ease;
        }
        .settings-nav .nav-link:hover:not(.active) {
            color: var(--bs-body-color);
            border-bottom-color: var(--bs-border-color);
        }
        .settings-nav .nav-link.active {
            color: var(--bs-primary) !important;
            border-bottom-color: var(--bs-primary) !important;
            font-weight: 500;
        }

        /* Elegant horizontal scroll for the tabs */
        .nav-scroll-area {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none;  /* IE and Edge */
            margin-bottom: 1.5rem;
        }
        .nav-scroll-area::-webkit-scrollbar {
            display: none; /* WebKit */
        }

        /* Content scroll area */
        .tab-scroll-area {
            overflow-x: hidden;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .tab-scroll-area::-webkit-scrollbar {
            width: 6px;
        }
        .tab-scroll-area::-webkit-scrollbar-track {
            background: transparent;
        }
        .tab-scroll-area::-webkit-scrollbar-thumb {
            background: var(--bs-secondary-bg);
            border-radius: 3px;
        }
        .tab-scroll-area::-webkit-scrollbar-thumb:hover {
            background: var(--bs-secondary);
        }

        /* Utility classes for SaaS UI adjustments */
        .border-dashed {
            border-style: dashed !important;
            border-width: 2px !important;
        }
        .radio-card {
            cursor: pointer;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
        .radio-card:hover {
            background-color: var(--bs-secondary-bg) !important;
            border-color: var(--bs-border-color) !important;
        }

        /* Form feedback styles */
        .form-feedback {
            display: none;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .form-feedback.show {
            display: block;
        }
        .form-feedback.is-valid {
            color: var(--bs-success);
        }
        .form-feedback.is-invalid {
            color: var(--bs-danger);
        }

        /* Loading state for buttons */
        .btn:disabled {
            pointer-events: none;
            opacity: 0.65;
        }
    </style>

    <div class="container-fluid px-0 d-flex flex-column w-100" style="height: calc(100vh - 120px);">

        <div class="mb-4 flex-shrink-0">
            <h2 class="fw-bold text-body mb-1">{{ __('messages.account_settings') }}</h2>
            <p class="text-body-secondary small mb-0">{{ __('messages.manage_personal_information') }}</p>
        </div>

        <div class="nav-scroll-area border-bottom flex-shrink-0">
            <ul class="nav nav-underline settings-nav gap-3" id="settingsTabs" role="tablist" style="font-size: 0.9rem;">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active px-0 bg-transparent border-top-0 border-start-0 border-end-0" id="details-tab" data-bs-toggle="tab" data-bs-target="#details-pane" type="button" role="tab">{{ __('messages.my_details') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-0 bg-transparent border-top-0 border-start-0 border-end-0" id="password-tab" data-bs-toggle="tab" data-bs-target="#password-pane" type="button" role="tab">{{ __('messages.password') }}</button>
                </li>
                @if($isAdmin)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link px-0 bg-transparent border-top-0 border-start-0 border-end-0" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin-pane" type="button" role="tab">{{ __('messages.admin_settings') }}</button>
                    </li>
                @endif
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-0 bg-transparent border-top-0 border-start-0 border-end-0" id="account-tab" data-bs-toggle="tab" data-bs-target="#account-pane" type="button" role="tab">{{ __('messages.account') }}</button>
                </li>
            </ul>
        </div>

        <div class="tab-content flex-grow-1 tab-scroll-area" id="settingsTabsContent">

            <!-- My Details Tab -->
            <div class="tab-pane fade show active" id="details-pane" role="tabpanel" aria-labelledby="details-tab">
                <div class="row gx-5 mb-5 pb-4 border-bottom">
                    <div class="col-md-4 mb-4 mb-md-0">
                        <h6 class="fw-bold text-body mb-1">{{ __('messages.personal_information') }}</h6>
                        <p class="text-body-secondary small mb-0">{{ __('messages.update_your_personal_details') }}</p>
                    </div>
                    <div class="col-md-8">
                        <form id="detailsForm" class="card border-0 shadow-sm rounded-4 p-4">
                            @csrf
                            <div class="row g-4" style="max-width: 600px;">
                                <div class="col-sm-6">
                                    <label class="form-label small fw-bold text-body-secondary">{{ __('messages.first_name') }}</label>
                                    <input type="text" name="first_name" class="form-control bg-body-tertiary border-secondary-subtle" value="{{ $user->first_name }}" required>
                                    <div class="form-feedback"></div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-bold text-body-secondary">{{ __('messages.last_name') }}</label>
                                    <input type="text" name="last_name" class="form-control bg-body-tertiary border-secondary-subtle" value="{{ $user->last_name }}" required>
                                    <div class="form-feedback"></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-body-secondary">{{ __('messages.email_address') }}</label>
                                    <input type="email" name="email" class="form-control bg-body-tertiary border-secondary-subtle" value="{{ $user->email }}" required>
                                    <div class="form-feedback"></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-body-secondary">{{ __('messages.language') }}</label>
                                    <select name="language" class="form-select bg-body-tertiary border-secondary-subtle">
                                        @foreach(config('locales.supported') as $locale)
                                            <option value="{{ $locale['key'] }}" 
                                                @selected(($preferences['language'] ?? config('locales.default')) === $locale['key'])>
                                                {{ $locale['flag'] }} {{ $locale['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-feedback"></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-body-secondary">{{ __('messages.timezone') }}</label>
                                    <select name="timezone" class="form-select bg-body-tertiary border-secondary-subtle" disabled>
                                        <option value="UTC" @selected(($preferences['timezone'] ?? 'UTC') === 'UTC')>UTC</option>
                                        <option value="Europe/Lisbon" @selected(($preferences['timezone'] ?? 'UTC') === 'Europe/Lisbon')>Europe/Lisbon</option>
                                        <option value="Europe/London" @selected(($preferences['timezone'] ?? 'UTC') === 'Europe/London')>Europe/London</option>
                                        <option value="Europe/Paris" @selected(($preferences['timezone'] ?? 'UTC') === 'Europe/Paris')>Europe/Paris</option>
                                    </select>
                                    <div class="form-text small text-body-secondary">{{ __('messages.timezone_settings_coming_soon') }}</div>
                                    <div class="form-feedback"></div>
                                </div>
                                <div class="col-12 mt-4 text-end">
                                    <button type="submit" class="btn btn-primary fw-medium px-4" id="detailsSubmit">{{ __('messages.save') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Password Tab -->
            <div class="tab-pane fade" id="password-pane" role="tabpanel" aria-labelledby="password-tab">
                <div class="row gx-5 mb-5 pb-4 border-bottom">
                    <div class="col-md-4 mb-4 mb-md-0">
                        <h6 class="fw-bold text-body mb-1">{{ __('messages.change_password') }}</h6>
                        <p class="text-body-secondary small mb-0">{{ __('messages.update_your_password') }}</p>
                    </div>
                    <div class="col-md-8">
                        <form id="passwordForm" class="card border-0 shadow-sm rounded-4 p-4">
                            @csrf
                            <div class="row g-4" style="max-width: 600px;">
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-body-secondary">{{ __('messages.current_password') }}</label>
                                    <input type="password" name="current_password" class="form-control bg-body-tertiary border-secondary-subtle" required>
                                    <div class="form-feedback"></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-body-secondary">{{ __('messages.new_password') }}</label>
                                    <input type="password" name="password" class="form-control bg-body-tertiary border-secondary-subtle" required>
                                    <div class="form-text small">{{ __('messages.must_be_more_than_8_characters') }}</div>
                                    <div class="form-feedback"></div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-body-secondary">{{ __('messages.confirm_new_password') }}</label>
                                    <input type="password" name="password_confirmation" class="form-control bg-body-tertiary border-secondary-subtle" required>
                                    <div class="form-feedback"></div>
                                </div>
                                <div class="col-12 mt-4 text-end">
                                    <button type="button" class="btn btn-outline-secondary fw-medium me-2" onclick="document.getElementById('passwordForm').reset()">{{ __('messages.cancel') }}</button>
                                    <button type="submit" class="btn btn-primary fw-medium px-4" id="passwordSubmit">{{ __('messages.save') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            @if($isAdmin)
                <!-- Admin Settings Tab -->
                <div class="tab-pane fade" id="admin-pane" role="tabpanel" aria-labelledby="admin-tab">
                    <div class="row gx-5 mb-5 pb-4 border-bottom">
                        <div class="col-md-4 mb-4 mb-md-0">
                            <h6 class="fw-bold text-body mb-1">{{ __('messages.application_settings') }}</h6>
                            <p class="text-body-secondary small mb-0">{{ __('messages.configure_global_settings') }}</p>
                        </div>
                        <div class="col-md-8">
                            <form id="adminForm" class="card border-0 shadow-sm rounded-4 p-4">
                                @csrf
                                <div class="row g-4" style="max-width: 600px;">
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-body-secondary">{{ __('messages.company_name') }}</label>
                                        <input type="text" name="company_name" class="form-control border" value="{{ $appSettings['company_name'] ?? '' }}" placeholder="{{ __('messages.enter_company_name') }}">
                                        <div class="form-feedback"></div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-body-secondary">{{ __('messages.website_logo') }}</label>
                                        <div class="d-flex flex-column gap-3">
                                            <div class="position-relative d-inline-block" style="width: fit-content;">
                                                <div id="logoPreview" class="d-flex align-items-center justify-content-center rounded-3 bg-body-secondary" style="width: 100px; height: 100px; border: 2px dashed var(--bs-border-color);">
                                                    @if(!empty($appSettings['logo_path']))
                                                        <img src="{{ asset('storage/' . $appSettings['logo_path']) }}" alt="{{ __('messages.logo') }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                                    @else
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="text-body-secondary" viewBox="0 0 16 16">
                                                            <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
                                                            <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z"/>
                                                        </svg>
                                                    @endif
                                                </div>
                                                <button type="button" id="clearLogo" class="btn btn-sm btn-danger position-absolute rounded-circle" style="width: 24px; height: 24px; padding: 0; right: -8px; top: -8px; display: none; align-items: center; justify-content: center;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708l2.647-2.646-2.647-2.646a.5.5 0 0 1 0-.708z"/></svg>
                                                </button>
                                            </div>
                                            <input type="file" name="logo" class="form-control" id="logoInput" accept="image/*">
                                            <input type="hidden" name="delete_logo" id="deleteLogo" value="0">
                                            <div class="form-text small text-body-secondary">{{ __('messages.recommended_size') }}</div>
                                            <div class="form-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-body-secondary">{{ __('messages.company_website') }}</label>
                                        <input type="url" name="company_website" class="form-control bg-body-tertiary border-secondary-subtle" value="{{ $appSettings['company_website'] ?? '' }}" disabled>
                                        <div class="form-text small text-body-secondary">{{ __('messages.website_settings_coming_soon') }}</div>
                                        <div class="form-feedback"></div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-body-secondary">{{ __('messages.support_email') }}</label>
                                        <input type="email" name="support_email" class="form-control bg-body-tertiary border-secondary-subtle" value="{{ $appSettings['support_email'] ?? '' }}" disabled>
                                        <div class="form-text small text-body-secondary">{{ __('messages.email_settings_coming_soon') }}</div>
                                        <div class="form-feedback"></div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-body-secondary">{{ __('messages.default_language') }}</label>
                                        <select name="default_language" class="form-select bg-body-tertiary border-secondary-subtle">
                                            <option value="PT" @selected(($appSettings['default_language'] ?? 'PT') === 'PT')>{{ __('messages.portuguese') }}</option>
                                            <option value="EN" @selected(($appSettings['default_language'] ?? 'PT') === 'EN')>{{ __('messages.english') }}</option>
                                        </select>
                                        <div class="form-feedback"></div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-body-secondary">{{ __('messages.default_timezone') }}</label>
                                        <select name="default_timezone" class="form-select bg-body-tertiary border-secondary-subtle" disabled>
                                            <option value="UTC" @selected(($appSettings['default_timezone'] ?? 'UTC') === 'UTC')>UTC</option>
                                            <option value="Europe/Lisbon" @selected(($appSettings['default_timezone'] ?? 'UTC') === 'Europe/Lisbon')>Europe/Lisbon</option>
                                            <option value="Europe/London" @selected(($appSettings['default_timezone'] ?? 'UTC') === 'Europe/London')>Europe/London</option>
                                            <option value="Europe/Paris" @selected(($appSettings['default_timezone'] ?? 'UTC') === 'Europe/Paris')>Europe/Paris</option>
                                        </select>
                                        <div class="form-text small text-body-secondary">{{ __('messages.timezone_settings_coming_soon') }}</div>
                                        <div class="form-feedback"></div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-body-secondary">{{ __('messages.currency') }}</label>
                                        <input type="text" name="currency" class="form-control bg-body-tertiary border-secondary-subtle" maxlength="3" placeholder="EUR" value="{{ $appSettings['currency'] ?? '' }}" disabled>
                                        <div class="form-text small text-body-secondary">{{ __('messages.currency_settings_coming_soon') }}</div>
                                        <div class="form-feedback"></div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input bg-body" type="checkbox" name="csv_enabled" id="csvEnabled" value="1" @checked(($appSettings['csv_enabled'] ?? false) === true || ($appSettings['csv_enabled'] ?? false) === '1')>
                                            <label class="form-check-label small fw-bold text-body-secondary" for="csvEnabled">
                                                {{ __('messages.enable_csv_export') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input bg-body" type="checkbox" name="user_registration_enabled" id="regEnabled" value="1" @checked(($appSettings['user_registration_enabled'] ?? false) === true || ($appSettings['user_registration_enabled'] ?? false) === '1')>
                                            <label class="form-check-label small fw-bold text-body-secondary" for="regEnabled">
                                                {{ __('messages.allow_user_registration') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-4 text-end">
                                        <button type="submit" class="btn btn-primary fw-medium px-4" id="adminSubmit">{{ __('messages.save') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Account Tab -->
            <div class="tab-pane fade" id="account-pane" role="tabpanel" aria-labelledby="account-tab">
                <div class="row gx-5 mb-5 pb-4">
                    <div class="col-md-4 mb-4 mb-md-0">
                        <h6 class="fw-bold text-body mb-1">{{ __('messages.delete_account') }}</h6>
                        <p class="text-body-secondary small mb-0">{{ __('messages.no_going_back') }}</p>
                    </div>
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-danger-subtle">
                            <p class="text-body small mb-3">{{ __('messages.type_to_delete') }} <strong>"{{ __('messages.delete_account_phrase', ['name' => strtoupper($user->first_name)]) }}"</strong> {{ __('messages.in_confirmation_field') }}</p>
                            <form id="deleteAccountForm">
                                @csrf
                                <div class="row g-4" style="max-width: 600px;">
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-body-secondary">{{ __('messages.confirmation_text') }}</label>
                                        <input type="text" name="confirmation_text" class="form-control border-danger" placeholder="{{ __('messages.confirmation_text') }}">
                                        <div class="form-feedback"></div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-body-secondary">{{ __('messages.confirm_your_password') }}</label>
                                        <input type="password" name="password" class="form-control border-danger">
                                        <div class="form-feedback"></div>
                                    </div>
                                    <div class="col-12 mt-4 text-end">
                                        <button type="button" class="btn btn-outline-secondary fw-medium me-2" onclick="document.getElementById('deleteAccountForm').reset()">{{ __('messages.cancel') }}</button>
                                        <button type="button" class="btn btn-danger fw-medium px-4" id="deleteAccountSubmit" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">{{ __('messages.delete_account') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Confirm Delete Account Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-danger">
                    <h5 class="modal-title" id="confirmDeleteLabel">{{ __('messages.absolutely_sure') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger mb-3"><strong>{{ __('messages.this_action_cannot_be_undone') }}</strong></p>
                    <p class="mb-3">{{ __('messages.type_to_delete') }} <strong>"{{ __('messages.delete_account_phrase', ['name' => strtoupper($user->first_name)]) }}"</strong> {{ __('messages.in_confirmation_field') }}</p>
                    <input type="text" id="confirmDeleteText" class="form-control border-danger mb-3" placeholder="{{ __('messages.confirmation_text') }}">
                    <label class="form-label small fw-bold text-body-secondary">{{ __('messages.confirm_your_password') }}</label>
                    <input type="password" id="confirmDeletePassword" class="form-control border-danger">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">{{ __('messages.delete_account') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
        <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <span id="successMessage"></span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <span id="errorMessage"></span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
        // Configuration
        const API_ROUTES = {
            updateUser: @json($routes['updateUser'] ?? '#'),
            updatePassword: @json($routes['updatePassword'] ?? '#'),
            updateAdmin: @json($routes['updateAdmin'] ?? '#'),
            deleteAccount: @json($routes['deleteAccount'] ?? '#'),
        };

        // Toast notification helper
        function showToast(message, type = 'success') {
            const toastElement = document.getElementById(type === 'success' ? 'successToast' : 'errorToast');
            const messageElement = document.getElementById(type === 'success' ? 'successMessage' : 'errorMessage');

            messageElement.textContent = message;
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
        }

        // Form submission helper
        async function submitForm(formId, endpoint) {
            const form = document.getElementById(formId);
            const submitBtn = form.querySelector('[type="submit"]');
            const originalText = submitBtn.textContent;

            try {
                // Disable button and show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>{{ __("messages.loading") }}';

                const formData = new FormData(form);
                const response = await fetch(endpoint, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    // Clear errors
                    form.querySelectorAll('.form-feedback').forEach(el => {
                        el.classList.remove('show', 'is-valid', 'is-invalid');
                        el.textContent = '';
                    });
                    form.querySelectorAll('.form-control, .form-select').forEach(el => {
                        el.classList.remove('is-invalid');
                    });

                    showToast(data.message || '{{ __("messages.updated_successfully") }}', 'success');

                    // Reset form after 1 second
                    setTimeout(() => {
                        form.reset();
                    }, 1000);
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const input = form.querySelector(`[name="${field}"]`);
                            if (input) {
                                const feedback = input.nextElementSibling;
                                input.classList.add('is-invalid');
                                if (feedback && feedback.classList.contains('form-feedback')) {
                                    feedback.classList.add('show', 'is-invalid');
                                    feedback.textContent = data.errors[field][0];
                                }
                            }
                        });
                        showToast('{{ __("messages.check_form_for_errors") }}', 'error');
                    } else {
                        showToast(data.error || '{{ __("messages.an_error_occurred") }}', 'error');
                    }
                }
            } catch (error) {
                console.error('Form submission error:', error);
                showToast('{{ __("messages.please_try_again") }}', 'error');
            } finally {
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }

        // Details form handler
        document.getElementById('detailsForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm('detailsForm', API_ROUTES.updateUser);
        });

        // Password form handler
        document.getElementById('passwordForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const originalHandler = submitForm;
            const form = this;
            const submitBtn = form.querySelector('[type="submit"]');
            const originalText = submitBtn.textContent;

            (async () => {
                try {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>{{ __("messages.loading") }}';

                    const formData = new FormData(form);
                    const response = await fetch(API_ROUTES.updatePassword, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });

                    const data = await response.json();

                    if (response.ok) {
                        // Clear errors
                        form.querySelectorAll('.form-feedback').forEach(el => {
                            el.classList.remove('show', 'is-valid', 'is-invalid');
                            el.textContent = '';
                        });
                        form.querySelectorAll('.form-control, .form-select').forEach(el => {
                            el.classList.remove('is-invalid');
                        });

                        showToast('{{ __("messages.password_changed_successfully") }}', 'success');
                        setTimeout(() => form.reset(), 1000);
                    } else {
                        if (data.errors) {
                            Object.keys(data.errors).forEach(field => {
                                const input = form.querySelector(`[name="${field}"]`);
                                if (input) {
                                    const feedback = input.nextElementSibling;
                                    input.classList.add('is-invalid');
                                    if (feedback && feedback.classList.contains('form-feedback')) {
                                        feedback.classList.add('show', 'is-invalid');
                                        feedback.textContent = data.errors[field][0];
                                    }
                                }
                            });
                            showToast('{{ __("messages.check_form_for_errors") }}', 'error');
                        } else {
                            showToast(data.error || '{{ __("messages.an_error_occurred") }}', 'error');
                        }
                    }
                } catch (error) {
                    console.error('Form submission error:', error);
                    showToast('{{ __("messages.please_try_again") }}', 'error');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            })();
        });

        // Admin form handler
        document.getElementById('adminForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm('adminForm', API_ROUTES.updateAdmin);
        });

        // Delete account form handler
        document.getElementById('confirmDeleteBtn')?.addEventListener('click', async function() {
            const confirmText = document.getElementById('confirmDeleteText').value;
            const password = document.getElementById('confirmDeletePassword').value;
            const expectedText = "{{ __('messages.delete_account_phrase', ['name' => strtoupper($user->first_name)]) }}";

            // Validation
            if (confirmText !== expectedText) {
                showToast('{{ __("messages.confirmation_text_incorrect") ?? "Confirmation text does not match" }}', 'error');
                document.getElementById('confirmDeleteText').classList.add('border-danger', 'is-invalid');
                return;
            }

            if (!password) {
                showToast('{{ __("messages.password_required") }}', 'error');
                document.getElementById('confirmDeletePassword').classList.add('border-danger', 'is-invalid');
                return;
            }

            const btn = this;
            const originalText = btn.textContent;

            try {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>{{ __("messages.loading") }}';

                const formData = new FormData();
                formData.append('confirmation_text', confirmText);
                formData.append('password', password);
                formData.append('_token', document.querySelector('input[name="_token"]').value);

                const response = await fetch(API_ROUTES.deleteAccount, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await response.json();

                if (response.ok) {
                    showToast('{{ __("messages.account_deleted_successfully") }}', 'success');
                    setTimeout(() => {
                        window.location.href = '/login?deleted=true';
                    }, 1500);
                } else {
                    showToast(data.error || '{{ __("messages.an_error_occurred") }}', 'error');
                    document.getElementById('confirmDeletePassword').classList.add('is-invalid');
                }
            } catch (error) {
                console.error('Delete account error:', error);
                showToast('{{ __("messages.please_try_again") }}', 'error');
            } finally {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });

        // Clear validation states when modal closes
        document.getElementById('confirmDeleteModal')?.addEventListener('hidden.bs.modal', function() {
            document.getElementById('confirmDeleteText').value = '';
            document.getElementById('confirmDeletePassword').value = '';
            document.getElementById('confirmDeleteText').classList.remove('is-invalid', 'border-danger');
            document.getElementById('confirmDeletePassword').classList.remove('is-invalid', 'border-danger');
        });

        // Logo preview handler
        const logoInput = document.getElementById('logoInput');
        const logoPreview = document.getElementById('logoPreview');
        const clearLogoBtn = document.getElementById('clearLogo');

        // Show/hide clear button based on whether logo exists
        function updateClearButtonVisibility() {
            const hasImage = logoPreview.querySelector('img') !== null;
            clearLogoBtn.style.display = hasImage ? 'flex' : 'none';
        }

        // Check on page load if logo exists
        updateClearButtonVisibility();

        // Handle file selection
        logoInput?.addEventListener('change', function(e) {
            document.getElementById('deleteLogo').value = '0';
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    logoPreview.innerHTML = `<img src="${event.target.result}" alt="{{ __('messages.logo') }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">`;
                    updateClearButtonVisibility();
                };
                reader.readAsDataURL(file);
            }
        });

        // Handle clear button click
        clearLogoBtn?.addEventListener('click', function(e) {
            e.preventDefault();
            logoInput.value = '';
            document.getElementById('deleteLogo').value = '1';
            logoPreview.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="text-body-secondary" viewBox="0 0 16 16">
                <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
                <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1h12z"/>
            </svg>`;
            updateClearButtonVisibility();
        });
    </script>

@endsection