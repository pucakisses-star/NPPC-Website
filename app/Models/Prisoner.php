<?php

namespace App\Models;

use App\Models\Concerns\HasPartialDates;
use App\Models\Scopes\NotUnderReviewScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property string $name
 * @property int $sort_order
 * @property string|null $photo
 * @property string|null $description
 * @property int|null $years_in_prison
 * @property string|null $state
 * @property string|null $address
 * @property float|null $lat
 * @property float|null $lng
 * @property string|null $first_name
 * @property string|null $middle_name
 * @property string|null $last_name
 * @property string|null $aka
 * @property string|null $race
 * @property string|null $gender
 * @property string|null $birthdate
 * @property string|null $death_date
 * @property int|null $age
 * @property array|null $ideologies
 * @property string|null $era
 * @property array|null $affiliation
 * @property bool $in_custody
 * @property bool $released
 * @property bool $in_exile
 * @property bool $currently_in_exile
 * @property bool $imprisoned_or_exiled
 * @property string|null $website
 * @property string|null $twitter
 * @property string|null $facebook
 * @property string|null $instagram
 * @property string|null $inmate_number
 * @property bool $awaiting_trial
 * @property bool $minor_case
 */
final class Prisoner extends Model
{
    use HasPartialDates;

    protected $appends = ['url', 'photo_url'];

    protected $casts = [
        'ideologies' => 'array',
        'affiliation' => 'array',
        'birthdate' => 'date',
        'death_date' => 'date',
        'date_precision' => 'array',
        'in_custody' => 'boolean',
        'released' => 'boolean',
        'in_exile' => 'boolean',
        'currently_in_exile' => 'boolean',
        'imprisoned_or_exiled' => 'boolean',
        'awaiting_trial' => 'boolean',
        'under_review' => 'boolean',
        'minor_case' => 'boolean',
    ];

    public function partialDateFields(): array
    {
        return ['birthdate', 'death_date'];
    }

    public static function booted(): void
    {
        parent::booted();

        self::addGlobalScope(new NotUnderReviewScope);

        self::creating(function ($model) {
            if (! $model->slug && $model->name) {
                $model->slug = self::generateUniqueSlug($model->name, $model->middle_name, $model->aka);
            }
        });

        self::updating(function ($model) {
            if ($model->isDirty('name') && $model->name) {
                $model->slug = self::generateUniqueSlug($model->name, $model->middle_name, $model->aka, $model->id);
            }
        });

        self::saving(function ($model) {
            if ($model->birthdate) {
                $end = $model->death_date ?? Carbon::now();
                $birth = $model->birthdate instanceof Carbon
                    ? $model->birthdate
                    : Carbon::parse($model->birthdate);
                $endC = $end instanceof Carbon ? $end : Carbon::parse($end);
                $age = (int) $birth->diffInYears($endC);
                $model->attributes['age'] = $age > 120 ? null : $age;
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

    private static function generateUniqueSlug(string $name, ?string $middleName = null, ?string $aka = null, ?string $excludeId = null): string
    {
        $baseSlug = Str::slug($name);

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
            $withMiddle = Str::slug($first.' '.$middleName.' '.$last);

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

    public function getUrlAttribute(): string
    {
        return '/prisoner/'.($this->slug ?: $this->id);
    }

    /** Convenience for admin queries that need to see under-review rows. */
    public static function withUnderReview(): Builder
    {
        return self::query()->withoutGlobalScope(NotUnderReviewScope::class);
    }

    public function getAgeAttribute($value): ?int
    {
        if (! $this->birthdate) {
            return $value !== null ? (int) $value : null;
        }

        $end = $this->death_date ?? Carbon::now();
        $age = (int) $this->birthdate->diffInYears($end);

        // A birthdate stored with an unknown/placeholder year (e.g. 1900)
        // produces an impossible age; suppress rather than display it.
        return $age > 120 ? null : $age;
    }

    /**
     * Return every calendar year (as an int) the prisoner spent any
     * portion of incarcerated, derived from each case's start and end
     * dates. Used by the Vue stats chart and by the admin display.
     *
     * Start is strictly the case's incarceration_date — cases without one are
     * skipped (no fall back to arrest_date or sentenced_date). End =
     * release_date → death_in_custody_date → the prisoner's death_date → today
     * (if still in custody).
     */
    public function getIncarcerationYearsArray(): array
    {
        $years = [];

        foreach ($this->cases as $case) {
            $start = $case->incarceration_date;
            if (! $start) {
                continue;
            }

            // End of this incarceration: an explicit release or death in
            // custody, else the prisoner's death date (they cannot be counted
            // as incarcerated past their death). If none of those is recorded,
            // only treat the incarceration as ongoing through today when the
            // prisoner is actually still detained (in custody or awaiting
            // trial). A released or exiled prisoner whose release date was
            // never recorded has an unknown end — counting them through the
            // present would wrongly inflate the recent years of the stats
            // chart (the "still in custody today" tail), so cap the range at
            // the documented incarceration year instead.
            $stillDetained = $this->in_custody || $this->awaiting_trial;
            $end = $case->release_date
                ?? $case->death_in_custody_date
                ?? ($this->death_date ? Carbon::parse($this->death_date) : null)
                ?? ($stillDetained ? Carbon::now() : $start);

            $startYear = (int) $start->format('Y');
            $endYear = (int) $end->format('Y');

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
    public function getYearsInPrisonAttribute($value): array
    {
        $computed = $this->getIncarcerationYearsArray();
        if ($computed) {
            return $computed;
        }
        if ($value !== null && $value > 0) {
            return [(int) $value];
        }

        return [];
    }

    public function cases(): HasMany
    {
        return $this->hasMany(PrisonerCase::class);
    }

    public function podcastEpisodes(): HasMany
    {
        return $this->hasMany(PodcastEpisode::class);
    }

    public function calendarEntries(): HasMany
    {
        return $this->hasMany(CalendarEntry::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo) {
            return null;
        }

        return Storage::url($this->photo);
    }
}
