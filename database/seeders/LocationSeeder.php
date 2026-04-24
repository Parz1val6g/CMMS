<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $parishes = DB::table('parishes')->limit(5)->get();

        $locations = [
            ['address' => 'Rua da Paz nº 123', 'postal_code' => '1100-381', 'latitude' => 38.7071, 'longitude' => -9.1433, 'landmark' => 'Perto da Estação de Comboios'],
            ['address' => 'Avenida Brasil nº 456', 'postal_code' => '1700-051', 'latitude' => 38.7200, 'longitude' => -9.1500, 'landmark' => 'Centro Comercial Colombo'],
            ['address' => 'Praça do Comércio nº 789', 'postal_code' => '1100-148', 'latitude' => 38.7075, 'longitude' => -9.1355, 'landmark' => 'Junto ao Tejo'],
            ['address' => 'Rua Garrett nº 101', 'postal_code' => '1200-204', 'latitude' => 38.7167, 'longitude' => -9.1433, 'landmark' => 'Chiado'],
            ['address' => 'Avenida Liberdade nº 202', 'postal_code' => '1250-096', 'latitude' => 38.7244, 'longitude' => -9.1461, 'landmark' => 'Avenida Principal'],
            ['address' => 'Rua de São Bento nº 303', 'postal_code' => '1200-821', 'latitude' => 38.7096, 'longitude' => -9.1492, 'landmark' => 'Príncipe Real'],
            ['address' => 'Rua da Conceição nº 404', 'postal_code' => '1100-161', 'latitude' => 38.7090, 'longitude' => -9.1374, 'landmark' => 'Baixa Pombalina'],
            ['address' => 'Avenida João Crisóstomo nº 505', 'postal_code' => '1050-074', 'latitude' => 38.7193, 'longitude' => -9.1456, 'landmark' => 'Marquês de Pombal'],
            ['address' => 'Rua Barata Salgueiro nº 606', 'postal_code' => '1000-088', 'latitude' => 38.7271, 'longitude' => -9.1480, 'landmark' => 'São Jorge'],
            ['address' => 'Rua Castilho nº 707', 'postal_code' => '1250-068', 'latitude' => 38.7249, 'longitude' => -9.1450, 'landmark' => 'Restauradores'],
        ];

        $count = 0;
        foreach ($parishes as $parish) {
            if ($count >= count($locations))
                break;

            DB::table('locations')->insert([
                'id' => Str::uuid(),
                'parish_id' => $parish->id,
                'postal_code' => $locations[$count]['postal_code'],
                'street_address' => $locations[$count]['address'],
                'landmark' => $locations[$count]['landmark'],
                'latitude' => $locations[$count]['latitude'],
                'longitude' => $locations[$count]['longitude'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $count++;
        }
    }
}
