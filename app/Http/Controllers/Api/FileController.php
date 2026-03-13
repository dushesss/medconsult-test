<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreFileRequest;
use App\Http\Responses\ApiResponse;
use App\Models\UserFile;
use App\Services\FileService;
use App\Services\UserActionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class FileController extends Controller
{
    public function __construct(
        private readonly FileService $fileService,
        private readonly UserActionService $userActionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = (int) $request->query('per_page', 15);
        $paginator = $this->fileService->paginateForUser($user, $perPage);

        $items = $paginator->getCollection()->map(fn (UserFile $f) => [
            'id' => $f->id,
            'original_name' => $f->original_name,
            'mime_type' => $f->mime_type,
            'size' => $f->size,
            'created_at' => $f->created_at?->toIso8601String(),
        ]);

        return ApiResponse::success([
            'items' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function store(StoreFileRequest $request): JsonResponse
    {
        $file = $request->file('file');
        if ($file === null) {
            return ApiResponse::error('Файл не передан', null, 422);
        }

        try {
            $record = $this->fileService->store(
                $request->user(),
                $file,
                $request->ip(),
                $request->userAgent()
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), null, 422);
        }

        return ApiResponse::success([
            'id' => $record->id,
            'original_name' => $record->original_name,
            'mime_type' => $record->mime_type,
            'size' => $record->size,
            'created_at' => $record->created_at?->toIso8601String(),
        ], 'Файл загружен', 201);
    }

    public function show(Request $request, UserFile $userFile): JsonResponse|StreamedResponse
    {
        $this->authorize('view', $userFile);

        $this->userActionService->log(
            $request->user(),
            'file_download',
            $userFile->original_name,
            ['user_file_id' => $userFile->id],
            $request->ip(),
            $request->userAgent()
        );

        return $this->fileService->downloadStream($userFile);
    }

    public function destroy(Request $request, UserFile $userFile): JsonResponse
    {
        $this->authorize('delete', $userFile);

        $this->fileService->delete($userFile);

        $this->userActionService->log(
            $request->user(),
            'file_delete',
            $userFile->original_name,
            ['user_file_id' => $userFile->id],
            $request->ip(),
            $request->userAgent()
        );

        return ApiResponse::success(null, 'Файл удалён');
    }
}
