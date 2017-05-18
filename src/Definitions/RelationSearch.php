<?php

namespace Laravel5Helpers\Definitions;

class RelationSearch
{
    public $column;

    public $value;

    public $relation;

    public function __construct($column, $value, $relation)
    {
        $this->column = $column;

        $this->value = $value;

        $this->relation = $relation;
    }
}
