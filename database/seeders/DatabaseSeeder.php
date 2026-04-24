<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            DistrictSeeder::class,
            MunicipalitySeeder::class,
            ParishSeeder::class,
            LocationSeeder::class,
            ServiceTypeSeeder::class,
            UnitSeeder::class,
            MaterialSeeder::class,
            SectorSeeder::class,
            TeamSeeder::class,
            WorkerSeeder::class,
            ClientSeeder::class,
            AppSettingSeeder::class,
        ]);
    }
}
