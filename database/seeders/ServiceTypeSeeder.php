<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Pavimentação', 'description' => 'Trabalhos de pavimentação e reparação de vias'],
            ['name' => 'Iluminação Pública', 'description' => 'Instalação e manutenção de iluminação pública'],
            ['name' => 'Abastecimento de Água', 'description' => 'Manutenção de sistemas de abastecimento de água'],
            ['name' => 'Saneamento', 'description' => 'Trabalhos de saneamento e redes de esgotos'],
            ['name' => 'Limpeza Urbana', 'description' => 'Serviços de limpeza e higiene urbana'],
            ['name' => 'Gestão de Resíduos', 'description' => 'Coleta e gestão de resíduos municipais'],
            ['name' => 'Manutenção de Jardins', 'description' => 'Manutenção de áreas verdes e jardins públicos'],
            ['name' => 'Reparação de Calçadas', 'description' => 'Reparação de passeios e calcetamento'],
            ['name' => 'Sinalização de Trânsito', 'description' => 'Instalação e manutenção de sinais de trânsito'],
            ['name' => 'Aprovação de Projetos', 'description' => 'Análise e aprovação de projetos de construção'],
            ['name' => 'Vistoria de Imóveis', 'description' => 'Inspeção técnica de imóveis'],
            ['name' => 'Emissão de Licenças', 'description' => 'Processamento de licenças comerciais'],
            ['name' => 'Reparação de Mobiliário Urbano', 'description' => 'Manutenção de bancos, caixotes e outros móveis'],
            ['name' => 'Pintura de Fachadas', 'description' => 'Trabalhos de pintura em espaços públicos'],
            ['name' => 'Instalação de Sinalética', 'description' => 'Colocação de placas informativas e direcionais'],
        ];

        foreach ($services as $service) {
            DB::table('service_types')->insert([
                'id' => Str::uuid(),
                'name' => $service['name'],
                'description' => $service['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
