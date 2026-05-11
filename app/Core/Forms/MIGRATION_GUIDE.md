# Migration Guide: Array Schemas → FormSchema Classes

Documenta como migrar um `PageController` do sistema antigo (schemas como arrays) para o novo sistema (`FormSchema` + `FormField` classes).

---

## Antes vs Depois

### Antes (array-based)

```php
// PageController
$formSchema = [
    ['key' => 'nif',        'label' => 'NIF',        'type' => 'text',  'rules' => 'required|max:20'],
    ['key' => 'first_name', 'label' => 'First Name', 'type' => 'text',  'rules' => 'required|max:250'],
    ['key' => 'email',      'label' => 'Email',      'type' => 'email', 'rules' => 'required|email'],
];
```

### Depois (FormSchema classes)

```php
// app/Features/Clients/ClientFormSchema.php
class ClientFormSchema
{
    public static function create(): FormSchema
    {
        return FormSchema::make(__('forms.clients.create_title'))
            ->field(TextInput::make('nif')->setLabel(...)->setRequired()->setRules('required|max:20'))
            ->field(TextInput::make('first_name')->setLabel(...)->setRequired()->setRules('required|max:250'))
            ->field(EmailInput::make('email')->setLabel(...)->setRequired()->setRules('required|email'));
    }

    public static function update(): FormSchema
    {
        // Idêntico ao create() mas com 'sometimes' em vez de 'required'
    }
}
```

---

## Checklist de migração (por feature)

- [ ] Criar `ClientFormSchema.php` com `create()` e `update()`
- [ ] Atualizar `PageController`: substituir arrays por `FeatureFormSchema::update()->toArray()`
- [ ] Atualizar `StoreFeatureRequest`: gerar `rules()` com `FormValidator::fromSchema()`
- [ ] Atualizar `UpdateFeatureRequest` idem
- [ ] Testar create/update sem erros
- [ ] Verificar validação (frontend + backend)

---

## Vantagens do novo sistema

| | Array | FormSchema |
|---|---|---|
| Type-safe | ❌ | ✅ |
| Condicionalidade (`when()`) | ❌ | ✅ |
| Validação centralizada | ❌ | ✅ |
| Reusabilidade create/update | ❌ | ✅ |

---

## Notas

**`create()` vs `update()`** — `create()` usa `required`, `update()` usa `sometimes` para permitir updates parciais.

**Condicionalidade:**
```php
SelectInput::make('team_id')->when('role', '==', 'worker')->setRequired()
```

**Metadados custom:**
```php
FileInput::make('photo')->meta('maxSize', 5120)->meta('accept', 'image/jpeg,image/png')
```
