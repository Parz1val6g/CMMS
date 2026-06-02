<?php

namespace App\Features\ServiceOrders\Jobs;

use App\Features\Notifications\Services\NotificationService;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Shared\Services\SandboxScanService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ScanServiceOrderPhoto implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 60;

    public function __construct(
        private string $serviceOrderId,
        private string $photoPath,
        private ?string $notifyUserId,
    ) {}

    public function handle(SandboxScanService $scanner, NotificationService $notifications): void
    {
        $serviceOrder = ServiceOrder::find($this->serviceOrderId);

        // Order deleted before scan completed — nothing to do.
        if (!$serviceOrder || $serviceOrder->photo_path !== $this->photoPath) {
            Storage::disk('public')->delete($this->photoPath);
            return;
        }

        $fullPath = Storage::disk('public')->path($this->photoPath);

        try {
            $scanner->scan($fullPath);
        } catch (UnprocessableEntityHttpException $e) {
            $this->rejectPhoto($serviceOrder, $notifications, $e->getMessage());
            return;
        } catch (\Throwable $e) {
            Log::error('ScanServiceOrderPhoto: scanner unreachable', [
                'service_order_id' => $this->serviceOrderId,
                'error' => $e->getMessage(),
            ]);
            // Scanner down — leave photo in place, fail the job so it lands in failed_jobs.
            throw $e;
        }
    }

    private function rejectPhoto(ServiceOrder $serviceOrder, NotificationService $notifications, string $reason): void
    {
        Storage::disk('public')->delete($this->photoPath);
        $serviceOrder->update(['photo_path' => null]);

        if (!$this->notifyUserId) {
            return;
        }

        $notifications->create(
            userId: $this->notifyUserId,
            title: __('messages.services.notifications.photo_scan_failed_title'),
            message: __('messages.services.notifications.photo_scan_failed_message', [
                'process' => $serviceOrder->process,
                'reason'  => $reason,
            ]),
            type: 'photo_scan_failed',
        );
    }
}
