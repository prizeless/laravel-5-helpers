<?php

namespace Laravel5Helpers\Repositories;

use Laravel5Helpers\Definitions\ResultOrder;
use Laravel5Helpers\Exceptions\NotFoundException;
use Laravel5Helpers\Exceptions\ResourceDeleteError;
use Laravel5Helpers\Exceptions\ResourceGetError;
use Laravel5Helpers\Exceptions\ResourceSaveError;
use Laravel5Helpers\Exceptions\ResourceUpdateError;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Laravel5Helpers\Definitions\Definition;
use PDOException;
use ReflectionClass;
use const null;

abstract class Repository
{
    const ORDER_ASC = 'ASC';
    protected $model;
    protected $relations = [];
    protected $relationCounts = [];
    protected $pageSize = 15;
    protected $order = null;

    /**
     * @param Definition $definition
     *
     * @return mixed
     * @throws ResourceSaveError
     */
    public function createResource(Definition $definition)
    {
        try {
            return $this->saveModel($definition);
        } catch (QueryException | PDOException $exception) {
            $this->logException($exception->getMessage());
            throw new ResourceSaveError($this->getModelShortName());
        }
    }

    protected function saveModel(Definition $definition)
    {
        $definition->validate();

        $fields = $definition->valuesToArray();
        $model  = $this->getModel();

        foreach ($fields as $column => $value) {
            $model->{$column} = $value;
        }

        $model->save();

        return $model;
    }

    abstract protected function getModel();

    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @return mixed
     * @throws ResourceGetError
     */
    public function getPaginated()
    {
        try {
            $query = $this->getModel();

            if (empty($this->relationCounts) === false) {
                $query = $query->withCount($this->relationCounts);
            }

            if (empty($this->order) === false) {
                return $query->orderBy($this->order->field, $this->order->direction)->paginate($this->pageSize);
            }

            return $query->paginate($this->pageSize);
        } catch (QueryException | PDOException $exception) {
            $this->logException($exception->getMessage());
            throw new ResourceGetError($this->getModelShortName());
        }
    }

    protected function getModelShortName()
    {
        return (new ReflectionClass($this->getModel()))->getShortName();
    }

    public function addRelationCount($count)
    {
        $this->relationCounts[] = $count;

        return $this;
    }

    /**
     * @param Definition $definition
     * @param $resourceId
     *
     * @return mixed
     * @throws NotFoundException
     * @throws ResourceUpdateError
     */
    public function editResource(Definition $definition, $resourceId)
    {
        try {
            return $this->editModel($definition, $resourceId);
        } catch (ModelNotFoundException | QueryException | PDOException $exception) {
            $this->logException($exception->getMessage());
            throw new NotFoundException($this->getModelShortName());
        }
    }

    protected function editModel(Definition $definition, $resourceId)
    {
        $definition->validate();
        $collection = $this->getCollectionById($resourceId);

        $fields = $definition->valuesToArray();
        foreach ($fields as $column => $value) {
            $collection->{$column} = $value;
        }
        $collection->save();

        return $collection;
    }

    protected function getCollectionById($resourceId)
    {
        return $this->getModel()->idOrUuId($resourceId);
    }

    /**
     * @param $resourceId
     *
     * @return mixed
     * @throws NotFoundException
     * @throws ResourceDeleteError
     */
    public function deleteResource($resourceId)
    {
        try {
            $collection = $this->getCollectionById($resourceId);
            $this->deleteRelatedRecords($collection);

            return $collection->delete();
        } catch (ModelNotFoundException | QueryException | PDOException $exception) {
            $this->logException($exception->getMessage());
            throw new NotFoundException($this->getModelShortName());
        }
    }

    /**
     * Delete all child relations like a BOSS
     *
     * @param $resource
     */
    protected function deleteRelatedRecords($resource)
    {
        if (empty($this->relations) === false) {
            foreach ($this->relations as $relation) {
                $this->tryDeleteRelations($resource, $relation);
            }
        }
    }

    protected function tryDeleteRelations($resource, $relation)
    {
        $collection = '\Illuminate\Database\Eloquent\Collection';
        if ($resource->{$relation} instanceof $collection) {
            foreach ($resource->{$relation} as $record) {
                $this->deleteRecord($record);
            }
        } else {
            $this->deleteRecord($resource->{$relation});
        }
    }

    /**
     * @param $record
     */
    protected function deleteRecord($record)
    {
        if (empty($record) === false && $record->count() > 0) {
            $record->delete();
        }
    }

    public function mustExist($id)
    {
        try {
            return $this->getModel()->findOrFail($id);
        } catch (ModelNotFoundException | QueryException | PDOException $exception) {
            $this->logException($exception->getMessage());
            throw new NotFoundException($this->getModelShortName());
        }
    }

    public function getResource($id)
    {
        try {
            return $this->getModel()->idOrUuId($id);
        } catch (QueryException | PDOException $exception) {
            $this->logException($exception->getMessage());
            throw new ResourceGetError($this->getModelShortName());
        }
    }

    public function addRelations(array $relations)
    {
        if (empty($relations) === false) {
            $this->relations = $relations;
        }

        return $this;
    }

    public function setResultOrder($field, $direction = self::ORDER_ASC)
    {
        $this->order = new ResultOrder($field, $direction);

        return $this;
    }

    protected function findByAttributes(array $attributes)
    {
        try {
            $model = $this->getModel();
            foreach ($attributes as $column => $value) {
                $model = $model->where($column, $value);
            }
            $result = $model->first();

            if (empty($result)) {
                throw new NotFoundException($this->getModelShortName());
            }

            return $result;
        } catch (QueryException | PDOException $exception) {
            $this->logException($exception->getMessage());
            throw new ResourceGetError($this->getModelShortName());
        }
    }

    protected function getRelations()
    {
        $model = $this->getModel();

        if (empty($this->relations) === false) {
            $model = $model->with($this->relations);
        }

        if (empty($this->relationCounts) === false) {
            $model = $model->withCount($this->relationCounts);
        }

        return $model;
    }

    protected function logException($error)
    {

    }
}
