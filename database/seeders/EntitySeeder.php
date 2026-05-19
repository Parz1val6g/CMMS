<?php

namespace Database\Seeders;

use App\Core\Enums\EntityType;
use App\Features\Entities\Models\Entity;
use App\Shared\Models\Role;
use App\Shared\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EntitySeeder extends Seeder
{
    /**
     * Create entities covering all EntityType values with realistic
     * Portuguese municipal and parish council data.
     */
    public function run(): void
    {
        $entidadeRole = Role::where('name', 'entidade')->firstOrFail();
        $parishes     = DB::table('parishes')->pluck('id');

        $entities = [
            // ── Municipal Councils ──
            [
                'entity_type' => EntityType::MUNICIPAL_COUNCIL,
                'name'        => 'Câmara Municipal de Mangualde',
                'nif'         => '506823410',
                'phone'       => '+351232610000',
                'email'       => 'geral@cm-mangualde.pt',
                'first_name'  => 'Marco',
                'last_name'   => 'Almeida',
            ],
            [
                'entity_type' => EntityType::MUNICIPAL_COUNCIL,
                'name'        => 'Câmara Municipal de Viseu',
                'nif'         => '506839813',
                'phone'       => '+351232427427',
                'email'       => 'geral@cm-viseu.pt',
                'first_name'  => 'Fernando',
                'last_name'   => 'Ruas',
            ],
            [
                'entity_type' => EntityType::MUNICIPAL_COUNCIL,
                'name'        => 'Câmara Municipal de Gouveia',
                'nif'         => '506755120',
                'phone'       => '+351238490210',
                'email'       => 'geral@cm-gouveia.pt',
                'first_name'  => 'Luís',
                'last_name'   => 'Tadeu',
            ],
            [
                'entity_type' => EntityType::MUNICIPAL_COUNCIL,
                'name'        => 'Câmara Municipal de Nelas',
                'nif'         => '506747810',
                'phone'       => '+351232944010',
                'email'       => 'geral@cm-nelas.pt',
                'first_name'  => 'Joaquim',
                'last_name'   => 'Amaral',
            ],
            // ── Parish Councils ──
            [
                'entity_type' => EntityType::PARISH_COUNCIL,
                'name'        => 'Junta de Freguesia de Mangualde',
                'nif'         => '506856212',
                'phone'       => '+351232613540',
                'email'       => 'jf-mangualde@freguesias.pt',
                'first_name'  => 'António',
                'last_name'   => 'Figueiredo',
            ],
            [
                'entity_type' => EntityType::PARISH_COUNCIL,
                'name'        => 'Junta de Freguesia de Abrunhosa-a-Velha',
                'nif'         => '507015678',
                'phone'       => '+351232641200',
                'email'       => 'jf-abrunhosa@freguesias.pt',
                'first_name'  => 'Manuel',
                'last_name'   => 'Marques',
            ],
            [
                'entity_type' => EntityType::PARISH_COUNCIL,
                'name'        => 'Junta de Freguesia de Cunha Baixa',
                'nif'         => '507019203',
                'phone'       => '+351232645100',
                'email'       => 'jf-cunhabaixa@freguesias.pt',
                'first_name'  => 'José',
                'last_name'   => 'Pereira',
            ],
            [
                'entity_type' => EntityType::PARISH_COUNCIL,
                'name'        => 'Junta de Freguesia de Espinho',
                'nif'         => '507022560',
                'phone'       => '+351232649300',
                'email'       => 'jf-espinho@freguesias.pt',
                'first_name'  => 'Carlos',
                'last_name'   => 'Neves',
            ],
            // ── Other Entities ──
            [
                'entity_type' => EntityType::OTHER,
                'name'        => 'Associação Humanitária dos Bombeiros Voluntários de Mangualde',
                'nif'         => '501876543',
                'phone'       => '+351232612800',
                'email'       => 'comando@bv-mangualde.pt',
                'first_name'  => 'Rui',
                'last_name'   => 'Fonseca',
            ],
            [
                'entity_type' => EntityType::OTHER,
                'name'        => 'Santa Casa da Misericórdia de Mangualde',
                'nif'         => '500987654',
                'phone'       => '+351232618200',
                'email'       => 'provedor@scm-mangualde.pt',
                'first_name'  => 'Maria',
                'last_name'   => 'Costa',
            ],
        ];

        $password = Hash::make(env('DEV_SEED_PASSWORD', 'password123'));

        foreach ($entities as $data) {
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

            if (!$user->roles()->where('role_id', $entidadeRole->id)->exists()) {
                $user->roles()->attach($entidadeRole->id);
            }

            Entity::firstOrCreate(
                ['nif' => $data['nif']],
                [
                    'user_id'     => $user->id,
                    'entity_type' => $data['entity_type']->value,
                    'name'        => $data['name'],
                    'phone'       => $data['phone'],
                    'location_id' => $parishes->isNotEmpty() ? $parishes->random() : null,
                ]
            );
        }

        $this->command->info('✅ Entidades semeadas: ' . count($entities) . ' entidades');
    }
}
