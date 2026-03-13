<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\UserFile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class FileService
{
    private const DISK = 'local';

    private const DIR_PREFIX = 'user-files';

    public function store(
        User $user,
        UploadedFile $file,
        ?string $ip = null,
        ?string $userAgent = null
    ): UserFile {
        $safeName = basename($file->getClientOriginalName());
        $storedName = Str::uuid()->toString().'_'.$safeName;
        $relativeDir = self::DIR_PREFIX.'/'.$user->id;
        $path = $file->storeAs($relativeDir, $storedName, self::DISK);

        $record = UserFile::query()->create([
            'user_id' => $user->id,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize() ?: 0,
        ]);

        app(UserActionService::class)->log(
            $user,
            'file_upload',
            'Загружен файл: '.$record->original_name,
            ['user_file_id' => $record->id],
            $ip,
            $userAgent
        );

        return $record;
    }

    /**
     * @return LengthAwarePaginator<int, UserFile>
     */
    public function paginateForUser(User $user, int $perPage): LengthAwarePaginator
    {
        $perPage = min(max($perPage, 1), 100);

        return $user->userFiles()
            ->latest('id')
            ->paginate($perPage);
    }

    public function downloadStream(UserFile $userFile): StreamedResponse
    {
        if (! Storage::disk(self::DISK)->exists($userFile->path)) {
            abort(404, 'Файл не найден на диске');
        }

        return Storage::disk(self::DISK)->download(
            $userFile->path,
            $userFile->original_name,
            ['Content-Type' => $userFile->mime_type ?: 'application/octet-stream']
        );
    }
}
