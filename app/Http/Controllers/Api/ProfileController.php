<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use App\Http\Responses\ApiResponse;
use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profileService
    ) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return ApiResponse::success(
            (new ProfileResource($user))->resolve()
        );
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->profileService->update(
            $request->user(),
            $request->validated(),
            $request->ip(),
            $request->userAgent()
        );

        return ApiResponse::success(
            (new ProfileResource($user))->resolve(),
            'Профиль обновлён'
        );
    }
}
