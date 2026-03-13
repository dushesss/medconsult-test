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

    public function __construct(
        private readonly UserActionService $userActionService
    ) {}

    public function store(
        User $user,
        UploadedFile $file,
        ?string $ip = null,
        ?string $userAgent = null
    ): UserFile {
        $quota = (int) config('medconsult.user_files_quota_bytes', 0);
        if ($quota > 0) {
            $used = (int) $user->userFiles()->sum('size');
            $add = (int) $file->getSize();
            if ($used + $add > $quota) {
                throw new \RuntimeException('Превышена квота хранилища для пользователя');
            }
        }

        $originalName = $file->getClientOriginalName();
        $storedBase = Str::uuid()->toString();
        $relativeDir = self::DIR_PREFIX.'/'.$user->id;
        $path = $file->storeAs($relativeDir, $storedBase, self::DISK);

        $fullPath = Storage::disk(self::DISK)->path($path);
        $mime = $this->detectMime($fullPath) ?: 'application/octet-stream';

        $record = UserFile::query()->create([
            'user_id' => $user->id,
            'path' => $path,
            'original_name' => $originalName,
            'mime_type' => $mime,
            'size' => $file->getSize() ?: 0,
        ]);

        $this->userActionService->log(
            $user,
            'file_upload',
            'Загружен файл: '.$record->original_name,
            ['user_file_id' => $record->id],
            $ip,
            $userAgent
        );

        return $record;
    }

    public function delete(UserFile $userFile): void
    {
        if (Storage::disk(self::DISK)->exists($userFile->path)) {
            Storage::disk(self::DISK)->delete($userFile->path);
        }
        $userFile->delete();
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

    private function detectMime(string $absolutePath): ?string
    {
        if (! is_readable($absolutePath)) {
            return null;
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $type = $finfo->file($absolutePath);

        return is_string($type) && $type !== '' ? $type : null;
    }
}
