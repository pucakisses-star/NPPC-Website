<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * @property string      $name
 * @property int         $sort_order
 * @property string|null $photo
 * @property string|null $description
 * @property int|null    $years_in_prison
 * @property string|null $state
 * @property string|null $address
 * @property float|null  $lat
 * @property float|null  $lng
 * @property string|null $first_name
 * @property string|null $middle_name
 * @property string|null $last_name
 * @property string|null $aka
 * @property string|null $race
 * @property string|null $gender
 * @property string|null $birthdate
 * @property string|null $death_date
 * @property int|null    $age
 * @property array|null  $ideologies
 * @property string|null $era
 * @property array|null  $affiliation
 * @property bool        $in_custody
 * @property bool        $released
 * @property bool        $in_exile
 * @property bool        $currently_in_exile
 * @property bool        $imprisoned_or_exiled
 * @property string|null $website
 * @property string|null $twitter
 * @property string|null $facebook
 * @property string|null $instagram
 * @property string|null $inmate_number
 * @property bool        $awaiting_trial
 */
final class Prisoner extends Model {
    protected $appends = ['url', 'photo_url', 'imprisoned_or_exiled'];

    protected $casts = [
        'ideologies'          => 'array',
        'affiliation'         => 'array',
        'birthdate'           => 'date',
        'death_date'          => 'date',
        'in_custody'          => 'boolean',
        'released'            => 'boolean',
        'in_exile'            => 'boolean',
        'currently_in_exile'  => 'boolean',
        'awaiting_trial'      => 'boolean',
    ];

    /**
     * imprisoned_or_exiled is no longer a stored column — it's
     * derived from in_custody and currently_in_exile so it can never
     * desync. This accessor is what serializes into ->toArray() /
     * ->toJson() and what API consumers see.
     */
    public function getImprisonedOrExiledAttribute(): bool
    {
        return (bool) ($this->in_custody || $this->currently_in_exile);
    }

    /**
     * Silently swallow legacy code that still tries to set
     * imprisoned_or_exiled (old seeders, the artisan prisoner:add
     * command, the Airtable importer). The value is derived from
     * in_custody / currently_in_exile and recomputed on every read.
     */
    public function setImprisonedOrExiledAttribute($value): void
    {
        // intentionally no-op
    }

    public static function booted(): void {
        parent::booted();

        static::creating(function ($model) {
            if (! $model->slug && $model->name) {
                $model->slug = self::generateUniqueSlug($model->name, $model->middle_name, $model->aka);
            }
            // Auto-assign sort_order = MAX(sort_order) + 1 so brand-new
            // prisoners land at the bottom of the list instead of all
            // collapsing to the default 0.
            if ((int) ($model->sort_order ?? 0) === 0) {
                $maxOrder = (int) self::query()->max('sort_order');
                $model->sort_order = $maxOrder + 1;
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('name') && $model->name) {
                $model->slug = self::generateUniqueSlug($model->name, $model->middle_name, $model->aka, $model->id);
            }
        });

        static::saving(function ($model) {
            if ($model->birthdate) {
                $end = $model->death_date ?? \Carbon\Carbon::now();
                $birth = $model->birthdate instanceof \Carbon\Carbon
                    ? $model->birthdate
                    : \Carbon\Carbon::parse($model->birthdate);
                $endC = $end instanceof \Carbon\Carbon ? $end : \Carbon\Carbon::parse($end);
                $model->attributes['age'] = (int) $birth->diffInYears($endC);
            }

            // A prisoner with a death_date is by definition no longer
            // in custody and no longer currently in exile, and
            // "released" should be true so they don't show up under
            // "active" filters. Force the flags whenever death_date
            // is present. (imprisoned_or_exiled is derived from
            // in_custody/currently_in_exile via the accessor and
            // doesn't need to be touched here.)
            if (! empty($model->death_date)) {
                $model->attributes['released']           = 1;
                $model->attributes['in_custody']         = 0;
                $model->attributes['currently_in_exile'] = 0;
            }

            // If the address is non-empty and lat/lng aren't set,
            // attempt a Mapbox geocode lookup. This populates the
            // map markers automatically as admins fill in addresses.
            // Manual lat/lng overrides are preserved — the geocoder
            // only runs when both are blank, and only when an
            // address is present.
            if (! empty(trim((string) $model->address))
                && (empty($model->lat) || empty($model->lng))) {
                try {
                    $coords = app(\App\Services\MapboxGeocoder::class)->geocode((string) $model->address);
                    if ($coords) {
                        if (empty($model->lat)) $model->attributes['lat'] = $coords[0];
                        if (empty($model->lng)) $model->attributes['lng'] = $coords[1];
                    }
                } catch (\Throwable $e) {
                    // Never let geocoding failure block a save.
                    \Illuminate\Support\Facades\Log::warning('Geocode-on-save skipped: ' . $e->getMessage());
                }
            }
        });
    }

