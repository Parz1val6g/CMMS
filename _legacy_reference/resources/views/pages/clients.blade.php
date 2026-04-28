@extends('layouts.app')

@section('title', __('Clients Management'))

@php
    $breadcrumbs = [
        ['name' => __('messages.dashboard'), 'url' => route('home')],
        ['name' => __('messages.clients'), 'url' => route('clients.index')],
    ];
@endphp

@section('content')
    <x-modal :formSchema="$createFormSchema" :routes="$routes" />
    <x-data-manager
        title="{{ __('Clients') }}"
        :items="$clients"
        :routes="$routes"
        :columns="$columns"
        :formSchema="$formSchema"
    />
@endsection