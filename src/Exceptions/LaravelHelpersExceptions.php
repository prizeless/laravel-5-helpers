<?php

namespace Laravel5Helpers\Exceptions;

class LaravelHelpersExceptions extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
