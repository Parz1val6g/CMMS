<?php

namespace App\Core\Forms;

use App\Core\Forms\Traits\FluentProperties;

/**
 * Classe base abstrata para campos de formulário.
 * 
 * Define a estrutura comum para todos os tipos de campos, incluindo:
 * - Metadados (label, placeholder, etc)
 * - Validação (rules, condições)
 * - Layout (col_span, customizações)
 * - Extensibilidade (metadata customizado)
 */
abstract class FormField
{
    protected string $key;
    protected ?string $placeholder = null;
    protected ?string $label = null;
    protected ?string $type = null;
    protected bool $required = false;
    protected ?FieldCondition $condition = null;
    protected ?int $colSpan = null;
    protected ?string $validationRules = null;
    protected array $metadata = [];
    protected ?string $helperText = null;
    protected array $helpExamples = [];
    protected string $validationTiming = 'submit'; // submit, blur, or both
    protected string $errorSeverity = 'error'; // error, warning, info

    public function __construct(string $key)
    {
        if (empty(trim($key))) {
            throw new \InvalidArgumentException('Field key cannot be empty');
        }
        $this->key = $key;
    }

    /**
     * Factory method para criar instância do campo.
     * 
     * @param string $key
     * @return static
     */
    public static function make(string $key): static
    {
        return new static($key);
    }

    // ===== Getters =====

    public function getKey(): string
    {
        return $this->key;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getRequired(): bool
    {
        return $this->required === true;
    }

    public function getColSpan(): ?int
    {
        return $this->colSpan;
    }

    public function getCondition(): ?FieldCondition
    {
        return $this->condition;
    }

    public function getRules(): ?string
    {
        return $this->validationRules;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getHelperText(): ?string
    {
        return $this->helperText;
    }

    public function getHelpExamples(): array
    {
        return $this->helpExamples;
    }

    public function getValidationTiming(): string
    {
        return $this->validationTiming;
    }

    public function getErrorSeverity(): string
    {
        return $this->errorSeverity;
    }

    // ===== Setters (Fluent) =====

    /**
     * Define o label do campo.
     * 
     * @param string $label
     * @return $this
     * @throws \InvalidArgumentException se label está vazio
     */
    public function setLabel(string $label): static
    {
        if (empty(trim($label))) {
            throw new \InvalidArgumentException('Label cannot be empty');
        }
        $this->label = $label;
        return $this;
    }

    public function setPlaceholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function setRequired(bool $required = true): static
    {
        $this->required = $required;
        return $this;
    }

    public function setColSpan(int $span): static
    {
        if ($span < 1 || $span > 12) {
            throw new \InvalidArgumentException('colSpan must be between 1 and 12');
        }
        $this->colSpan = $span;
        return $this;
    }

    /**
     * Define regras de validação Laravel para este campo.
     * 
     * Exemplo: 'required|email|max:255'
     * 
     * @param string $rules
     * @return $this
     */
    public function setRules(string $rules): static
    {
        $this->validationRules = $rules;
        return $this;
    }

    /**
     * Define texto de ajuda para o usuário (exibido abaixo do label).
     * 
     * Exemplo: 'Enter the full name including first and last names'
     * 
     * @param string $text
     * @return $this
     */
    public function helperText(string $text): static
    {
        $this->helperText = $text;
        return $this;
    }

    /**
     * Define exemplos de ajuda para o campo.
     * 
     * Exemplo: ['example@email.com', 'john@company.com']
     * 
     * @param array $examples
     * @return $this
     */
    public function helpExamples(array $examples): static
    {
        $this->helpExamples = $examples;
        return $this;
    }

    /**
     * Define quando validar este campo.
     * 
     * Opções:
     * - 'submit': Apenas no submit (padrão)
     * - 'blur': Quando o campo perde foco
     * - 'both': Blur + debounce ao digitar (1-2 segundos)
     * 
     * @param string $timing
     * @return $this
     */
    public function validationTiming(string $timing = 'both'): static
    {
        if (!in_array($timing, ['submit', 'blur', 'both'])) {
            throw new \InvalidArgumentException('Invalid validation timing. Must be submit, blur, or both');
        }
        $this->validationTiming = $timing;
        return $this;
    }

    /**
     * Define a severidade da mensagem de erro.
     * 
     * Opções: 'error' (vermelho), 'warning' (amarelo), 'info' (azul)
     * 
     * @param string $severity
     * @return $this
     */
    public function errorSeverity(string $severity = 'error'): static
    {
        if (!in_array($severity, ['error', 'warning', 'info'])) {
            throw new \InvalidArgumentException('Invalid error severity. Must be error, warning, or info');
        }
        $this->errorSeverity = $severity;
        return $this;
    }

    // ===== Condicionalidade =====

    /**
     * Define condição de visibilidade/validação para este campo.
     * 
     * Campo é mostrado/validado apenas se condição é verdadeira.
     * 
     * Exemplo:
     *   ->when('role_id', '==', 'admin')
     *   ->when('age', '>=', 18)
     * 
     * @param string $field Nome do campo a comparar
     * @param string $operator Operador: ==, !=, <, >, <=, >=
     * @param mixed $value Valor para comparar
     * @return $this
     */
    public function when(string $field, string $operator, mixed $value): static
    {
        $this->condition = new FieldCondition($field, $operator, $value);
        return $this;
    }

    /**
     * Remove a condição deste campo (torna-o visível sempre).
     * 
     * @return $this
     */
    public function clearCondition(): static
    {
        $this->condition = null;
        return $this;
    }

    // ===== Metadados Customizados =====

    /**
     * Define metadado customizado para extensibilidade.
     * 
     * Útil para passar dados específicos do tipo (ex: apiKey, currency, etc)
     * 
     * Exemplo:
     *   ->meta('apiKey', config('services.google_maps'))
     *   ->meta('currency', 'EUR')
     * 
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function meta(string $key, mixed $value): static
    {
        $this->metadata[$key] = $value;
        return $this;
    }

    /**
     * Obtém metadado customizado.
     * 
     * @param string $key
     * @param mixed $default Valor padrão se key não existe
     * @return mixed
     */
    public function getMeta(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    // ===== Serialização =====

    /**
     * Converte campo para array JSON para enviar ao frontend.
     * 
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'key' => $this->key,
            'type' => $this->type,
            'label' => $this->label,
            'required' => $this->required,
        ];

        // Adiciona campos opcionais apenas se preenchidos
        if ($this->placeholder !== null) {
            $data['placeholder'] = $this->placeholder;
        }
        if ($this->colSpan !== null) {
            $data['span'] = $this->colSpan;
        }
        if ($this->validationRules !== null) {
            $data['rules'] = $this->validationRules;
        }
        if ($this->condition !== null) {
            $data['condition'] = $this->condition->toArray();
        }
        if (!empty($this->metadata)) {
            $data['metadata'] = $this->metadata;
        }

        // Adiciona metadados de UX se preenchidos
        if ($this->helperText !== null) {
            $data['helperText'] = $this->helperText;
        }
        if (!empty($this->helpExamples)) {
            $data['helpExamples'] = $this->helpExamples;
        }
        if ($this->validationTiming !== 'submit') {
            $data['validationTiming'] = $this->validationTiming;
        }
        if ($this->errorSeverity !== 'error') {
            $data['errorSeverity'] = $this->errorSeverity;
        }

        return $data;
    }
}