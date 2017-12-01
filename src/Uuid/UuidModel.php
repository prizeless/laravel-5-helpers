<?php

namespace Laravel5Helpers\Uuid;

use Illuminate\Database\Eloquent\Model;

class UuidModel extends Model
{
    use UuidModelTrait;

    protected $hidden = ['id'];

    public function scopeLike($query, $field, $value)
    {
        return $query->where($field, 'LIKE', "%$value%");
    }

    public function scopeORWhereWildCard($query, $field, $value)
    {
        return $query->orWhere($field, 'LIKE', "%$value%");
    }

    public function scopeWhereWildCard($query, $field, $value)
    {
        return $query->where($field, 'LIKE', "%$value%");
    }
}
