@extends('layouts.app')

@section('title', __('Dashboard Operacional'))

@php
    $breadcrumbs = [
        ['name' => __('messages.dashboard'), 'url' => route('home')],
    ];
@endphp

@section('content')
    <x-scrollable>
        <div class="space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Ordens em Curso</h3>
                    <div class="mt-2 flex items-baseline text-3xl font-extrabold text-gray-900">
                        {{ $kpis['active_orders'] }}
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Tarefas Pendentes</h3>
                    <div class="mt-2 flex items-baseline text-3xl font-extrabold text-gray-900">
                        {{ $kpis['pending_tasks'] }}
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Equipas no Terreno (MTs)</h3>
                    <div class="mt-2 flex items-baseline text-3xl font-extrabold text-gray-900">
                        {{ $kpis['active_mini_tasks'] }}
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-indigo-500">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Horas Registadas Hoje</h3>
                    <div class="mt-2 flex items-baseline text-3xl font-extrabold text-gray-900">
                        {{ number_format($kpis['today_work_hours'], 1) }} <span class="ml-2 text-sm text-gray-500">hrs</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900 text-red-600">
                            <i class="fas fa-exclamation-triangle mr-2"></i> Ordens Críticas (Prioridade Alta)
                        </h3>
                    </div>
                    <ul class="divide-y divide-gray-200">
                        @forelse($criticalOrders as $order)
                            <li class="p-6 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-blue-600 truncate">{{ $order->process }}</p>
                                        <p class="mt-2 flex items-center text-sm text-gray-500">
                                            <i class="fas fa-map-marker-alt mr-1.5 text-gray-400"></i>
                                            {{ $order->location->parish->name ?? 'Local desconhecido' }}
                                        </p>
                                    </div>
                                    <div class="ml-4 flex-shrink-0 flex flex-col items-end">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Alta Prioridade
                                        </span>
                                        <p class="mt-2 text-sm text-gray-500">
                                            Criado: {{ $order->created_at->format('d/m/Y') }}
                                        </p>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="p-6 text-center text-gray-500">
                                Não existem ordens críticas pendentes. Excelente trabalho!
                            </li>
                        @endforelse
                    </ul>
                </div>

                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-900">
                            <i class="fas fa-clipboard-list mr-2"></i> Intervenções Concluídas Recentemente
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="flow-root">
                            <ul class="-mb-8">
                                @forelse($recentWorkLogs as $log)
                                    <li>
                                        <div class="relative pb-8">
                                            @if(!$loop->last)
                                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                            @endif
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                        <i class="fas fa-check text-white text-xs"></i>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500">
                                                            <span class="font-medium text-gray-900">
                                                                {{ $log->workers->first()->user->first_name ?? 'Equipa' }}
                                                            </span> 
                                                            registou trabalho em 
                                                            <a href="#" class="font-medium text-blue-600 hover:underline">
                                                                {{ $log->miniTask->task->name ?? 'Tarefa Removida' }}
                                                            </a>
                                                        </p>
                                                        <p class="text-sm text-gray-500 mt-1">
                                                            "{{ Str::limit($log->description, 60) }}" ({{ $log->duration_minutes }} min)
                                                        </p>
                                                    </div>
                                                    <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                        <time datetime="{{ $log->completed_at }}">{{ \Carbon\Carbon::parse($log->completed_at)->diffForHumans() }}</time>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <p class="text-sm text-gray-500 text-center">Nenhum registo de trabalho submetido recentemente.</p>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </x-scrollable>
@endsection