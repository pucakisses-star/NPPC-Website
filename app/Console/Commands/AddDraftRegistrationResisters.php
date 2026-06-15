<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Batch 4 of the comprehensive-sweep additions: the 1980s draft-registration
 * resisters — young men criminally prosecuted (1982–86) for publicly refusing
 * to register with the Selective Service System after registration was revived
 * in 1980. Cross-checked as not already in the database. Only about nine were
 * ever imprisoned; this adds the most prominent:
 *   - Benjamin Sasway (first indicted; first jailed; 30 months)
 *   - Enten Eller     (first convicted; Church of the Brethren; alt. service)
 *   - Paul Jacob      (went underground; served 5½ months)
 *   - Gillam Kerley   (3 years; verdict later overturned)
 *   - Edward Hasbrouck(6 months at FPC Lewisburg; later chronicled the cases)
 * Sourced to UPI/AP, the Washington Post, the court records (US v. Jacob,
 * 767 F.2d 505; US v. Kerley, 838 F.2d 932), and resisters.info. Idempotent.
 */
class AddDraftRegistrationResisters extends Command {
    protected $signature = 'prisoners:add-draft-resisters';
    protected $description = 'Add the 1980s draft-registration resisters (Sasway, Eller, Jacob, Kerley, Hasbrouck)';

