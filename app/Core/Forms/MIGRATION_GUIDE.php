<?php

/**
 * GUIA DE MIGRAÇÃO: De Schemas Array para FormSchema Classes
 * 
 * Este arquivo documenta como migrar um PageController do sistema antigo
 * (schemas como arrays) para o novo sistema (FormSchema + FormField classes).
 * 
 * ===========================================================================
 * ANTES (Array-based) - Antigo Sistema
 * ===========================================================================
 */

// Arquivo: app/Features/Clients/Controllers/ClientPageController.php
// ANTIGO:

/*
class ClientPageController extends Controller {
    public function index() {
        $clients = Client::all();

        $formSchema = [
            ['key' => 'nif', 'label' => 'NIF', 'type' => 'text', 'rules' => 'required|max:20'],
            ['key' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'rules' => 'required|max:250'],
            ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'rules' => 'required|email'],
            ['key' => 'phone', 'label' => 'Phone', 'type' => 'text'],
        ];

        $createFormSchema = $formSchema;

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'formSchema' => $formSchema,
            'createFormSchema' => $createFormSchema,
        ]);
    }
}
*/

/**
 * ===========================================================================
 * DEPOIS (FormSchema Classes) - Novo Sistema
 * ===========================================================================
 */

// Passo 1: Criar uma classe ClientFormSchema
// Arquivo: app/Features/Clients/Schemas/ClientFormSchema.php

namespace App\Features\Clients\Schemas;

use App\Core\Forms\{FormSchema, TextInput, EmailInput};

class ClientFormSchema
{
    /**
     * Schema para form de CREATE (novo cliente)
     */
    public static function create(): FormSchema
    {
        return FormSchema::make('Novo Cliente')
            ->field(
                TextInput::make('nif')
                    ->setLabel('NIF')
                    ->setRequired()
                    ->setRules('required|max:20|unique:clients,nif')
            )
            ->field(
                TextInput::make('first_name')
                    ->setLabel('First Name')
                    ->setRequired()
                    ->setRules('required|max:250')
            )
            ->field(
                EmailInput::make('email')
                    ->setLabel('Email')
                    ->setRequired()
                    ->setRules('required|email|unique:clients,email')
            )
            ->field(
                TextInput::make('phone')
                    ->setLabel('Phone')
                    ->setPlaceholder('+351 910 000 000')
            );
    }

    /**
     * Schema para form de UPDATE (editar cliente)
     * 
     * Normalmente idêntico ao create(), mas com campos opcionais
     */
    public static function update(): FormSchema
    {
        return FormSchema::make('Editar Cliente')
            ->field(
                TextInput::make('nif')
                    ->setLabel('NIF')
                    ->setRules('max:20|unique:clients,nif')
                // Nota: sem required(), permitindo updates parciais
            )
            ->field(
                TextInput::make('first_name')
                    ->setLabel('First Name')
                    ->setRules('max:250')
            )
            ->field(
                EmailInput::make('email')
                    ->setLabel('Email')
                    ->setRules('email|unique:clients,email')
            )
            ->field(
                TextInput::make('phone')
                    ->setLabel('Phone')
                    ->setPlaceholder('+351 910 000 000')
            );
    }
}

// Passo 2: Atualizar PageController
// Arquivo: app/Features/Clients/Controllers/ClientPageController.php (NOVO)

namespace App\Features\Clients\Controllers;

use App\Features\Clients\Schemas\ClientFormSchema;
use Inertia\Inertia;
use App\Models\Client;

class ClientPageController extends Controller
{
    public function index()
    {
        $clients = Client::all();

        // Usar o novo sistema FormSchema
        $createSchema = ClientFormSchema::create();
        $updateSchema = ClientFormSchema::update();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'formSchema' => $updateSchema->toArray(),      // Para editar
            'createFormSchema' => $createSchema->toArray(), // Para criar
        ]);
    }
}

// Passo 3: Atualizar FormRequest
// Arquivo: app/Features/Clients/Requests/StoreClientRequest.php (NOVO)

namespace App\Features\Clients\Requests;

use App\Core\Forms\FormValidator;
use App\Features\Clients\Schemas\ClientFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Adicionar autorização real conforme necessário
    }

    public function rules(): array
    {
        $schema = ClientFormSchema::create();
        $validator = new FormValidator();

        // Gera regras automaticamente a partir do schema
        return $validator->fromSchema($schema, $this->all());
    }
}

// Alternativa: Usar trait genérico (futura simplificação)

namespace App\Features\Clients\Requests;

use App\Core\Forms\Traits\WithFormSchema;
use App\Features\Clients\Schemas\ClientFormSchema;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    use WithFormSchema;

    protected function schema()
    {
        return ClientFormSchema::create();
    }

    public function authorize(): bool
    {
        return true;
    }
}

