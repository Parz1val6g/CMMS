<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Ordered layers for deterministic, exhaustive seeding.
     * Every role gets 1 user (UserSeeder), entities cover all enum states.
     */
    public function run(): void
    {
        $this->call([
                // ── Layer 1: Foundation ──
            RoleSeeder::class,
            RolePermissionSeeder::class,
            DistrictSeeder::class,           // 18 districts (hardcoded from concelho.sql)
            MunicipalitySeeder::class,       // 278 municipalities (hardcoded from concelho.sql)
            ParishSeeder::class,             // 2882 parishes (hardcoded from freguesias.sql)

                // ── Layer 2: Users (1 per role) ──
            UserSeeder::class,               // admin, manager, equipment_manager, supervisor, worker, client

                // ── Layer 3: Configuration ──
            ServiceTypeSeeder::class,
            UnitSeeder::class,
            MaterialSeeder::class,
            EquipmentTypeSeeder::class,      // predefined equipment types
            CountingTypeSeeder::class,       // predefined counting types
            EquipmentSeeder::class,          // all statuses: active, maintenance, reserved, inactive
            AppSettingSeeder::class,

                // ── Layer 4: Organisation ──
            SectorSeeder::class,             // references users (heads)
            TeamSeeder::class,               // references sectors
            WorkerSeeder::class,             // references users + teams

                // ── Layer 5: Clients & Locations ──
            ClientSeeder::class,             // creates user accounts + clients
            LocationSeeder::class,           // references parishes

                // ── Layer 6: Service Orders & Tasks (Testing Gallery) ──
            ServiceOrderSeeder::class,       // exhaustive: all status×priority×workflow combos
            TaskSeeder::class,               // all TaskStatus values
            MiniTaskSeeder::class,           // all MiniTaskStatus values

                // ── Layer 7: Execution Data ──
            WorkLogSeeder::class,            // all WorkLogStatus values

                // ── Layer 8: Attachments ──
            AttachmentSeeder::class,
        ]);
    }
}
