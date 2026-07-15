<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $name
 * @property string $slug
 * @property string $avatar
 * @property string $avatar_url
 * @property string $url
 */
final class Author extends Model {
    use HasSlug;

    public $timestamps = false;
    protected $appends = ['avatar_url', 'url'];

    public function articles(): HasMany {
        return $this->hasMany(Article::class);
    }

    public function getAvatarUrlAttribute(): ?string {
        if (! $this->avatar) {
            return null;
        }

        return '/storage/'.$this->avatar;
    }

    public function getUrlAttribute(): ?string {
        return $this->slug ? '/author/'.$this->slug : null;
    }
}
