<?php

namespace Laravel5Helpers\Repositories;

use Laravel5Helpers\Exceptions\NotFoundException;
use Laravel5Helpers\Exceptions\ResourceSaveError;
use Laravel5Helpers\Exceptions\ValidationError;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Laravel5Helpers\Definitions\Definition;

abstract class Repository
{
    protected $model;

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
        } catch (ValidationError $exception) {
            throw new ResourceSaveError($exception->getMessage());
        }
    }

    /**
     * @param Definition $definition
     * @param $resourceId
     * @return mixed
     * @throws NotFoundException
     * @throws ResourceSaveError
     */
    public function editResource(Definition $definition, $resourceId)
    {
        try {
            return $this->editModel($definition, $resourceId);
        } catch (\PDOException $exception) {
            throw new NotFoundException($this->getModelShortName());
        } catch (QueryException $exception) {
            throw new NotFoundException($this->getModelShortName());
        } catch (ValidationError $exception) {
            throw new NotFoundException($this->getModelShortName());
        } catch (ModelNotFoundException $exception) {
            throw new NotFoundException($this->getModelShortName());
        }
    }

    /**
     * @param $resourceId
     * @throws NotFoundException
     */
    public function deleteResource($resourceId)
    {
        try {
            $collection = $this->getCollectionById($resourceId);
            return $collection->delete();
        } catch (\PDOException $exception) {
            throw new NotFoundException($this->getModelShortName());
        } catch (QueryException $exception) {
            throw new NotFoundException($this->getModelShortName());
        } catch (ModelNotFoundException $exception) {
            throw new NotFoundException($this->getModelShortName());
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

    /**
     * @param $resourceId
     * @return mixed
     * @throws NotFoundException
     */
    private function getCollectionById($resourceId)
    {
        $collection = $this->getModel()->idOrUuId($resourceId);

        return $collection;
    }

    private function getModelShortName()
    {
        return (new \ReflectionClass($this->getModel()))->getShortName();
    }
}
