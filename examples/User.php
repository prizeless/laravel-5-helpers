<?php

namespace App\Definitions;

use Laravel5Helpers\Definitions\Definition;

class User extends Definition
{
    public $username;

    public $first_name;

    public $last_name;

    protected function setValidators()
    {
        $this->validators = [];
    }
}
