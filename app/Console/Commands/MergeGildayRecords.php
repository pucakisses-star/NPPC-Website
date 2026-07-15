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
            $canon->description = 'William "Lefty" Gilday was a minor league baseball player and anti-war activist who served as a member of Students for a Democratic Society (SDS) and the Weather Underground. On September 23, 1970, Gilday and other members were involved in a bank expropriation attempting to fund the anti-war movement which ended with a police officer being killed. Gilday was tried and found guilty for the killing of the Boston police officer. He was sentenced to death but his sentence was later reduced to life imprisonment. Gilday died in prison in 2011.';
            $canon->setPartialDate('death_date', 2011, 9, 9);
            $canon->in_custody = false;
            $canon->released = true;
            $canon->save();

            // Delete the duplicate record (and its cases) entirely — its single
            // case is just a duplicate of the same murder, so nothing to keep.
            if ($dup && $dup->id !== $canon->id) {
                $dup->cases()->delete();
                $dup->delete();
                $this->info('Deleted duplicate william-gilday.');
            }

            // Rebuild exactly one murder case. Delete any existing cases first so
            // re-runs (and the earlier bug that left two duplicate cases, which
            // made the API sum ~41 + ~41 = 82 years) collapse back to a single
            // ~41-year term (captured 1970 → died in custody 2011).
            $shirley = Institution::firstOrCreate(['name' => 'MCI Shirley'], ['city' => 'Shirley', 'state' => 'Massachusetts']);
            $canon->cases()->delete();
            $case = new PrisonerCase(['prisoner_id' => $canon->id]);
            $case->fill([
                'prisoner_id' => $canon->id,
                'institution_id' => $shirley->id,
                'charges' => 'First-degree murder of Boston Police Officer Walter Schroeder, shot responding to the silent alarm during the September 23, 1970 robbery of the State Street Bank and Trust Company (Brighton branch) — a $26,585 bank expropriation by five radicals to fund the anti-war movement.',
                'convicted' => 'Yes — convicted of the murder of Officer Schroeder.',
                'sentence' => 'Sentenced to death; commuted to life imprisonment. He remained imprisoned for the rest of his life and died in custody at MCI Shirley on September 9, 2011, at age 82.',
                'arrest_date' => '1970-09-28',        // captured after a 4-day manhunt
                'incarceration_date' => '1970-09-28',
                'death_in_custody_date' => '2011-09-09',
            ]);
            $case->save();
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
