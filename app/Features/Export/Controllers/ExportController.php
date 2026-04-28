<?php

namespace App\Features\Export\Controllers;

use App\Features\Export\Services\CsvExportService;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\WorkLogs\Models\WorkLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(
        private CsvExportService $csvExportService
    ) {}

    public function serviceOrders(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', ServiceOrder::class);

        return $this->csvExportService->exportServiceOrders($request->only([
            'status', 'priority', 'date_from', 'date_to',
        ]));
    }

    public function workLogs(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', WorkLog::class);

        return $this->csvExportService->exportWorkLogs($request->only([
            'mini_task_id', 'status', 'date_from', 'date_to', 'worker_id',
        ]));
    }
}
