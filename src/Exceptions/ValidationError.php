<?php

namespace Laravel5Helpers\Exceptions;

class ValidationError extends LaravelHelpersExceptions
{
    public function __construct($message)
    {
        $message = 'Validation failed with error: ' . $message;

        parent::__construct($message);
    }
}
