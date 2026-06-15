<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Batch 3 of the comprehensive-sweep additions: Native American / American Indian
 * Movement-era political cases (1970s), cross-checked as not already in the
 * database (Peltier, Russell Means, Dennis Banks, John Trudell, and Anna Mae
 * Aquash are already in):
 *   - Yvonne Wanrow  (Colville; landmark self-defense case, 20 yrs reversed)
 *   - Paul Skyhorse + Richard Mohawk (AIM; 1974 George Aird frame-up, acquitted)
 *   - Kenneth Loud Hawk + Russell Redner (US v. Loud Hawk, 12-yr case dismissed)
 * Sourced to the court records (State v. Wanrow, 88 Wn.2d 221; United States v.
 * Loud Hawk, 474 U.S. 302), the Washington Post, the Center for Constitutional
 * Rights, and the movement defense campaigns. Idempotent (skips by name).
 */
class AddNativeAmericanPrisoners extends Command {
    protected $signature = 'prisoners:add-native-american';
    protected $description = 'Add Wanrow, Skyhorse & Mohawk, and Loud Hawk & Redner (AIM-era Native American cases)';

    private const SKYHORSE_MOHAWK = "%s was one of two American Indian Movement activists — Paul Skyhorse and Richard Mohawk — charged with the October 10, 1974 murder of George Aird, a taxicab driver beaten and stabbed to death at an AIM encampment in Box Canyon, in the hills outside Los Angeles. The two were jailed for roughly three years awaiting trial. The prosecution had no physical evidence tying them to the killing; the people actually found with the victim's blood on their clothing and his keys in a pocket — Marvin Redshirt and Marcella Eaglestaff — were instead given immunity to testify against the AIM defendants, and the movement, infiltrated at the time by FBI operative Douglass Durham, regarded the case as a frame-up meant to discredit it. After a nearly 13-month trial — among the longest and costliest in California history — and 62 hours of jury deliberation, Skyhorse and Mohawk were acquitted of all charges on May 24, 1978.";

    private const SKYHORSE_CHARGES = 'Murder in the October 10, 1974 killing of Los Angeles cab driver George Aird at an American Indian Movement camp in Box Canyon — a prosecution brought without physical evidence, in which the people forensically tied to the crime were given immunity to testify against the AIM defendants. Widely viewed as an FBI-era frame-up to discredit the movement.';
    private const SKYHORSE_CONVICTED = 'No — acquitted of all charges on May 24, 1978 after a nearly 13-month trial.';
    private const SKYHORSE_SENTENCE = 'No conviction; held roughly three years in pretrial detention before the 1978 acquittal.';

    private const LOUD_HAWK = "%s was an American Indian Movement defendant in United States v. Loud Hawk, one of the longest-running criminal prosecutions in U.S. history. On November 14, 1975 — five months after the deadly shootout with the FBI at Pine Ridge — Oregon state troopers, acting on an FBI tip, stopped two vehicles near Ontario, Oregon; Kenneth Loud Hawk and Russell Redner were arrested while Dennis Banks and Leonard Peltier fled the scene. Officers reported finding dynamite, partly assembled time bombs, ammunition, blasting caps, and weapons, but Oregon authorities destroyed the dynamite, crippling the evidence. The federal weapons-and-explosives case then dragged on for some twelve years through repeated appeals — reaching the U.S. Supreme Court in 1986 (United States v. Loud Hawk, 474 U.S. 302) on the question of speedy trial — before the charges were finally dismissed in 1988 for the government's violation of the defendants' right to a speedy trial.";

    private const LOUD_HAWK_CHARGES = 'Federal weapons and explosives charges stemming from the November 14, 1975 stop of two vehicles near Ontario, Oregon, days after the Pine Ridge shootout — a prosecution that lasted roughly twelve years and was hobbled when Oregon authorities destroyed the dynamite said to be the central evidence.';
    private const LOUD_HAWK_CONVICTED = 'No — after some twelve years of litigation (including United States v. Loud Hawk, 474 U.S. 302), the charges were dismissed in 1988 for violation of the defendants\' right to a speedy trial.';
    private const LOUD_HAWK_SENTENCE = 'No conviction; the federal prosecution was dismissed in 1988 on speedy-trial grounds after roughly twelve years.';

