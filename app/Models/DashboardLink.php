<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * A curated link shown in the dashboard ticker and newswire, managed separately
 * from Articles. Appears when published_at is set and in the past.
 *
 * @property string                          $title
 * @property string                          $url
 * @property string|null                     $source
 * @property \Illuminate\Support\Carbon|null $published_at
 */
final class DashboardLink extends Model {
    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function scopePublished(Builder $query): Builder {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
