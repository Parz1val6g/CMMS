<?php

namespace Database\Seeders;

use App\Shared\Models\User;
use App\Shared\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::pluck('id', 'name');

        $users = [
            'admin'             => ['first_name' => 'João',     'last_name' => 'Almeida',     'phone' => '+351912345678', 'email' => 'joao.almeida@cm-mangualde.pt'],
            'manager'           => ['first_name' => 'Maria',    'last_name' => 'Pereira',     'phone' => '+351912345679', 'email' => 'maria.pereira@cm-mangualde.pt'],
            'equipment_manager' => ['first_name' => 'Pedro',    'last_name' => 'Carvalho',    'phone' => '+351912345700', 'email' => 'pedro.carvalho@cm-mangualde.pt'],
            'supervisor'        => ['first_name' => 'Rui',      'last_name' => 'Gonçalves',   'phone' => '+351912345682', 'email' => 'rui.goncalves@cm-mangualde.pt'],
            'worker'            => ['first_name' => 'António',  'last_name' => 'Ferreira',    'phone' => '+351912345683', 'email' => 'antonio.ferreira@cm-mangualde.pt'],
            'client'            => ['first_name' => 'Carlos',   'last_name' => 'Rodrigues',   'phone' => '+351912345684', 'email' => 'carlos.rodrigues@clientes.pt'],
            'task_manager'      => ['first_name' => 'Sofia',    'last_name' => 'Marques',     'phone' => '+351912345701', 'email' => 'sofia.marques@cm-mangualde.pt'],
            'mini_task_manager' => ['first_name' => 'Hugo',     'last_name' => 'Ribeiro',     'phone' => '+351912345702', 'email' => 'hugo.ribeiro@cm-mangualde.pt'],
            'work_log_manager'  => ['first_name' => 'Inês',     'last_name' => 'Teixeira',    'phone' => '+351912345703', 'email' => 'ines.teixeira@cm-mangualde.pt'],
            'sector_manager'    => ['first_name' => 'Nuno',     'last_name' => 'Costa',       'phone' => '+351912345704', 'email' => 'nuno.costa@cm-mangualde.pt'],
            'ticket_manager'    => ['first_name' => 'Marco',    'last_name' => 'Lopes',       'phone' => '+351912345705', 'email' => 'marco.lopes@cm-mangualde.pt'],
        ];

        $password = Hash::make(env('DEV_SEED_PASSWORD', 'password123'));

        foreach ($users as $roleName => $data) {
            $roleId = $roles[$roleName] ?? null;
            if (!$roleId) continue;

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'first_name' => $data['first_name'],
                    'last_name'  => $data['last_name'],
                    'phone'      => $data['phone'],
                    'password'   => $password,
                    'status'     => 'active',
                ]
            );

            if (!$user->roles()->where('role_id', $roleId)->exists()) {
                $user->roles()->attach($roleId);
            }
        }
    }
}
