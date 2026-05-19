<?php

namespace Database\Seeders;

use App\Features\Clients\Models\Client;
use App\Features\Clients\Models\ClientLocation;
use App\Features\Locations\Models\Location;
use App\Shared\Models\Role;
use App\Shared\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clientRole = Role::where('name', 'client')->firstOrFail();
        $parishes   = DB::table('parishes')->pluck('id');

        if ($parishes->isEmpty()) {
            $this->command->warn('ClientSeeder: no parishes found — skipping client locations.');
        }

        $locationPairs = [
            [
                ['name' => 'Sede',     'street' => 'Rua da Paz nº 123',                'postal' => '3530-001', 'landmark' => 'Perto da Câmara Municipal',  'lat' => 40.6033, 'lon' => -7.7611],
                ['name' => 'Armazém',  'street' => 'Zona Industrial Lote 1',            'postal' => '3530-100', 'landmark' => 'Parque Industrial',          'lat' => 40.6300, 'lon' => -7.7850],
            ],
            [
                ['name' => 'Escritório','street' => 'Avenida da Europa nº 100',         'postal' => '3500-001', 'landmark' => 'Palácio do Gelo',             'lat' => 40.6610, 'lon' => -7.9090],
                ['name' => 'Filial',    'street' => 'Rua do Mercado nº 78',             'postal' => '3530-003', 'landmark' => 'Junto ao Mercado Municipal',  'lat' => 40.6020, 'lon' => -7.7600],
            ],
            [
                ['name' => 'Sede',     'street' => 'Avenida dos Combatentes nº 45',     'postal' => '3530-002', 'landmark' => 'Centro da Cidade',            'lat' => 40.6045, 'lon' => -7.7620],
                ['name' => 'Depósito',  'street' => 'Zona Industrial Lote 12',           'postal' => '3530-101', 'landmark' => 'Armazéns',                   'lat' => 40.6310, 'lon' => -7.7860],
            ],
            [
                ['name' => 'Sede',     'street' => 'Rua Dr. António José de Almeida nº 25', 'postal' => '3500-002', 'landmark' => 'Centro Histórico',      'lat' => 40.6600, 'lon' => -7.9100],
                ['name' => 'Oficina',  'street' => 'Estrada de Nelas nº 200',            'postal' => '3500-007', 'landmark' => 'Saída para Nelas',            'lat' => 40.6580, 'lon' => -7.9150],
            ],
            [
                ['name' => 'Escritório','street' => 'Praça da República nº 1',           'postal' => '3500-003', 'landmark' => 'Praça Central',               'lat' => 40.6620, 'lon' => -7.9080],
                ['name' => 'Armazém',   'street' => 'Rua da Estação nº 8',              'postal' => '3530-060', 'landmark' => 'Estação de Comboios',         'lat' => 40.6080, 'lon' => -7.7580],
            ],
            [
                ['name' => 'Sede',     'street' => 'Rua do Comércio nº 50',             'postal' => '3500-004', 'landmark' => 'Zona Comercial',              'lat' => 40.6630, 'lon' => -7.9110],
                ['name' => 'Filial',    'street' => 'Rua Principal nº 10',              'postal' => '3530-010', 'landmark' => 'Aldeia de Abrunhosa',         'lat' => 40.6000, 'lon' => -7.7700],
            ],
            [
                ['name' => 'Sede',     'street' => 'Avenida Alberto Sampaio nº 300',    'postal' => '3500-005', 'landmark' => 'Hospital Distrital',           'lat' => 40.6590, 'lon' => -7.9120],
                ['name' => 'Garagem',   'street' => 'Caminho do Moinho s/n',             'postal' => '3530-020', 'landmark' => 'Zona Industrial',             'lat' => 40.6100, 'lon' => -7.7750],
            ],
            [
                ['name' => 'Escritório','street' => 'Rua da Sé nº 15',                  'postal' => '3500-006', 'landmark' => 'Sé de Viseu',                 'lat' => 40.6640, 'lon' => -7.9070],
                ['name' => 'Depósito',  'street' => 'Rua da Fonte nº 5',                'postal' => '3530-030', 'landmark' => 'Largo da Fonte',              'lat' => 40.5950, 'lon' => -7.7800],
            ],
            [
                ['name' => 'Sede',     'street' => 'Rua do Parque nº 88',              'postal' => '3500-008', 'landmark' => 'Parque da Cidade',             'lat' => 40.6650, 'lon' => -7.9060],
                ['name' => 'Armazém',   'street' => 'Estrada Nacional nº 234',          'postal' => '3530-040', 'landmark' => 'Zona Rural',                  'lat' => 40.6200, 'lon' => -7.7900],
            ],
            [
                ['name' => 'Sede',     'street' => 'Avenida D. Duarte nº 120',         'postal' => '3500-009', 'landmark' => 'Zona Nobre',                  'lat' => 40.6660, 'lon' => -7.9140],
                ['name' => 'Oficina',   'street' => 'Rua do Souto nº 20',              'postal' => '3530-050', 'landmark' => 'Junto à Capela',              'lat' => 40.5900, 'lon' => -7.7650],
            ],
            [
                ['name' => 'Escritório','street' => 'Rua dos Loureiros nº 33',          'postal' => '3500-010', 'landmark' => 'Bairro Residencial',           'lat' => 40.6570, 'lon' => -7.9130],
                ['name' => 'Armazém',   'street' => 'Largo da Igreja Matriz',           'postal' => '3530-004', 'landmark' => 'Igreja Matriz',               'lat' => 40.6050, 'lon' => -7.7630],
            ],
            [
                ['name' => 'Sede',     'street' => 'Avenida dos Bombeiros nº 60',      'postal' => '3500-020', 'landmark' => 'Quartel dos Bombeiros',        'lat' => 40.6670, 'lon' => -7.9050],
                ['name' => 'Depósito',  'street' => 'Rua das Flores nº 12',            'postal' => '3530-005', 'landmark' => 'Zona Residencial',            'lat' => 40.6010, 'lon' => -7.7590],
            ],
            [
                ['name' => 'Sede',     'street' => 'Rua da Escola nº 15',              'postal' => '3500-030', 'landmark' => 'Escola Secundária',            'lat' => 40.6680, 'lon' => -7.9160],
                ['name' => 'Filial',    'street' => 'Rua da Paz nº 123',               'postal' => '3530-001', 'landmark' => 'Perto da Câmara Municipal',  'lat' => 40.6033, 'lon' => -7.7611],
            ],
            [
                ['name' => 'Escritório','street' => 'Rua do Comércio nº 50',            'postal' => '3500-004', 'landmark' => 'Zona Comercial',              'lat' => 40.6630, 'lon' => -7.9110],
                ['name' => 'Armazém',   'street' => 'Zona Industrial Lote 1',           'postal' => '3530-100', 'landmark' => 'Parque Industrial',          'lat' => 40.6300, 'lon' => -7.7850],
            ],
            [
                ['name' => 'Sede',     'street' => 'Avenida da Europa nº 100',          'postal' => '3500-001', 'landmark' => 'Palácio do Gelo',             'lat' => 40.6610, 'lon' => -7.9090],
                ['name' => 'Garagem',   'street' => 'Estrada de Nelas nº 200',          'postal' => '3500-007', 'landmark' => 'Saída para Nelas',            'lat' => 40.6580, 'lon' => -7.9150],
            ],
        ];

        $clients = [
            ['company' => 'Construções Silva',        'contact' => 'Carlos Silva',       'nif' => '500123456'],
            ['company' => 'Engenharia e Obras',       'contact' => 'Ana Martins',        'nif' => '500234567'],
            ['company' => 'Limpeza Urbana Lda',       'contact' => 'José Ferreira',      'nif' => '500345678'],
            ['company' => 'Instalações Elétricas Costa', 'contact' => 'Rui Costa',       'nif' => '500456789'],
            ['company' => 'Fornecedor de Materiais',  'contact' => 'Pedro Santos',       'nif' => '500567890'],
            ['company' => 'Serviços de Jardinagem',   'contact' => 'Mário Oliveira',     'nif' => '500678901'],
            ['company' => 'Reparações Gerais Lda',    'contact' => 'Luísa Mendes',       'nif' => '500789012'],
            ['company' => 'Consultoria Técnica',      'contact' => 'Fernando Pereira',   'nif' => '500890123'],
            ['company' => 'Pinturas e Decorações Mendes', 'contact' => 'Ricardo Mendes', 'nif' => '500901234'],
            ['company' => 'Canalizações Rodrigues',   'contact' => 'António Rodrigues',  'nif' => '501012345'],
            ['company' => 'Climatização e AVAC',      'contact' => 'Miguel Lopes',       'nif' => '501123456'],
            ['company' => 'Telhados e Coberturas',    'contact' => 'Sandra Silva',       'nif' => '501234567'],
            ['company' => 'Segurança e Vigilância',   'contact' => 'Paulo Teixeira',     'nif' => '501345678'],
            ['company' => 'Elevadores do Centro',     'contact' => 'Mariana Costa',      'nif' => '501456789'],
            ['company' => 'Estucadores e Gesseiros',  'contact' => 'Hélder Gomes',       'nif' => '501567890'],
        ];

        foreach ($clients as $i => $c) {
            $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '.', str_replace([' Lda',' Unipessoal'], '', $c['company'])));
            $email = $slug . '@empresas.pt';

            $nameParts = explode(' ', $c['contact']);
            $firstName = $nameParts[0];
            $lastName  = implode(' ', array_slice($nameParts, 1));

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'first_name' => $firstName,
                    'last_name'  => $lastName ?: '.',
                    'phone'      => '+351' . (960000000 + ($i * 1000)),
                    'password'   => Hash::make(env('DEV_SEED_PASSWORD', 'password123')),
                    'status'     => 'active',
                ]
            );

            if (!$user->roles()->where('role_id', $clientRole->id)->exists()) {
                $user->roles()->attach($clientRole->id);
            }

            $client = Client::firstOrCreate(
                ['user_id' => $user->id],
                ['nif' => $c['nif']]
            );

            if ($parishes->isNotEmpty() && $client->wasRecentlyCreated) {
                $pair = $locationPairs[$i] ?? $locationPairs[0];
                foreach ($pair as $j => $loc) {
                    $location = Location::create([
                        'parish_id'      => $parishes->random(),
                        'postal_code'    => $loc['postal'],
                        'street_address' => $loc['street'],
                        'landmark'       => $loc['landmark'],
                        'latitude'       => $loc['lat'],
                        'longitude'      => $loc['lon'],
                    ]);
                    ClientLocation::create([
                        'client_id'   => $client->id,
                        'location_id' => $location->id,
                        'name'        => $loc['name'],
                        'is_primary'  => $j === 0,
                    ]);
                }
            }
        }
    }
}
