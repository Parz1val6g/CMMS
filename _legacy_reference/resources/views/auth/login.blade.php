@extends('layouts.app')

@section('title', __('Sign In'))

@section('content')
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5 col-lg-4">

            <div class="text-center mb-4">
                <h2 class="fw-bold">{{ __('Welcome back') }}</h2>
                <p class="text-muted">{{ __('Please enter your credentials to sign in.') }}</p>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <form method="POST" action="{{ route('login.submit') }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">{{ __('Email Address') }}</label>
                            <input 
                                type="email" 
                                class="form-control @error('email') is-invalid @enderror" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required 
                                autofocus
                                placeholder="name@example.com"
                                aria-label="{{ __('Email Address') }}"
                            >
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">{{ __('Password') }}</label>
                            <input 
                                type="password" 
                                class="form-control @error('password') is-invalid @enderror" 
                                id="password" 
                                name="password" 
                                required
                                aria-label="{{ __('Password') }}"
                            >
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label user-select-none" for="remember">{{ __('Remember me') }}</label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-2 fw-bold">
                                {{ __('Sign In') }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            @if(\App\Helpers\FeatureFlags::isUserRegistrationEnabled())
                <div class="text-center mt-4">
                    <p class="text-muted">{{ __('Don\'t have an account?') }} <a href="{{ route('register') }}" class="text-decoration-none fw-semibold">{{ __('Register here') }}</a>.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="accountDeletedModal" tabindex="-1" aria-labelledby="accountDeletedLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="accountDeletedLabel">{{ __('Account Deleted') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <svg class="text-danger mb-3" xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                        <path d="M8.5 6.5a.5.5 0 0 0-1 0v3.362a.5.5 0 0 0 1 0V6.5z"/>
                        <path d="M13.854 2.146a.5.5 0 0 1 0 .708l-11 11a.5.5 0 0 1-.708-.708l11-11a.5.5 0 0 1 .708 0Z"/>
                        <path d="M2.5 1A1.5 1.5 0 0 0 1 2.5v11A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-11A1.5 1.5 0 0 0 13.5 1h-11zM2 2.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 .5.5v11a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11z"/>
                    </svg>
                    <p class="lead fw-bold mb-2">{{ __('Your account has been deleted') }}</p>
                    <p class="text-body-secondary mb-0">{{ __('Your account and all associated data have been permanently removed. You can create a new account at any time.') }}</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">{{ __('OK') }}</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('deleted') === 'true') {
                const modal = new bootstrap.Modal(document.getElementById('accountDeletedModal'));
                modal.show();
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        </script>
    @endpush

@endsection