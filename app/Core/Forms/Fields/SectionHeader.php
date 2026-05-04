<?php

namespace App\Core\Forms\Fields;
use App\Core\Forms\FormField;

class SectionHeader extends FormField
{
    protected ?string $type = 'section-header';

    public function __construct(string $key)
    {
        parent::__construct($key);
        $this->required = false;
    }
}