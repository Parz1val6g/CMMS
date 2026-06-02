<?php

namespace App\Shared\Jobs;

use App\Features\Notifications\Services\NotificationService;
use App\Shared\Models\Attachment;
use App\Shared\Services\SandboxScanService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ScanAttachment implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 60;

    public function __construct(
        private string $attachmentId,
        private ?string $notifyUserId,
    ) {}

    public function handle(SandboxScanService $scanner, NotificationService $notifications): void
    {
        $attachment = Attachment::find($this->attachmentId);

        if (!$attachment) {
            return;
        }

        $fullPath = Storage::disk('public')->path($attachment->file_path);

        try {
            $scanner->scan($fullPath);
        } catch (UnprocessableEntityHttpException $e) {
            $this->rejectAttachment($attachment, $notifications, $e->getMessage());
            return;
        } catch (\Throwable $e) {
            Log::error('ScanAttachment: scanner unreachable', [
                'attachment_id' => $this->attachmentId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function rejectAttachment(Attachment $attachment, NotificationService $notifications, string $reason): void
    {
        $fileName = $attachment->file_name;

        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        if (!$this->notifyUserId) {
            return;
        }

        $notifications->create(
            userId: $this->notifyUserId,
            title: __('messages.services.notifications.attachment_scan_failed_title'),
            message: __('messages.services.notifications.attachment_scan_failed_message', [
                'file'   => $fileName,
                'reason' => $reason,
            ]),
            type: 'attachment_scan_failed',
        );
    }
}
