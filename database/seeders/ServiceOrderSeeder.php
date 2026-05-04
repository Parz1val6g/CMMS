<?php

namespace Database\Seeders;

use App\Core\Enums\ServiceOrderStatus as SOStatus;
use App\Core\Enums\Priority;
use App\Features\ServiceOrders\Models\ServiceOrder;
use App\Features\Clients\Models\Client;
use App\Shared\Models\User;
use App\Features\Locations\Models\Location;
use App\Features\ServiceTypes\Models\ServiceType;
use Illuminate\Database\Seeder;

class ServiceOrderSeeder extends Seeder
{
    public function run(): void
    {
        $clients      = Client::all();
        $managers     = User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'manager']))->get();
        $locations    = Location::all();
        $serviceTypes = ServiceType::all();

        if ($clients->isEmpty() || $managers->isEmpty() || $locations->isEmpty() || $serviceTypes->isEmpty()) {
            return;
        }

        // Realistic service order descriptions grouped by service type
        $descriptions = [
            'Pavimentação' => [
                'Reparação de piso danificado na via pública com aplicação de nova camada de asfalto',
                'Remodelação de passeios e lancis na zona central com calcetamento tradicional',
                'Correção de buracos e fissuras no pavimento após período de chuvas intensas',
                'Pavimentação de arruamento em zona urbana com preparação de fundação e drenagem',
            ],
            'Iluminação Pública' => [
                'Substituição de luminárias fundidas na Av. Principal por LED de baixo consumo',
                'Instalação de postes de iluminação no parque infantil com sistema fotovoltaico',
                'Reparação de quadro elétrico de iluminação pública com substituição de disjuntores',
                'Manutenção preventiva da rede de iluminação incluindo limpeza de luminárias',
            ],
            'Abastecimento de Água' => [
                'Reparação de rotura na conduta principal de água com escavação e soldadura',
                'Substituição de válvula de corte na Rua Nova com instalação de ventosa',
                'Instalação de novo ramal de ligação domiciliária com contador individual',
                'Reparação de fuga de água detectada com reposição de pavimento',
            ],
            'Saneamento' => [
                'Limpeza de coletor entupido com recurso a camião de hidrojato',
                'Reparação de caixa de visita danificada com substituição de tampa e aro',
                'Substituição de tubagem de saneamento em troço de 50 metros',
                'Desobstrução de ramal de esgotos com remoção de raízes',
            ],
            'Limpeza Urbana' => [
                'Limpeza extraordinária de via pública após evento municipal com remoção de resíduos',
                'Remoção de resíduos volumosos e monos abandonados na via pública',
                'Lavagem de contentores de lixo com recurso a equipamento de hidrolimpeza',
                'Limpeza de terreno baldio com remoção de vegetação e resíduos',
            ],
            'Gestão de Resíduos' => [
                'Instalação de novos ecopontos para recolha seletiva na zona histórica',
                'Substituição de contentores subterrâneos danificados na praça central',
                'Operação de remoção de monos na freguesia com recurso a equipa dedicada',
            ],
            'Manutenção de Jardins' => [
                'Podas de árvores na Alameda Central com remoção de ramos secos',
                'Plantação de espécies autóctones no jardim municipal com sistema de rega',
                'Manutenção de relvados e canteiros com corte de erva e fertilização',
                'Controlo de pragas no parque urbano com tratamento fitossanitário',
            ],
            'Reparação de Calçadas' => [
                'Reparação de calçada portuguesa na Praça Municipal com reposição de cubos',
                'Substituição de lajetas partidas no passeio da Rua Direita',
                'Reconstrução de lancil danificado na zona do mercado municipal',
            ],
            'Sinalização de Trânsito' => [
                'Colocação de sinais de trânsito na nova rotunda de acesso à zona industrial',
                'Substituição de sinal vertical danificado após acidente na EN 234',
                'Pintura de passadeiras na Av. Central com tinta termoplástica',
                'Reparação de semáforo avariado no cruzamento da estação',
            ],
            'Aprovação de Projetos' => [
                'Análise de projeto de construção civil para edifício habitacional',
                'Parecer técnico para alteração de fachada em imóvel classificado',
                'Vistoria para licenciamento de obra de remodelação comercial',
            ],
            'Vistoria de Imóveis' => [
                'Inspeção técnica a edifício municipal para avaliação de segurança',
                'Vistoria para licença de habitação de novo empreendimento',
                'Avaliação de danos estruturais após sinistro em edifício',
            ],
            'Emissão de Licenças' => [
                'Processamento de licença comercial para novo estabelecimento',
                'Renovação de licença de ocupação de via pública para esplanada',
                'Emissão de alvará de construção para obra particular',
            ],
        ];

        $now = now();
        $threeMonthsAgo = (clone $now)->modify('-3 months');
        $counter = 0;

        for ($i = 0; $i < 40; $i++) {
            $client     = $clients->random();
            $manager    = $managers->random();
            $location   = $locations->random();
            $serviceType = $serviceTypes->random();
            $counter++;

            $createdAt = fake()->dateTimeBetween($threeMonthsAgo, $now);
            // Avoid DST spring-forward gap (Europe/Lisbon: Mar last Sun 01:00-02:00)
            if ($createdAt->format('Y-m-d') === '2026-03-29' && $createdAt->format('H') === '01') {
                $createdAt->modify('+1 hour');
            }
            $executionDate = (clone $createdAt)->modify('+' . rand(1, 30) . ' days');

            // Status distribution: ~20% completed, ~35% in_progress, ~30% pending, ~15% cancelled
            $statusRoll = rand(1, 100);
            $status = match (true) {
                $statusRoll <= 20 => SOStatus::COMPLETED->value,
                $statusRoll <= 55 => SOStatus::IN_PROGRESS->value,
                $statusRoll <= 85 => SOStatus::PENDING->value,
                default           => SOStatus::CANCELLED->value,
            };

            // Priority: ~15% urgent, ~25% high, ~40% normal, ~20% low
            $priorityRoll = rand(1, 100);
            $priority = match (true) {
                $priorityRoll <= 15 => Priority::URGENT->value,
                $priorityRoll <= 40 => Priority::HIGH->value,
                $priorityRoll <= 80 => Priority::NORMAL->value,
                default             => Priority::LOW->value,
            };

            $typeName = $serviceType->name;
            $typeDesc = $descriptions[$typeName] ?? ['Execução de serviço municipal'];
            $description = $typeDesc[array_rand($typeDesc)];
            $process = 'OS/' . $createdAt->format('Y') . '/' . str_pad((string)$counter, 4, '0', STR_PAD_LEFT);

            // Urgent: force execution_date in the past + in_progress
            if ($priority === Priority::URGENT->value && $status === SOStatus::PENDING->value) {
                $status = SOStatus::IN_PROGRESS->value;
                $executionDate = (clone $threeMonthsAgo)->modify('+' . rand(1, 15) . ' days');
            }

            ServiceOrder::create([
                'process'         => $process,
                'client_id'       => $client->id,
                'manager_id'      => $manager->id,
                'location_id'     => $location->id,
                'service_type_id' => $serviceType->id,
                'priority'        => $priority,
                'execution_date'  => $executionDate,
                'status'          => $status,
                'created_at'      => $createdAt,
                'updated_at'      => $createdAt,
            ]);
        }
    }
}
