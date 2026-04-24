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
        ];

        $index = 0;
        foreach ($teams as $team) {
            for ($i = 0; $i < 3; $i++) {
                if ($index >= count($workerNames))
                    $index = 0;

                $name = $workerNames[$index];
                $counter = rand(1000, 9999);
                $email = strtolower($name['first_name']) . '.' . strtolower($name['last_name']) . $counter . '@workers.cm.pt';
                $phone = '+351' . (900000000 + ($index * 100000));

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

                $index++;
            }
        }
    }
}
