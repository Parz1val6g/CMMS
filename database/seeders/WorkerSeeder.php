<?php

namespace Database\Seeders;

use App\Shared\Models\User;
use App\Shared\Models\Role;
use App\Features\Workers\Models\Worker;
use App\Features\Teams\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WorkerSeeder extends Seeder
{
    public function run(): void
    {
        $workerRole = Role::where('name', 'worker')->firstOrFail();
        $teams      = Team::all();

        if ($teams->isEmpty()) return;

        $workers = [
            ['first_name' => 'Manuel',    'last_name' => 'Oliveira',     'cost' => 12.50],
            ['first_name' => 'Francisco', 'last_name' => 'Silva',        'cost' => 14.00],
            ['first_name' => 'José',      'last_name' => 'Santos',       'cost' => 11.50],
            ['first_name' => 'Joaquim',   'last_name' => 'Fernandes',    'cost' => 15.00],
            ['first_name' => 'Fernando',  'last_name' => 'Pinto',        'cost' => 13.00],
            ['first_name' => 'Américo',   'last_name' => 'Neves',        'cost' => 12.00],
            ['first_name' => 'Augusto',   'last_name' => 'Mendes',       'cost' => 16.00],
            ['first_name' => 'Carlos',    'last_name' => 'Coelho',       'cost' => 14.50],
            ['first_name' => 'Domingos',  'last_name' => 'Simões',       'cost' => 13.50],
            ['first_name' => 'Vítor',     'last_name' => 'Cardoso',      'cost' => 11.00],
            ['first_name' => 'Rogério',   'last_name' => 'Azevedo',      'cost' => 15.50],
            ['first_name' => 'Adelino',   'last_name' => 'Magalhães',    'cost' => 14.00],
            ['first_name' => 'Alcino',    'last_name' => 'Barbosa',      'cost' => 12.00],
            ['first_name' => 'Armindo',   'last_name' => 'Fonseca',      'cost' => 16.50],
            ['first_name' => 'Jorge',     'last_name' => 'Matos',        'cost' => 13.00],
            ['first_name' => 'Horácio',   'last_name' => 'Araújo',       'cost' => 14.50],
            ['first_name' => 'Aníbal',    'last_name' => 'Nunes',        'cost' => 15.00],
            ['first_name' => 'Celestino', 'last_name' => 'Guerra',       'cost' => 12.50],
            ['first_name' => 'Duarte',    'last_name' => 'Macedo',       'cost' => 13.50],
            ['first_name' => 'Ernesto',   'last_name' => 'Cruz',         'cost' => 11.00],
        ];

        $idx = 0;
        foreach ($teams as $team) {
            for ($i = 0; $i < 3 && $idx < count($workers); $i++, $idx++) {
                $w = $workers[$idx];
                $email = strtolower($w['first_name']) . '.' . strtolower($w['last_name'])
                       . '@cm-mangualde.pt';

                $user = User::create([
                    'first_name' => $w['first_name'],
                    'last_name'  => $w['last_name'],
                    'phone'      => '+351' . (920000000 + $idx),
                    'email'      => $email,
                    'password'   => Hash::make(env('DEV_SEED_PASSWORD', 'password123')),
                    'status'     => 'active',
                ]);
                $user->roles()->attach($workerRole->id);

                Worker::create([
                    'user_id'       => $user->id,
                    'team_id'       => $team->id,
                    'cost_per_hour' => $w['cost'],
                ]);
            }
        }
    }
}
