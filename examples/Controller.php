<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Definitions\Definition;
use App\Repositories\Repository;
use App\Http\Requests\ApiTypeRequest;

class Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $result = $this->getRepository()->getModel()->get();

            return new JsonResponse($result);
        } catch (LaravelHelpersExceptions $exception) {

        } catch (AppException $exception) {

        }
    }

    public function store(Request $request)
    {
        try {
            $definition = $this->getDefinition($request);
            $result = $this->getRepository()->createResource($definition);

            return new JsonResponse($result);
        } catch (LaravelHelpersExceptions $exception) {

        } catch (AppException $exception) {

        }
    }


    public function show($id)
    {
        try {
            $result = $this->getRepository()->getResource($id);

            return new JsonResponse($result);
        } catch (LaravelHelpersExceptions $exception) {

        } catch (AppException $exception) {

        }
    }


    public function update(Request $request, $id)
    {
        try {
            $definition = $this->getDefinition($request);
            $this->getRepository()->editResource($definition, $id);

        } catch (LaravelHelpersExceptions $exception) {

        } catch (AppException $exception) {

        }
    }


    public function destroy($id)
    {
        try {
            $this->getRepository()->deleteResource($id);

        } catch (LaravelHelpersExceptions $exception) {

        } catch (AppException $exception) {

        }
    }

    protected function getDefinition(ApiTypeRequest $request)
    {
        if (empty($this->definition) === true) {
            $this->definition = new Definition($request->json()->all());
        }

        return $this->definition;
    }

    protected function getRepository()
    {
        if (empty($this->repository) === true) {
            $this->repository = new Repository;
        }

        return $this->repository;
    }
}
