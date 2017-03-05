<?php

namespace Laravel5Helpers\Exceptions;

class ResourceUpdateError extends LaravelHelpersExceptions
{
    public function __construct($model)
    {
        parent::__construct('There was an error updating ' . $model);
    }
}
