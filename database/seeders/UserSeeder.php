<?php

namespace Database\Seeders;

use App\Shared\Models\User;
use App\Shared\Models\Role;
use App\Features\Workers\Models\Worker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles    = Role::pluck('id', 'name');
        $password = Hash::make(env('DEV_SEED_PASSWORD', 'password123'));

        $users = [
            // ── Superuser — todos os roles ──
            [
                'roles'      => array_keys($roles->toArray()),
                'first_name' => 'Rui',
                'last_name'  => 'Cabral',
                'phone'      => '+351900000000',
                'email'      => 'rui@splnet.pt',
                'password'   => Hash::make('rui_pwd_123'),
            ],
            // ── UC1 roles ──
            [
                'roles'      => ['admin'],
                'first_name' => 'João',
                'last_name'  => 'Almeida',
                'phone'      => '+351912345678',
                'email'      => 'joao.almeida@cm-mangualde.pt',
            ],
            [
                // Also head of Departamento de Urbanismo (SectorSeeder ref)
                'roles'      => ['manager', 'sector_manager'],
                'first_name' => 'Maria',
                'last_name'  => 'Pereira',
                'phone'      => '+351912345679',
                'email'      => 'maria.pereira@cm-mangualde.pt',
            ],
            [
                'roles'      => ['attendant'],
                'first_name' => 'Ana',
                'last_name'  => 'Lima',
                'phone'      => '+351912345680',
                'email'      => 'ana.lima@cm-mangualde.pt',
            ],
            [
                // Also head of Departamento de Água e Saneamento (SectorSeeder ref)
                'roles'      => ['task_manager', 'sector_manager'],
                'first_name' => 'Sofia',
                'last_name'  => 'Marques',
                'phone'      => '+351912345701',
                'email'      => 'sofia.marques@cm-mangualde.pt',
            ],
            [
                'roles'      => ['worker'],
                'first_name' => 'António',
                'last_name'  => 'Ferreira',
                'phone'      => '+351912345683',
                'email'      => 'antonio.ferreira@cm-mangualde.pt',
            ],
            [
                'roles'      => ['client'],
                'first_name' => 'Carlos',
                'last_name'  => 'Rodrigues',
                'phone'      => '+351912345684',
                'email'      => 'carlos.rodrigues@clientes.pt',
            ],
            // sector_manager for Departamento de Limpeza Urbana (SectorSeeder ref)
            [
                'roles'      => ['sector_manager'],
                'first_name' => 'Nuno',
                'last_name'  => 'Costa',
                'phone'      => '+351912345704',
                'email'      => 'nuno.costa@cm-mangualde.pt',
            ],
            // sector_manager for Departamento de Obras e Viação (SectorSeeder + TeamSeeder ref)
            [
                'roles'      => ['sector_manager'],
                'first_name' => 'Rui',
                'last_name'  => 'Gonçalves',
                'phone'      => '+351912345682',
                'email'      => 'rui.goncalves@cm-mangualde.pt',
            ],
            [
                'roles'      => ['team_manager'],
                'first_name' => 'Filipe',
                'last_name'  => 'Santos',
                'phone'      => '+351912345706',
                'email'      => 'filipe.santos@cm-mangualde.pt',
            ],
            // ── UC1 work log approval ──
            [
                'roles'      => ['work_log_manager'],
                'first_name' => 'Rita',
                'last_name'  => 'Silva',
                'phone'      => '+351912345707',
                'email'      => 'rita.silva@cm-mangualde.pt',
            ],
            // ── Non-UC1 feature roles ──
            [
                'roles'      => ['equipment_manager'],
                'first_name' => 'Pedro',
                'last_name'  => 'Carvalho',
                'phone'      => '+351912345700',
                'email'      => 'pedro.carvalho@cm-mangualde.pt',
            ],
            [
                'roles'      => ['ticket_manager'],
                'first_name' => 'Marco',
                'last_name'  => 'Lopes',
                'phone'      => '+351912345705',
                'email'      => 'marco.lopes@cm-mangualde.pt',
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'first_name' => $data['first_name'],
                    'last_name'  => $data['last_name'],
                    'phone'      => $data['phone'],
                    'password'   => $data['password'] ?? $password,
                    'status'     => 'active',
                ]
            );

            foreach ($data['roles'] as $roleName) {
                $roleId = $roles[$roleName] ?? null;
                if ($roleId && !$user->roles()->where('role_id', $roleId)->exists()) {
                    $user->roles()->attach($roleId);
                }
            }

            // Ensure António Ferreira (the E2E worker user) has a Worker record
            if ($data['email'] === 'antonio.ferreira@cm-mangualde.pt') {
                Worker::firstOrCreate(
                    ['user_id' => $user->id],
                    ['cost_per_hour' => 12.00]
                );
            }
        }
    }
}
