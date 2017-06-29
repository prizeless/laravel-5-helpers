<?php

namespace Laravel5Helpers\Repositories;

use Laravel5Helpers\Definitions\ResultOrder;
use Laravel5Helpers\Exceptions\NotFoundException;
use Laravel5Helpers\Exceptions\ResourceDeleteError;
use Laravel5Helpers\Exceptions\ResourceGetError;
use Laravel5Helpers\Exceptions\ResourceSaveError;
use Laravel5Helpers\Exceptions\ResourceUpdateError;
use Laravel5Helpers\Exceptions\ValidationError;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Laravel5Helpers\Definitions\Definition;
use const null;

abstract class Repository
{
    protected $model;

    protected $relations = [];

    protected $pageSize = 15;

    protected $order = null;

    const ORDER_ASC = 'ASC';

    const ORDER_DESC = 'DESC';

    /**
     * @param Definition $definition
     * @return mixed
     * @throws ResourceSaveError
     */
    public function createResource(Definition $definition)
    {
        try {
            return $this->saveModel($definition);
        } catch (QueryException $exception) {
            throw new ResourceSaveError;
        } catch (\PDOException $exception) {
            throw new ResourceSaveError;
        }
    }

    public function getPaginated()
    {
        try {
            $query = $this->getModel();

            if (empty($this->order) === false) {
                return $query->orderBy($this->order->field, $this->order->direction)->paginate($this->pageSize);
            }

            $query->paginate($this->pageSize);
        } catch (QueryException $exception) {
            throw new ResourceGetError($this->getModelShortName());
        } catch (\PDOException $exception) {
            throw new ResourceGetError($this->getModelShortName());
        }
    }


    /**
     * @param Definition $definition
     * @param $resourceId
     * @return mixed
     * @throws NotFoundException
     * @throws ResourceUpdateError
     */
    public function editResource(Definition $definition, $resourceId)
    {
        try {
            return $this->editModel($definition, $resourceId);
        } catch (\PDOException $exception) {
            throw new ResourceUpdateError($this->getModelShortName());
        } catch (QueryException $exception) {
            throw new ResourceUpdateError($this->getModelShortName());
        } catch (ModelNotFoundException $exception) {
            throw new NotFoundException($this->getModelShortName());
        }
    }

    /**
     * @param $resourceId
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
        } catch (\PDOException $exception) {
            throw new ResourceDeleteError($this->getModelShortName());
        } catch (QueryException $exception) {
            throw new ResourceDeleteError($this->getModelShortName());
        } catch (ModelNotFoundException $exception) {
            throw new NotFoundException($this->getModelShortName());
        }
    }

    private function deleteRecord($record)
    {
        if ($record->count() > 0) {
            $record->delete();
        }
    }

    /**
     * Delete all child relations like a BOSS
     * @param $resource
     */
    private function deleteRelatedRecords($resource)
    {
        if (empty($this->relations) === false) {
            foreach ($this->relations as $relation) {
                $this->tryDeleteRelations($resource, $relation);
            }
        }
    }

    private function tryDeleteRelations($resource, $relation)
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
     * @param Definition $definition
     * @param $resourceId
     * @return mixed
     * @throws NotFoundException
     * @throws ValidationError
     */
    private function editModel(Definition $definition, $resourceId)
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

    /**
     * @param $id
     * @return mixed
     * @throws NotFoundException
     */
    public function mustExist($id)
    {
        try {
            return $this->getModel()->findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            throw new NotFoundException($this->getModelShortName());
        }
    }

    /**
     * @param $id
     * @throws NotFoundException
     */
    public function getResource($id)
    {
        try {
            return $this->getModel()->idOrUuId($id);
        } catch (QueryException $exception) {
            throw new NotFoundException($this->getModelShortName());
        } catch (\PDOException $exception) {
            throw new NotFoundException($this->getModelShortName());
        }
    }

    /**
     * @param array $attributes
     * @return mixed
     * @throws NotFoundException
     */
    protected function findByAttributes(array $attributes)
    {
        $result = $this->getModel()->where($attributes)->first();

        if (empty($result)) {
            throw new NotFoundException($this->getModelShortName());
        }

        return $result;
    }

    abstract protected function getModel();

    /**
     * @param Definition $definition
     * @return mixed
     * @throws ValidationError
     */
    private function saveModel(Definition $definition)
    {
        $definition->validate();

        $fields = $definition->valuesToArray();
        $model = $this->getModel();

        foreach ($fields as $column => $value) {
            $model->{$column} = $value;
        }

        $model->save();

        return $model;
    }

    protected function getRelations()
    {
        if (empty($this->relations) === false) {
            return $this->getModel()->with($this->relations);
        }

        return $this->getModel();
    }

    public function addRelations(array $relations)
    {
        if (empty($relations) === false) {
            $this->relations = $relations;
        }

        return $this;
    }

    /**
     * @param $resourceId
     * @return mixed
     * @throws NotFoundException
     */
    private function getCollectionById($resourceId)
    {
        return $this->getModel()->idOrUuId($resourceId);
    }

    protected function getModelShortName()
    {
        return (new \ReflectionClass($this->getModel()))->getShortName();
    }

    public function setResultOrder($field, $direction = self::ORDER_ASC)
    {
        $this->order = new ResultOrder($field, $direction);

        return $this;
    }
}
