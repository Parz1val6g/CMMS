<?php

namespace App\Core\Forms\Fields;
use App\Core\Forms\FormField;

class FileInput extends FormField
{
    protected ?string $type = 'file';

    public function __construct(string $key)
    {
        parent::__construct($key);
        // Sensible defaults for file fields
        $this->validationTiming = 'submit'; // Only validate on submit for files
        $this->helperText = 'Maximum file size: 5MB. Supported formats: PNG, JPG, PDF';
    }

    /**
     * Define tipos de arquivo aceitos.
     * 
     * Exemplo: 'image/jpeg,image/png' ou '.pdf,.doc'
     * 
     * @param string $accept
     * @return $this
     */
    public function accept(string $accept): static
    {
        $this->meta('accept', $accept);
        return $this;
    }

    public function getAccept(): ?string
    {
        return $this->getMeta('accept');
    }

    /**
     * Set maximum file size in MB.
     */
    public function maxSize(int $sizeMB): static
    {
        $this->meta('maxSize', $sizeMB);
        $this->helperText("Maximum file size: {$sizeMB}MB");
        return $this;
    }
}