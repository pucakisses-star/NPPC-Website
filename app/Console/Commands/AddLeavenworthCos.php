<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds the World War I conscientious objectors court-martialed and confined at
 * the Fort Leavenworth Disciplinary Barracks — mostly Mennonite, Hutterite,
 * Amish, and Brethren religious objectors who refused military service.
 *
 * Group 1 (29 men) had not been examined by the Army's Board of Inquiry before
 * their court-martials. Group 2 (83 men) were examined at Fort Leavenworth on
 * January 4–5, 1919, and found to be sincere objectors who would have qualified
 * for furloughs.
 *
 * Only the shared, documented group facts are asserted (WWI court-martial,
 * Fort Leavenworth confinement, the Board of Inquiry finding for Group 2);
 * specific church affiliation, home state, sentence, and case dates are left
 * blank rather than guessed. Race is left unset (run prisoners:infer-race).
 * Idempotent (skips by name), so anyone already in the database — e.g.
 * Maurice A. Hess — is left untouched.
 */
final class AddLeavenworthCos extends Command
{
    protected $signature = 'prisoners:add-leavenworth-cos';

    protected $description = 'Add the WWI Fort Leavenworth conscientious objectors (Board of Inquiry groups 1 & 2)';

    private const GROUP1 = [
        'Christian F. Lillig', 'Andrew Adolf Hofer', 'Herman Loewen', 'Aden E. Heckman', 'Martin S. Duncan',
        'Abraham Jesse Wingert', 'Philip I. Hauser', 'Burdtt W. Stine', 'David O. Hiebert', 'Andrew Ana Hofer',
        'Edward R. Heiser', 'Charley P. Graber', 'Peter W. Pankratz', 'Carl J. Maier', 'Percy H. Peters',
        'Peter M. Waldner', 'Ray Metzler', 'Robert E. Fox', 'Daniel J. Schmidt', 'Chris Hershberger',
        'Roby L. Barnes', 'Robert J. Morrow', 'Benjamin Baltzer', 'Peter Johnson', 'Noah Leatherman',
        'John Jacob Plenert', 'Gerhard M. Baergen', 'Maurice A. Hess', 'Carl A. Schmidt',
    ];

    private const GROUP2 = [
        'Jesse L. Brenaman', 'James Cook', 'William Goppert', 'Menno Richer', 'Everett R. Fisher', 'Harry D. Blough',
        'Ivan Hochstetler', 'Abraham Neuenschwander', 'William Nusbaum', 'Jesse E. Myers', 'Enos N. Hooley',
        'Owen J. Miller', 'Payson Miller', 'John P. Leichty', 'Forrest Hostetler', 'Omer Neuenschwander',
        'Charles H. Walker', 'Lawrence Williamson', 'Manna C. Woodworth', 'Henry H. Miller', 'Austin L. Hewett',
        'Julian Dumbrouski', 'Clarence George Maurer', 'Allen Schmidt', 'William A. Dunham', 'Cornelius Voth',
        'Tony J. Adams', 'Edward H. Mull', 'Guy H. Little', 'Walter W. Oliver', 'Jacob E. Tschetter', 'Jesse Cover',
        'Benjamin F. Randolph', 'Henry E. Reimer', 'Edward J. Waltner', 'Bouke Blom', 'David R. Troyer',
        'Samuel M. Hershberger', 'Paul C. Villiard', 'Jacob N. Martens', 'Abraham Goertz', 'Gerhard M. Klippenstein',
        'Elmer Hershberger', 'Joseph Eash', 'William O. Smith', 'J. Virgil Stauffer', 'Allen B. Christophol',
        'Oscar Hochstetler', 'Amos T. Bontrager', 'Elmer Leichty', 'Russell A. Lantz', 'Ren Metzler', 'Karl I. Garber',
        'Claude C. Culp', 'Paul L. Whitely', 'Tillman H. Soldner', 'Charles E. McPherson', 'Philip H. Pound',
        'Monroe Wulff', 'Stanley S. Brandberg', 'James B. McDonald', 'Desco H. Walker', 'Charles H. Waters',
        'Jacob H. Barkman', 'Bernard E. Fast', 'Ezra E. Barnhart', 'Ulysses DeRosa', 'Alonzo H. Sampley',
        'Peter P. Dirksen', 'Isaac T. Dirks', 'John T. Neufeld', 'David Lemke', 'Charles T. Clay', 'Will D. Proctor',
        'Jake J. Schmidt', 'Glen H. Witherbee', 'Herman F. Reimer', 'Ross R. Gillman', 'Theodore Huebner',
        'George E. Elstun', 'Peter Hiebert', 'Lawrence E. Mitchener', 'Harry Sommers',
    ];

    private const DESC_G1 = 'was a World War I conscientious objector who refused military service and was '
        .'court-martialed and confined at the Fort Leavenworth Disciplinary Barracks in Kansas. He was among the '
        .'objectors who had not been examined by the U.S. Army\'s Board of Inquiry before their court-martials.';

    private const DESC_G2 = 'was a World War I conscientious objector who refused military service and was '
        .'court-martialed and confined at the Fort Leavenworth Disciplinary Barracks in Kansas. When the U.S. Army\'s '
        .'Board of Inquiry examined him at Fort Leavenworth on January 4–5, 1919, it concluded that he was a sincere '
        .'conscientious objector who would have qualified for a furlough.';

    public function handle(): int
    {
        $institution = Institution::where('name', 'Fort Leavenworth Disciplinary Barracks')->first()
            ?? Institution::create([
                'name' => 'Fort Leavenworth Disciplinary Barracks',
                'city' => 'Fort Leavenworth',
                'state' => 'Kansas',
            ]);

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($institution, &$created, &$skipped) {
            foreach ([['names' => self::GROUP1, 'blurb' => self::DESC_G1],
                ['names' => self::GROUP2, 'blurb' => self::DESC_G2]] as $group) {
                foreach ($group['names'] as $name) {
                    if (Prisoner::withUnderReview()->where('name', $name)->exists()) {
                        $skipped++;

                        continue;
                    }

                    [$first, $middle, $last] = $this->splitName($name);

                    $prisoner = Prisoner::create([
                        'name' => $name,
                        'first_name' => $first,
                        'middle_name' => $middle,
                        'last_name' => $last,
                        'gender' => 'Male',
                        'era' => '1910s',
                        'ideologies' => ['Pacifism', 'Conscientious objection'],
                        'affiliation' => [],
                        'description' => $name.' '.$group['blurb'],
                        'in_custody' => false,
                        'released' => true,
                        'in_exile' => false,
                        'currently_in_exile' => false,
                        'awaiting_trial' => false,
                    ]);

                    PrisonerCase::create([
                        'prisoner_id' => $prisoner->id,
                        'institution_id' => $institution->id,
                        'charges' => 'Court-martialed as a conscientious objector who refused military service '
                            .'during World War I; confined at the Fort Leavenworth Disciplinary Barracks.',
                        'convicted' => 'Yes — court-martialed for refusing military service.',
                    ]);

                    $created++;
                }
            }
        });

        $this->info("Done. Created {$created}; skipped {$skipped} already present.");

        return self::SUCCESS;
    }

    /** @return array{0:string,1:?string,2:string} [first, middle|null, last] */
    private function splitName(string $name): array
    {
        $toks = preg_split('/\s+/', trim($name));
        $first = array_shift($toks);
        $last = array_pop($toks) ?? $first;
        $middle = $toks ? implode(' ', $toks) : null;

        return [$first, $middle, $last];
    }
}
