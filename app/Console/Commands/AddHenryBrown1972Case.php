<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Henry "Sha Sha" Brown's earlier case — the 1972–1974 prosecution for the
 * murder of NYPD Officers Gregory Foster and Rocco Laurie — as a SECOND case on
 * his record (his existing case covers the later bank-expropriation / weapons /
 * 1976 Tombs-escape episode that ended at FCI Lewisburg). Also fills out his bio
 * with this earlier chapter. Idempotent: the case is only added if not already
 * present, and the description is set to a fixed comprehensive text.
 *
 * Timeline (per contemporaneous New York Times reporting and the Twymon Myers /
 * BLA record): arrested Feb 14, 1972 in Brooklyn after a car crash and shootout,
 * two weeks after the Jan 27, 1972 East Village killing of the two officers;
 * attempted to saw through his cell bars July 27, 1973; escaped Sept 27, 1973 by
 * feigning a peptic ulcer to reach the Kings County Hospital clinic; recaptured
 * Oct 3, 1973 in a Bushwick tenement raid; acquitted March 21, 1974.
 */
class AddHenryBrown1972Case extends Command
{
    protected $signature = 'prisoners:add-henry-brown-1972-case';

    protected $description = 'Add Henry "Sha Sha" Brown\'s 1972–1974 Foster/Laurie officer-murder case and expand his bio';

    private const BIO = 'Henry "Sha Sha" Brown was a member of the Black Liberation Army. Two weeks after the January 27, 1972 killing of NYPD Officers Gregory Foster and Rocco Laurie in Manhattan\'s East Village, Brown and fellow BLA members were pulled over in Brooklyn on February 14, 1972; after a high-speed crash and a shootout with the NYPD, Brown fled on foot but was tracked down and captured a few blocks away. Held for trial on the officers\' murders, he tried to saw through his cell bars with a smuggled hacksaw blade on July 27, 1973, and then on September 27, 1973 escaped by feigning a peptic ulcer to be taken to the Kings County Hospital clinic for X-rays — inside an isolated changing booth he scaled an eight-foot partition wall and slipped past his guards. He was recaptured exactly a week later, on October 3, 1973, in a raid on a tenement in the Bushwick section of Brooklyn, arrested alongside four others and identified by a fingerprint match after he refused to give his name. On March 21, 1974 he was acquitted of the murders. He remained a target of the state thereafter: he was later charged with bank expropriation and weapons offenses, was briefly liberated from the Manhattan House of Detention (the Tombs) in 1976 and recaptured, and was eventually held at FCI Lewisburg as a North American political prisoner. He was listed in PFOC Breakthrough magazine through the 1980s.';

    public function handle(): int
    {
        DB::transaction(function () {
            $prisoner = Prisoner::withUnderReview()
                ->where('name', 'Henry Brown')
                ->where('aka', 'like', '%Sha Sha%')
                ->first();

            if (! $prisoner) {
                $this->warn('Henry "Sha Sha" Brown not found — nothing to do.');

                return;
            }

            // Expand the bio to cover this earlier chapter (idempotent set).
            $prisoner->description = self::BIO;
            $prisoner->save();

            // Add the 1972–1974 case only if it isn't already present.
            if ($prisoner->cases()->where('charges', 'like', '%Foster%')->exists()) {
                $this->info('1972–1974 Foster/Laurie case already present — bio refreshed, case left as-is.');

                return;
            }

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges' => 'Murder of NYPD Officers Gregory Foster and Rocco Laurie (the January 27, 1972 East Village killings), as an alleged member of the Black Liberation Army.',
                'convicted' => 'No — acquitted at trial on March 21, 1974.',
                'arrest_date' => '1972-02-14',
                'incarceration_date' => '1972-02-14',
                'release_date' => '1974-03-21',
                'sentence' => 'Acquitted; no sentence. Held for roughly two years awaiting and during trial, apart from a week at large after a September 1973 escape.',
                'imprisoned_for_days' => 760,
            ]);

            $this->info('Added the 1972–1974 Foster/Laurie case for '.$prisoner->name.'.');
        });

        return self::SUCCESS;
    }
}
