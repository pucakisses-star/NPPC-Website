<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * A curated link shown in the dashboard ticker and newswire, managed separately
 * from Articles. Appears when published_at is set and in the past. When given
 * coordinates it is also plotted as an event marker on the dashboard map.
 *
 * @property string                          $title
 * @property string                          $url
 * @property string|null                     $source
 * @property float|null                      $lat
 * @property float|null                      $lng
 * @property string|null                     $location_label
 * @property string|null                     $category
 * @property \Illuminate\Support\Carbon|null $published_at
 */
final class DashboardLink extends Model {
    /** Event categories — the key is stored, the value is the display label. */
    public const CATEGORIES = [
        'protest'     => 'Protest',
        'arrest'      => 'Arrest',
        'prosecution' => 'Prosecution',
        'other'       => 'Other',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function scopePublished(Builder $query): Builder {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /** Published links that also carry coordinates, for the map. */
    public function scopeOnMap(Builder $query): Builder {
        return $query->published()->whereNotNull('lat')->whereNotNull('lng');
    }
}
