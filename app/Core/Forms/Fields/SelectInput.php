<?php

namespace App\Core\Forms\Fields;
use App\Core\Forms\FormField;

class SelectInput extends FormField
{
    protected ?string $type = 'select';
    protected array $options = [];
    protected bool $multiple = false;

    public function __construct(string $key)
    {
        parent::__construct($key);
        // Sensible defaults for select fields
        $this->validationTiming = 'blur'; // Validate on blur
    }

    /**
     * Define opções para o select.
     *
     * Exemplo:
     *   setOptions([
     *     ['value' => 1, 'label' => 'Option 1'],
     *     ['value' => 2, 'label' => 'Option 2'],
     *   ])
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Enable multiple selection mode.
     *
     * @param bool $multiple
     * @return $this
     */
    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;
        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function toArray(): array
    {
        $arr = parent::toArray();
        $arr['options'] = $this->options;
        if ($this->multiple) {
            $arr['multiple'] = true;
        }
        return $arr;
    }
}