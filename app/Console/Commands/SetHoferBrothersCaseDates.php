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
 * conscientious objectors who died in military custody during World War I:
 *
 *   Joseph Hofer  — incarcerated May 25, 1918; died (released) November 29, 1918
 *   Michael Hofer — incarcerated May 25, 1918; died (released) December 2, 1918
 *
 * May 25, 1918 is the date the Rockport Colony (South Dakota) draftees were
 * ordered to report to Camp Lewis, Washington, where they refused military
 * orders. Their imprisonment ran Camp Lewis → Alcatraz → the Fort Leavenworth
 * Disciplinary Barracks, where both died. The release date is set to each
 * man's death date (per instruction), and the prisoner death_date is kept
 * consistent with it.
 *
 * Updates the existing prisoner records (matched by slug); updates their first
 * case or creates one if absent. Institution/charges/sentence are only filled
 * when empty so existing detail is preserved. Idempotent.
 */
final class SetHoferBrothersCaseDates extends Command
{
    protected $signature = 'prisoners:set-hofer-case-dates';

    protected $description = 'Set May 25, 1918 incarceration and death-date release for Joseph and Michael Hofer';

    /** slug => [death year, month, day] */
    private const MEN = [
        'joseph-hofer' => [1918, 11, 29],
        'michael-hofer' => [1918, 12, 2],
    ];

    public function handle(): int
    {
        $leavenworth = Institution::firstOrCreate(
            ['name' => 'Fort Leavenworth Disciplinary Barracks'],
            ['city' => 'Fort Leavenworth', 'state' => 'Kansas']
        )->id;

        foreach (self::MEN as $slug => [$dy, $dm, $dd]) {
            $prisoner = Prisoner::withUnderReview()->where('slug', $slug)->first();
            if (! $prisoner) {
                $this->warn("Not found: {$slug} — skipped.");

                continue;
            }

            DB::transaction(function () use ($prisoner, $slug, $dy, $dm, $dd, $leavenworth) {
                // Keep death date consistent with the release date.
                $prisoner->setPartialDate('death_date', $dy, $dm, $dd);
                $prisoner->save();

                $case = $prisoner->cases()->first() ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->prisoner_id = $prisoner->id;

                $case->setPartialDate('incarceration_date', 1918, 5, 25);
                $case->setPartialDate('release_date', $dy, $dm, $dd);

                $start = Carbon::create(1918, 5, 25);
                $end = Carbon::create($dy, $dm, $dd);
                $case->imprisoned_for_days = $start->diffInDays($end);

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
                        .'and mistreated), then transferred to the Fort Leavenworth Disciplinary Barracks in November 1918, '
                        .'where he died of pneumonia worsened by his treatment.';
                }
                $case->save();
            });

            $this->info("Set dates for {$prisoner->name}: incarcerated 1918-05-25, released {$dy}-".sprintf('%02d-%02d', $dm, $dd).".");
        }

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Done.');

        return self::SUCCESS;
    }
}
