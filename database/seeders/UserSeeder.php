<?php

namespace Database\Seeders;

use App\Shared\Models\User;
use App\Shared\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Create exactly ONE user per role for testing.
     * Password defaults to 'password123'; override with DEV_SEED_PASSWORD in .env.
     */
    public function run(): void
    {
        $roles = Role::pluck('id', 'name');

        $users = [
            'admin'             => ['first_name' => 'João',    'last_name' => 'Silva',     'phone' => '+351912345678', 'email' => 'admin@cm.pt'],
            'manager'           => ['first_name' => 'Maria',   'last_name' => 'Santos',    'phone' => '+351912345679', 'email' => 'maria.santos@cm.pt'],
            'equipment_manager' => ['first_name' => 'Pedro',   'last_name' => 'Equipamentos','phone'=> '+351912345700','email' => 'pedro.equipamentos@cm.pt'],
            'supervisor'        => ['first_name' => 'Rui',     'last_name' => 'Supervisor','phone' => '+351912345682', 'email' => 'rui.supervisor@cm.pt'],
            'worker'            => ['first_name' => 'António', 'last_name' => 'Operário',  'phone' => '+351912345683', 'email' => 'antonio.worker@cm.pt'],
            'client'            => ['first_name' => 'Carlos',  'last_name' => 'Cliente',   'phone' => '+351912345684', 'email' => 'carlos.cliente@clientes.pt'],
        ];

        foreach ($users as $roleName => $data) {
            $roleId = $roles[$roleName] ?? null;
            if (!$roleId) {
                continue;
            }

            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'phone'      => $data['phone'],
                'email'      => $data['email'],
                'password'   => Hash::make(env('DEV_SEED_PASSWORD', 'password123')),
                'status'     => 'active',
            ]);

            $user->roles()->attach($roleId);
        }
    }
}
