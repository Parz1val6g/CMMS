<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SectorSeeder extends Seeder
{
    public function run(): void
    {
        $headUserId = DB::table('users')->where('email', 'maria.santos@cm.pt')->value('id');

        $sectors = [
            ['name' => 'Departamento de Obras e Viação', 'head_id' => $headUserId],
            ['name' => 'Departamento de Urbanismo', 'head_id' => $headUserId],
            ['name' => 'Departamento de Limpeza Urbana', 'head_id' => $headUserId],
            ['name' => 'Departamento de Água e Saneamento', 'head_id' => $headUserId],
        ];

        foreach ($sectors as $sector) {
            DB::table('sectors')->insert([
                'id' => Str::uuid(),
                'name' => $sector['name'],
                'head_id' => $sector['head_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
