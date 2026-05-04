<?php

namespace App\Exceptions;

use InvalidArgumentException;

class EquipmentUnavailableException extends InvalidArgumentException
{
    public function __construct(string $message = 'Equipment is not available for loan.')
    {
        parent::__construct($message);
    }
}
