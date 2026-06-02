<?php

namespace App\Shared\Services;

use App\Shared\Jobs\ScanAttachment;
use App\Shared\Models\Attachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AttachmentService
{
    public function upload(
        UploadedFile $file,
        ?string $attachableType = null,
        ?string $attachableId = null,
        ?string $equipmentId = null,
        ?string $uploadedById = null,
    ): Attachment {
        if (($attachableType && !$attachableId) || (!$attachableType && $attachableId)) {
            throw new InvalidArgumentException('Both attachable_type and attachable_id must be provided together.');
        }

        $short = $attachableType ? class_basename($attachableType) : 'orphan';
        $folder = $equipmentId ? "equipment/{$equipmentId}" : Str::plural(strtolower($short)) . "/{$attachableId}";
        $path = $file->store("attachments/{$folder}", 'public');

        $ext = $file->getClientOriginalExtension();
        $safeName = Str::uuid() . '.' . $ext;

        $attachment = Attachment::create([
            'equipment_id'    => $equipmentId,
            'attachable_type' => $attachableType,
            'attachable_id'   => $attachableId,
            'file_path'       => $path,
            'file_name'       => $safeName,
            'mime_type'       => $file->getMimeType(),
        ]);

        ScanAttachment::dispatch($attachment->id, $uploadedById);

        return $attachment;
    }

    public function delete(Attachment $attachment): bool
    {
        Storage::disk('public')->delete($attachment->file_path);

        return $attachment->delete();
    }
}
