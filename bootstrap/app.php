<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $exception, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }
            if ($exception instanceof ValidationException) {
                return response()->json(['message' => 'Validation failed.', 'errors' => $exception->errors()], 422);
            }
            if ($exception instanceof AuthenticationException) {
                return response()->json(['message' => 'Unauthenticated.', 'errors' => (object) []], 401);
            }
            if ($exception instanceof HttpExceptionInterface) {
                $status = $exception->getStatusCode();

                return response()->json(['message' => Response::$statusTexts[$status] ?? 'Request failed.', 'errors' => (object) []], $status, $exception->getHeaders());
            }
            if (! config('app.debug')) {
                return response()->json(['message' => 'Server error.', 'errors' => (object) []], 500);
            }

            return null;
        });
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
