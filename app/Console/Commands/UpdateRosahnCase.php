<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Fills in Eve Rosahn's case from the Second Circuit opinion In re Grand Jury
 * Proceedings Involving Eve Rosahn, 671 F.2d 690 (2d Cir. Jan. 29, 1982):
 *
 *  - Civil contempt under 28 U.S.C. § 1826 for refusing to comply with a
 *    federal grand jury subpoena duces tecum (declining to provide photographs,
 *    fingerprints, handwriting exemplars, and hair samples) in the S.D.N.Y.
 *    investigation of the October 20, 1981 Brink's/Nyack armored-truck robbery.
 *  - Found in contempt by Judge Irving Ben Cooper and confined (held at the
 *    Metropolitan Correctional Center) until released on bail on Dec. 30, 1981.
 *  - The Second Circuit vacated the contempt adjudication on Jan. 29, 1982,
 *    holding that closing the contempt proceeding to the public over her
 *    objection violated her Fifth Amendment due process rights.
 *
 * Updates her existing case in place. Idempotent; matches by slug, then name.
 */
final class UpdateRosahnCase extends Command
{
    protected $signature = 'prisoners:update-rosahn-case';

    protected $description = "Document Eve Rosahn's grand-jury civil-contempt case (671 F.2d 690)";

    public function handle(): int
    {
        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'eve-rosahn')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Rosahn%')->first();

        if (! $prisoner) {
            $this->error('No Eve Rosahn record found.');

            return self::FAILURE;
        }

        if (empty($prisoner->state)) {
            $prisoner->state = 'New York';
            $prisoner->save();
            $this->info('Set state: New York.');
        }

        $institution = Institution::firstOrCreate(
            ['name' => 'Metropolitan Correctional Center, New York'],
            ['city' => 'New York', 'state' => 'New York'],
        );

        $case = $prisoner->cases()->first() ?? $prisoner->cases()->make([]);
        $case->institution_id = $institution->id;
        $case->charges = 'Civil contempt under 28 U.S.C. § 1826 for refusing to comply with a federal grand '
            .'jury subpoena duces tecum — declining to provide photographs, fingerprints, handwriting '
            .'exemplars, and hair samples — in the Southern District of New York grand jury investigation of '
            .'the October 20, 1981 Brink\'s/Nyack armored-truck robbery.';
        $case->convicted = 'Civil contempt (28 U.S.C. § 1826); the adjudication was vacated by the U.S. Court '
            .'of Appeals for the Second Circuit on Jan. 29, 1982 (671 F.2d 690), which held that trying the '
            .'contempt in secret over her objection violated her Fifth Amendment right to a public trial.';
        $case->judge = 'Irving Ben Cooper (S.D.N.Y.)';
        $case->sentence = 'Coercive civil confinement until compliance, expiration of the grand jury\'s term, '
            .'or May 9, 1983 (whichever came first); released on bail pending appeal on Dec. 30, 1981.';
        $case->setPartialDate('incarceration_date', 1981, 11);
        $case->setPartialDate('release_date', 1981, 12, 30);
        $case->save();

        $this->info("Documented Eve Rosahn's case (671 F.2d 690). View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
