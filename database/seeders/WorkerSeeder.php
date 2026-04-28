<?php

namespace Database\Seeders;

use App\Core\Enums\SystemStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class WorkerSeeder extends Seeder
{
    public function run(): void
    {
        $managerRole = DB::table('roles')->where('name', 'manager')->first();
        $teams = DB::table('teams')->get();

        $workerNames = [
            ['first_name' => 'António', 'last_name' => 'Silva'],
            ['first_name' => 'Pedro', 'last_name' => 'Santos'],
            ['first_name' => 'João', 'last_name' => 'Oliveira'],
            ['first_name' => 'José', 'last_name' => 'Ferreira'],
            ['first_name' => 'Miguel', 'last_name' => 'Costa'],
            ['first_name' => 'Rosa', 'last_name' => 'Gonçalves'],
            ['first_name' => 'Rui', 'last_name' => 'Martins'],
            ['first_name' => 'Mariana', 'last_name' => 'Alves'],
            ['first_name' => 'Paulo', 'last_name' => 'Ribeiro'],
            ['first_name' => 'Conceição', 'last_name' => 'Teixeira'],
            ['first_name' => 'Luís', 'last_name' => 'Fonseca'],
            ['first_name' => 'Carla', 'last_name' => 'Neves'],
            ['first_name' => 'André', 'last_name' => 'Pinto'],
            ['first_name' => 'Teresa', 'last_name' => 'Araújo'],
            ['first_name' => 'Nuno', 'last_name' => 'Almeida'],
            ['first_name' => 'Sandra', 'last_name' => 'Rodrigues'],
            ['first_name' => 'Hélder', 'last_name' => 'Gomes'],
            ['first_name' => 'Patrícia', 'last_name' => 'Guimarães'],
            ['first_name' => 'Ricardo', 'last_name' => 'Vieira'],
            ['first_name' => 'Mónica', 'last_name' => 'Barbosa'],
        ];

        $phoneBase = 900000000;
        foreach ($teams as $team) {
            for ($i = 0; $i < 3; $i++) {
                $name = $workerNames[($phoneBase - 900000000) % count($workerNames)];
                $counter = rand(1000, 9999);
                $email = strtolower($name['first_name']) . '.' . strtolower($name['last_name']) . $counter . '@workers.cm.pt';
                $phone = '+351' . $phoneBase;

                $userId = Str::uuid();
                $workerId = Str::uuid();

                DB::table('users')->insert([
                    'id' => $userId,
                    'first_name' => $name['first_name'],
                    'last_name' => $name['last_name'],
                    'phone' => $phone,
                    'email' => $email,
                    'password' => Hash::make('password123'),
                    'status' => 'active',
                    'locale' => 'pt',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('user_roles')->insert([
                    'user_id' => $userId,
                    'role_id' => $managerRole->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('workers')->insert([
                    'id' => $workerId,
                    'user_id' => $userId,
                    'team_id' => $team->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $phoneBase++;
            }
        }
    }
}
