<?php

namespace Database\Seeders;

use App\Shared\Models\User;
use App\Shared\Models\Role;
use App\Features\Clients\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clientRole = Role::where('name', 'client')->firstOrFail();

        $clients = [
            ['company' => 'Construções Silva Lda',              'contact' => 'Carlos Silva',       'nif' => '500123456'],
            ['company' => 'Engenharia e Obras Lda',             'contact' => 'Ana Martins',        'nif' => '500234567'],
            ['company' => 'Empresa de Limpeza Urbana',          'contact' => 'José Ferreira',      'nif' => '500345678'],
            ['company' => 'Instalações Elétricas Costa',        'contact' => 'Rui Costa',          'nif' => '500456789'],
            ['company' => 'Fornecedor de Materiais Lda',        'contact' => 'Pedro Santos',       'nif' => '500567890'],
            ['company' => 'Serviços de Jardinagem Lda',         'contact' => 'Mário Oliveira',     'nif' => '500678901'],
            ['company' => 'Reparações Gerais Lda',              'contact' => 'Luísa Mendes',       'nif' => '500789012'],
            ['company' => 'Consultoria Técnica Lda',            'contact' => 'Fernando Pereira',   'nif' => '500890123'],
            ['company' => 'Pinturas e Decorações Mendes',       'contact' => 'Ricardo Mendes',     'nif' => '500901234'],
            ['company' => 'Canalizações Rodrigues Unipessoal',  'contact' => 'António Rodrigues',  'nif' => '501012345'],
            ['company' => 'Climatização e AVAC Lda',            'contact' => 'Miguel Lopes',       'nif' => '501123456'],
            ['company' => 'Telhados e Estruturas Lda',          'contact' => 'Sandra Silva',       'nif' => '501234567'],
            ['company' => 'Segurança e Vigilância Lda',         'contact' => 'Paulo Teixeira',     'nif' => '501345678'],
            ['company' => 'Elevadores Lda',                     'contact' => 'Mariana Costa',      'nif' => '501456789'],
            ['company' => 'Estucadores e Gesseiros Lda',        'contact' => 'Hélder Gomes',       'nif' => '501567890'],
        ];

        foreach ($clients as $c) {
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '.', $c['company']));
            $email = $slug . '@empresas.pt';

            $nameParts = explode(' ', $c['contact']);
            $firstName = $nameParts[0];
            $lastName  = implode(' ', array_slice($nameParts, 1));

            $user = User::create([
                'first_name' => $firstName,
                'last_name'  => $lastName ?: '.',
                'phone'      => '+351' . (910000000 + random_int(0, 59999999)),
                'email'      => $email,
                'password'   => Hash::make(env('DEV_SEED_PASSWORD', 'password123')),
                'status'     => 'active',
            ]);
            $user->roles()->attach($clientRole->id);

            Client::create([
                'user_id' => $user->id,
                'nif'     => $c['nif'],
            ]);
        }
    }
}
