<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AttachmentSeeder extends Seeder
{
    public function run(): void
    {
        $serviceOrders = DB::table('service_orders')->pluck('id');
        $miniTasks     = DB::table('mini_tasks')->pluck('id');

        if ($serviceOrders->isEmpty() && $miniTasks->isEmpty()) return;

        $attachments = [];

        // One attachment per service order
        foreach ($serviceOrders as $soId) {
            $attachments[] = [
                'id'               => Str::uuid(),
                'service_order_id' => $soId,
                'mini_task_id'     => null,
                'file_path'        => "uploads/so/{$soId}/report.pdf",
                'file_name'        => 'Relatório.pdf',
                'mime_type'        => 'application/pdf',
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        // One attachment per mini-task (if any service orders exist)
        foreach ($miniTasks as $mtId) {
            $attachments[] = [
                'id'               => Str::uuid(),
                'service_order_id' => null,
                'mini_task_id'     => $mtId,
                'file_path'        => "uploads/mt/{$mtId}/photo.jpg",
                'file_name'        => 'Fotografia.jpg',
                'mime_type'        => 'image/jpeg',
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        foreach ($attachments as $att) {
            $exists = DB::table('attachments')
                ->where(function ($q) use ($att) {
                    $q->where('service_order_id', $att['service_order_id'])
                      ->orWhere('mini_task_id', $att['mini_task_id']);
                })
                ->exists();

            if (!$exists) {
                DB::table('attachments')->insert($att);
            }
        }
    }
}
