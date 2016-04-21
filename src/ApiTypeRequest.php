<?php

namespace Laravel5Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class ApiTypeRequest extends Request
{
    public function response(array $errors)
    {
        $firstError = '';

        foreach ($errors as $key => $val) {
            $firstError = $val[0];
        }

        return new JsonResponse($firstError, 500);
    }
}
