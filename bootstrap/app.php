<?php

declare(strict_types=1);

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $apiError = static function (Request $request, string $message, mixed $errors, int $status): ?\Illuminate\Http\JsonResponse {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'data' => null,
                'message' => $message,
                'errors' => $errors,
            ], $status);
        };

        $exceptions->render(function (ValidationException $e, Request $request) use ($apiError) {
            return $apiError($request, 'Ошибка валидации', $e->errors(), 422);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($apiError) {
            return $apiError($request, 'Требуется авторизация', null, 401);
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) use ($apiError) {
            return $apiError($request, $e->getMessage() ?: 'Доступ запрещён', null, 403);
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) use ($apiError) {
            return $apiError($request, 'Ресурс не найден', null, 404);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($apiError) {
            return $apiError($request, 'Не найдено', null, $e->getStatusCode());
        });

        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request) use ($apiError) {
            return $apiError($request, 'Слишком много запросов', null, 429);
        });

        $exceptions->render(function (HttpException $e, Request $request) use ($apiError) {
            if ($e instanceof TooManyRequestsHttpException || $e instanceof NotFoundHttpException) {
                return null;
            }
            $status = $e->getStatusCode();
            if ($status >= 400 && $status < 500) {
                return $apiError($request, $e->getMessage() ?: 'Ошибка запроса', null, $status);
            }

            return null;
        });

        $exceptions->render(function (\Throwable $e, Request $request) use ($apiError) {
            if (! $request->is('api/*')) {
                return null;
            }
            if ($e instanceof ValidationException
                || $e instanceof AuthenticationException
                || $e instanceof AuthorizationException
                || $e instanceof ModelNotFoundException
                || $e instanceof NotFoundHttpException
                || $e instanceof TooManyRequestsHttpException
            ) {
                return null;
            }
            if ($e instanceof HttpException && $e->getStatusCode() < 500) {
                return null;
            }

            report($e);
            $msg = config('app.debug') ? $e->getMessage() : 'Внутренняя ошибка сервера';

            return $apiError($request, $msg, null, 500);
        });
    })->create();
