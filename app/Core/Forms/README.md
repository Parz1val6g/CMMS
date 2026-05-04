# Form System Architecture v2

## 🎯 Overview

Sistema refatorado e modernizado para definição, validação e customização de formulários. Baseado em classes FormField + FormSchema builder + FormValidator + FieldCondition.

**Principais melhorias:**
- ✅ Type-safe (type hints completos)
- ✅ Condicionalidade integrada (`when()`)
- ✅ Metadados extensíveis (`meta()`)
- ✅ Validação centralizada (FormValidator extrai rules)
- ✅ Backward-compatible (arrays antigos ainda funcionam)

---

## 📁 Arquitetura

```
app/Core/Forms/
├── FormField.php              # Base abstrata para campos
├── FormSchema.php             # Builder para compor múltiplos campos
├── FormValidator.php          # Gera regras Laravel a partir de schema
├── FieldCondition.php         # Encapsula condicionalidade (when)
├── Traits/
│   └── FluentProperties.php    # Simplifica getter/setter (magic methods)
├── Examples/
│   └── FormSchemaExamples.php  # Exemplos de uso (Cliente, Worker, ServiceOrder)
└── MIGRATION_GUIDE.php         # Como migrar de arrays para FormSchema classes
```

---

## 🏗️ Componentes Principais

### 1. **FormField (Base Abstrata)**

Classe base para todos os tipos de campo. Define interface comum e lógica compartilhada.

```php
use App\Core\Forms\{FormField, TextInput, EmailInput};

$field = TextInput::make('name')
    ->setLabel('Nome Completo')
    ->setRequired()
    ->setRules('required|max:250')
    ->when('role', '==', 'admin')
    ->meta('icon', 'user')
    ->toArray();

// Output:
// {
//   "key": "name",
//   "type": "text",
//   "label": "Nome Completo",
//   "required": true,
//   "rules": "required|max:250",
//   "condition": { "field": "role", "operator": "==", "value": "admin" },
//   "metadata": { "icon": "user" }
// }
```

**Métodos principais:**
- `setLabel(string)` — Define label (obrigatório)
- `setPlaceholder(string)` — Placeholder
- `setRequired(bool)` — Required/optional
- `setRules(string)` — Regras de validação (ex: 'required|email|max:255')
- `when(field, operator, value)` — Condicional (mostra se condição é true)
- `meta(key, value)` — Metadados customizados
- `toArray()` — Serializa para JSON

---

### 2. **Tipos de Campo (Input Classes)**

Especializações de FormField para cada tipo:

| Classe | Type | Uso |
|--------|------|-----|
| `TextInput` | `text` | Texto simples |
| `EmailInput` | `email` | Email (validação tipo) |
| `NumberInput` | `number` | Número (validação tipo) |
| `SelectInput` | `select` | Dropdown com opções |
| `TextAreaInput` | `textarea` | Texto multilinha |
| `FileInput` | `file` | Upload de arquivo |
| `CheckboxInput` | `checkbox` | Checkbox |
| `RadioInput` | `radio` | Radio buttons |
| `MapInput` | `map` | Google Maps picker |
| `SectionHeader` | `section-header` | Visual grouping |

```php
SelectInput::make('role')
    ->setLabel('Papel')
    ->setRequired()
    ->setOptions([
        ['value' => 'admin', 'label' => 'Admin'],
        ['value' => 'user', 'label' => 'User'],
    ])
```

---

### 3. **FieldCondition**

Encapsula condicionalidade (when). Valida operadores e avalia condições.

```php
$condition = new FieldCondition('role', '==', 'admin');
$condition->evaluate('admin')   // true
$condition->evaluate('user')    // false

// Operadores permitidos: ==, !=, <, >, <=, >=
// (Operadores lógicos and/or/xor foram removidos — não fazem sentido aqui)
```

---

### 4. **FormSchema (Builder)**

Compõe múltiplos FormField em um schema coerente.

```php
use App\Core\Forms\FormSchema;

$schema = FormSchema::make('Cliente')
    ->field(TextInput::make('name')
        ->setLabel('Nome')
        ->setRequired())
    ->field(EmailInput::make('email')
        ->setLabel('Email')
        ->setRequired())
    ->field(TextInput::make('phone')
        ->setLabel('Telefone'));

// Manipulação
$schema->only(['name', 'email']);        // Remove phone
$schema->except(['phone']);              // Remove phone
$schema->remove('phone');                // Remove phone
$schema->findByKey('email');             // Encontra campo

// Validação
$errors = $schema->validate();           // Retorna array de erros
if ($schema->isValid()) { /* ... */ }    // Check rápido

// Serialização (enviar ao frontend)
$json = json_encode($schema->toArray());
```

---

### 5. **FormValidator**

Gera regras de validação Laravel a partir de um FormSchema.

```php
use App\Core\Forms\FormValidator;

$schema = ClientFormSchema::create();
$validator = new FormValidator();

// Gera regras (considerando custom rules, tipo, condicionalidade)
$rules = $validator->fromSchema($schema, $request->all());

// Output:
// [
//   'name' => ['required', 'string'],
//   'email' => ['required', 'email', 'unique:clients,email'],
//   'phone' => ['nullable', 'string'],
// ]
```

**Integração com FormRequest:**

```php
class StoreClientRequest extends FormRequest
{
    public function rules(): array
    {
        $schema = ClientFormSchema::create();
        $validator = new FormValidator();
        return $validator->fromSchema($schema, $this->all());
    }
}
```

---

## 📝 Exemplo: Formulário Completo

