<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SectorSeeder extends Seeder
{
    public function run(): void
    {
        $headUserId = DB::table('users')->where('email', 'maria.santos@cm.pt')->first()->id;

        $sectors = [
            ['name' => 'Departamento de Obras e Viação', 'head_id' => $headUserId],
            ['name' => 'Departamento de Urbanismo', 'head_id' => $headUserId],
            ['name' => 'Departamento de Limpeza Urbana', 'head_id' => DB::table('users')->where('email', 'carlos.oliveira@cm.pt')->first()->id],
            ['name' => 'Departamento de Água e Saneamento', 'head_id' => DB::table('users')->where('email', 'fernanda.pereira@cm.pt')->first()->id],
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
