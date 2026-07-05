<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Authoritative correction of Henry "Sha Sha" Brown's record against the primary
 * source (The New York Times, "Brown Is Recaptured With Four in Raid on a
 * Brooklyn Tenement," Oct. 4, 1973). Supersedes the earlier Henry-Brown commands
 * (prior edits mistakenly relocated the 1972 shootout to New York). Run this one.
 *
 * Documented timeline:
 *   - Jan 27, 1972: NYPD Officers Gregory Foster and Rocco Laurie murdered in
 *     Manhattan's East Village.
 *   - Feb 14, 1972 (18 days later): Brown captured in a shootout with police in
 *     ST. LOUIS (fellow BLA member Ronald Carter killed); convicted of assault
 *     with intent to kill, 25-year sentence, held at the Missouri State
 *     Penitentiary.
 *   - 1973: indicted for the Foster/Laurie murders, extradited to New York, held
 *     at the Brooklyn House of Detention.
 *   - Jul 27, 1973: caught cutting through cell bars with a hacksaw blade.
 *   - Sep 27, 1973: escaped a Kings County Hospital clinic (vaulted an 8-ft
 *     partition while there for an X-ray).
 *   - Oct 3, 1973: recaptured at 79 Menahan St, Bushwick, with four others
 *     (Timothy Adams and Elliott White Haskins among them).
 *   - Mar 21, 1974: acquitted of the officers' murders.
 *
 * Two cases result: the St. Louis assault conviction (25 years) and the New York
 * murder case (acquitted). Idempotent — updates existing cases or creates them.
 */
class FixHenryBrownRecord extends Command
{
    protected $signature = 'prisoners:fix-henry-brown-record';

    protected $description = 'Authoritatively correct Henry "Sha Sha" Brown: St. Louis assault conviction + NY murder acquittal, per the NYT';

    private const BIO = 'Henry "Sha Sha" Brown was a member of the Black Liberation Army. Eighteen days after the January 27, 1972 killing of NYPD Officers Gregory Foster and Rocco Laurie in Manhattan\'s East Village, Brown was captured on February 14, 1972 in a shootout with police in St. Louis — in which fellow BLA member Ronald Carter was killed — and was convicted of assault with intent to kill and sentenced to 25 years, held at the Missouri State Penitentiary. Indicted in 1973 for the Foster and Laurie murders, he was extradited to New York and held at the Brooklyn House of Detention. On July 27, 1973 he and three other inmates were caught after cutting through five steel cell bars with a hacksaw blade; then on September 27, 1973, taken to a Kings County Hospital clinic for an X-ray, he vaulted an eight-foot partition and escaped. He was recaptured a week later, on October 3, 1973, when police raided a tenement at 79 Menahan Street in the Bushwick section of Brooklyn and took him with four other men — among them Timothy Adams and Elliott White Haskins. On March 21, 1974 he was acquitted of the officers\' murders. He was later held in federal custody at FCI Lewisburg as a North American political prisoner (Federal Bureau of Prisons register number 01517-045), was listed in PFOC Breakthrough magazine, and was no longer in Bureau of Prisons custody as of October 5, 1984.';

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

            $missouri = Institution::firstOrCreate(['name' => 'Missouri State Penitentiary'], ['city' => 'Jefferson City', 'state' => 'Missouri']);
            $brooklyn = Institution::firstOrCreate(['name' => 'Brooklyn House of Detention'], ['city' => 'Brooklyn', 'state' => 'New York']);

            $prisoner->description = self::BIO;
            $prisoner->middle_name = 'Stuart';   // per BOP: HENRY STUART BROWN
            $prisoner->race = 'Black';
            $prisoner->gender = 'Male';
            $prisoner->inmate_number = '01517-045';   // Federal BOP register number
            $prisoner->save();

            $cases = $prisoner->cases()->get();
            $murderCase = $cases->first(fn ($c) => str_contains((string) $c->charges, 'Foster'));
            $stLouisCase = $cases->first(fn ($c) => ! str_contains((string) $c->charges, 'Foster'));

            // --- New York: Foster/Laurie murders (acquitted) ---
            $murderCase ??= new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $murderCase->fill([
                'institution_id' => $brooklyn->id,
                'charges' => 'Murder of NYPD Officers Gregory Foster and Rocco Laurie (the January 27, 1972 East Village killings), as an alleged member of the Black Liberation Army.',
                'convicted' => 'No — acquitted at trial on March 21, 1974.',
                'sentence' => 'Acquitted; no sentence. Extradited from Missouri to New York in 1973 to stand trial and held at the Brooklyn House of Detention, from which he escaped (Sept 1973) and was recaptured (Oct 1973) before the acquittal.',
                'arrest_date' => null,
                'incarceration_date' => null,
                'release_date' => '1974-03-21',
                'imprisoned_for_days' => null,
            ]);
            $murderCase->save();

            // --- St. Louis: assault with intent to kill (the conviction) ---
            $stLouisCase ??= new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $stLouisCase->fill([
                'institution_id' => $missouri->id,
                'charges' => 'Assault with intent to kill — from the February 14, 1972 shootout with police in St. Louis (18 days after the Foster/Laurie murders in New York), in which fellow BLA member Ronald Carter was killed.',
                'convicted' => 'Yes — convicted of assault with intent to kill in the St. Louis shootout.',
                'sentence' => '25 years. He was ultimately held in federal custody at FCI Lewisburg (Bureau of Prisons register no. 01517-045) and was no longer in BOP custody as of October 5, 1984.',
                'arrest_date' => '1972-02-14',
                'incarceration_date' => '1972-02-14',
                'release_date' => '1984-10-05',
                'imprisoned_for_days' => null,
            ]);
            $stLouisCase->save();

            $this->info('Corrected Henry "Sha Sha" Brown: St. Louis assault conviction (25 yrs) + NY murder acquittal (1974).');
        });

        return self::SUCCESS;
    }
}
