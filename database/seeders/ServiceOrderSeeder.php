<?php

namespace Database\Seeders;

use App\Core\Enums\ServiceOrderStatus;
use App\Core\Enums\ServicesOrdersPriority;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ServiceOrderSeeder extends Seeder
{
    public function run(): void
    {
        $clients = DB::table('clients')->get();
        $managers = DB::table('users')
            ->whereIn('id', function ($q) {
                $q->select('user_id')->from('user_roles')
                    ->whereIn('role_id', function ($q2) {
                        $q2->select('id')->from('roles')->whereIn('name', ['admin', 'manager']);
                    });
            })->get();
        $locations = DB::table('locations')->get();
        $serviceTypes = DB::table('service_types')->get();

        if ($clients->isEmpty() || $managers->isEmpty() || $locations->isEmpty() || $serviceTypes->isEmpty()) {
            return;
        }

        $statuses = ServiceOrderStatus::cases();
        $priorities = ServicesOrdersPriority::cases();

        $serviceDescriptions = [
            'Pavimentação' => [
                'Reparação de piso danificado na via pública',
                'Aplicação de nova camada de asfalto na estrada municipal',
                'Correção de buracos e fissuras no pavimento',
                'Remodelação de passeios e lancis',
                'Pavimentação de arruamento em zona urbana',
            ],
            'Iluminação Pública' => [
                'Substituição de luminárias fundidas na Av. Principal',
                'Instalação de postes de iluminação no parque infantil',
                'Reparação de quadro elétrico de iluminação pública',
                'Manutenção preventiva da rede de iluminação',
                'Substituição de cabos danificados na Rua das Flores',
            ],
            'Abastecimento de Água' => [
                'Reparação de rotura na conduta de água',
                'Substituição de válvula de corte na Rua Nova',
                'Instalação de novo ramal de ligação',
                'Manutenção da estação elevatória',
                'Reparação de fuga de água detectada',
            ],
            'Saneamento' => [
                'Limpeza de coletor entupido',
                'Reparação de caixa de visita danificada',
                'Substituição de tubagem de saneamento',
                'Desobstrução de ramal de esgotos',
                'Vistoria a rede de drenagem',
            ],
            'Limpeza Urbana' => [
                'Limpeza extraordinária de via pública',
                'Remoção de resíduos volumosos',
                'Lavagem de contentores de lixo',
                'Limpeza de terreno baldio',
                'Higienização de contentores enterrados',
            ],
            'Gestão de Resíduos' => [
                'Recolha seletiva de resíduos na zona histórica',
                'Instalação de novos ecopontos',
                'Substituição de contentores danificados',
                'Operação de remoção de monos',
                'Manutenção do centro de triagem',
            ],
            'Manutenção de Jardins' => [
                'Podas de árvores na Alameda Central',
                'Plantação de espécies autóctones no jardim municipal',
                'Manutenção de relvados e canteiros',
                'Instalação de sistema de rega automática',
                'Controlo de pragas no parque urbano',
            ],
            'Reparação de Calçadas' => [
                'Reparação de calçada portuguesa na Praça Municipal',
                'Substituição de lajetas partidas no passeio',
                'Reconstrução de lancil danificado',
                'Nivelamento de pavimento em pedra',
                'Remodelação de acesso pedonal',
            ],
            'Sinalização de Trânsito' => [
                'Colocação de sinais de trânsito na nova rotunda',
                'Substituição de sinal vertical danificado',
                'Pintura de passadeiras na Av. Central',
                'Instalação de sinalização temporária para obras',
                'Reparação de semáforo avariado',
            ],
            'Aprovação de Projetos' => [
                'Análise de projeto de construção civil',
                'Parecer técnico para alteração de fachada',
                'Vistoria para licenciamento de obra',
                'Avaliação de impacto urbanístico',
                'Emissão de parecer para loteamento',
            ],
            'Vistoria de Imóveis' => [
                'Inspeção técnica a edifício municipal',
                'Vistoria para licença de habitação',
                'Avaliação de danos estruturais',
                'Inspeção periódica a equipamentos públicos',
                'Vistoria pré-compra de imóvel',
            ],
            'Emissão de Licenças' => [
                'Processamento de licença comercial',
                'Renovação de licença de ocupação de via pública',
                'Emissão de alvará de construção',
                'Licenciamento de evento público',
                'Autorização para esplanada',
            ],
        ];

        $processCounters = [];
        $now = now();
        $threeMonthsAgo = (clone $now)->modify('-3 months');

        for ($i = 0; $i < 55; $i++) {
            $client = $clients->random();
            $manager = $managers->random();
            $location = $locations->random();
            $serviceType = $serviceTypes->random();

            $year = $now->format('Y');
            if (!isset($processCounters[$year])) {
                $processCounters[$year] = 1000;
            }

            $executionDate = fake()->dateTimeBetween($threeMonthsAgo, $now);

            // Distribute statuses: ~20% completed, ~30% in_progress, ~30% pending, ~20% cancelled
            $statusRoll = rand(1, 100);
            $status = match (true) {
                $statusRoll <= 20 => ServiceOrderStatus::COMPLETED->value,
                $statusRoll <= 50 => ServiceOrderStatus::IN_PROGRESS->value,
                $statusRoll <= 80 => ServiceOrderStatus::PENDING->value,
                default => ServiceOrderStatus::CANCELLED->value,
            };

            // ~15% urgent, ~25% high, ~40% normal, ~20% low
            $priorityRoll = rand(1, 100);
            $priority = match (true) {
                $priorityRoll <= 15 => ServicesOrdersPriority::URGENT->value,
                $priorityRoll <= 40 => ServicesOrdersPriority::HIGH->value,
                $priorityRoll <= 80 => ServicesOrdersPriority::NORMAL->value,
                default => ServicesOrdersPriority::LOW->value,
            };

            // Make some critical: urgent + overdue (execution_date in the past + still pending/in_progress)
            if ($i < 5 && $status !== ServiceOrderStatus::COMPLETED->value && $status !== ServiceOrderStatus::CANCELLED->value) {
                $priority = ServicesOrdersPriority::URGENT->value;
                $executionDate = (clone $threeMonthsAgo)->modify('+' . rand(1, 30) . ' days');
            }

            $counter = $processCounters[$year]++;
            $typeName = $serviceType->name ?? 'Serviço';
            $descriptions = $serviceDescriptions[$typeName] ?? ['Execução de serviço municipal'];
            $description = $descriptions[array_rand($descriptions)];

            // Check if we have a specific column for description - looking at migration, there isn't.
            // But the model has process field. We'll use process as a meaningful identifier.
            $process = sprintf('%s/%d/%04d', strtoupper(substr(str_replace(['ç', 'ã', 'á', 'é', 'í', 'ó', 'ú', 'ê', 'ô'], ['c', 'a', 'a', 'e', 'i', 'o', 'u', 'e', 'o'], $typeName), 0, 3)), $year, $counter);
            // Generate a unique process
            $process = 'OS/' . $year . '/' . str_pad($counter, 4, '0', STR_PAD_LEFT);

            $id = Str::uuid();
            DB::table('service_orders')->insert([
                'id' => $id,
                'process' => $process,
                'client_id' => $client->id,
                'manager_id' => $manager->id,
                'location_id' => $location->id,
                'service_type_id' => $serviceType->id,
                'priority' => $priority,
                'execution_date' => $executionDate,
                'status' => $status,
                'created_at' => $executionDate,
                'updated_at' => $executionDate,
            ]);
        }
    }
}
