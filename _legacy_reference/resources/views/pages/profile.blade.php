@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                        <h1 class="mb-4">{{ __('messages.profile') }}</h1>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ __('messages.user_information') }}</h5>
                        <p><strong>{{ __('messages.name') }}:</strong> {{ auth()->user()->full_name }}</p>
                        <p><strong>{{ __('messages.email') }}:</strong> {{ auth()->user()->email }}</p>
                        <p><strong>{{ __('messages.role') }}:</strong> {{ ucfirst(auth()->user()->role->value) }}</p>
                        <p><strong>{{ __('messages.status') }}:</strong> {!! auth()->user()->getStatusBadgeColor() !!}</p>

                        <hr>

                        <a href="{{ route('home') }}" class="btn btn-secondary">{{ __('messages.back') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
