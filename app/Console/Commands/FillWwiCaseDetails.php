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
 * Back-fills charges, sentence, incarceration/release dates, institution, and
 * (where a person died in custody) death date onto the cases of a set of World
 * War I–era political prisoners — but ONLY where that information is currently
 * missing. Existing values are never overwritten.
 *
 * Every person in database/data/wwi_case_details.json was verified to already
 * exist in the database (matched by slug), so nothing is created; the command
 * only fills gaps on their existing case (or creates a case if a record somehow
 * has none). Idempotent and safe to re-run.
 */
final class FillWwiCaseDetails extends Command
{
    protected $signature = 'prisoners:fill-wwi-case-details {--dry : Report what would change without writing}';

    protected $description = 'Fill missing charges/sentence/dates on WWI prisoners\' cases (never overwrites)';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry');
        $path = database_path('data/wwi_case_details.json');
        $rows = json_decode((string) file_get_contents($path), true);
        if (! is_array($rows)) {
            $this->error('Could not read '.$path);

            return self::FAILURE;
        }

        $touched = 0;

        foreach ($rows as $r) {
            $slug = $r['slug'];
            $prisoner = Prisoner::withUnderReview()->where('slug', $slug)->first();
            if (! $prisoner) {
                $this->warn("Not found (skipped): {$slug}");

                continue;
            }

            $filled = [];

            DB::transaction(function () use ($r, $prisoner, $dry, &$filled) {
                $case = $prisoner->cases()->first() ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->prisoner_id = $prisoner->id;

                if (! empty($r['charges']) && empty($case->charges)) {
                    $case->charges = $r['charges'];
                    $filled[] = 'charges';
                }
                if (! empty($r['sentence']) && empty($case->sentence)) {
                    $case->sentence = $r['sentence'];
                    $filled[] = 'sentence';
                }
                if (! empty($r['incarceration']) && empty($case->incarceration_date)) {
                    [$y, $m, $d] = array_pad($r['incarceration'], 3, null);
                    $case->setPartialDate('incarceration_date', $y, $m, $d);
                    $filled[] = 'incarceration_date';
                }
                if (! empty($r['release']) && empty($case->release_date)) {
                    [$y, $m, $d] = array_pad($r['release'], 3, null);
                    $case->setPartialDate('release_date', $y, $m, $d);
                    $filled[] = 'release_date';
                }
                if (! empty($r['institution']) && empty($case->institution_id)) {
                    $case->institution_id = Institution::firstOrCreate(
                        ['name' => $r['institution']],
                        ['city' => $r['institution_city'] ?? null, 'state' => $r['institution_state'] ?? null]
                    )->id;
                    $filled[] = 'institution';
                }

                // Day count only when both ends are known to the day and it's empty.
                if (empty($case->imprisoned_for_days)
                    && ! empty($r['incarceration']) && ! empty($r['release'])
                    && ($r['incarceration'][2] ?? null) && ($r['release'][2] ?? null)) {
                    $case->imprisoned_for_days = Carbon::create(...$r['incarceration'])
                        ->diffInDays(Carbon::create(...$r['release']));
                    $filled[] = 'imprisoned_for_days';
                }

                // Death date for those who died in custody.
                if (! empty($r['death']) && empty($prisoner->death_date)) {
                    [$y, $m, $d] = array_pad($r['death'], 3, null);
                    $prisoner->setPartialDate('death_date', $y, $m, $d);
                    $filled[] = 'death_date';
                }

                if (! $dry && $filled) {
                    $case->save();
                    if (in_array('death_date', $filled, true)) {
                        $prisoner->save();
                    }
                }
            });

            if ($filled) {
                $touched++;
                $verb = $dry ? 'would fill' : 'filled';
                $this->info("{$prisoner->name}: {$verb} ".implode(', ', $filled));
            } else {
                $this->line("{$prisoner->name}: already complete — no change");
            }
        }

        if (! $dry && $touched > 0) {
            Cache::forget(PrisonerApiController::cacheKey());
        }
        $this->info(($dry ? 'Would touch ' : 'Touched ')."{$touched} record(s).");

        return self::SUCCESS;
    }
}
