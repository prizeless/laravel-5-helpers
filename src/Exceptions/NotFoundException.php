<?php

namespace Laravel5Helpers\Exceptions;

class NotFoundException extends LaravelHelpersExceptions
{
    public function __construct($message)
    {
        parent::__construct('Resource ' . $message . ' could not be found.');
    }
}
