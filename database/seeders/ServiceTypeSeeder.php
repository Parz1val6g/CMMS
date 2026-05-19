<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Pavimentação',        'description' => 'Reparação e manutenção de pavimentos rodoviários, incluindo tapamento de buracos e fresagem'],
            ['name' => 'Iluminação Pública',  'description' => 'Instalação, substituição e manutenção de luminárias, candeeiros e projetores de via pública'],
            ['name' => 'Abastecimento de Água', 'description' => 'Manutenção e reparação de condutas, ramais e acessórios da rede de distribuição de água'],
            ['name' => 'Saneamento Básico',    'description' => 'Limpeza, desobstrução e reparação de coletores, sarjetas e redes de drenagem de águas residuais'],
            ['name' => 'Limpeza Urbana',      'description' => 'Varredura mecânica e manual, lavagem de ruas, recolha de resíduos e monos domésticos'],
            ['name' => 'Recolha de Resíduos',  'description' => 'Recolha seletiva e indiferenciada de resíduos sólidos urbanos e gestão de ecopontos'],
            ['name' => 'Manutenção de Jardins', 'description' => 'Poda de árvores, corte de relva, plantação de arbustos e manutenção de canteiros e espaços verdes'],
            ['name' => 'Calcetamento',         'description' => 'Reparação e substituição de calçada portuguesa em passeios, praças e arruamentos pedonais'],
            ['name' => 'Sinalização de Trânsito', 'description' => 'Instalação, manutenção e pintura de sinais verticais, horizontais e semáforos'],
            ['name' => 'Aprovação de Projetos','description' => 'Análise técnica, verificação de conformidade e emissão de pareceres para projetos de construção civil'],
            ['name' => 'Vistoria de Imóveis',  'description' => 'Inspeção técnica de imóveis para efeitos de licenciamento, habitabilidade e segurança estrutural'],
            ['name' => 'Emissão de Licenças',  'description' => 'Processamento, análise e emissão de licenças comerciais, industriais e de utilização do espaço público'],
            ['name' => 'Mobiliário Urbano',     'description' => 'Manutenção e substituição de bancos de jardim, papeleiras, abrigos e outros equipamentos urbanos'],
            ['name' => 'Drenagem Pluvial',     'description' => 'Limpeza e desobstrução de sumidouros, valetas, aquedutos e sistemas de drenagem de águas pluviais'],
            ['name' => 'Reparação de Edifícios','description' => 'Trabalhos de conservação, pintura e pequenas reparações em edifícios e instalações municipais'],
        ];

        foreach ($types as $t) {
            DB::table('service_types')->insert([
                'id'          => Str::uuid(),
                'name'        => $t['name'],
                'description' => $t['description'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }
}
