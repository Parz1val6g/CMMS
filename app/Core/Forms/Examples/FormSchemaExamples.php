<?php

namespace App\Core\Forms\Examples;

use App\Core\Forms\{
    FormSchema,
    TextInput,
    EmailInput,
    SelectInput,
    NumberInput,
    FileInput,
    TextAreaInput,
    CheckboxInput,
    MapInput,
    SectionHeader,
    FormValidator,
};

/**
 * Exemplos de uso do novo sistema de formulários.
 * 
 * Demonstra:
 * 1. Criação de schemas com múltiplos tipos de campo
 * 2. Condicionalidade (when)
 * 3. Validação
 * 4. Customização com metadados
 */
class FormSchemaExamples
{
    /**
     * Exemplo 1: Formulário simples de Cliente
     * 
     * Campos: name, email, phone
     * Todos obrigatórios.
     */
    public static function clientFormSchema(): FormSchema
    {
        return FormSchema::make('Cliente')
            ->field(TextInput::make('name')
                ->setLabel('Nome Completo')
                ->setPlaceholder('João Silva')
                ->setRequired())

            ->field(EmailInput::make('email')
                ->setLabel('Email')
                ->setRequired()
                ->setRules('required|email|max:255|unique:clients,email'))

            ->field(TextInput::make('phone')
                ->setLabel('Telefone')
                ->setPlaceholder('+351 910 000 000'));
    }

    /**
     * Exemplo 2: Formulário com condicionalidade
     * 
     * Campos: role, team_id (mostra apenas se role == 'worker')
     */
    public static function workerFormSchema(): FormSchema
    {
        return FormSchema::make('Worker')
            ->field(TextInput::make('name')
                ->setLabel('Nome')
                ->setRequired())

            ->field(SelectInput::make('role')
                ->setLabel('Papel')
                ->setRequired()
                ->setOptions([
                    ['value' => 'admin', 'label' => 'Administrador'],
                    ['value' => 'worker', 'label' => 'Trabalhador'],
                    ['value' => 'client', 'label' => 'Cliente'],
                ]))

            // Este campo só aparece se role == 'worker'
            ->field(SelectInput::make('team_id')
                ->setLabel('Equipa')
                ->when('role', '==', 'worker')
                ->setOptions([
                    ['value' => 1, 'label' => 'Equipa A'],
                    ['value' => 2, 'label' => 'Equipa B'],
                ]));
    }

    /**
     * Exemplo 3: Formulário com seções, uploads e mapas
     * 
     * Usado em ServiceOrder: sections, photos, location
     */
    public static function serviceOrderFormSchema(): FormSchema
    {
        return FormSchema::make('Ordem de Serviço')
            // ===== SEÇÃO 1: INFORMAÇÕES BÁSICAS =====
            ->field(SectionHeader::make('section-core')
                ->setLabel('Informações Básicas'))

            ->field(TextInput::make('process')
                ->setLabel('Processo')
                ->setRequired())

            ->field(TextAreaInput::make('description')
                ->setLabel('Descrição')
                ->setRows(5)
                ->setRequired())

            ->field(SelectInput::make('client_id')
                ->setLabel('Cliente')
                ->setRequired()
                ->setOptions([
                    ['value' => 1, 'label' => 'Client 1'],
                    ['value' => 2, 'label' => 'Client 2'],
                ]))

            // ===== SEÇÃO 2: FOTOGRAFIA =====
            ->field(SectionHeader::make('section-photo')
                ->setLabel('Fotografia'))

            ->field(FileInput::make('photo')
                ->setLabel('Carregar Foto')
                ->setRequired()
                ->accept('image/jpeg,image/png')
                ->meta('maxSize', 5120)) // 5MB

            // ===== SEÇÃO 3: LOCALIZAÇÃO =====
            ->field(SectionHeader::make('section-map')
                ->setLabel('Localização'))

            ->field(MapInput::make('location')
                ->setLabel('Selecionar no Mapa')
                ->apiKey(config('services.google_maps_api_key'))
                ->meta('defaultZoom', 15)
                ->meta('center', ['lat' => 38.7, 'lng' => -9.2]));
    }

    /**
     * Exemplo 4: Validação de schema
     */
    public static function validateSchema(): void
    {
        $schema = self::clientFormSchema();

        $errors = $schema->validate();
        if (!empty($errors)) {
            echo "Schema inválido:\n";
            foreach ($errors as $error) {
                echo "- $error\n";
            }
        } else {
            echo "Schema válido!\n";
        }
    }

    /**
     * Exemplo 5: Gerar regras de validação Laravel
     */
    public static function generateValidationRules(): void
    {
        $schema = self::clientFormSchema();
        $validator = new FormValidator();

        // Simular dados atuais (para condicionalidade)
        $data = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
        ];

        $rules = $validator->fromSchema($schema, $data);

        echo "Regras de validação geradas:\n";
        foreach ($rules as $field => $fieldRules) {
            echo "$field: [" . implode(', ', $fieldRules) . "]\n";
        }
    }

    /**
     * Exemplo 6: Manipulação de schema (only, except, remove)
     */
    public static function manipulateSchema(): void
    {
        $schema = self::clientFormSchema();

        // Manter apenas name e email (remove phone)
        $schema->only(['name', 'email']);

        // Ou remover campos específicos:
        // $schema->remove('phone');

        // Ou remover múltiplos:
        // $schema->removeMany(['phone', 'address']);

        // Ou manter todos exceto:
        // $schema->except(['phone']);

        // Encontrar campo específico:
        $emailField = $schema->findByKey('email');
        if ($emailField) {
            echo "Email field: " . $emailField->getLabel() . "\n";
        }
    }

    /**
     * Exemplo 7: Conversão para JSON (enviar ao frontend)
     */
    public static function schemaToJSON(): string
    {
        $schema = self::clientFormSchema();
        return json_encode($schema->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}

/**
 * OUTPUT ESPERADO:
 * 
 * ===== Client Form Schema (JSON) =====
 * {
 *   "title": "Cliente",
 *   "inputs": [
 *     {
 *       "key": "name",
 *       "type": "text",
 *       "label": "Nome Completo",
 *       "required": true,
 *       "placeholder": "João Silva"
 *     },
 *     {
 *       "key": "email",
 *       "type": "email",
 *       "label": "Email",
 *       "required": true,
 *       "rules": "required|email|max:255|unique:clients,email"
 *     },
 *     {
 *       "key": "phone",
 *       "type": "text",
 *       "label": "Telefone",
 *       "required": false,
 *       "placeholder": "+351 910 000 000"
 *     }
 *   ]
 * }
 * 
 * ===== Worker Form Schema (com condicionalidade) =====
 * {
 *   "inputs": [
 *     {
 *       "key": "name",
 *       "type": "text",
 *       "label": "Nome",
 *       "required": true
 *     },
 *     {
 *       "key": "role",
 *       "type": "select",
 *       "label": "Papel",
 *       "required": true,
 *       "options": [...]
 *     },
 *     {
 *       "key": "team_id",
 *       "type": "select",
 *       "label": "Equipa",
 *       "required": false,
 *       "condition": {
 *         "field": "role",
 *         "operator": "==",
 *         "value": "worker"
 *       },
 *       "options": [...]
 *     }
 *   ]
 * }
 */
