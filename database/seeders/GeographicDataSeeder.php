<?php

namespace Database\Seeders;

use App\Shared\Models\District;
use App\Shared\Models\Municipality;
use App\Shared\Models\Parish;
use Illuminate\Database\Seeder;

class GeographicDataSeeder extends Seeder
{
    public function run(): void
    {
        // Viseu District
        $viseu = District::create(['name' => 'Viseu']);

        // Municipalities in Viseu District
        $municipalities = [
            'Mangualde' => [
                'Abrunhosa-a-Velha',
                'Alcafache',
                'Cunha Baixa',
                'Espinho',
                'Fornos de Maceira Dão',
                'Freixiosa',
                'Mangualde',
                'Mesquitela',
                'Moimenta de Maceira Dão',
                'Póvoa de Cervães',
                'Quintela de Azurva',
                'Santiago de Cassurrães',
                'São João da Fresta',
                'Travanca de Oriz',
            ],
            'Viseu' => [
                'Abraveses',
                'Bodiosa',
                'Calde',
                'Campo',
                'Cavernães',
                'Cepões',
                'Cota',
                'Couto de Baixo',
                'Couto de Cima',
                'Fail',
                'Farminhão',
                'Fragosela',
                'Lordosa',
                'Mundão',
                'Orgens',
                'Pendilhe',
                'Ranhados',
                'Repeses',
                'Rio de Loba',
                'Santa Maria de Viseu',
                'Santos Evos',
                'São Cipriano',
                'São João de Lourosa',
                'São Pedro de France',
                'Silgueiros',
                'Torredeita',
                'Vila Chã de Sá',
            ],
            'Tondela' => [
                'Barreiro de Besteiros',
                'Campo de Besteiros',
                'Canas de Santa Maria',
                'Caparrosa',
                'Castelões',
                'Dardavaz',
                'Ferreiros do Dão',
                'Guardão',
                'Lajeosa do Dão',
                'Lobão da Beira',
                'Molelos',
                'Mouraz',
                'Parada de Gonta',
                'Santiago de Besteiros',
                'São João do Monte',
                'Tondela',
                'Tourigo',
            ],
            'Lamego' => [
                'Almacave',
                'Avões',
                'Cepões',
                'Ferreiros',
                'Figueira',
                'Lalim',
                'Lazarim',
                'Magueija',
                'Meijinhos',
                'Melcões',
                'Parada do Bispo',
                'Penajoia',
                'Penude',
                'Pretarouca',
                'Sé',
                'Valdigem',
                'Várzea de Abrunhais',
            ],
            'São Pedro do Sul' => [
                'Bordonhos',
                'Candal',
                'Carvalhais',
                'Covas',
                'Fiães',
                'Manhouce',
                'Pindelo dos Milagres',
                'Pinho',
                'Santa Cruz da Trapa',
                'São Félix',
                'São Miguel',
                'São Pedro do Sul',
                'Serrazes',
                'Sul',
                'Valadares',
                'Várzea',
            ],
        ];

        foreach ($municipalities as $municipio => $parishes) {
            $mun = Municipality::create([
                'name' => $municipio,
                'district_id' => $viseu->id,
            ]);

            foreach ($parishes as $parish) {
                Parish::create([
                    'name' => $parish,
                    'municipality_id' => $mun->id,
                ]);
            }
        }
    }
}
