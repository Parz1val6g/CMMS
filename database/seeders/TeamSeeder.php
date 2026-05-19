<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $sectors = DB::table('sectors')->select('id', 'name')->get()->keyBy('name');
        $users   = DB::table('users')->select('id', 'email')->get()->keyBy('email');

        $responsibleEmail = 'rui.goncalves@cm-mangualde.pt';
        $responsibleId = $users[$responsibleEmail]->id ?? null;

        $teams = [
            'Departamento de Obras e Viação' => [
                'Equipa de Pavimentação',
                'Equipa de Sinalização',
                'Equipa de Calcetamento',
            ],
            'Departamento de Urbanismo' => [
                'Equipa de Licenciamento',
                'Equipa de Fiscalização Técnica',
            ],
            'Departamento de Limpeza Urbana' => [
                'Equipa de Recolha de Resíduos',
                'Equipa de Manutenção de Jardins',
                'Equipa de Limpeza de Vias',
            ],
            'Departamento de Água e Saneamento' => [
                'Equipa de Redes de Água',
                'Equipa de Saneamento',
            ],
        ];

        foreach ($teams as $sectorName => $teamList) {
            $sectorId = $sectors[$sectorName]->id ?? null;
            if (!$sectorId) continue;

            foreach ($teamList as $teamName) {
                DB::table('teams')->insert([
                    'id'             => Str::uuid(),
                    'name'           => $teamName,
                    'sector_id'      => $sectorId,
                    'responsible_id' => $responsibleId,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }
    }
}
