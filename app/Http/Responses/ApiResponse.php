<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    public static function success(mixed $data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        $body = [
            'data' => $data,
            'message' => $message,
            'errors' => null,
        ];

        return response()->json($body, $status);
    }

    public static function error(?string $message = null, mixed $errors = null, int $status = 400): JsonResponse
    {
        $body = [
            'data' => null,
            'message' => $message,
            'errors' => $errors,
        ];

        return response()->json($body, $status);
    }
}
