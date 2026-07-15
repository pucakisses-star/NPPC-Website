<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Fills in dates and the year of birth for Father William J. "Bix" Bichsel SJ
 * (Disarm Now Plowshares, Naval Base Kitsap-Bangor, 2009). Sets his birth year
 * (1928 — he was 81 at the 2009 action and died in 2015 at 86; the register's
 * "age 98" is erroneous) and the case timeline: arrested Nov 2, 2009; indicted
 * Sept 2010; convicted Dec 13, 2010; sentenced and taken into custody Mar 28,
 * 2011; released from the ~3-month prison term about June 28, 2011, followed by
 * home detention. Updates the existing record in place (the peace-activists
 * importer skips names that already exist), matched by name/aka. Idempotent.
 */
final class SetBichselCase extends Command
{
    protected $signature = 'prisoners:set-bichsel-case';

    protected $description = 'Set William "Bix" Bichsel birth year and Disarm Now Plowshares case dates';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()
            ->where('name', 'William Bichsel')
            ->orWhere('aka', 'like', '%Bichsel%')
            ->first();

        if (! $prisoner) {
            $this->warn('William Bichsel not found — run prisoners:add-peace-activists first.');

            return self::SUCCESS;
        }

        // Year of birth (1928). Only widen precision if none is set finer.
        $prisoner->setPartialDate('birthdate', 1928);
        $prisoner->save();

        // The Disarm Now case: match by its charges, else the first case, else create.
        $case = $prisoner->cases()
            ->where(function ($q) {
                $q->where('charges', 'like', '%Disarm Now%')
                    ->orWhere('charges', 'like', '%Kitsap%')
                    ->orWhere('charges', 'like', '%Strategic Weapons%')
                    ->orWhere('charges', 'like', '%naval installation%');
            })
            ->first()
            ?? $prisoner->cases()->first()
            ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);

        $case->prisoner_id = $prisoner->id;
        if (! $case->institution_id) {
            $case->institution_id = Institution::firstOrCreate(
                ['name' => 'SeaTac Federal Detention Center'],
                ['city' => 'SeaTac', 'state' => 'Washington'],
            )->id;
        }
        if (! $case->charges) {
            $case->charges = 'Conspiracy; depredation of property of the United States; trespass on a naval installation — Disarm Now Plowshares action at the Strategic Weapons Facility Pacific, Naval Base Kitsap-Bangor, Washington.';
        }
        $case->indicted = 'September 2010';
        $case->convicted = 'Yes — federal jury, U.S. District Court for the Western District of Washington, December 13, 2010.';
        $case->sentence = '3 months in federal prison plus 6 months home detention. He began at the SeaTac Federal Detention Center, was transferred on April 18, 2011 toward Tennessee for a separate Y-12 case, and was held at the Knox County Sheriff\'s Detention Facility while completing the Disarm Now term.';
        $case->setPartialDate('arrest_date', 2009, 11, 2);
        $case->setPartialDate('sentenced_date', 2011, 3, 28);
        $case->setPartialDate('incarceration_date', 2011, 3, 28);
        $case->setPartialDate('release_date', 2011, 6, 28);
        $case->save();

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Set William Bichsel birth year (1928) and Disarm Now case dates (slug: '.$prisoner->slug.').');

        return self::SUCCESS;
    }
}
