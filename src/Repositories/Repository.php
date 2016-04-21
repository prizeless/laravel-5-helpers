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
     * @param $userId
     * @return mixed
     * @throws NotFoundException
     * @throws ResourceSaveError
     */
    public function editResource(Definition $definition, $resourceId, $userId)
    {
        try {
            return $this->editModel($definition, $resourceId, $userId);
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
     * @param $userId
     * @throws NotFoundException
     */
    public function deleteResource($resourceId, $userId)
    {
        try {
            $collection = $this->getCollectionById($resourceId, $userId);
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
     * @param $userId
     * @return mixed
     * @throws NotFoundException
     * @throws ValidationError
     */
    private function editModel(Definition $definition, $resourceId, $userId)
    {
        $definition->validate();
        $collection = $this->getCollectionById($resourceId, $userId);

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
     * @param $userId
     * @throws NotFoundException
     */
    public function getResource($id, $userId)
    {
        try {
            $result = $this->getModel()->idOrUuId($id);

            if ($result->user_id !== $userId) {
                throw  new NotFoundException($this->getModelShortName());
            }

            return $result;
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
     * @param $userId
     * @return mixed
     * @throws NotFoundException
     */
    private function getCollectionById($resourceId, $userId)
    {
        $collection = $this->getModel()->idOrUuId($resourceId);

        if ($collection->user_id !== $userId) {
            throw new NotFoundException($this->getModelShortName());
        }
        return $collection;
    }

    private function getModelShortName()
    {
        return (new \ReflectionClass($this->getModel()))->getShortName();
    }
}
