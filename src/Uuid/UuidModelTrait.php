<?php
namespace Laravel5Helpers\Uuid;

use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait UuidModelTrait
{
    public static function bootUuidModelTrait()
    {
        static::creating(function ($model) {
            $model->uuid = Uuid::uuid4()->toString();
        });

        static::saving(function ($model) {
            $originalUuid = $model->getOriginal('uuid');

            $originalUuid = empty($originalUuid) === true ? Uuid::uuid4()->toString() : $originalUuid;

            if ($originalUuid !== $model->uuid) {
                $model->uuid = $originalUuid;
            }
        });
    }

    /**
     * @param $query
     * @param $uuid
     * @param bool $first
     * @return mixed
     */
    public function scopeUuid($query, $uuid, $first = true)
    {
        if (!is_string($uuid) || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) !== 1)) {
            throw (new ModelNotFoundException)->setModel(get_class($this));
        }

        $search = $query->where('uuid', $uuid);

        return $first ? $search->firstOrFail() : $search;
    }

    /**
     * @param $query
     * @param $idOrUuid
     * @param bool $first
     * @return mixed
     */
    public function scopeIdOrUuId($query, $idOrUuid, $first = true)
    {
        if (!is_string($idOrUuid) && !is_numeric($idOrUuid)) {
            throw (new ModelNotFoundException)->setModel(get_class($this));
        }

        if (!is_string($idOrUuid) && !is_numeric($idOrUuid)) {
            throw (new ModelNotFoundException)->setModel(get_class($this));
        }

        if (preg_match('/^([0-9]+|[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12})$/', $idOrUuid)) {
            $search = $query->where('uuid', $idOrUuid);
        } else {
            $search = $query->where('id', $idOrUuid);
        }

        return $first ? $search->firstOrFail() : $search;
    }
}
