<?php

namespace App\Exceptions;

use InvalidArgumentException;

class InvalidStateTransitionException extends InvalidArgumentException
{
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: __('messages.services.equipment.invalid_transition'));
    }
}
