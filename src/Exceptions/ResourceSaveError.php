<?php

namespace Laravel5Helpers\Exceptions;

class ResourceSaveError extends LaravelHelpersExceptions
{
    public function __construct($message = 'There was an saving data.')
    {
        parent::__construct($message);
    }
}
