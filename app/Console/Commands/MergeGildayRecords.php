<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Merges the two duplicate records for William "Lefty" Gilday — the Weather
 * Underground member who killed Boston Police Officer Walter Schroeder in the
 * September 23, 1970 Brighton bank robbery.
 *
 * Keeps `william-lefty-gilday` (which has the photo and inmate number W33537)
 * as canonical, folds in the duplicate `william-gilday`'s data (death date,
 * alias, the extra ideology, the bank/prison detail), fills his previously
 * empty murder case, then deletes the duplicate. Idempotent — if the duplicate
 * is already gone it just re-asserts the canonical fields.
 */
class MergeGildayRecords extends Command
{
    protected $signature = 'prisoners:merge-gilday-records';

    protected $description = 'Merge the duplicate William "Lefty" Gilday records into one and fill his case';

    public function handle(): int
    {
        DB::transaction(function () {
            $canon = Prisoner::withUnderReview()->where('slug', 'william-lefty-gilday')->first();
            if (! $canon) {
                $this->warn('Canonical william-lefty-gilday not found — nothing to do.');

                return;
            }
            $dup = Prisoner::withUnderReview()->where('slug', 'william-gilday')->first();

            // Merge fields into the canonical record.
            $canon->aka = 'Lefty Gilday';
            $canon->ideologies = ['Anti-War', 'Anti-imperialism'];
            $canon->description = str_replace('September 10, 2011', 'September 9, 2011', (string) $canon->description);
            $canon->setPartialDate('death_date', 2011, 9, 9);
            $canon->in_custody = false;
            $canon->released = true;
            $canon->save();

            // Fill his (currently absent) murder case.
            $shirley = Institution::firstOrCreate(['name' => 'MCI Shirley'], ['city' => 'Shirley', 'state' => 'Massachusetts']);
            $case = $canon->cases()->first() ?? new PrisonerCase(['prisoner_id' => $canon->id]);
            $case->fill([
                'prisoner_id' => $canon->id,
                'institution_id' => $shirley->id,
                'charges' => 'First-degree murder of Boston Police Officer Walter Schroeder, shot responding to the silent alarm during the September 23, 1970 robbery of the State Street Bank and Trust Company (Brighton branch) — a $26,585 bank expropriation by five radicals to fund the anti-war movement.',
                'convicted' => 'Yes — convicted of the murder of Officer Schroeder.',
                'sentence' => 'Sentenced to death; commuted to life imprisonment. He remained imprisoned for the rest of his life and died in custody at MCI Shirley on September 9, 2011, at age 82.',
                'death_in_custody_date' => '2011-09-09',
            ]);
            $case->setPartialDate('incarceration_date', 1970, 9); // captured Sept 1970
            $case->save();

            // Fold in the duplicate, then delete it.
            if ($dup && $dup->id !== $canon->id) {
                foreach ($dup->cases()->get() as $dc) {
                    if (trim((string) $dc->charges) !== '') {
                        $dc->prisoner_id = $canon->id;
                        $dc->save();
                    } else {
                        $dc->delete();
                    }
                }
                $dup->delete();
                $this->info('Deleted duplicate william-gilday and merged into william-lefty-gilday.');
            } else {
                $this->info('Duplicate already merged/absent; canonical record re-asserted.');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
