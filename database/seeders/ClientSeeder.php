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
            ['name' => 'Pinturas e Decorações Mendes', 'nif' => '500901234'],
            ['name' => 'Canalizações Rodrigues Unipessoal', 'nif' => '501012345'],
            ['name' => 'Climatização e AVAC Lda', 'nif' => '501123456'],
            ['name' => 'Telhados e Estruturas Lda', 'nif' => '501234567'],
            ['name' => 'Segurança e Vigilância Lda', 'nif' => '501345678'],
            ['name' => 'Elevadores Lda', 'nif' => '501456789'],
            ['name' => 'Estucadores e Gesseiros Lda', 'nif' => '501567890'],
        ];

        $now = now();

        foreach ($clients as $client) {
            $email = strtolower(
                str_replace(
                    [' ', 'ç', 'ã', 'á', 'à', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'ú', 'ü'],
                    ['.', 'c', 'a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'u', 'u'],
                    $client['name']
                )
            ) . '@empresas.pt';
            $phone = '+351' . (910000000 + random_int(0, 59999999));

            $userId = Str::uuid();

            DB::table('users')->insert([
                'id' => $userId,
                'first_name' => $client['name'],
                'last_name' => '',
                'phone' => $phone,
                'email' => $email,
                'password' => Hash::make('password123'),
                'status' => 'active',
                'locale' => 'pt',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('clients')->insert([
                'id' => Str::uuid(),
                'user_id' => $userId,
                'nif' => $client['nif'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
