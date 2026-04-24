<?php

namespace Database\Seeders;

use App\Core\Enums\SystemStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        $managerRole = DB::table('roles')->where('name', 'manager')->first();

        $users = [
            // Admin
            [
                'id' => Str::uuid(),
                'first_name' => 'João',
                'last_name' => 'Silva',
                'phone' => '+351912345678',
                'email' => 'admin@cm.pt',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'locale' => 'pt',
                'role_id' => $adminRole->id,
            ],
            // Managers (Chefes de Sector)
            [
                'id' => Str::uuid(),
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'phone' => '+351912345679',
                'email' => 'maria.santos@cm.pt',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'locale' => 'pt',
                'role_id' => $managerRole->id,
            ],
            [
                'id' => Str::uuid(),
                'first_name' => 'Carlos',
                'last_name' => 'Oliveira',
                'phone' => '+351912345680',
                'email' => 'carlos.oliveira@cm.pt',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'locale' => 'pt',
                'role_id' => $managerRole->id,
            ],
            [
                'id' => Str::uuid(),
                'first_name' => 'Fernanda',
                'last_name' => 'Pereira',
                'phone' => '+351912345681',
                'email' => 'fernanda.pereira@cm.pt',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'locale' => 'pt',
                'role_id' => $managerRole->id,
            ],
        ];

        foreach ($users as $user) {
            $roleId = $user['role_id'];
            unset($user['role_id']);

            $user['created_at'] = now();
            $user['updated_at'] = now();

            DB::table('users')->insert($user);
            DB::table('user_roles')->insert([
                'user_id' => $user['id'],
                'role_id' => $roleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
