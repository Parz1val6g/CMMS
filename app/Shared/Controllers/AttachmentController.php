<?php

namespace App\Shared\Controllers;

use App\Shared\Models\Attachment;
use App\Shared\Services\AttachmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function __construct(
        private AttachmentService $attachmentService
    ) {}

    /**
     * Upload a new attachment (polymorphic: equipment_id XOR attachable_type+attachable_id)
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Attachment::class);

        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:jpeg,png,jpg,webp,pdf,doc,docx,xls,xlsx,csv'],
            'equipment_id' => ['nullable', 'exists:equipments,id', 'prohibits:attachable_type,attachable_id'],
            'attachable_type' => ['nullable', 'string', 'max:255', 'prohibits:equipment_id'],
            'attachable_id' => ['nullable', 'string', 'max:36', 'prohibits:equipment_id'],
        ]);

        if (!$request->equipment_id && !$request->attachable_type) {
            return response()->json(['message' => 'Must provide either equipment_id or attachable_type+attachable_id.'], 422);
        }

        $attachment = $this->attachmentService->upload(
            $request->file('file'),
            $request->attachable_type,
            $request->attachable_id,
            $request->equipment_id,
            $request->user()?->id,
        );

        return response()->json([
            'message' => 'File uploaded successfully',
            'attachment' => [
                'id' => $attachment->id,
                'file_name' => $attachment->file_name,
                'url' => Storage::url($attachment->file_path),
            ]
        ], 201);
    }

    /**
     * Delete an attachment
     */
    public function destroy(Attachment $attachment): JsonResponse
    {
        $this->authorize('delete', $attachment);

        $this->attachmentService->delete($attachment);

        return response()->json(['message' => 'Attachment deleted successfully']);
    }
}
