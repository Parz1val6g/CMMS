<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // ── Layer 1: Foundation ──
            RoleSeeder::class,
            RolePermissionSeeder::class,
            GeographicDataSeeder::class,     // districts → municipalities → parishes
            DistrictSeeder::class,
            MunicipalitySeeder::class,
            ParishSeeder::class,

            // ── Layer 2: Users & Profile Data ──
            UserSeeder::class,               // admin + managers
            UserPreferenceSeeder::class,

            // ── Layer 3: Configuration ──
            ServiceTypeSeeder::class,
            UnitSeeder::class,
            MaterialSeeder::class,
            AppSettingSeeder::class,

            // ── Layer 4: Organisation ──
            SectorSeeder::class,             // references users (heads)
            TeamSeeder::class,               // references sectors
            WorkerSeeder::class,             // references users + teams

            // ── Layer 5: Clients & Locations ──
            ClientSeeder::class,             // creates user accounts + clients
            LocationSeeder::class,           // references parishes

            // ── Layer 6: Service Orders & Tasks ──
            ServiceOrderSeeder::class,       // references clients, managers, locations, service_types
            TaskSeeder::class,               // references service_orders, users
            MiniTaskSeeder::class,           // references tasks, users

            // ── Layer 7: Execution Data ──
            WorkLogSeeder::class,            // references mini_tasks, workers, materials; creates pivot data

            // ── Layer 8: Legacy pivot seeders (extra data) ──
            MiniTaskWorkerTeamSeeder::class,
            WorkLogMaterialSeeder::class,
            WorkLogWorkerSeeder::class,

            // ── Layer 9: Attachments ──
            AttachmentSeeder::class,

            // ── Layer 10: Demo Scenario ──
            GouveiaScenarioSeeder::class,
        ]);
    }
}
