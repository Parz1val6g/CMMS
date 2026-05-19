<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AppSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'municipality_name', 'value' => 'Câmara Municipal de Mangualde', 'section' => 'general'],
            ['key' => 'contact_email', 'value' => 'geral@cm-mangualde.pt', 'section' => 'general'],
            ['key' => 'contact_phone', 'value' => '+351 232 610 000', 'section' => 'general'],
            ['key' => 'timezone', 'value' => 'Europe/Lisbon', 'section' => 'general'],
            ['key' => 'locale', 'value' => 'pt', 'section' => 'general'],
            ['key' => 'currency', 'value' => 'EUR', 'section' => 'general'],
            ['key' => 'maintenance_mode', 'value' => '0', 'section' => 'system'],
            ['key' => 'api_rate_limit_public', 'value' => '5', 'section' => 'api'],
            ['key' => 'api_rate_limit_auth', 'value' => '60', 'section' => 'api'],
            ['key' => 'api_rate_limit_sensitive', 'value' => '10', 'section' => 'api'],
            ['key' => 'cache_ttl_list', 'value' => '3600', 'section' => 'cache'],
            ['key' => 'cache_ttl_item', 'value' => '1800', 'section' => 'cache'],
            ['key' => 'max_upload_size', 'value' => '10485760', 'section' => 'files'],
            ['key' => 'allowed_file_types', 'value' => 'pdf,docx,xlsx,jpg,png', 'section' => 'files'],
            ['key' => 'export_batch_size', 'value' => '1000', 'section' => 'export'],
        ];

        foreach ($settings as $setting) {
            DB::table('app_settings')->insert([
                'id' => Str::uuid(),
                'key' => $setting['key'],
                'value' => $setting['value'],
                'section' => $setting['section'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
