<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $parishes = DB::table('parishes')->get();

        if ($parishes->isEmpty()) {
            return;
        }

        $locations = [
            // Mangualde area
            ['address' => 'Rua da Paz nº 123', 'postal_code' => '3530-001', 'latitude' => 40.6033, 'longitude' => -7.7611, 'landmark' => 'Perto da Câmara Municipal'],
            ['address' => 'Avenida dos Combatentes nº 45', 'postal_code' => '3530-002', 'latitude' => 40.6045, 'longitude' => -7.7620, 'landmark' => 'Centro da Cidade'],
            ['address' => 'Rua do Mercado nº 78', 'postal_code' => '3530-003', 'latitude' => 40.6020, 'longitude' => -7.7600, 'landmark' => 'Junto ao Mercado Municipal'],
            ['address' => 'Largo da Igreja Matriz', 'postal_code' => '3530-004', 'latitude' => 40.6050, 'longitude' => -7.7630, 'landmark' => 'Igreja Matriz'],
            ['address' => 'Rua das Flores nº 12', 'postal_code' => '3530-005', 'latitude' => 40.6010, 'longitude' => -7.7590, 'landmark' => 'Zona Residencial'],
            // Viseu area
            ['address' => 'Avenida da Europa nº 100', 'postal_code' => '3500-001', 'latitude' => 40.6610, 'longitude' => -7.9090, 'landmark' => 'Palácio do Gelo'],
            ['address' => 'Rua Dr. António José de Almeida nº 25', 'postal_code' => '3500-002', 'latitude' => 40.6600, 'longitude' => -7.9100, 'landmark' => 'Centro Histórico'],
            ['address' => 'Praça da República nº 1', 'postal_code' => '3500-003', 'latitude' => 40.6620, 'longitude' => -7.9080, 'landmark' => 'Praça Central'],
            ['address' => 'Rua do Comércio nº 50', 'postal_code' => '3500-004', 'latitude' => 40.6630, 'longitude' => -7.9110, 'landmark' => 'Zona Comercial'],
            ['address' => 'Avenida Alberto Sampaio nº 300', 'postal_code' => '3500-005', 'latitude' => 40.6590, 'longitude' => -7.9120, 'landmark' => 'Hospital Distrital'],
            // More Viseu locations
            ['address' => 'Rua da Sé nº 15', 'postal_code' => '3500-006', 'latitude' => 40.6640, 'longitude' => -7.9070, 'landmark' => 'Sé de Viseu'],
            ['address' => 'Estrada de Nelas nº 200', 'postal_code' => '3500-007', 'latitude' => 40.6580, 'longitude' => -7.9150, 'landmark' => 'Saída para Nelas'],
            ['address' => 'Rua do Parque nº 88', 'postal_code' => '3500-008', 'latitude' => 40.6650, 'longitude' => -7.9060, 'landmark' => 'Parque da Cidade'],
            ['address' => 'Avenida D. Duarte nº 120', 'postal_code' => '3500-009', 'latitude' => 40.6660, 'longitude' => -7.9140, 'landmark' => 'Zona Nobre'],
            ['address' => 'Rua dos Loureiros nº 33', 'postal_code' => '3500-010', 'latitude' => 40.6570, 'longitude' => -7.9130, 'landmark' => 'Bairro Residencial'],
            // Rural areas
            ['address' => 'Rua Principal nº 10', 'postal_code' => '3530-010', 'latitude' => 40.6000, 'longitude' => -7.7700, 'landmark' => 'Aldeia de Abrunhosa'],
            ['address' => 'Caminho do Moinho s/n', 'postal_code' => '3530-020', 'latitude' => 40.6100, 'longitude' => -7.7750, 'landmark' => 'Zona Industrial'],
            ['address' => 'Rua da Fonte nº 5', 'postal_code' => '3530-030', 'latitude' => 40.5950, 'longitude' => -7.7800, 'landmark' => 'Largo da Fonte'],
            ['address' => 'Estrada Nacional nº 234', 'postal_code' => '3530-040', 'latitude' => 40.6200, 'longitude' => -7.7900, 'landmark' => 'Zona Rural'],
            ['address' => 'Rua do Souto nº 20', 'postal_code' => '3530-050', 'latitude' => 40.5900, 'longitude' => -7.7650, 'landmark' => 'Junto à Capela'],
            // Industrial areas
            ['address' => 'Zona Industrial Lote 1', 'postal_code' => '3530-100', 'latitude' => 40.6300, 'longitude' => -7.7850, 'landmark' => 'Parque Industrial'],
            ['address' => 'Zona Industrial Lote 12', 'postal_code' => '3530-101', 'latitude' => 40.6310, 'longitude' => -7.7860, 'landmark' => 'Armazéns'],
            ['address' => 'Rua da Estação nº 8', 'postal_code' => '3530-060', 'latitude' => 40.6080, 'longitude' => -7.7580, 'landmark' => 'Estação de Comboios'],
            ['address' => 'Avenida dos Bombeiros nº 60', 'postal_code' => '3500-020', 'latitude' => 40.6670, 'longitude' => -7.9050, 'landmark' => 'Quartel dos Bombeiros'],
            ['address' => 'Rua da Escola nº 15', 'postal_code' => '3500-030', 'latitude' => 40.6680, 'longitude' => -7.9160, 'landmark' => 'Escola Secundária'],
        ];

        $now = now();

        foreach ($locations as $location) {
            $parish = $parishes->random();

            DB::table('locations')->insert([
                'id' => Str::uuid(),
                'parish_id' => $parish->id,
                'postal_code' => $location['postal_code'],
                'street_address' => $location['address'],
                'landmark' => $location['landmark'],
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
