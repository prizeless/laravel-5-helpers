<?php

namespace Laravel5Helpers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

abstract class ApiTypeRequest extends FormRequest
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
