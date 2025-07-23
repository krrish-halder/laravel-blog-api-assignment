<?php

use Illuminate\Foundation\Application;
use App\Http\Middleware\HandleLocalization;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(HandleLocalization::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return new JsonResponse([
                    'status' => false,
                    'message' => 'Unauthorized access. Please provide a valid API token.',
                ], 401);
            }

            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access. Please log in.',
            ], 401);
        });
    })->create();
