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
        $adminRole   = Role::where('name', 'admin')->firstOrFail();
        $managerRole = Role::where('name', 'manager')->firstOrFail();

        // ── Admin (test login) ──
        $admin = User::create([
            'first_name' => 'João',
            'last_name'  => 'Silva',
            'phone'      => '+351912345678',
            'email'      => 'admin@cm.pt',
            'password'   => Hash::make('password123'),
            'status'     => 'active',
        ]);
        $admin->roles()->attach($adminRole->id);

        // ── Managers ──
        $managers = [
            ['first_name' => 'Maria',    'last_name' => 'Santos',   'phone' => '+351912345679', 'email' => 'maria.santos@cm.pt'],
            ['first_name' => 'Carlos',   'last_name' => 'Oliveira', 'phone' => '+351912345680', 'email' => 'carlos.oliveira@cm.pt'],
            ['first_name' => 'Fernanda', 'last_name' => 'Pereira',  'phone' => '+351912345681', 'email' => 'fernanda.pereira@cm.pt'],
        ];

        foreach ($managers as $data) {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'],
                'phone'      => $data['phone'],
                'email'      => $data['email'],
                'password'   => Hash::make('password123'),
                'status'     => 'active',
            ]);
            $user->roles()->attach($managerRole->id);
        }
    }
}
