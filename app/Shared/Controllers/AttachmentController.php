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
     * Upload a new attachment
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Attachment::class);

        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:jpeg,png,jpg,webp,pdf,doc,docx,xls,xlsx,csv'], // 10MB max + Strict Mimes
            'service_order_id' => ['nullable', 'exists:service_orders,id', 'prohibits:mini_task_id'],
            'mini_task_id' => ['nullable', 'exists:mini_tasks,id', 'prohibits:service_order_id'],
        ]);

        if (!$request->service_order_id && !$request->mini_task_id) {
            return response()->json(['message' => 'Must provide either service_order_id or mini_task_id.'], 422);
        }

        $attachment = $this->attachmentService->upload(
            $request->file('file'),
            $request->service_order_id,
            $request->mini_task_id
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
