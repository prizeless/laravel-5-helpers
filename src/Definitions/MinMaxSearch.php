<?php

namespace Laravel5Helpers\Definitions;

class MinMaxSearch
{
    public $fieldName;

    public $minValue;

    public $maxValue;

    public function __construct($fieldName, $minValue, $maxValue)
    {
        $this->fieldName = $fieldName;

        $this->minValue = $minValue;

        $this->maxValue = $maxValue;
    }
}