    private static function generateUniqueSlug(string $name, ?string $middleName = null, ?string $aka = null, ?string $excludeId = null): string {
        $baseSlug = \Illuminate\Support\Str::slug($name);

        $query = self::where('slug', $baseSlug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if (! $query->exists()) {
            return $baseSlug;
        }

        // Try with middle name
        if ($middleName) {
            $parts = explode(' ', $name);
            $first = $parts[0] ?? '';
            $last = end($parts);
            $withMiddle = \Illuminate\Support\Str::slug($first.' '.$middleName.' '.$last);

            $query2 = self::where('slug', $withMiddle);
            if ($excludeId) {
                $query2->where('id', '!=', $excludeId);
            }
            if (! $query2->exists()) {
                return $withMiddle;
            }
        }

        // Append number
        $counter = 2;
        while (true) {
            $slug = $baseSlug.'-'.$counter;
            $query3 = self::where('slug', $slug);
            if ($excludeId) {
                $query3->where('id', '!=', $excludeId);
            }
            if (! $query3->exists()) {
                return $slug;
            }
            $counter++;
        }
    }

    public function getUrlAttribute(): string {
        return '/prisoner/'.($this->slug ?: $this->id);
    }

    public function getAgeAttribute($value): ?int {
        if (! $this->birthdate) {
            return $value !== null ? (int) $value : null;
        }

        $end = $this->death_date ?? \Carbon\Carbon::now();

        return (int) $this->birthdate->diffInYears($end);
    }

    /**
     * Return every calendar year (as an int) the prisoner spent any
     * portion of incarcerated, derived from each case's start and end
     * dates. Used by the Vue stats chart and by the admin display.
     *
     * Falls back through start = incarceration_date → arrest_date →
     * sentenced_date and end = release_date → death_in_custody_date →
     * today (if still in custody).
     */
    public function getIncarcerationYearsArray(): array {
        $years = [];

        foreach ($this->cases as $case) {
            $start = $case->incarceration_date ?? $case->arrest_date ?? $case->sentenced_date;
            if (! $start) {
                continue;
            }

            $end = $case->release_date ?? $case->death_in_custody_date ?? \Carbon\Carbon::now();

            $startYear = (int) $start->format('Y');
            $endYear   = (int) $end->format('Y');

            for ($y = $startYear; $y <= $endYear; $y++) {
                $years[$y] = $y;
            }
        }

        ksort($years);

        return array_values($years);
    }

    /**
     * Override the stored integer years_in_prison so reads always
     * return the array of every year incarcerated. Falls back to the
     * stored integer (cast to a single-element array) if no cases have
     * date information.
     */
    public function getYearsInPrisonAttribute($value): array {
        $computed = $this->getIncarcerationYearsArray();
        if ($computed) {
            return $computed;
        }
        if ($value !== null && $value > 0) {
            return [(int) $value];
        }
        return [];
    }

    public function cases(): HasMany {
        return $this->hasMany(PrisonerCase::class);
    }

    public function podcastEpisodes(): HasMany {
        return $this->hasMany(PodcastEpisode::class);
    }

    public function calendarEntries(): HasMany {
        return $this->hasMany(CalendarEntry::class);
    }

    public function getPhotoUrlAttribute(): ?string {
        if (! $this->photo) {
            return null;
        }

        return Storage::url($this->photo);
    }
}
