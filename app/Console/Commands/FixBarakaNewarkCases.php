<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;

/**
 * Records LeRoi Jones (Amiri Baraka)'s full 1967 Newark prosecution. He was in
 * custody in two separate stretches plus a contempt matter, so — because each
 * case row holds only one incarceration→release pair — it is modeled as three
 * cases:
 *
 *   1. Weapons, arrest & pretrial detention: arrested July 14, 1967 during the
 *      rebellion (beaten, Essex County Jail) and released on bail July 22, 1967
 *      — 8 days.
 *   2. Weapons, post-conviction incarceration: convicted November 6, 1967;
 *      sentenced January 4, 1968 to 2½–3 years + a $1,000 fine and held until
 *      released on $25,000 bail January 9, 1968 pending appeal — 5 days. The
 *      conviction was reversed April 21, 1969, so the full sentence was never
 *      served.
 *   3. Criminal contempt: 30 days at the Essex County Jail imposed November 6,
 *      1967 by Judge Leon W. Kapp; reversed on appeal (State v. Jones, 1969).
 *      No served-time dates (at liberty pending appeal).
 *
 * Idempotent — each case is upserted by a charge keyword.
 */
final class FixBarakaNewarkCases extends Command
{
    protected $signature = 'prisoners:fix-baraka-newark-cases';

    protected $description = "Record Baraka's 1967 Newark cases: arrest detention, post-conviction custody, and the 30-day contempt";

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

        // 1. Weapons — arrest & pretrial detention (the existing revolver case
        // that is not the post-conviction one).
        $this->upsert(
            $prisoner,
            fn ($c) => str_contains(strtolower((string) $c->charges), 'revolver') && ! str_contains(strtolower((string) $c->charges), 'post-conviction'),
            [
                'charges' => 'Unlawful possession of two revolvers; resisting arrest (Newark rebellion, July 1967)',
                'arrest_date' => '1967-07-14',
                'incarceration_date' => '1967-07-14',
                'release_date' => '1967-07-22',
                'convicted' => 'Yes — convicted November 6, 1967; conviction reversed on appeal April 21, 1969',
                'sentence' => 'Arrest detention: held 8 days after the July 14, 1967 arrest, then released on bail July 22, 1967 (pre-trial).',
                'institution_id' => $essex->id,
                'judge' => 'Leon W. Kapp',
            ],
            'weapons arrest/pretrial detention',
        );

        // 2. Weapons — post-conviction incarceration.
        $this->upsert(
            $prisoner,
            fn ($c) => str_contains(strtolower((string) $c->charges), 'post-conviction'),
            [
                'charges' => 'Unlawful possession of two revolvers — post-conviction incarceration (sentence vacated on appeal)',
                'incarceration_date' => '1968-01-04',
                'release_date' => '1968-01-09',
                'sentenced_date' => '1968-01-04',
                'convicted' => 'Convicted November 6, 1967; reversed on appeal April 21, 1969',
                'sentence' => '2½ to 3 years plus a $1,000 fine; jailed at sentencing on January 4, 1968 and released on $25,000 bail January 9, 1968 pending appeal. Conviction reversed April 21, 1969 — the full sentence was never served.',
                'institution_id' => $essex->id,
                'judge' => 'Leon W. Kapp',
            ],
            'weapons post-conviction incarceration',
        );

        // 3. Criminal contempt.
        $this->upsert(
            $prisoner,
            fn ($c) => str_contains(strtolower((string) $c->charges), 'contempt'),
            [
                'charges' => 'Criminal contempt of court (summary contempt during the 1967 Newark weapons trial)',
                'convicted' => 'Adjudged guilty of criminal contempt November 6, 1967; reversed on appeal (State v. Jones, 1969)',
                'sentence' => '30 days in the Essex County Jail, imposed November 6, 1967 by Judge Leon W. Kapp immediately after the verdict; reversed on appeal',
                'sentenced_date' => '1967-11-06',
                'judge' => 'Leon W. Kapp',
                'institution_id' => $essex->id,
            ],
            'criminal contempt',
        );

        return self::SUCCESS;
    }

    private function upsert(Prisoner $prisoner, callable $match, array $data, string $label): void
    {
        $case = $prisoner->cases()->get()->first($match);

        if ($case) {
            $case->fill($data)->save();
            $verb = 'Updated';
        } else {
            $data['prisoner_id'] = $prisoner->id;
            $case = PrisonerCase::create($data);
            $verb = 'Added';
        }

        $days = $case->fresh()->imprisoned_for_days;
        $this->info("{$verb} {$label}".($days ? " ({$days} days)" : '').'.');
    }
}