/**
 * ===========================================================================
 * COMPARATIVO: ANTES vs DEPOIS
 * ===========================================================================
 * 
 * ANTES (Array):
 * - ❌ Sem validação de tipos de campo
 * - ❌ Sem condicionalidade
 * - ❌ Sem metadados customizados
 * - ❌ Validação repetida em FormRequest
 * - ✅ Compacto
 * 
 * DEPOIS (FormSchema):
 * - ✅ Type-safe (FormField classes)
 * - ✅ Condicionalidade integrada (when())
 * - ✅ Metadados extensíveis (meta())
 * - ✅ Validação centralizada (FormValidator extrai de schema)
 * - ✅ Reusabilidade (create() vs update())
 * - ✅ Documentação automática (Docblocks)
 * - ⚠️ Mais código inicialmente (mas vantagens a longo prazo)
 * 
 * ===========================================================================
 * CHECKLIST DE MIGRAÇÃO (Por Feature)
 * ===========================================================================
 * 
 * [ ] 1. Criar Schemas/FeatureFormSchema.php com create() e update()
 * [ ] 2. Atualizar PageController:
 *       - Substituir formSchema array por FeatureFormSchema::update()->toArray()
 *       - Substituir createFormSchema array por FeatureFormSchema::create()->toArray()
 * [ ] 3. Atualizar StoreFeatureRequest:
 *       - Gerar rules() com FormValidator::fromSchema()
 * [ ] 4. Atualizar UpdateFeatureRequest idem
 * [ ] 5. Testar create/update funciona sem erros
 * [ ] 6. Verificar validação funciona (frontend + backend)
 * [ ] 7. Commit e documentar mudanças
 * 
 * ===========================================================================
 * EXEMPLO REAL: ClientFormSchema Completo
 * ===========================================================================
 */

namespace App\Features\Clients\Schemas;

use App\Core\Forms\{FormSchema, TextInput, EmailInput, SelectInput};
use App\Models\Team;

class ClientFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make('Novo Cliente')
            ->field(
                TextInput::make('nif')
                    ->setLabel('NIF')
                    ->setPlaceholder('123456789')
                    ->setRequired()
                    ->setRules('required|string|max:20|unique:clients,nif')
            )
            ->field(
                TextInput::make('first_name')
                    ->setLabel('Primeiro Nome')
                    ->setRequired()
                    ->setRules('required|string|max:250')
            )
            ->field(
                TextInput::make('last_name')
                    ->setLabel('Último Nome')
                    ->setRequired()
                    ->setRules('required|string|max:250')
            )
            ->field(
                EmailInput::make('email')
                    ->setLabel('Email')
                    ->setPlaceholder('client@example.com')
                    ->setRequired()
                    ->setRules('required|email|max:255|unique:clients,email')
            )
            ->field(
                TextInput::make('phone')
                    ->setLabel('Telefone')
                    ->setPlaceholder('+351 910 000 000')
                    ->setRules('nullable|string|max:20')
            )
            ->field(
                SelectInput::make('team_id')
                    ->setLabel('Equipa')
                    ->setOptions(self::teamOptions())
                    ->setRules('nullable|exists:teams,id')
            );
    }

    public static function update(): FormSchema
    {
        return FormSchema::make('Editar Cliente')
            ->field(
                TextInput::make('nif')
                    ->setLabel('NIF')
                    ->setRules('sometimes|string|max:20|unique:clients,nif')
            )
            ->field(
                TextInput::make('first_name')
                    ->setLabel('Primeiro Nome')
                    ->setRules('sometimes|string|max:250')
            )
            ->field(
                TextInput::make('last_name')
                    ->setLabel('Último Nome')
                    ->setRules('sometimes|string|max:250')
            )
            ->field(
                EmailInput::make('email')
                    ->setLabel('Email')
                    ->setRules('sometimes|email|max:255|unique:clients,email')
            )
            ->field(
                TextInput::make('phone')
                    ->setLabel('Telefone')
                    ->setRules('nullable|string|max:20')
            )
            ->field(
                SelectInput::make('team_id')
                    ->setLabel('Equipa')
                    ->setOptions(self::teamOptions())
                    ->setRules('nullable|exists:teams,id')
            );
    }

    /**
     * Helper para obter opções de teams
     */
    private static function teamOptions(): array
    {
        return Team::all()
            ->map(fn($team) => [
                'value' => $team->id,
                'label' => $team->name,
            ])
            ->toArray();
    }
}

/**
 * ===========================================================================
 * NOTAS IMPORTANTES
 * ===========================================================================
 * 
 * 1. Rules em FormField vs FormRequest:
 *    - FormField::setRules() define as regras (ex: 'required|email|max:255')
 *    - FormRequest::rules() consome estas regras via FormValidator
 *    - Mantém compatibilidade com Laravel validation standard
 * 
 * 2. Condicionalidade (when):
 *    Exemplo: Campo team_id obrigatório apenas se role == 'worker'
 *    
 *    ->field(
 *        SelectInput::make('team_id')
 *            ->setLabel('Equipa')
 *            ->when('role', '==', 'worker')  // Condicional!
 *            ->setRequired()
 *    )
 *    
 *    Frontend: Mostra/esconde conforme condicional
 *    Backend: FormValidator respeita condição ao validar
 * 
 * 3. Metadados Custom (meta()):
 *    Útil para dados específicos de tipo ou componente
 *    
 *    ->field(
 *        FileInput::make('photo')
 *            ->meta('maxSize', 5120)  // 5MB
 *            ->meta('accept', 'image/jpeg,image/png')
 *    )
 *    
 *    Frontend pode ler metadata e aplicar regras específicas
 * 
 * 4. Separação create() vs update():
 *    - create() pode ter campos required adicionais
 *    - update() usa 'sometimes' em vez de 'required'
 *    - Reutiliza > 80% do schema (apenas regras mudam)
 * 
 * 5. Backward Compatibility:
 *    Antigo sistema (arrays) continua funcionando
 *    Permite migração gradual por feature
 *    Não quebra código existente
 */
