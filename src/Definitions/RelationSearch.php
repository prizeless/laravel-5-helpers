<?php

namespace Laravel5Helpers\Definitions;

class RelationSearch
{
    public $column;

    public $value;

    public $relation;

    public $operator;

    public function __construct($column, $value, $relation, $operator = '=')
    {
        $this->column = $column;

        $this->value = $value;

        $this->relation = $relation;

        $this->operator = $operator;
    }
}
