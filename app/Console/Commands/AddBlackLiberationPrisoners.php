<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Batch 2 of the comprehensive-sweep additions: Black-liberation / civil-rights
 * frame-up and political-speech cases (1960s–70s), cross-checked as not already
 * in the database:
 *   - Dessie Woods  (Georgia self-defense-against-rape case; 22 years)
 *   - Bill Epton    (Progressive Labor Party; 1965 criminal-anarchy conviction)
 *   - Robert Collier(1965 Statue of Liberty plot built on an NYPD provocateur)
 *   - Lee Otis Johnson (Houston SNCC; 30 years for one marijuana cigarette)
 * Sourced to the court records (Woods v. Linahan 648 F.2d 973; Epton v. New York
 * 390 U.S. 29; United States v. Bowe 360 F.2d 1; Johnson v. Beto 337 F. Supp.
 * 1371), UPI/AP, and the movement defense campaigns. Idempotent (skips by name).
 */
class AddBlackLiberationPrisoners extends Command {
    protected $signature = 'prisoners:add-black-liberation';
    protected $description = 'Add Dessie Woods, Bill Epton, Robert Collier, and Lee Otis Johnson (Black-liberation frame-up cases)';

    public function handle(): int {
        $cases = [
            [
                'name' => 'Dessie Woods', 'first' => 'Dessie', 'last' => 'Woods',
                'gender' => 'Female', 'race' => 'Black', 'birthdate' => null, 'death' => null,
                'state' => 'Georgia', 'era' => '1970s',
                'ideologies' => ['Black liberation', "Women's self-defense"],
                'affiliation' => [],
                'institution' => ['name' => 'Georgia Rehabilitation Center for Women', 'city' => 'Hardwick', 'state' => 'Georgia'],
                'bio' => 'Dessie Woods was a young Black Georgia woman who became a national cause célèbre after she shot and killed a white man, Ronnie Horne, with his own gun on June 16, 1975 while resisting what she said was an attempted rape. Woods and her companion Cheryl Todd were hitchhiking when Horne picked them up; he became abusive and demanded sex, and — according to Woods\'s account — reached for a pistol on the car seat, whereupon she wrestled it away, shot him, and took about $120 from his wallet. An all-white jury convicted her in February 1976 of voluntary manslaughter and armed robbery in Wheeler County, and she was sentenced to 22 years. A "National Committee to Defend Dessie Woods," led by the African People\'s Socialist Party, made her case a symbol of Black women\'s right to self-defense against sexual violence, and civil-rights and feminist organizations across the country took it up. A federal appeals court (Woods v. Linahan) later set aside the armed-robbery conviction as unsupported by the evidence while leaving the manslaughter conviction standing. Woods was released from the Georgia Rehabilitation Center for Women at Hardwick in 1981 after about five years and afterward relocated to California.',
                'charges' => 'Voluntary manslaughter and armed robbery for the June 16, 1975 shooting of Ronnie Horne, a white man who picked her and a companion up hitchhiking — a killing Woods said was self-defense against an attempted rape, carried out with Horne\'s own pistol. Her supporters viewed the prosecution as the criminalization of a Black woman\'s self-defense against sexual assault.',
                'convicted' => 'Yes — convicted by an all-white jury in February 1976 of voluntary manslaughter and armed robbery; the armed-robbery conviction was later set aside on federal appeal (Woods v. Linahan) as unsupported by the evidence.',
                'sentence' => '22 years; released from the Georgia Rehabilitation Center for Women at Hardwick in 1981 after serving about five years.',
            ],
            [
                'name' => 'Bill Epton', 'first' => 'Bill', 'last' => 'Epton',
                'gender' => 'Male', 'race' => 'Black', 'birthdate' => null, 'death' => null,
                'state' => 'New York', 'era' => '1960s',
                'ideologies' => ['Communism', 'Black liberation'],
                'affiliation' => ['Progressive Labor Party'],
                'institution' => null,
                'bio' => 'Bill Epton (William Leo Epton, 1932–2002) was a Harlem-born electrician and a vice-chairman of the Progressive Labor Party who led its Harlem branch, the Harlem Progressive Labor Movement. After an off-duty police lieutenant shot and killed 15-year-old James Powell in Harlem on July 16, 1964 — the spark for the Harlem rebellion of that summer — Epton helped organize protests and was arrested for speeches denouncing the police. In December 1965 a New York jury convicted him of criminal anarchy (advocating the overthrow of organized government), conspiracy to riot, and conspiracy to advocate criminal anarchy. It was the first prosecution under New York\'s criminal-anarchy statute since the cases that followed the 1919 Red Scare, and Epton was sentenced to three concurrent one-year terms, which he served. His appeal, Epton v. New York, reached the U.S. Supreme Court, which in 1968 declined to hear it (390 U.S. 29) over the dissents of Justices Douglas, Stewart, and Black, who viewed the conviction as punishment of political speech.',
                'charges' => 'Criminal anarchy (advocating the overthrow of organized government), conspiracy to riot, and conspiracy to advocate criminal anarchy — for speeches the Progressive Labor Party leader gave amid the July 1964 Harlem rebellion that followed the police killing of 15-year-old James Powell. It was the first New York criminal-anarchy prosecution since the 1919 Red Scare.',
                'convicted' => 'Yes — convicted by a New York jury in December 1965; the U.S. Supreme Court declined to review the case in 1968 (Epton v. New York, 390 U.S. 29) over three dissents.',
                'sentence' => 'Three concurrent one-year terms (served).',
            ],
            [
                'name' => 'Robert Collier', 'first' => 'Robert', 'last' => 'Collier',
                'gender' => 'Male', 'race' => 'Black', 'birthdate' => null, 'death' => null,
                'state' => 'New York', 'era' => '1960s',
                'ideologies' => ['Black liberation', 'Black nationalism'],
                'affiliation' => ['Black Liberation Front'],
                'institution' => null,
                'bio' => 'Robert Steele Collier was a 28-year-old Black activist convicted in a 1965 federal case over a plot to dynamite the Statue of Liberty, the Liberty Bell, and the Washington Monument. Collier and two co-defendants — Walter Bowe and Khaleel Sayyed — were arrested on February 16, 1965 after an undercover New York City police officer, Raymond Wood, infiltrated their circle; a Canadian woman, Michelle Duclos, was accused of bringing the dynamite across the border. Collier maintained that Wood had acted as an agent provocateur, and the case (United States v. Bowe, 360 F.2d 1) was long cited by the movement as an example of police entrapment of Black radicals — a view that gained force in 2021, when the family of Raymond Wood released a deathbed letter in which he said the NYPD had directed him to infiltrate Black and civil-rights groups and lure members into crimes. Collier was sentenced to ten years, reduced to five, and served about 21 months. After his release he remained active in the Black freedom movement, working with the Black Panther Party and Harlem community programs.',
                'charges' => 'Federal conspiracy to destroy national monuments — a 1965 plot to dynamite the Statue of Liberty, the Liberty Bell, and the Washington Monument — built on the infiltration of his circle by undercover NYPD officer Raymond Wood. Collier maintained that Wood was an agent provocateur who instigated the plot.',
                'convicted' => 'Yes — convicted in 1965 (United States v. Bowe, 360 F.2d 1); sentenced to ten years, reduced to five, of which he served about 21 months.',
                'sentence' => 'Ten years, reduced to five; served roughly 21 months.',
            ],
            [
                'name' => 'Lee Otis Johnson', 'first' => 'Lee Otis', 'last' => 'Johnson',
                'gender' => 'Male', 'race' => 'Black', 'birthdate' => null, 'death' => null,
                'state' => 'Texas', 'era' => '1960s',
                'ideologies' => ['Civil rights', 'Black Power'],
                'affiliation' => ['Student Nonviolent Coordinating Committee (SNCC)'],
                'institution' => null,
                'bio' => 'Lee Otis Johnson was a Houston civil-rights organizer with the Student Nonviolent Coordinating Committee (SNCC) who became a nationally known symbol of selective prosecution after he was sentenced to 30 years in prison for giving a single marijuana cigarette to an undercover narcotics agent. The arrest came in 1968, days after Johnson had denounced Houston\'s mayor at a memorial rally for the assassinated Dr. Martin Luther King Jr. An all-white jury convicted him and imposed the 30-year term — a sentence so disproportionate to the alleged offense that "Free Lee Otis Johnson" became a rallying cry on Texas campuses and beyond. In 1972 a federal court overturned the conviction (Johnson v. Beto, 337 F. Supp. 1371), finding constitutional violations in his prosecution, and Johnson was freed after roughly four years behind bars.',
                'charges' => 'Delivery of a single marijuana cigarette to an undercover narcotics agent — a charge brought in 1968, days after the SNCC organizer publicly denounced Houston\'s mayor at a memorial for Dr. Martin Luther King Jr., and widely seen as political retaliation.',
                'convicted' => 'Yes — convicted by an all-white jury and sentenced to 30 years; a federal court overturned the conviction in 1972 (Johnson v. Beto, 337 F. Supp. 1371).',
                'sentence' => '30 years for one marijuana cigarette; freed in 1972 after about four years when the conviction was struck down.',
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
                    'race'           => $c['race'],
                    'birthdate'      => $c['birthdate'],
                    'death_date'     => $c['death'],
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
