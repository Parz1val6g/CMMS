@extends('layouts.app')

@section('title', __('Service Orders Management'))

@php
    $breadcrumbs = [
        ['name' => __('messages.dashboard'), 'url' => route('home')],
        ['name' => __('messages.service_orders'), 'url' => route('service-orders.index')],
    ];
@endphp

@section('content')
    <x-modal :formSchema="$createFormSchema" :routes="$routes" size="lg" />
    <x-data-manager
        title="{{ __('Service Orders') }}"
        :items="$service_orders"
        :routes="$routes"
        :columns="$columns"
        :formSchema="$formSchema"
        :filterSchema="$filterSchema ?? []"
    />
@endsection