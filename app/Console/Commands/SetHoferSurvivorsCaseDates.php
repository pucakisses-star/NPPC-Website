<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Sets the case incarceration and release dates for the two Hutterite
 * conscientious objectors who SURVIVED military imprisonment during World War I
 * (the brothers-in-arms of Joseph and Michael Hofer, who died):
 *
 *   David Hofer — incarcerated May 25, 1918; released December 1918 (exact day
 *                 undocumented; freed immediately after his brother Michael's
 *                 death to accompany the two bodies home to the colony).
 *   Jacob Wipf  — incarcerated May 25, 1918; released April 13, 1919 (held four
 *                 months longer than the others).
 *
 * Because these two were released alive, only release_date is set — death_date
 * is deliberately left untouched. imprisoned_for_days is computed only when the
 * release day is known (Jacob). Institution/charges/sentence are filled only
 * when empty so existing detail is preserved. Idempotent; matched by slug.
 */
final class SetHoferSurvivorsCaseDates extends Command
{
    protected $signature = 'prisoners:set-hofer-survivors-case-dates';

    protected $description = 'Set May 25, 1918 incarceration and unique release dates for David Hofer and Jacob Wipf';

    /** slug => [release year, month, day|null, note] */
    private const MEN = [
        'david-hofer' => [1918, 12, null, 'Released in December 1918, soon after his brothers died, and accompanied their bodies home to the colony.'],
        'jacob-wipf' => [1919, 4, 13, 'Held four months longer than the others and finally released on April 13, 1919, after a spell in the hospital.'],
    ];

    public function handle(): int
    {
        $leavenworth = Institution::firstOrCreate(
            ['name' => 'Fort Leavenworth Disciplinary Barracks'],
            ['city' => 'Fort Leavenworth', 'state' => 'Kansas']
        )->id;

        foreach (self::MEN as $slug => [$ry, $rm, $rd, $note]) {
            $prisoner = Prisoner::withUnderReview()->where('slug', $slug)->first();
            if (! $prisoner) {
                $this->warn("Not found: {$slug} — skipped.");

                continue;
            }

            DB::transaction(function () use ($prisoner, $ry, $rm, $rd, $note, $leavenworth) {
                $case = $prisoner->cases()->first() ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->prisoner_id = $prisoner->id;

                $case->setPartialDate('incarceration_date', 1918, 5, 25);
                $case->setPartialDate('release_date', $ry, $rm, $rd);

                // Only assert a day count when the release day is known.
                if ($rd !== null) {
                    $case->imprisoned_for_days = Carbon::create(1918, 5, 25)->diffInDays(Carbon::create($ry, $rm, $rd));
                }

                if (empty($case->institution_id)) {
                    $case->institution_id = $leavenworth;
                }
                if (empty($case->charges)) {
                    $case->charges = 'Refusal of military service as a Hutterite conscientious objector (World War I). '
                        .'Ordered to report to Camp Lewis, Washington, on May 25, 1918, he and his brothers refused to '
                        .'put on the uniform or perform military duties.';
                }
                if (empty($case->convicted)) {
                    $case->convicted = 'Yes — court-martialed and sentenced to twenty years of hard labor.';
                }
                if (empty($case->sentence)) {
                    $case->sentence = 'Twenty years hard labor. Held at Camp Lewis, then Alcatraz (in solitary, chained '
                        .'and mistreated), then the Fort Leavenworth Disciplinary Barracks. '.$note;
                }
                $case->save();
            });

            $dstr = $rd !== null ? sprintf('%04d-%02d-%02d', $ry, $rm, $rd) : sprintf('%04d-%02d', $ry, $rm);
            $this->info("Set dates for {$prisoner->name}: incarcerated 1918-05-25, released {$dstr}.");
        }

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Done.');

        return self::SUCCESS;
    }
}
