<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $sectors = DB::table('sectors')->get();

        $teams = [];
        foreach ($sectors as $sector) {
            if ($sector->name === 'Departamento de Obras e Viação') {
                $teams[] = ['sector_id' => $sector->id, 'name' => 'Equipa de Pavimentação'];
                $teams[] = ['sector_id' => $sector->id, 'name' => 'Equipa de Iluminação'];
                $teams[] = ['sector_id' => $sector->id, 'name' => 'Equipa de Reparações'];
            } elseif ($sector->name === 'Departamento de Urbanismo') {
                $teams[] = ['sector_id' => $sector->id, 'name' => 'Equipa de Licenças'];
                $teams[] = ['sector_id' => $sector->id, 'name' => 'Equipa de Inspeção'];
            } elseif ($sector->name === 'Departamento de Limpeza Urbana') {
                $teams[] = ['sector_id' => $sector->id, 'name' => 'Equipa de Limpeza'];
                $teams[] = ['sector_id' => $sector->id, 'name' => 'Equipa de Jardinagem'];
            } elseif ($sector->name === 'Departamento de Água e Saneamento') {
                $teams[] = ['sector_id' => $sector->id, 'name' => 'Equipa de Água'];
                $teams[] = ['sector_id' => $sector->id, 'name' => 'Equipa de Esgotos'];
            }
        }

        foreach ($teams as $team) {
            DB::table('teams')->insert([
                'id' => Str::uuid(),
                'sector_id' => $team['sector_id'],
                'name' => $team['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
