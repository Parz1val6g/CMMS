<?php

namespace App\Features\Export\Services;

use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\WorkLogs\Models\WorkLog;
use Illuminate\Support\LazyCollection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
    public function exportServiceOrders(array $filters = []): StreamedResponse
    {
        $query = ServiceOrder::with(['client', 'manager', 'serviceType', 'tasks']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $headers = [
            'ID', 'Process', 'Client', 'Manager', 'Service Type',
            'Priority', 'Status', 'Task Count', 'Completed Tasks',
            'Execution Date', 'Created At',
        ];

        return $this->streamCsv('service-orders.csv', $headers, $query->lazy(), function (ServiceOrder $so) {
            $tasks = $so->tasks;
            $totalTasks = $tasks->count();
            $completedTasks = $tasks->where('status', 'completed')->count();

            return [
                $so->id,
                $so->process,
                $so->client?->name ?? 'N/A',
                $so->manager?->name ?? 'N/A',
                $so->serviceType?->name ?? 'N/A',
                $so->priority,
                $so->status,
                $totalTasks,
                $completedTasks,
                $so->execution_date?->format('Y-m-d') ?? 'N/A',
                $so->created_at->format('Y-m-d H:i'),
            ];
        });
    }

    public function exportWorkLogs(array $filters = []): StreamedResponse
    {
        $query = WorkLog::with(['miniTask.task.serviceOrder', 'workers.user', 'materials']);

        if (!empty($filters['mini_task_id'])) {
            $query->where('mini_task_id', $filters['mini_task_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['date_from'])) {
            $query->where('started_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('started_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['worker_id'])) {
            $query->whereHas('workers', fn($q) => $q->where('worker_id', $filters['worker_id']));
        }

        $headers = [
            'ID', 'Mini Task', 'Service Order', 'Description',
            'Started At', 'Completed At', 'Duration (min)',
            'Status', 'Worker(s)', 'Material(s) Used',
        ];

        return $this->streamCsv('work-logs.csv', $headers, $query->lazy(), function (WorkLog $wl) {
            $workerNames = $wl->workers->pluck('user.name')->implode('; ');
            $materialSummary = $wl->materials
                ->map(fn($m) => $m->name . ' x' . ($m->pivot->quantity_used ?? 0))
                ->implode('; ');

            return [
                $wl->id,
                $wl->miniTask?->name ?? 'N/A',
                $wl->miniTask?->task?->serviceOrder?->process ?? 'N/A',
                $wl->description,
                $wl->started_at->format('Y-m-d H:i'),
                $wl->completed_at?->format('Y-m-d H:i') ?? 'N/A',
                $wl->completed_at ? $wl->started_at->diffInMinutes($wl->completed_at) : 'N/A',
                $wl->status ?? 'N/A',
                $workerNames ?: 'N/A',
                $materialSummary ?: 'None',
            ];
        });
    }

    private function streamCsv(string $filename, array $headers, LazyCollection $rows, callable $mapper): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($headers, $rows, $mapper) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel compat
            fputcsv($handle, $headers, ';');

            foreach ($rows as $row) {
                fputcsv($handle, $mapper($row), ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        return $response;
    }
}
