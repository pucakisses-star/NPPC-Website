<?php

namespace App\Models;

use App\Models\Concerns\HasPartialDates;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $prisoner_id
 * @property string|null $institution_id
 * @property string|null $charges
 * @property string|null $arrest_date
 * @property string|null $indicted
 * @property string|null $convicted
 * @property string|null $plead
 * @property string|null $sentenced_date
 * @property string|null $incarceration_date
 * @property string|null $release_date
 * @property string|null $death_in_custody_date
 * @property string|null $in_exile_since
 * @property string|null $end_of_exile
 * @property string|null $prosecutor
 * @property string|null $judge
 * @property string|null $sentence
 * @property int|null $imprisoned_for_days
 * @property int|null $in_exile_for_days
 */
final class PrisonerCase extends Model
{
    use HasPartialDates;

    protected $table = 'prisoner_cases';

    protected $casts = [
        'arrest_date' => 'date',
        'sentenced_date' => 'date',
        'incarceration_date' => 'date',
        'release_date' => 'date',
        'death_in_custody_date' => 'date',
        'in_exile_since' => 'date',
        'end_of_exile' => 'date',
        'date_precision' => 'array',
    ];

    public function partialDateFields(): array
    {
        return [
            'arrest_date', 'sentenced_date', 'incarceration_date', 'release_date',
            'death_in_custody_date', 'in_exile_since', 'end_of_exile',
        ];
    }

    public static function booted(): void
    {
        parent::booted();

        self::saving(function (self $case) {
            // A death in custody ends the incarceration on that date, so the
            // release date is set to the same day (this also makes
            // imprisoned_for_days, computed below, stop at death).
            if ($case->death_in_custody_date) {
                $case->release_date = $case->death_in_custody_date;
                $case->mirrorDatePrecision('death_in_custody_date', 'release_date');
            }

            // Auto-derive in_exile_since from release_date when the prisoner
            // is flagged as exiled but the case row has no in_exile_since
            // explicitly set. The common path: a defendant is held in
            // immigration custody, then released *into* exile (deported
            // or self-deported); release_date and in_exile_since are the
            // same day. For prisoners with a documented gap between
            // release and exile (e.g. bail-jumpers like Bill Haywood),
            // in_exile_since should be set explicitly and this branch
            // does nothing because the field is non-null.
            if (! $case->in_exile_since && $case->release_date) {
                $prisoner = $case->prisoner;
                if ($prisoner && ($prisoner->in_exile || $prisoner->currently_in_exile)) {
                    $case->in_exile_since = $case->release_date;
                    $case->mirrorDatePrecision('release_date', 'in_exile_since');
                }
            }

            if ($case->incarceration_date && $case->release_date) {
                $case->imprisoned_for_days = (int) Carbon::parse($case->incarceration_date)
                    ->diffInDays(Carbon::parse($case->release_date));
            } elseif ($case->incarceration_date && ! $case->release_date) {
                // No release date recorded. Only count up to today when the
                // prisoner is actually still detained (in custody or awaiting
                // trial); a released prisoner whose release date was never
                // recorded has an unknown end, so leave it null rather than
                // inflating "time served" all the way to the present. Mirrors
                // the stats-chart logic in Prisoner::activeYears().
                $prisoner = $case->prisoner;
                $stillDetained = $prisoner && ($prisoner->in_custody || $prisoner->awaiting_trial);
                $case->imprisoned_for_days = $stillDetained
                    ? (int) Carbon::parse($case->incarceration_date)->diffInDays(Carbon::today())
                    : null;
            } else {
                $case->imprisoned_for_days = null;
            }

            if ($case->in_exile_since && $case->end_of_exile) {
                $case->in_exile_for_days = (int) Carbon::parse($case->in_exile_since)
                    ->diffInDays(Carbon::parse($case->end_of_exile));
            } elseif ($case->in_exile_since && ! $case->end_of_exile) {
                $case->in_exile_for_days = (int) Carbon::parse($case->in_exile_since)
                    ->diffInDays(Carbon::today());
            } else {
                $case->in_exile_for_days = null;
            }
        });
    }

    public function prisoner(): BelongsTo
    {
        return $this->belongsTo(Prisoner::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
}
