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
 * Records that the existing James McInerney (the 1919 Centralia IWW defendant)
 * is the same man who, booked as "Jim Mack" (IWW prisoner no. 4888), was held
 * after the 1916 Everett Massacre — confirmed by sources describing the
 * Centralia McInerney as "an Irish immigrant who was a veteran of the Everett
 * Massacre." Adds his "Jim Mack" alias, his birth and death dates (born
 * Aug 15, 1886 in Scariff, County Clare, Ireland; died Aug 13, 1930), the
 * Everett Massacre affiliation, a note in his bio, and a second case for the
 * 1916 Everett detention — without disturbing his existing Centralia case.
 * Idempotent (only adds the Everett case once).
 */
final class FillJamesMcInerneyEverett extends Command
{
    protected $signature = 'prisoners:fill-james-mcinerney-everett';

    protected $description = 'Add James McInerney\'s 1916 Everett Massacre case to his existing (Centralia) record';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()
            ->where('name', 'James McInerney')
            ->get()
            ->first(fn ($p) => str_contains((string) $p->description, 'Centralia')
                || in_array('Industrial Workers of the World (IWW)', (array) $p->affiliation, true));

        if (! $prisoner) {
            $this->error('James McInerney (Centralia) not found.');

            return self::FAILURE;
        }

        $jail = Institution::firstOrCreate(
            ['name' => 'Snohomish County Jail'],
            ['city' => 'Everett', 'state' => 'Washington']
        )->id;

        DB::transaction(function () use ($prisoner, $jail) {
            if (empty($prisoner->aka)) {
                $prisoner->aka = 'Jim Mack';
            }
            // Exact dates: born Aug 15, 1886, Scariff, County Clare, Ireland; died Aug 13, 1930.
            $prisoner->setPartialDate('birthdate', 1886, 8, 15);
            $prisoner->setPartialDate('death_date', 1930, 8, 13);
            $aff = (array) $prisoner->affiliation;
            if (! in_array('Everett Massacre defendants', $aff, true)) {
                $aff[] = 'Everett Massacre defendants';
                $prisoner->affiliation = $aff;
            }
            if (! str_contains((string) $prisoner->description, 'Everett')) {
                $prisoner->description = rtrim((string) $prisoner->description)
                    .' McInerney was also a veteran of the 1916 Everett Massacre: booked as "Jim Mack" (IWW prisoner no. 4888), he was among the 74 Wobblies jailed on murder charges after the shooting at the Everett dock, before those charges were dropped.';
            }
            $prisoner->save();

            // Align his Centralia death-in-custody date with the confirmed date.
            foreach ($prisoner->cases as $c) {
                if (! empty($c->death_in_custody_date)) {
                    $c->setPartialDate('death_in_custody_date', 1930, 8, 13);
                    $c->save();
                }
            }

            // Add the Everett case only if it isn't already present.
            $hasEverett = $prisoner->cases()->where('charges', 'like', '%Everett%')->exists();

            if (! $hasEverett) {
                $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->fill([
                    'prisoner_id' => $prisoner->id,
                    'institution_id' => $jail,
                    'charges' => 'First-degree murder — for the deaths of deputies Jefferson Beard and Charles Curtis in the November 5, 1916 Everett Massacre. Held as one of 74 IWW members charged after the shooting (booked as "Jim Mack," IWW prisoner no. 4888).',
                    'convicted' => 'No — charged and held for trial; after Thomas H. Tracy\'s acquittal on May 5, 1917, the charges against the remaining Everett defendants were dropped.',
                    'sentence' => 'Held in the Snohomish County jail from November 1916 until release in 1917 after the charges were dropped — about six months of pretrial detention.',
                ]);
                $case->setPartialDate('arrest_date', 1916, 11, 5);
                $case->setPartialDate('incarceration_date', 1916, 11, 5);
                $case->setPartialDate('release_date', 1917, 5);
                $case->save();
                $this->info('Added Everett 1916 case to James McInerney.');
            } else {
                $this->info('Everett case already present; left as-is.');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Filled James McInerney (Everett + Centralia).');

        return self::SUCCESS;
    }
}
