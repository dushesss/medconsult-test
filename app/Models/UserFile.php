<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

final class UserFile extends Model
{
    use BelongsToUser;
    protected $fillable = [
        'user_id',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];
}
