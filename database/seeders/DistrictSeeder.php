<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        $districts = [
            [
                'id' => '25e4eb4e-651b-4fc9-ad5e-ed4b82cdfce1',
                'name' => 'Aveiro',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'a39872a7-ce21-4a19-b99d-f75e9698d1d8',
                'name' => 'Beja',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '66632382-ec08-43be-a0bf-9625c98f5c3f',
                'name' => 'Braga',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'a9e9ee0f-7bee-46c7-929c-e496f2323d8b',
                'name' => 'Bragança',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '14664ca5-7ebb-4a4d-ae09-80c21c3ded61',
                'name' => 'Castelo Branco',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '2b8160d0-f3d3-4663-b49a-fafab1e6fb0f',
                'name' => 'Coimbra',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '7ff124a2-8d8e-4a61-bc71-5cbbe850c86b',
                'name' => 'Faro',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '158d9e77-3fa2-4e51-a870-04999bba924a',
                'name' => 'Guarda',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '34ad6d35-2f26-4a1e-b1ea-0d5e17f48ad9',
                'name' => 'Leiria',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '50de4563-fc97-4660-98f6-2c369ee43ad5',
                'name' => 'Lisboa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'e63c7a0a-c935-4dda-9242-48dde7c5e026',
                'name' => 'Portalegre',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '5f419008-9e7e-4406-a6bf-704f3f5bca10',
                'name' => 'Porto',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '47c5183a-582c-4b17-b40c-8ccab7e54b44',
                'name' => 'Santarém',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '3d4b5ac5-fdad-4e7b-b313-063f7d9c0501',
                'name' => 'Setúbal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => '4ef1f9a6-5f51-49a9-85f3-402accaa7cdb',
                'name' => 'Viana do Castelo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'b73d267f-5292-4f93-a348-d121dc733cfb',
                'name' => 'Vila Real',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'c8074abc-5256-4339-9c3b-d8f2ae2ec6b7',
                'name' => 'Viseu',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'ebf0137e-d482-4a1f-ae5f-3e955f5b2c57',
                'name' => 'Évora',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('districts')->insert($districts);
    }
}
