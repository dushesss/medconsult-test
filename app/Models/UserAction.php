<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

final class UserAction extends Model
{
    use BelongsToUser;
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'level',
        'description',
        'payload',
        'ip',
        'user_agent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
