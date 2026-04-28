@extends('layouts.app')

@section('title', __('Locations Management'))

@php
    $breadcrumbs = [
        ['name' => __('messages.dashboard'), 'url' => route('home')],
        ['name' => __('messages.locations'), 'url' => route('locations.index')],
    ];
@endphp

@section('content')
    <x-modal :formSchema="$createFormSchema" :routes="$routes" />
    <x-data-manager
        title="{{ __('Locations') }}"
        :items="$locations"
        :routes="$routes"
        :columns="$columns"
        :formSchema="$formSchema"
        :filterSchema="$filterSchema ?? []"
    />
@endsection