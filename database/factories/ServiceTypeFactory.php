<?php

namespace Database\Factories;

use App\Features\ServiceTypes\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceType>
 */
class ServiceTypeFactory extends Factory
{
    protected $model = ServiceType::class;

    private const POOL = [
        'Pavimentação'           => 'Reparação e manutenção de pavimentos rodoviários, incluindo tapamento de buracos e fresagem',
        'Iluminação Pública'     => 'Instalação, substituição e manutenção de luminárias, candeeiros e projetores de via pública',
        'Abastecimento de Água'  => 'Manutenção e reparação de condutas, ramais e acessórios da rede de distribuição de água',
        'Saneamento Básico'      => 'Limpeza, desobstrução e reparação de coletores, sarjetas e redes de drenagem de águas residuais',
        'Limpeza Urbana'         => 'Varredura mecânica e manual, lavagem de ruas, recolha de resíduos e monos domésticos',
        'Recolha de Resíduos'    => 'Recolha seletiva e indiferenciada de resíduos sólidos urbanos e gestão de ecopontos',
        'Manutenção de Jardins'  => 'Poda de árvores, corte de relva, plantação de arbustos e manutenção de canteiros e espaços verdes',
        'Calcetamento'           => 'Reparação e substituição de calçada portuguesa em passeios, praças e arruamentos pedonais',
        'Sinalização de Trânsito'=> 'Instalação, manutenção e pintura de sinais verticais, horizontais e semáforos',
        'Aprovação de Projetos'  => 'Análise técnica, verificação de conformidade e emissão de pareceres para projetos de construção civil',
        'Vistoria de Imóveis'    => 'Inspeção técnica de imóveis para efeitos de licenciamento, habitabilidade e segurança estrutural',
        'Emissão de Licenças'    => 'Processamento, análise e emissão de licenças comerciais, industriais e de utilização do espaço público',
        'Mobiliário Urbano'      => 'Manutenção e substituição de bancos de jardim, papeleiras, abrigos e outros equipamentos urbanos',
        'Drenagem Pluvial'       => 'Limpeza e desobstrução de sumidouros, valetas, aquedutos e sistemas de drenagem de águas pluviais',
        'Reparação de Edifícios' => 'Trabalhos de conservação, pintura e pequenas reparações em edifícios e instalações municipais',
    ];

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(array_keys(self::POOL));

        return [
            'name'        => $name,
            'description' => self::POOL[$name],
        ];
    }
}
