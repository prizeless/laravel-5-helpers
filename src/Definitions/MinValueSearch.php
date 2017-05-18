<?php

namespace Laravel5Helpers\Definitions;

class MinValueSearch
{
    public $fieldName;

    public $minValue;

    public function __construct($fieldName, $minValue)
    {
        $this->fieldName = $fieldName;

        $this->minValue = $minValue;
    }
}
