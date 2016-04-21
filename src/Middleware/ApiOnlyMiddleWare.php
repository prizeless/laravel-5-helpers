<?php

namespace Laravel5Helpers\Middleware;
use Illuminate\Http\Request;

abstract class ApiOnlyMiddleWare
{
    protected $only = ['api/*',];

    protected function shouldPassThrough(Request $request)
    {
        foreach ($this->only as $only) {
            if ($only !== '/') {
                $only = trim($only, '/');
            }

            if ($request->is($only) === false) {
                return true;
            }
        }

        return false;
    }
}