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
                'id'              => Str::uuid(),
                'attachable_type' => 'App\\Features\\ServiceOrders\\Models\\ServiceOrder',
                'attachable_id'   => $soId,
                'file_path'       => "uploads/so/{$soId}/report.pdf",
                'file_name'       => 'Relatório.pdf',
                'mime_type'       => 'application/pdf',
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }

        // One attachment per mini-task
        foreach ($miniTasks as $mtId) {
            $attachments[] = [
                'id'              => Str::uuid(),
                'attachable_type' => 'App\\Features\\MiniTasks\\Models\\MiniTask',
                'attachable_id'   => $mtId,
                'file_path'       => "uploads/mt/{$mtId}/photo.jpg",
                'file_name'       => 'Fotografia.jpg',
                'mime_type'       => 'image/jpeg',
                'created_at'      => now(),
                'updated_at'      => now(),
            ];
        }

        foreach ($attachments as $att) {
            $exists = DB::table('attachments')
                ->where('attachable_type', $att['attachable_type'])
                ->where('attachable_id', $att['attachable_id'])
                ->exists();

            if (!$exists) {
                DB::table('attachments')->insert($att);
            }
        }
    }
}
