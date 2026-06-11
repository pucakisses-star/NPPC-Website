<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string              $title
 * @property string              $slug
 * @property Collection<Article> $articles
 */
final class Category extends Model {
    use HasSlug;

    public function articles(): HasMany {
        return $this->hasMany(Article::class);
    }
}
