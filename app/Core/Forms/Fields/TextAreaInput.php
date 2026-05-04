<?php

namespace App\Core\Forms\Fields;
use App\Core\Forms\FormField;

class TextAreaInput extends FormField
{
    protected ?string $type = 'textarea';
    protected ?int $rows = null;

    public function __construct(string $key)
    {
        parent::__construct($key);
        // Sensible defaults for textarea fields
        $this->validationTiming = 'blur'; // Validate on blur for larger content
        $this->setRows(4); // Default 4 rows
    }

    public function setRows(int $rows): static
    {
        if ($rows < 1) {
            throw new \InvalidArgumentException('Rows must be greater than 0');
        }
        $this->rows = $rows;
        return $this;
    }

    public function getRows(): ?int
    {
        return $this->rows;
    }

    public function toArray(): array
    {
        $arr = parent::toArray();
        if ($this->rows !== null) {
            $arr['rows'] = $this->rows;
        }
        return $arr;
    }
}