<?php
namespace App\Shared\Services;
use App\Shared\Models\Attachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
class AttachmentService
{
    /**
     * Upload and attach a file.
     */
    public function upload(UploadedFile $file, ?string $serviceOrderId = null, ?string $miniTaskId = null): Attachment
    {
        // Enforce strict business logic from db_tables.sql
        if ($serviceOrderId && $miniTaskId)
            throw new InvalidArgumentException('Attachment cannot belong to both a Service Order and a Mini Task.');

        if (!$serviceOrderId && !$miniTaskId)
            throw new InvalidArgumentException('Attachment must belong to either a Service Order or a Mini Task.');

        // Keep local storage highly organized
        $folder = $serviceOrderId ? "service-orders/{$serviceOrderId}" : "mini-tasks/{$miniTaskId}";
        $path = $file->store("attachments/{$folder}", 'public');

        // Use server-generated UUID filename — never trust client-supplied names
        $ext = $file->getClientOriginalExtension();
        $safeName = Str::uuid() . '.' . $ext;

        return Attachment::create([
            'service_order_id' => $serviceOrderId,
            'mini_task_id' => $miniTaskId,
            'file_path' => $path,
            'file_name' => $safeName,
            'mime_type' => $file->getMimeType(),
        ]);
    }
    /**
     * Safely delete the file from disk and the database record.
     */
    public function delete(Attachment $attachment): bool
    {
        Storage::disk('public')->delete($attachment->file_path);

        return $attachment->delete();
    }
}