<?php

namespace Laravel5Helpers\Exceptions;

class ResourceSaveError extends LaravelHelpersExceptions
{
    public function __construct($message = 'There was an error saving data.')
    {
        parent::__construct($message);
    }
}