```php
namespace App\Features\Clients\Schemas;

use App\Core\Forms\{FormSchema, TextInput, EmailInput, SelectInput};
use App\Models\Team;

class ClientFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make('Novo Cliente')
            ->field(TextInput::make('nif')
                ->setLabel('NIF')
                ->setRequired()
                ->setRules('required|max:20|unique:clients,nif'))
            
            ->field(TextInput::make('first_name')
                ->setLabel('Primeiro Nome')
                ->setRequired())
            
            ->field(EmailInput::make('email')
                ->setLabel('Email')
                ->setRequired()
                ->setRules('required|email|unique:clients,email'))
            
            ->field(SelectInput::make('team_id')
                ->setLabel('Equipa')
                ->setOptions(Team::pluck('name', 'id')->map(fn($name, $id) => 
                    ['value' => $id, 'label' => $name])->toArray())
                ->when('role', '==', 'worker'));  // Mostra apenas se worker
    }

    public static function update(): FormSchema
    {
        // Idêntico ao create(), mas com campos opcionais (setRules com 'sometimes')
        return self::create();
    }
}
```

**Uso no PageController:**

```php
class ClientPageController
{
    public function index()
    {
        return Inertia::render('Clients/Index', [
            'formSchema' => ClientFormSchema::update()->toArray(),
            'createFormSchema' => ClientFormSchema::create()->toArray(),
        ]);
    }
}
```

---

## 🔄 Condicionalidade

Campos podem estar visíveis/validáveis apenas sob certas condições.

```php
->field(SelectInput::make('team_id')
    ->setLabel('Equipa')
    ->when('role', '==', 'worker')  // Mostra se role == 'worker'
    ->setRequired())

->field(TextInput::make('description')
    ->setLabel('Descrição')
    ->when('type', '!=', 'simple')  // Mostra se type != 'simple'
)

->field(NumberInput::make('discount')
    ->setLabel('Desconto')
    ->when('is_vip', '==', true)    // Mostra se is_vip == true
)
```

**Frontend (React):** Mostra/esconde dinamicamente conforme valores dos outros campos
**Backend (FormValidator):** Aplica `Rule::requiredIf()` apenas se condição é true

---

## 🏷️ Metadados Customizados

Para dados específicos de tipo ou componente:

```php
FileInput::make('photo')
    ->meta('maxSize', 5120)      // 5MB
    ->meta('accept', 'image/*')

MapInput::make('location')
    ->meta('apiKey', config('services.google_maps'))
    ->meta('defaultZoom', 15)
    ->meta('center', ['lat' => 38.7, 'lng' => -9.2])

// Acessar no backend:
$field->getMeta('maxSize')  // 5120
```

---

## 🚀 Migração de Arrays para FormSchema

**ANTES (Array):**
```php
$formSchema = [
    ['key' => 'name', 'label' => 'Nome', 'type' => 'text', 'rules' => 'required'],
    ['key' => 'email', 'label' => 'Email', 'type' => 'email'],
];
```

**DEPOIS (FormSchema):**
```php
$formSchema = FormSchema::make('Cliente')
    ->field(TextInput::make('name')
        ->setLabel('Nome')
        ->setRequired())
    ->field(EmailInput::make('email')
        ->setLabel('Email'))
    ->toArray();
```

**Passo-a-passo:**
1. Criar `app/Features/{Feature}/Schemas/{Feature}FormSchema.php`
2. Atualizar PageController para usar schema classes
3. Atualizar FormRequest para usar FormValidator
4. Testar create/update funciona
5. Commit

(Ver `MIGRATION_GUIDE.php` para exemplo completo)

---

## ✅ Checklist de Implementação

### Priority 1 (DONE ✅)
- [x] FormField base com type hints
- [x] FieldCondition (validação de operadores)
- [x] FormSchema com métodos (only, except, remove, findByKey)
- [x] FormValidator que respeita rules + tipo + condicionalidade
- [x] Input types especializadas (TextInput, SelectInput, etc)
- [x] Suporte a metadados (meta())
- [x] Namespace corretos

### Priority 2 (To implement)
- [ ] Trait WithFormSchema para FormRequest automático
- [ ] Frontend: useFormSchema hook (Zod validation)
- [ ] Frontend: Conditional rendering in FormField.jsx
- [ ] Frontend: Custom components support
- [ ] Migração Clients feature (example)
- [ ] Migração Workers feature (example)

### Priority 3 (Future)
- [ ] I18n para labels
- [ ] Validation feedback (frontend)
- [ ] Custom validators (FormField + FormValidator)
- [ ] Dynamic form arrays
- [ ] Multi-step forms

---

## 🧪 Testes

Exemplo de teste unitário:

```php
class FormSchemaTest extends TestCase
{
    public function test_form_schema_validation()
    {
        $schema = FormSchema::make('Test')
            ->field(TextInput::make('name')->setLabel('Name'));
        
        $this->assertTrue($schema->isValid());
    }

    public function test_form_validator_generates_rules()
    {
        $schema = EmailInput::make('email')->setLabel('Email')->setRequired();
        $validator = new FormValidator();
        
        $rules = $validator->fromSchema(
            FormSchema::make()->field($schema)
        );
        
        $this->assertContains('required', $rules['email']);
        $this->assertContains('email', $rules['email']);
    }
}
```

---

## 📚 Referências

- [FormField.php](FormField.php) — Base classe (type-safe, metadados)
- [FormSchema.php](FormSchema.php) — Builder fluente
- [FormValidator.php](FormValidator.php) — Gerador de rules
- [FieldCondition.php](FieldCondition.php) — Condicionalidade
- [Examples/FormSchemaExamples.php](Examples/FormSchemaExamples.php) — Exemplos
- [MIGRATION_GUIDE.php](MIGRATION_GUIDE.php) — Como migrar
