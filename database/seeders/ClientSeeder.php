<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            ['name' => 'Construções Silva Lda', 'nif' => '500123456'],
            ['name' => 'Engenharia e Obras Lda', 'nif' => '500234567'],
            ['name' => 'Empresa de Limpeza Urbana', 'nif' => '500345678'],
            ['name' => 'Instalações Elétricas Costa', 'nif' => '500456789'],
            ['name' => 'Fornecedor de Materiais', 'nif' => '500567890'],
            ['name' => 'Serviços de Jardinagem', 'nif' => '500678901'],
            ['name' => 'Reparações Gerais Lda', 'nif' => '500789012'],
            ['name' => 'Consultoria Técnica', 'nif' => '500890123'],
        ];

        foreach ($clients as $client) {
            $email = strtolower(str_replace(' ', '.', $client['name'])) . '@providers.pt';
            $phone = '+351' . (910000000 + rand(0, 999999));

            $userId = Str::uuid();

            DB::table('users')->insert([
                'id' => $userId,
                'first_name' => $client['name'],
                'last_name' => 'Fornecedor',
                'phone' => $phone,
                'email' => $email,
                'password' => Hash::make('password123'),
                'status' => 'active',
                'locale' => 'pt',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('clients')->insert([
                'id' => Str::uuid(),
                'user_id' => $userId,
                'nif' => $client['nif'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
