<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserActionResource;
use App\Http\Responses\ApiResponse;
use App\Services\UserActionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserActionController extends Controller
{
    public function __construct(
        private readonly UserActionService $userActionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $includeTelemetry = filter_var($request->query('include_telemetry'), FILTER_VALIDATE_BOOLEAN);

        $paginator = $this->userActionService->paginateForUser(
            $user,
            (int) $request->query('per_page', 15),
            $includeTelemetry
        );

        return ApiResponse::success([
            'actions' => UserActionResource::collection($paginator->items())->resolve(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
