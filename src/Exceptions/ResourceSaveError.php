<?php

namespace Laravel5Helpers\Exceptions;

class ResourceSaveError extends LaravelHelpersExceptions
{
    public function __construct($model)
    {
        parent::__construct('Error saving data for ' . $model);
    }
}
