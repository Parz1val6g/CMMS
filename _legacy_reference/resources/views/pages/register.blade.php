<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'SaaS Platform') }} - {{ __('Register') }}</title>

        @vite([
            'resources/css/app.css',
            'resources/js/app.js'
        ])
    </head>
    <body class="bg-light">
        <div class="container-fluid d-flex justify-content-center align-items-center min-vh-100" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
            <div class="col-12 col-sm-8 col-md-6 col-lg-4 px-3">
                <div class="card shadow-lg border-0 rounded-4 px-3 py-4 mt-4 mb-4">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center text-white rounded-4 mb-3 shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                                    <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0Zm-2-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path d="M2 13c0 1 1 1 1 1h5.256A4.493 4.493 0 0 1 8 12.5a4.49 4.49 0 0 1 1.544-3.393C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4Z" />
                                </svg>
                            </div>
                            <h4 class="fw-bold text-body mb-0">{{ __('messages.create_account') }}</h4>
                            <p class="text-body-secondary small mt-1">{{ __('messages.start_using_platform') }}</p>
                        </div>

                        <form action="#" method="POST" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label for="first_name" class="form-label fw-semibold small text-body-secondary">{{ __('messages.full_name') }}</label>
                                <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" class="form-control form-control-lg bg-body-tertiary border-0 fs-6 shadow-none @error('first_name') is-invalid @enderror" placeholder="{{ __('messages.ex_john_doe') }}" required autofocus aria-label="{{ __('messages.full_name') }}">
                                @error('first_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold small text-body-secondary">{{ __('messages.work_email') }}</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control form-control-lg bg-body-tertiary border-0 fs-6 shadow-none @error('email') is-invalid @enderror" placeholder="name@company.com" required aria-label="{{ __('messages.work_email') }}">
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold small text-body-secondary">{{ __('messages.password') }}</label>
                                <input type="password" name="password" id="password" class="form-control form-control-lg bg-body-tertiary border-0 fs-6 shadow-none @error('password') is-invalid @enderror" placeholder="{{ __('messages.minimum_8_characters') }}" required aria-label="{{ __('messages.password') }}">
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label fw-semibold small text-body-secondary">{{ __('messages.confirm_password') }}</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-lg bg-body-tertiary border-0 fs-6 shadow-none" required aria-label="{{ __('messages.confirm_password') }}">
                            </div>

                            <div class="mb-4 form-check">
                                <input type="checkbox" name="accept_terms" id="accept_terms" class="form-check-input border-secondary-subtle" required>
                                <label class="form-check-label text-body-secondary" style="font-size: 0.8rem;" for="accept_terms">
                                    {{ __('messages.i_agree_to_the') }} <a href="#" class="text-decoration-none fw-semibold" style="color: #4f46e5;">{{ __('messages.terms_of_service') }}</a> {{ __('messages.and') }} <a href="#" class="text-decoration-none fw-semibold" style="color: #4f46e5;">{{ __('messages.privacy_policy') }}</a>.
                                </label>
                            </div>

                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-lg fw-semibold text-white shadow-sm" style="background-color: #4f46e5; border-color: #4f46e5;">{{ __('messages.create_account') }}</button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <span class="small text-body-secondary fw-medium">{{ __('messages.already_have_an_account') }}</span>
                            <a href="{{ route('login') }}" class="text-decoration-none small fw-bold ms-1" style="color: #4f46e5;">{{ __('messages.sign_in') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>