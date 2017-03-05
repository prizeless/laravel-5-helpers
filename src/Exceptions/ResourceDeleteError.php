<?php

namespace Laravel5Helpers\Exceptions;

class ResourceDeleteError extends LaravelHelpersExceptions
{
    public function __construct($model)
    {
        parent::__construct('There was an error deleting '.$model);
    }
}
