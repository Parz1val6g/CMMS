<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $sectorMap = DB::table('sectors')->pluck('id', 'name')->toArray();

        $obras       = $sectorMap['Departamento de Obras e Viação'] ?? null;
        $urbanismo   = $sectorMap['Departamento de Urbanismo'] ?? null;
        $agua        = $sectorMap['Departamento de Água e Saneamento'] ?? null;
        $limpeza     = $sectorMap['Departamento de Limpeza Urbana'] ?? null;

        $types = [
            // Obras e Viação
            ['sector' => $obras,     'name' => 'Pavimentação',           'description' => 'Reparação e manutenção de pavimentos rodoviários, incluindo tapamento de buracos e fresagem'],
            ['sector' => $obras,     'name' => 'Iluminação Pública',     'description' => 'Instalação, substituição e manutenção de luminárias, candeeiros e projetores de via pública'],
            ['sector' => $obras,     'name' => 'Calcetamento',           'description' => 'Reparação e substituição de calçada portuguesa em passeios, praças e arruamentos pedonais'],
            ['sector' => $obras,     'name' => 'Sinalização de Trânsito','description' => 'Instalação, manutenção e pintura de sinais verticais, horizontais e semáforos'],
            ['sector' => $obras,     'name' => 'Reparação de Edifícios', 'description' => 'Trabalhos de conservação, pintura e pequenas reparações em edifícios e instalações municipais'],
            ['sector' => $obras,     'name' => 'Mobiliário Urbano',      'description' => 'Manutenção e substituição de bancos de jardim, papeleiras, abrigos e outros equipamentos urbanos'],

            // Urbanismo
            ['sector' => $urbanismo, 'name' => 'Aprovação de Projetos',  'description' => 'Análise técnica, verificação de conformidade e emissão de pareceres para projetos de construção civil'],
            ['sector' => $urbanismo, 'name' => 'Vistoria de Imóveis',    'description' => 'Inspeção técnica de imóveis para efeitos de licenciamento, habitabilidade e segurança estrutural'],
            ['sector' => $urbanismo, 'name' => 'Emissão de Licenças',    'description' => 'Processamento, análise e emissão de licenças comerciais, industriais e de utilização do espaço público'],

            // Água e Saneamento
            ['sector' => $agua,      'name' => 'Abastecimento de Água',  'description' => 'Manutenção e reparação de condutas, ramais e acessórios da rede de distribuição de água'],
            ['sector' => $agua,      'name' => 'Saneamento Básico',      'description' => 'Limpeza, desobstrução e reparação de coletores, sarjetas e redes de drenagem de águas residuais'],
            ['sector' => $agua,      'name' => 'Drenagem Pluvial',       'description' => 'Limpeza e desobstrução de sumidouros, valetas, aquedutos e sistemas de drenagem de águas pluviais'],

            // Limpeza Urbana
            ['sector' => $limpeza,   'name' => 'Limpeza Urbana',         'description' => 'Varredura mecânica e manual, lavagem de ruas, recolha de resíduos e monos domésticos'],
            ['sector' => $limpeza,   'name' => 'Recolha de Resíduos',    'description' => 'Recolha seletiva e indiferenciada de resíduos sólidos urbanos e gestão de ecopontos'],
            ['sector' => $limpeza,   'name' => 'Manutenção de Jardins',  'description' => 'Poda de árvores, corte de relva, plantação de arbustos e manutenção de canteiros e espaços verdes'],
        ];

        foreach ($types as $t) {
            DB::table('service_types')->insert([
                'id'          => Str::uuid(),
                'sector_id'   => $t['sector'],
                'name'        => $t['name'],
                'description' => $t['description'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }
}
