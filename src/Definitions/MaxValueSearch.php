<?php

namespace Laravel5Helpers\Definitions;

class MaxValueSearch
{
    public $fieldName;

    public $maxValue;

    public function __construct($fieldName, $maxValue)
    {
        $this->fieldName = $fieldName;

        $this->maxValue = $maxValue;
    }
}
