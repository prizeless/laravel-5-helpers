<?php

namespace Laravel5Helpers\Uuid;

use Illuminate\Database\Eloquent\Model;

class UuidModel extends Model
{
    use UuidModelTrait;

    protected $hidden = ['id'];
}