    public function handle(): int {
        $cases = [
            [
                'name' => 'Yvonne Wanrow', 'first' => 'Yvonne', 'last' => 'Wanrow',
                'gender' => 'Female', 'race' => 'Native American', 'state' => 'Washington', 'era' => '1970s',
                'ideologies' => ['Indigenous rights', "Women's self-defense"],
                'affiliation' => ['Confederated Tribes of the Colville Reservation'],
                'bio' => 'Yvonne Wanrow (born 1943; later known as Yvonne Swan) is a member of the Sinixt (Arrow Lakes) people of the Confederated Tribes of the Colville Reservation whose prosecution for killing a white child molester became a landmark of both Native and women\'s self-defense law. On August 11, 1972, Wanrow — her leg in a cast and on crutches — was at a friend\'s home in Spokane, Washington when William Wesler, a 6-foot-2 intoxicated 62-year-old known to have molested a neighborhood child, entered the house; when he moved toward her three-year-old nephew, she drew a pistol and shot and killed him, also wounding his companion. An all-white jury convicted her in 1973 of second-degree murder and first-degree assault and she was sentenced to 20 years. In 1977 the Washington Supreme Court reversed her conviction in State v. Wanrow, holding that a jury must judge a self-defense claim from the defendant\'s own perspective — including her circumstances as a woman facing a larger male attacker — rather than that of a hypothetical "reasonable man." It was the first American decision to recognize the particular self-defense situation of women, and a cause célèbre for the Native and feminist movements, with the Center for Constitutional Rights joining her defense. In 1979 Wanrow pleaded guilty to reduced charges of manslaughter and second-degree assault and received five years\' probation and community service, having spent only the three days in jail that followed her arrest.',
                'charges' => 'Second-degree (felony) murder and first-degree assault for the August 11, 1972 shooting of William Wesler, a white man and known child molester, in defense of the children in the Spokane home where she was staying — a self-defense killing her supporters in the Native and women\'s movements held up as the criminalization of a Native woman protecting children.',
                'convicted' => 'Convicted by an all-white jury in 1973 and sentenced to 20 years; the Washington Supreme Court reversed in 1977 (State v. Wanrow). In 1979 she pleaded guilty to reduced charges (manslaughter and second-degree assault).',
                'sentence' => 'Originally 20 years; after the reversal, five years\' probation and community service on the 1979 plea — she served only the three days in jail following her arrest.',
            ],
            [
                'name' => 'Paul Skyhorse', 'first' => 'Paul', 'last' => 'Skyhorse',
                'gender' => 'Male', 'race' => 'Native American', 'state' => 'California', 'era' => '1970s',
                'ideologies' => ['Indigenous rights', 'Red Power'],
                'affiliation' => ['American Indian Movement'],
                'bio' => sprintf(self::SKYHORSE_MOHAWK, 'Paul Skyhorse (Paul Durant)'),
                'charges' => self::SKYHORSE_CHARGES, 'convicted' => self::SKYHORSE_CONVICTED, 'sentence' => self::SKYHORSE_SENTENCE,
            ],
            [
                'name' => 'Richard Mohawk', 'first' => 'Richard', 'last' => 'Mohawk',
                'gender' => 'Male', 'race' => 'Native American', 'state' => 'California', 'era' => '1970s',
                'ideologies' => ['Indigenous rights', 'Red Power'],
                'affiliation' => ['American Indian Movement'],
                'bio' => sprintf(self::SKYHORSE_MOHAWK, 'Richard Mohawk'),
                'charges' => self::SKYHORSE_CHARGES, 'convicted' => self::SKYHORSE_CONVICTED, 'sentence' => self::SKYHORSE_SENTENCE,
            ],
            [
                'name' => 'Kenneth Loud Hawk', 'first' => 'Kenneth', 'last' => 'Loud Hawk',
                'gender' => 'Male', 'race' => 'Native American', 'state' => 'Oregon', 'era' => '1970s',
                'ideologies' => ['Indigenous rights', 'Red Power'],
                'affiliation' => ['American Indian Movement'],
                'bio' => sprintf(self::LOUD_HAWK, 'Kenneth Moses Loud Hawk'),
                'charges' => self::LOUD_HAWK_CHARGES, 'convicted' => self::LOUD_HAWK_CONVICTED, 'sentence' => self::LOUD_HAWK_SENTENCE,
            ],
            [
                'name' => 'Russell Redner', 'first' => 'Russell', 'last' => 'Redner',
                'gender' => 'Male', 'race' => 'Native American', 'state' => 'Oregon', 'era' => '1970s',
                'ideologies' => ['Indigenous rights', 'Red Power'],
                'affiliation' => ['American Indian Movement'],
                'bio' => sprintf(self::LOUD_HAWK, 'Russell Redner'),
                'charges' => self::LOUD_HAWK_CHARGES, 'convicted' => self::LOUD_HAWK_CONVICTED, 'sentence' => self::LOUD_HAWK_SENTENCE,
            ],
        ];

        DB::transaction(function () use ($cases) {
            foreach ($cases as $c) {
                if (Prisoner::where('name', $c['name'])->exists()) {
                    $this->warn("Skipped (already exists): {$c['name']}");
                    continue;
                }

                $prisoner = Prisoner::create([
                    'name'           => $c['name'],
                    'first_name'     => $c['first'],
                    'last_name'      => $c['last'],
                    'description'    => $c['bio'],
                    'gender'         => $c['gender'],
                    'race'           => $c['race'],
                    'state'          => $c['state'],
                    'era'            => $c['era'],
                    'ideologies'     => $c['ideologies'],
                    'affiliation'    => $c['affiliation'],
                    'in_custody'     => false,
                    'released'       => true,
                    'awaiting_trial' => false,
                ]);

                PrisonerCase::create([
                    'prisoner_id' => $prisoner->id,
                    'charges'     => $c['charges'],
                    'convicted'   => $c['convicted'],
                    'sentence'    => $c['sentence'],
                ]);

                $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
            }
        });

        return self::SUCCESS;
    }
}
