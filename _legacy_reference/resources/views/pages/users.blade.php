@extends('layouts.app')

@section('title', __('Users Management'))

@php
    $breadcrumbs = [
        ['name' => __('messages.dashboard'), 'url' => route('home')],
        ['name' => __('messages.users'), 'url' => route('users.index')],
    ];
@endphp

@section('content')
    <x-modal :formSchema="$createFormSchema" :routes="$routes" />
    <x-data-manager
        title="{{ __('Users') }}"
        :items="$users"
        :routes="$routes"
        :columns="$columns"
        :formSchema="$formSchema"
    />
@endsection