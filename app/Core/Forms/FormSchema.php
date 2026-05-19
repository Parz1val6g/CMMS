<?php

namespace App\Core\Forms;

/**
 * Builder para construir esquemas de formulário com múltiplos campos.
 * 
 * Oferece uma API fluente para definir, manipular e validar campos de forma
 * estruturada. Suporta validação básica de schema.
 * 
 * Exemplo:
 *   FormSchema::make('Cliente')
 *     ->field(TextInput::make('name')->setLabel('Nome')->setRequired())
 *     ->field(EmailInput::make('email')->setLabel('Email'))
 *     ->field(TextInput::make('password')->setLabel('Senha')->when('role_id', '==', 'admin'))
 */
class FormSchema
{
    /**
     * Título do formulário (opcional).
     */
    protected ?string $title = null;

    /**
     * Número de colunas da grelha (default 1).
     */
    protected int $columns = 1;

    /**
     * Array de FormField, indexed por key para evitar duplicatas.
     * 
     * @var array<string, FormField>
     */
    protected array $inputs = [];

    /**
     * Constructor.
     * 
     * @param string|null $title Título opcional do formulário
     */
    public function __construct(?string $title = null)
    {
        $this->title = $title;
    }

    /**
     * Factory method.
     * 
     * @param string|null $title
     * @return static
     */
    public static function make(?string $title = null): static
    {
        return new static($title);
    }

    // ===== Manipulação de Campos =====

    /**
     * Adiciona um ou mais FormField ao schema.
     * 
     * @param FormField|FormField[] $inputs
     * @return $this
     * @throws \InvalidArgumentException se algum input não é FormField
     */
    public function field(FormField|array $inputs): static
    {
        $fieldsToAdd = is_array($inputs) ? $inputs : [$inputs];

        foreach ($fieldsToAdd as $input) {
            if (!$input instanceof FormField) {
                throw new \InvalidArgumentException(
                    'Todos inputs devem ser instâncias de FormField. ' .
                    'Recebido: ' . gettype($input)
                );
            }
            // Usa key como índice para evitar duplicatas
            $this->inputs[$input->getKey()] = $input;
        }

        return $this;
    }

    /**
     * Define múltiplos campos de uma vez (atalho para field()).
     * 
     * @param FormField[] $inputs
     * @return $this
     */
    public function schema(array $inputs): static
    {
        return $this->field($inputs);
    }

    /**
     * Remove um campo pelo key.
     * 
     * @param string $key
     * @return $this
     */
    public function remove(string $key): static
    {
        unset($this->inputs[$key]);
        return $this;
    }

    /**
     * Remove múltiplos campos pelos keys.
     * 
     * @param string[] $keys
     * @return $this
     */
    public function removeMany(array $keys): static
    {
        foreach ($keys as $key) {
            $this->remove($key);
        }
        return $this;
    }

    /**
     * Mantém apenas os campos especificados (remove todos os outros).
     * 
     * @param string[] $keys
     * @return $this
     */
    public function only(array $keys): static
    {
        $this->inputs = array_filter(
            $this->inputs,
            fn(string $k) => in_array($k, $keys),
            ARRAY_KEY_FILTER_FLAG_KEY
        );
        return $this;
    }

    /**
     * Remove os campos especificados (mantém todos os outros).
     * 
     * @param string[] $keys
     * @return $this
     */
    public function except(array $keys): static
    {
        $this->inputs = array_filter(
            $this->inputs,
            fn(string $k) => !in_array($k, $keys),
            ARRAY_KEY_FILTER_FLAG_KEY
        );
        return $this;
    }

    /**
     * Encontra um campo pelo key.
     * 
     * @param string $key
     * @return FormField|null
     */
    public function findByKey(string $key): ?FormField
    {
        return $this->inputs[$key] ?? null;
    }

    /**
     * Obtém todos os campos.
     * 
     * @return array<string, FormField>
     */
    public function getInputs(): array
    {
        return $this->inputs;
    }

    /**
     * Define novo título.
     * 
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Obtém título.
     * 
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Define o número de colunas da grelha do formulário.
     * 
     * @param int $columns Número de colunas (1-6)
     * @return $this
     */
    public function setColumns(int $columns): static
    {
        if ($columns < 1 || $columns > 6) {
            throw new \InvalidArgumentException('Columns must be between 1 and 6');
        }
        $this->columns = $columns;
        return $this;
    }

    /**
     * Obtém o número de colunas.
     * 
     * @return int
     */
    public function getColumns(): int
    {
        return $this->columns;
    }

    // ===== Validação =====

    /**
     * Valida o schema e retorna array de erros.
     * 
     * Verificações:
     * - Campo sem label
     * - Campo sem type
     * 
     * @return array<int, string> Array vazio se válido, senão array de mensagens de erro
     */
    public function validate(): array
    {
        $errors = [];

        foreach ($this->inputs as $field) {
            if ($field->getLabel() === null) {
                $errors[] = "Campo '{$field->getKey()}' não tem label";
            }
            if ($field->getType() === null) {
                $errors[] = "Campo '{$field->getKey()}' não tem type";
            }
        }

        return $errors;
    }

    /**
     * Verifica se schema é válido.
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        return empty($this->validate());
    }

    // ===== Serialização =====

    /**
     * Converte schema para array JSON para enviar ao frontend.
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'inputs' => array_values(array_map(
                fn(FormField $field) => $field->toArray(),
                $this->inputs
            )),
        ];

        if ($this->title !== null) {
            $data['title'] = $this->title;
        }

        if ($this->columns !== 1) {
            $data['columns'] = $this->columns;
        }

        return $data;
    }
}