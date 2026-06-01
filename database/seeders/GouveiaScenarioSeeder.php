<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * Standalone demo seeder — not called from DatabaseSeeder.
 *
 * Seeds 20 service orders geographically concentrated in Mangualde,
 * covering a realistic priority distribution for a demo session.
 * Run manually: php artisan db:seed --class=GouveiaScenarioSeeder
 */
class GouveiaScenarioSeeder extends Seeder
{
    private const MANGUALDE_CENTER_LAT = 40.6033;
    private const MANGUALDE_CENTER_LNG = -7.7611;
    private const OFFSET_RANGE = 0.005;

    public function run(): void
    {
        $parish  = DB::table('parishes')->inRandomOrder()->first();
        $clients = DB::table('clients')->get();
        $managers = DB::table('users')
            ->whereIn('id', function ($q) {
                $q->select('user_id')->from('user_roles')
                    ->whereIn('role_id', function ($q2) {
                        $q2->select('id')->from('roles')->whereIn('name', ['admin', 'manager']);
                    });
            })->get();
        $attendants = DB::table('users')
            ->whereIn('id', function ($q) {
                $q->select('user_id')->from('user_roles')
                    ->whereIn('role_id', function ($q2) {
                        $q2->select('id')->from('roles')->where('name', 'attendant');
                    });
            })->get();
        $serviceTypes = DB::table('service_types')->get();

        if (!$parish || $clients->isEmpty() || $managers->isEmpty() || $serviceTypes->isEmpty()) {
            $this->command->warn('GouveiaScenarioSeeder: missing prerequisite data — skipping.');
            return;
        }

        $now         = now();
        $processYear = $now->format('Y');
        $attendant   = $attendants->first();

        $addresses = [
            'Praça do Município, 1',
            'Jardim Público',
            'Rua Direita, 25',
            'Bairro do Castelo, 10',
            'Avenida dos Combatentes, 42',
            'Rua Dr. António José de Almeida, 7',
            'Largo de São Pedro, 3',
            'Rua da Misericórdia, 15',
            'Avenida da Liberdade, 30',
            'Rua do Mercado, 8',
            'Rua de Santa Maria, 12',
            'Rua Nova, 20',
            'Bairro da Ponte, 5',
            'Rua da Fonte, 18',
            'Travessa do Rossio, 4',
            'Rua do Comércio, 22',
            'Largo do Município, 2',
            'Rua da Igreja, 9',
            'Bairro do Sobrado, 14',
            'Estrada Nacional 234, km 184',
        ];

        $landmarks = [
            'Edifício da Câmara Municipal de Mangualde',
            'Zona verde central',
            'Arteria principal do centro histórico',
            'Zona histórica junto ao castelo',
            'Rotunda da entrada da cidade',
            'Junto aos Paços do Concelho',
            'Igreja de São Pedro',
            'Antigo hospital municipal',
            'Saída para Viseu',
            'Praça do Mercado Municipal',
            'Zona residencial histórica',
            'Bairro novo',
            'Junto à ponte sobre a ribeira',
            'Largo da Fonte Velha',
            'Cruzamento central',
            'Zona comercial tradicional',
            'Frente aos Paços do Município',
            'Igreja Matriz de Mangualde',
            'Urbanização recente',
            'Via principal de acesso à cidade',
        ];

        // Priority distribution: 5 urgent, 10 normal, 5 low
        $priorities = array_merge(
            array_fill(0, 5, 'urgent'),
            array_fill(0, 10, 'normal'),
            array_fill(0, 5, 'low')
        );

        $urgentDescriptions = [
            'Rotura de conduta principal na Rua do Mercado com inundação da via pública',
            'Falha elétrica nos semáforos da rotunda da Avenida dos Combatentes',
            'Queda de árvore de grande porte sobre a via pública no Jardim Público',
            'Derrocada de talude junto ao Bairro do Castelo após chuvas intensas',
            'Inundação na Praça do Município por rotura de conduta de saneamento',
        ];

        $normalDescriptions = [
            'Manutenção de pavimentos na Avenida dos Combatentes',
            'Poda de árvores e arbustos no Jardim Público',
            'Reparação de calçada portuguesa na Rua Direita',
            'Substituição de luminárias fundidas na Avenida da Liberdade',
            'Limpeza de coletores pluviais no Bairro da Ponte',
            'Reparação de passeios danificados na Rua do Mercado',
            'Manutenção do sistema de rega automática do Jardim Público',
            'Pintura de passadeiras na Rua Nova com tinta termoplástica',
            'Substituição de lancis partidos na Travessa do Rossio',
            'Reparação de gradeamentos de proteção no Largo de São Pedro',
        ];

        $lowDescriptions = [
            'Pintura de sinalética vertical na zona histórica de Mangualde',
            'Limpeza de sarjetas entupidas na Rua da Fonte',
            'Substituição de tampas de saneamento partidas na Rua do Comércio',
            'Plantação de arbustos ornamentais no Bairro do Sobrado',
            'Remoção de monos e resíduos volumosos junto à Estrada Nacional 234',
        ];

        $allDescriptions = array_merge($urgentDescriptions, $normalDescriptions, $lowDescriptions);

        $locationIds = [];
        $counter     = 2000;

        foreach ($addresses as $i => $address) {
            $latOffset = (mt_rand(-1000, 1000) / 100000) * self::OFFSET_RANGE * 200;
            $lngOffset = (mt_rand(-1000, 1000) / 100000) * self::OFFSET_RANGE * 200;

            $id            = Str::uuid();
            $locationIds[] = $id;

            DB::table('locations')->insert([
                'id'             => $id,
                'parish_id'      => $parish->id,
                'postal_code'    => '3530-' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT),
                'street_address' => $address,
                'landmark'       => $landmarks[$i],
                'latitude'       => self::MANGUALDE_CENTER_LAT + $latOffset,
                'longitude'      => self::MANGUALDE_CENTER_LNG + $lngOffset,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }

        foreach ($addresses as $i => $address) {
            $client      = $clients->random();
            $manager     = $managers->random();
            $serviceType = $serviceTypes->random();
            $locationId  = $locationIds[$i];
            $priority    = $priorities[$i];
            $description = $allDescriptions[$i];

            $counter++;
            $process = sprintf('OS/%s/%04d', $processYear, $counter);

            $status = $priority === 'urgent' ? 'in_progress' : 'pending';

            DB::table('service_orders')->insert([
                'id'              => Str::uuid(),
                'process'         => $process,
                'client_id'       => $client->id,
                'manager_id'      => $manager->id,
                'created_by'      => $attendant?->id,
                'location_id'     => $locationId,
                'service_type_id' => $serviceType->id,
                'priority'        => $priority,
                'start_date'      => $now->toDateString(),
                'end_date'        => $now->copy()->addDays(30)->toDateString(),
                'status'          => $status,
                'description'     => $description,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }

        $this->command->info('✅ Mangualde scenario seeded: 20 locations + 20 service orders.');
    }
}
