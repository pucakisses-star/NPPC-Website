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
    protected $appends = ['url', 'photo_url'];

    protected $casts = [
        'ideologies'          => 'array',
        'affiliation'         => 'array',
        'birthdate'           => 'date',
        'death_date'          => 'date',
        'in_custody'          => 'boolean',
        'released'            => 'boolean',
        'in_exile'            => 'boolean',
        'currently_in_exile'  => 'boolean',
        'imprisoned_or_exiled' => 'boolean',
        'awaiting_trial'      => 'boolean',
        'under_review'        => 'boolean',
    ];

    public static function booted(): void {
        parent::booted();

        static::addGlobalScope(new \App\Models\Scopes\NotUnderReviewScope);

        static::creating(function ($model) {
            if (! $model->slug && $model->name) {
                $model->slug = self::generateUniqueSlug($model->name, $model->middle_name, $model->aka);
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

            // Keep imprisoned_or_exiled in sync with the active-state
            // flags. This column is used by the public "currently
            // active" lists; if it desyncs from in_custody and
            // currently_in_exile, released prisoners can leak back into
            // those lists. Auto-derive on every save.
            $model->attributes['imprisoned_or_exiled'] =
                ($model->in_custody || $model->currently_in_exile) ? 1 : 0;
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

    /** Convenience for admin queries that need to see under-review rows. */
    public static function withUnderReview(): \Illuminate\Database\Eloquent\Builder {
        return self::query()->withoutGlobalScope(\App\Models\Scopes\NotUnderReviewScope::class);
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
