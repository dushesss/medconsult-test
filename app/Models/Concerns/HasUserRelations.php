<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\UserAction;
use App\Models\UserFile;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasUserRelations
{
    /**
     * @return HasMany<UserAction, $this>
     */
    public function userActions(): HasMany
    {
        return $this->hasMany(UserAction::class);
    }

    /**
     * @return HasMany<UserFile, $this>
     */
    public function userFiles(): HasMany
    {
        return $this->hasMany(UserFile::class);
    }
}