    public function handle(): int {
        $cases = [
            [
                'name' => 'Benjamin Sasway', 'first' => 'Benjamin', 'last' => 'Sasway',
                'gender' => 'Male', 'state' => 'California', 'era' => '1980s',
                'ideologies' => ['Draft resistance', 'Anti-war'], 'affiliation' => [],
                'institution' => null,
                'bio' => 'Benjamin H. Sasway was the first American indicted since the Vietnam War for refusing to register with the Selective Service System, and the first jailed for it. A 21-year-old San Diego State University student, Sasway had refused to register in 1980 and said openly and often that draft registration was "immoral and incompatible with a free society." Indicted in San Diego in 1982, he was barred at trial from telling the jury about his political motives, and in August 1982 a federal jury — after deliberating about 50 minutes — convicted him of refusing to register. On October 4, 1982 a judge sentenced him to 30 months in a minimum-security federal prison, making him the first person imprisoned for draft-registration resistance since the Vietnam era.',
                'charges' => 'Failure to register with the Selective Service System — as the first person indicted for draft-registration refusal since the Vietnam War (San Diego, 1982).',
                'convicted' => 'Yes — convicted by a federal jury in August 1982 after being barred from telling the jury his political motives.',
                'sentence' => '30 months in a minimum-security federal prison — the first American since the Vietnam War jailed for refusing to register.',
            ],
            [
                'name' => 'Enten Eller', 'first' => 'Enten', 'last' => 'Eller',
                'gender' => 'Male', 'state' => 'Virginia', 'era' => '1980s',
                'ideologies' => ['Draft resistance', 'Christian pacifism'], 'affiliation' => ['Church of the Brethren'],
                'institution' => null,
                'bio' => 'Enten Eller, a Bridgewater College senior and the son of a Church of the Brethren minister, became on August 17, 1982 the first American convicted of refusing to register for the draft in the 1980s. A conscientious objector who held that registration violated his religious beliefs, Eller forbade his attorneys to mount a defense at his one-day, nonjury trial before U.S. District Judge James C. Turk, who rejected the religious-belief defense and convicted him. Turk, saying he was impressed by the sincerity of Eller\'s convictions, did not imprison him: he was placed on probation and ordered to register within 90 days or face prison, and when he continued to refuse, was directed to perform two years of alternative service, which he carried out at a food bank in Roanoke.',
                'charges' => 'Failure to register with the Selective Service System — a refusal Eller, a Church of the Brethren conscientious objector, grounded in his religious opposition to war.',
                'convicted' => 'Yes — convicted on August 17, 1982 in a nonjury trial before Judge James C. Turk, the first American convicted of draft-registration refusal in the 1980s.',
                'sentence' => 'Probation and, after he continued to refuse to register, two years of alternative service at a Roanoke food bank rather than prison.',
            ],
            [
                'name' => 'Paul Jacob', 'first' => 'Paul', 'last' => 'Jacob',
                'gender' => 'Male', 'state' => 'Arkansas', 'era' => '1980s',
                'ideologies' => ['Draft resistance', 'Libertarianism'], 'affiliation' => [],
                'institution' => null,
                'bio' => 'Paul Jacob was an Arkansas draft-registration resister who became one of only about nine Americans imprisoned for non-registration since the Vietnam War. On January 5, 1981 he announced at a Little Rock protest that he had not registered and would not, and he was indicted in September 1982. After a period living underground, Jacob was tried in Little Rock in July 1985 — Congressman Ron Paul testified on his behalf — and convicted of violating the Military Selective Service Act. He was sentenced to five years\' imprisonment with four and a half years suspended on the condition of community service, and served about five and a half months in federal prison. Jacob went on to a long career as a libertarian activist, leading national term-limits and ballot-initiative campaigns.',
                'charges' => 'Violating the Military Selective Service Act by refusing to register — after publicly announcing his non-registration at a 1981 Little Rock protest.',
                'convicted' => 'Yes — convicted at a July 1985 federal trial in Little Rock (at which Rep. Ron Paul testified for him); United States v. Jacob, 767 F.2d 505 (8th Cir. 1985).',
                'sentence' => 'Five years, with four and a half suspended for community service; served about five and a half months in federal prison.',
            ],
            [
                'name' => 'Gillam Kerley', 'first' => 'Gillam', 'last' => 'Kerley',
                'gender' => 'Male', 'state' => 'Wisconsin', 'era' => '1980s',
                'ideologies' => ['Draft resistance', 'Anti-war'], 'affiliation' => [],
                'institution' => null,
                'bio' => 'Gillam Kerley was a Madison, Wisconsin draft-registration resister and organizer who was indicted in September 1982 for refusing to register with the Selective Service System. Convicted by a jury, he was sentenced by U.S. District Judge John Shabaz to three years in prison and a $10,000 fine. Kerley served about four months before the U.S. Court of Appeals for the Seventh Circuit intervened — first vacating the sentence and then, on reconsideration, overturning the guilty verdict altogether and ordering a new trial (United States v. Kerley, 838 F.2d 932, 7th Cir. 1988). He remained an outspoken opponent of registration throughout.',
                'charges' => 'Failure to register with the Selective Service System (indicted September 1982 in the Western District of Wisconsin).',
                'convicted' => 'Convicted by a jury and sentenced to three years plus a $10,000 fine; the Seventh Circuit later overturned the verdict and ordered a new trial (United States v. Kerley, 838 F.2d 932).',
                'sentence' => 'Three years in prison; served about four months before the conviction was overturned on appeal.',
            ],
            [
                'name' => 'Edward Hasbrouck', 'first' => 'Edward', 'last' => 'Hasbrouck',
                'gender' => 'Male', 'state' => 'Massachusetts', 'era' => '1980s',
                'ideologies' => ['Draft resistance', 'Anti-war'], 'affiliation' => [],
                'institution' => ['name' => 'Federal Prison Camp, Lewisburg', 'city' => 'Lewisburg', 'state' => 'Pennsylvania'],
                'bio' => 'Edward Hasbrouck was a Boston draft-registration resister singled out for prosecution in 1982 — by then-U.S. Attorney William Weld and prosecutor Robert Mueller — as one of the most vocal of the several million young men who refused to register after registration was revived in 1980. In January 1983 U.S. District Judge David Nelson sentenced him to two years\' probation and 1,000 hours of community service, but in November 1983 that probation was revoked in favor of a six-month prison term. Hasbrouck served roughly four and a half months across a jail near Boston, the federal facility at Danbury, and the Federal Prison Camp at Lewisburg, Pennsylvania, from November 1983 to April 1984. He later became a travel writer ("The Practical Nomad") and the leading chronicler of draft-registration resistance, maintaining the definitive public record of the prosecutions at resisters.info.',
                'charges' => 'Knowingly and willfully refusing to submit to registration with the Selective Service System — selected for prosecution in 1982 as one of the most vocal nonregistrants.',
                'convicted' => 'Yes — convicted in 1983; initially given probation and community service, which was revoked in November 1983 in favor of a prison term.',
                'sentence' => 'A six-month prison term; served about four and a half months (Nov 1983–April 1984), most of it at the Federal Prison Camp in Lewisburg, Pennsylvania.',
            ],
        ];

        DB::transaction(function () use ($cases) {
            foreach ($cases as $c) {
                if (Prisoner::where('name', $c['name'])->exists()) {
                    $this->warn("Skipped (already exists): {$c['name']}");
                    continue;
                }

                $institutionId = null;
                if ($c['institution']) {
                    $inst = Institution::firstOrCreate(
                        ['name' => $c['institution']['name']],
                        ['city' => $c['institution']['city'], 'state' => $c['institution']['state']]
                    );
                    $institutionId = $inst->id;
                }

                $prisoner = Prisoner::create([
                    'name'           => $c['name'],
                    'first_name'     => $c['first'],
                    'last_name'      => $c['last'],
                    'description'    => $c['bio'],
                    'gender'         => $c['gender'],
                    'state'          => $c['state'],
                    'era'            => $c['era'],
                    'ideologies'     => $c['ideologies'],
                    'affiliation'    => $c['affiliation'],
                    'in_custody'     => false,
                    'released'       => true,
                    'awaiting_trial' => false,
                ]);

                PrisonerCase::create([
                    'prisoner_id'    => $prisoner->id,
                    'institution_id' => $institutionId,
                    'charges'        => $c['charges'],
                    'convicted'      => $c['convicted'],
                    'sentence'       => $c['sentence'],
                ]);

                $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
            }
        });

        return self::SUCCESS;
    }
}
