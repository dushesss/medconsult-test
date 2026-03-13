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
    public function show(Request $request): JsonResponse
    {
        return ApiResponse::success(
            (new ProfileResource($request->user()))->resolve()
        );
    }

    public function update(UpdateProfileRequest $request, ProfileService $profileService): JsonResponse
    {
        $user = $profileService->update($request->user(), $request->validated());

        return ApiResponse::success(
            (new ProfileResource($user))->resolve(),
            'Profile updated'
        );
    }
}
