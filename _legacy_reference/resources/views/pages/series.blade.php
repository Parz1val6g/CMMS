@extends('layouts.app')

@section('title', __('Series Management'))

@php
    $breadcrumbs = [
        ['name' => __('messages.dashboard'), 'url' => route('home')],
        ['name' => __('messages.series'), 'url' => route('series.index')],
    ];
@endphp

@section('content')
    <x-modal :formSchema="$createFormSchema" :routes="$routes" />
    <x-data-manager
        title="{{ __('Series') }}"
        :items="$series"
        :routes="$routes"
        :columns="$columns"
        :formSchema="$formSchema"
    />
@endsection