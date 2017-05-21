<?php

namespace Laravel5Helpers\Definitions;

class ResultOrder
{
    public $field;

    public $direction;

    public function __construct($field, $direction)
    {
        $this->field = $field;

        $this->direction = $direction;
    }
}
