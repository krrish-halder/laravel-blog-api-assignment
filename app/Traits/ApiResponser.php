<?php

namespace App\Traits;

trait ApiResponser
{
    protected function successResponse($message, $data = null, $code = 200)
    {
        $response = [
            'status' => 1,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    protected function errorResponse($message, $code = 400, $errors = null)
    {
        $response = [
            'status' => 0,
            'message' => $message,
        ];

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
