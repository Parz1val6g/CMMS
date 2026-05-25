<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SectorSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->select('id', 'email')->get()->keyBy('email');

        $heads = [
            'Departamento de Obras e Viação'    => 'rui.goncalves@cm-mangualde.pt',
            'Departamento de Urbanismo'         => 'maria.pereira@cm-mangualde.pt',
            'Departamento de Limpeza Urbana'    => 'nuno.costa@cm-mangualde.pt',
            'Departamento de Água e Saneamento' => 'sofia.marques@cm-mangualde.pt',
        ];

        foreach ($heads as $name => $email) {
            $headId = $users[$email]->id ?? null;
            DB::table('sectors')->insert([
                'id'         => Str::uuid(),
                'name'       => $name,
                'head_id'    => $headId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
