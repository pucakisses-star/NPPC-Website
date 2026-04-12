<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $name
 * @property string $avatar
 * @property string $avatar_url
 */
final class Author extends Model {
    public $timestamps = false;
    protected $appends = ['avatar_url'];

    public function articles(): HasMany {
        return $this->hasMany(Article::class);
    }

    public function getAvatarUrlAttribute(): ?string {
        if (! $this->avatar) {
            return null;
        }

        return '/storage/'.$this->avatar;
    }
}
