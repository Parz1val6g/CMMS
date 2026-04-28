@extends('layouts.app')

@section('title', __('Service Types Management'))

@php
    $breadcrumbs = [
        ['name' => __('messages.dashboard'), 'url' => route('home')],
        ['name' => __('messages.service_types'), 'url' => route('service-types.index')],
    ];
@endphp

@section('content')
    <x-modal :formSchema="$createFormSchema" :routes="$routes" />
    <x-data-manager
        title="{{ __('Service Types') }}"
        :items="$service_types"
        :routes="$routes"
        :columns="$columns"
        :formSchema="$formSchema"
    />
@endsection