<?php

namespace App\Traits;

use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

trait ApiExceptionHandler
{
    public static function handle($exceptions)
    {
        // Validation errors
        $exceptions->render(function (ValidationException $e, $request) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        });

        // Not authenticated
        $exceptions->render(function (AuthenticationException $e, $request) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access. Please log in.',
            ], 401);
        });

        // Not authorized
        $exceptions->render(function (AuthorizationException $e, $request) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to perform this action.',
            ], 403);
        });

        // Model not found
        $exceptions->render(function (ModelNotFoundException $e, $request) {
            return response()->json([
                'status' => false,
                'message' => 'Resource not found.',
            ], 404);
        });

        // Route not found
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            return response()->json([
                'status' => false,
                'message' => 'API route not found.',
            ], 404);
        });

        // Method not allowed (e.g., GET instead of POST)
        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
            return response()->json([
                'status' => false,
                'message' => 'HTTP method not allowed.',
            ], 405);
        });
    }
}
