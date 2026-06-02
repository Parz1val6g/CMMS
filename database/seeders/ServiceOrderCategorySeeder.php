<?php

namespace Database\Seeders;

use App\Features\ServiceOrderCategories\Models\ServiceOrderCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ServiceOrderCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Reparação',    'description' => 'Conserto de infraestruturas, equipamentos ou instalações'],
            ['name' => 'Manutenção',   'description' => 'Manutenção preventiva ou corretiva programada'],
            ['name' => 'Instalação',   'description' => 'Instalação de novos equipamentos ou infraestruturas'],
            ['name' => 'Evento',       'description' => 'Apoio logístico e operacional a eventos'],
            ['name' => 'Inspeção',     'description' => 'Vistoria, fiscalização ou auditoria técnica'],
            ['name' => 'Limpeza',      'description' => 'Serviços de limpeza urbana ou de instalações'],
            ['name' => 'Obra',         'description' => 'Trabalhos de construção civil ou beneficiação'],
            ['name' => 'Emergência',   'description' => 'Resposta a situações urgentes ou imprevistas'],
        ];

        foreach ($categories as $data) {
            ServiceOrderCategory::firstOrCreate(
                ['name' => $data['name']],
                array_merge($data, ['id' => Str::uuid()])
            );
        }
    }
}
