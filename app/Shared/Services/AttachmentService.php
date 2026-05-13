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
     * Upload and attach a file to any attachable entity.
     *
     * @param  UploadedFile       $file
     * @param  string|null        $attachableType  Morph type (ServiceOrder::class, MiniTask::class, Equipment::class)
     * @param  string|null        $attachableId    Morph ID
     * @param  string|null        $equipmentId     Direct FK to equipment (optional, for convenience)
     * @return Attachment
     */
    public function upload(
        UploadedFile $file,
        ?string $attachableType = null,
        ?string $attachableId = null,
        ?string $equipmentId = null,
    ): Attachment {
        if (($attachableType && !$attachableId) || (!$attachableType && $attachableId)) {
            throw new InvalidArgumentException('Both attachable_type and attachable_id must be provided together.');
        }

        $short = $attachableType ? class_basename($attachableType) : 'orphan';
        $folder = $equipmentId ? "equipment/{$equipmentId}" : Str::plural(strtolower($short)) . "/{$attachableId}";
        $path = $file->store("attachments/{$folder}", 'public');

        $ext = $file->getClientOriginalExtension();
        $safeName = Str::uuid() . '.' . $ext;

        return Attachment::create([
            'equipment_id' => $equipmentId,
            'attachable_type' => $attachableType,
            'attachable_id' => $attachableId,
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