<?php

namespace Laravel5Helpers\Exceptions;

class ResourceGetError extends LaravelHelpersExceptions
{
    public function __construct($resource)
    {
        parent::__construct('Error getting resource ' . $resource);
    }
}
