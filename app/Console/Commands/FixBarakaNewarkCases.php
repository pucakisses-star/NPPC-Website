<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;

/**
 * Records LeRoi Jones (Amiri Baraka)'s full 1967 Newark prosecution.
 *
 *   - Weapons case: arrested July 14, 1967 during the rebellion (beaten, held
 *     at the Essex County Jail) and released on bail July 22, 1967 — an 8-day
 *     initial detention. Convicted November 6, 1967; sentenced January 4, 1968
 *     to 2½–3 years plus a $1,000 fine and released on $25,000 bail January 9,
 *     1968 pending appeal; conviction reversed April 21, 1969 — the prison
 *     sentence was never served. Recorded custody is the July 14–22, 1967
 *     detention (8 days); the later events are noted in the sentence text.
 *
 *   - Criminal contempt: a separate 30-day Essex County Jail sentence imposed
 *     November 6, 1967 by Judge Leon W. Kapp immediately after the verdict;
 *     reversed on appeal (State v. Jones, 1969). No served-time dates are
 *     recorded (he was at liberty pending appeal).
 *
 * Replaces the earlier prisoners:set-baraka-release-date command. Idempotent.
 */
final class FixBarakaNewarkCases extends Command
{
    protected $signature = 'prisoners:fix-baraka-newark-cases';

    protected $description = "Record Baraka's 1967 Newark weapons case (July 1967 detention) and the 30-day contempt sentence";

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'LeRoi Jones')->first();

        if (! $prisoner) {
            $this->warn('LeRoi Jones not found, nothing to do.');

            return self::SUCCESS;
        }

        $essex = Institution::firstOrCreate(
            ['name' => 'Essex County Jail'],
            ['city' => 'Newark', 'state' => 'New Jersey'],
        );

        // Weapons case — the existing non-contempt case.
        $weapons = $prisoner->cases->first(fn ($c) => str_contains(strtolower((string) $c->charges), 'revolver'))
            ?? $prisoner->cases->first(fn ($c) => ! str_contains(strtolower((string) $c->charges), 'contempt'));

        if ($weapons) {
            $weapons->arrest_date = '1967-07-14';
            $weapons->incarceration_date = '1967-07-14';
            $weapons->release_date = '1967-07-22';
            $weapons->institution_id = $essex->id;
            $weapons->sentence = 'Held 8 days after his July 14, 1967 arrest, then released on bail July 22, 1967. Convicted November 6, 1967 and sentenced January 4, 1968 to 2½–3 years plus a $1,000 fine; released on $25,000 bail January 9, 1968 pending appeal. Conviction reversed April 21, 1969 — the prison sentence was never served.';
            $weapons->save();
            $weapons->refresh();
            $this->info("Updated weapons case: July 14–22, 1967 ({$weapons->imprisoned_for_days} days initial detention).");
        } else {
            $this->warn('No weapons case found to update.');
        }

        // Criminal contempt — a separate case.
        $contemptData = [
            'charges' => 'Criminal contempt of court (summary contempt during the 1967 Newark weapons trial)',
            'convicted' => 'Adjudged guilty of criminal contempt November 6, 1967; reversed on appeal (State v. Jones, 1969)',
            'sentence' => '30 days in the Essex County Jail, imposed November 6, 1967 by Judge Leon W. Kapp immediately after the verdict; reversed on appeal',
            'sentenced_date' => '1967-11-06',
            'judge' => 'Leon W. Kapp',
            'institution_id' => $essex->id,
        ];

        $contempt = $prisoner->cases->first(fn ($c) => str_contains(strtolower((string) $c->charges), 'contempt'));

        if ($contempt) {
            $contempt->fill($contemptData)->save();
            $this->info('Updated contempt case.');
        } else {
            $contemptData['prisoner_id'] = $prisoner->id;
            PrisonerCase::create($contemptData);
            $this->info('Added 30-day criminal contempt case (November 6, 1967).');
        }

        return self::SUCCESS;
    }
}
