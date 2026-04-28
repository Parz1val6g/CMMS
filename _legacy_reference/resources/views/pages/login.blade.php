<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'SaaS Platform') }} - {{ __('Sign In') }}</title>

        @vite([
            'resources/css/app.css',
            'resources/js/app.js'
        ])
    </head>
    <body class="bg-light">
        <div class="container-fluid d-flex justify-content-center align-items-center min-vh-100" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);">
            <div class="col-12 col-sm-8 col-md-6 col-lg-4 px-3">
                <div class="card shadow-lg border-0 rounded-4 px-3 py-4">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center text-white rounded-4 mb-3 shadow-sm" style="width: 56px; height: 56px; background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
                                    <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z" />
                                </svg>
                            </div>
                            <h4 class="fw-bold text-body mb-0">{{ __('Sign In') }}</h4>
                        </div>

                        <form action="{{ route('login.attempt') }}" method="POST" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold small text-body-secondary">{{ __('messages.email_or_username') }}</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control form-control-lg bg-body-tertiary border-0 fs-6 shadow-none @error('email') is-invalid @enderror" required autofocus aria-label="{{ __('messages.email_or_username') }}">
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold small text-body-secondary mb-1">{{ __('Password') }}</label>
                                <input type="password" name="password" id="password" class="form-control form-control-lg bg-body-tertiary border-0 fs-6 shadow-none @error('password') is-invalid @enderror" required aria-label="{{ __('Password') }}">
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid mb-4 mt-2">
                                <button type="submit" class="btn btn-lg fw-semibold text-white shadow-sm" style="background-color: #4f46e5; border-color: #4f46e5;">{{ __('Sign In') }}</button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <a href="{{ route('register') }}" class="text-decoration-none small text-body-secondary fw-medium">{{ __('messages.create_an_account') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>