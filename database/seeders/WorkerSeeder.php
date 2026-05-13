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

        if ($teams->isEmpty()) {
            return;
        }

        $workers = [
            ['first_name' => 'António',   'last_name' => 'Silva'],
            ['first_name' => 'Pedro',     'last_name' => 'Santos'],
            ['first_name' => 'João',      'last_name' => 'Oliveira'],
            ['first_name' => 'José',      'last_name' => 'Ferreira'],
            ['first_name' => 'Miguel',    'last_name' => 'Costa'],
            ['first_name' => 'Rosa',      'last_name' => 'Gonçalves'],
            ['first_name' => 'Rui',       'last_name' => 'Martins'],
            ['first_name' => 'Mariana',   'last_name' => 'Alves'],
            ['first_name' => 'Paulo',     'last_name' => 'Ribeiro'],
            ['first_name' => 'Conceição', 'last_name' => 'Teixeira'],
            ['first_name' => 'Luís',      'last_name' => 'Fonseca'],
            ['first_name' => 'Carla',     'last_name' => 'Neves'],
            ['first_name' => 'André',     'last_name' => 'Pinto'],
            ['first_name' => 'Teresa',    'last_name' => 'Araújo'],
            ['first_name' => 'Nuno',      'last_name' => 'Almeida'],
            ['first_name' => 'Sandra',    'last_name' => 'Rodrigues'],
            ['first_name' => 'Hélder',    'last_name' => 'Gomes'],
            ['first_name' => 'Patrícia',  'last_name' => 'Guimarães'],
            ['first_name' => 'Ricardo',   'last_name' => 'Vieira'],
            ['first_name' => 'Mónica',    'last_name' => 'Barbosa'],
        ];

        $idx = 0;
        foreach ($teams as $team) {
            for ($i = 0; $i < 3 && $idx < count($workers); $i++, $idx++) {
                $w = $workers[$idx];
                $email = strtolower($w['first_name']) . '.' . strtolower($w['last_name'])
                       . ($idx + 1000) . '@workers.cm.pt';

                $user = User::create([
                    'first_name' => $w['first_name'],
                    'last_name'  => $w['last_name'],
                    'phone'      => '+3519' . str_pad((string)(10000000 + $idx), 8, '0', STR_PAD_LEFT),
                    'email'      => $email,
                    'password'   => Hash::make(env('DEV_SEED_PASSWORD', 'password123')),
                    'status'     => 'active',
                ]);
                $user->roles()->attach($workerRole->id);

                Worker::create([
                    'user_id' => $user->id,
                    'team_id' => $team->id,
                    'cost_per_hour' => 15.00,
                ]);
            }
        }
    }
}
