<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Enriches the eight Heart Mountain Fair Play Committee records (already added
 * by prisoners:add-heart-mountain) with the detailed case record from James
 * Omura, "Nisei Naysayer" (ed. Arthur A. Hansen, Stanford UP, 2018),
 * "Rocky Mountain Resistance," pp. 205–219: the United States v. Okamoto
 * prosecution (D. Wyo., 1944) — statute, indictment, judge, prosecutors,
 * custody details, and the 1945 Tenth Circuit reversal. Updates existing
 * records by name (idempotent); warns if a record is missing.
 */
class EnrichHeartMountainCase extends Command {
    protected $signature = 'prisoners:enrich-heart-mountain';
    protected $description = 'Enrich the Heart Mountain FPC records with the United States v. Okamoto (1944) case details';

    private const JUDGE = 'Eugene Rice (U.S. District Court, visiting from Oklahoma)';
    private const PROSECUTOR = 'U.S. Attorney Carl Sackett; Asst. U.S. Attorney John Pickett';
    private const INDICTED = 'Secret grand-jury indictment, Cheyenne, WY — May 10, 1944';

    private const LEADER_CHARGES = 'Conspiracy to counsel, aid, and abet evasion of the draft, under §11 of the Selective Training and Service Act of 1940 — United States v. Okamoto, U.S. District Court for the District of Wyoming, 1944.';
    private const LEADER_CONVICTED = 'Yes — convicted on November 1, 1944. The Tenth Circuit reversed the convictions 2–1 in 1945 (citing Keegan v. United States) and the Justice Department declined to retry; the leaders were freed after about eighteen months.';
    private const LEADER_SENTENCE = 'Removed to the U.S. Penitentiary at Leavenworth on November 2, 1944 (terms reported at two to four years); released after roughly eighteen months when the convictions were reversed on appeal.';

    public function handle(): int {
        $leavenworth = Institution::firstOrCreate(
            ['name' => 'United States Penitentiary, Leavenworth'],
            ['city' => 'Leavenworth', 'state' => 'Kansas']
        );

        // Per-person enriched biographies.
        $bios = [
            'Kiyoshi Okamoto' => "Kiyoshi Okamoto was the founder and leader of the Heart Mountain Fair Play Committee, the organized draft-resistance movement among the Japanese Americans incarcerated at the Heart Mountain camp in Wyoming during World War II. The Committee held that it was unconstitutional to draft imprisoned Nisei unless their rights as U.S. citizens were first restored. Indicted with the other leaders for conspiracy to counsel draft evasion (United States v. Okamoto), Okamoto — in his fifties and older than his co-defendants — refused to be bonded out of the Wyoming jails, reportedly telling the court \"Count me out!\" He filed a complaint over his jail conditions through ACLU attorney A. L. Wirin to U.S. Attorney General Francis Biddle, and kept apart from the other leaders while in custody. Convicted on November 1, 1944 and removed to the federal penitentiary at Leavenworth the next day, he was freed after about eighteen months when the Tenth Circuit reversed the convictions in 1945.",
            'Frank Emi' => "Frank Emi was a Los Angeles grocer and judo instructor who became the most prominent leader of the Heart Mountain Fair Play Committee, which organized Nisei at the Wyoming camp to refuse the WWII draft until their constitutional rights as imprisoned citizens were restored. Indicted for conspiracy to counsel draft evasion in United States v. Okamoto, Emi was bonded out of jail before the trial — ACLU attorney A. L. Wirin volunteering to await payment of his fees — and was convicted with the six other leaders on November 1, 1944. Removed to Leavenworth the next day, he was released after about eighteen months when the Tenth Circuit reversed the convictions in 1945. Emi remained a leading voice for the resisters' vindication for the rest of his life.",
            'Sam Horino' => "Isamu \"Sam\" Horino was a leader of the Heart Mountain Fair Play Committee. Before the draft-conspiracy indictment he had already been charged with disloyalty by the War Relocation Authority and sent to the Tule Lake segregation center. Tried with the other FPC leaders in United States v. Okamoto for conspiracy to counsel draft evasion, he was convicted on November 1, 1944, removed to Leavenworth the next day, and released after about eighteen months when the Tenth Circuit reversed the convictions in 1945.",
            'Guntaro Kubota' => "Guntaro Kubota was an Issei (first-generation immigrant) leader of the Heart Mountain Fair Play Committee who stood with the Nisei resisters despite not being subject to the draft himself. Bonded out of jail by friends before trial, he was tried with the other FPC leaders in United States v. Okamoto for conspiracy to counsel draft evasion, convicted on November 1, 1944, and removed to Leavenworth the next day. He was released after about eighteen months when the Tenth Circuit reversed the convictions in 1945.",
            'Paul Nakadate' => "Paul Nakadate was a leader of the Heart Mountain Fair Play Committee and one of the seven leaders tried in United States v. Okamoto for conspiracy to counsel draft evasion. Within the Committee he defended the Denver journalist James Omura against accusations from some members that Omura was a \"spy.\" Convicted on November 1, 1944 and removed to Leavenworth the next day, he was released after about eighteen months when the Tenth Circuit reversed the convictions in 1945.",
            'Min Tamesa' => "Minoru \"Min\" Tamesa was one of the seven leaders of the Heart Mountain Fair Play Committee tried in United States v. Okamoto for conspiracy to counsel evasion of the draft, after the Committee urged imprisoned Nisei to refuse induction until their rights as citizens were restored. Convicted on November 1, 1944 and removed to the Leavenworth federal penitentiary the next day, he was released after about eighteen months when the Tenth Circuit reversed the convictions in 1945.",
            'Ben Wakaye' => "Tsutomu \"Ben\" Wakaye was one of the seven leaders of the Heart Mountain Fair Play Committee tried in United States v. Okamoto for conspiracy to counsel evasion of the draft. Convicted on November 1, 1944 and removed to the Leavenworth federal penitentiary the next day, he was released after about eighteen months when the Tenth Circuit reversed the convictions in 1945.",
            'James Omura' => "James Matsumoto Omura (born Utaka Matsumoto; also known as Jimmie Omura) was the English-section editor of the Denver Japanese American newspaper Rocky Shimpo, who used his editorials to defend the right of the Heart Mountain draft resisters to challenge their incarceration — though he was not himself a member of the Fair Play Committee. Before dawn on July 20, 1944, FBI agents and U.S. marshals arrested him at his cabin in Lakewood, Colorado; arraigned in Denver the same day, he pleaded not guilty and was jailed in Cheyenne, Wyoming, where he was held in solitary confinement in a vacated women's section for his first nineteen days, initially denied shaving, reading, and writing materials. Unable to post even a reduced \$1,500 bond — his family was broke and Denver bonding companies refused to write draft-case bonds — he spent about 65 days in jail before ACLU attorney A. L. Wirin arranged a bond and he was released on September 23, 1944. Defended by Sidney Jacobs and Wyoming co-counsel L. C. Sampson on First Amendment grounds, Omura was tried with the seven Fair Play Committee leaders in United States v. Okamoto and, on November 1, 1944, was the only one of the eight defendants acquitted. Ostracized within his own community for years afterward, he was later honored as a defender of press freedom and Japanese American civil rights.",
        ];

        // Per-person case-field overrides on top of the shared leader defaults.
        $caseOverrides = [
            'James Omura' => [
                'charges'        => 'Conspiracy to counsel, aid, and abet evasion of the draft (§11, Selective Training and Service Act of 1940) — for his Rocky Shimpo editorials supporting the Heart Mountain resisters; tried in United States v. Okamoto (1944) with a First Amendment defense.',
                'convicted'      => 'No — acquitted on November 1, 1944 after about six hours of deliberation, the only one of the eight defendants found not guilty.',
                'sentence'       => 'None — acquitted.',
                'arrest_date'    => '1944-07-20',
                'institution_id' => null,
            ],
        ];

        DB::transaction(function () use ($bios, $caseOverrides, $leavenworth) {
            foreach ($bios as $name => $bio) {
                $prisoner = Prisoner::where('name', $name)->first();
                if (! $prisoner) {
                    $this->warn("Missing (run prisoners:add-heart-mountain first): {$name}");
                    continue;
                }

                $prisoner->update(['description' => $bio]);

                $fields = [
                    'charges'        => self::LEADER_CHARGES,
                    'convicted'      => self::LEADER_CONVICTED,
                    'sentence'       => self::LEADER_SENTENCE,
                    'judge'          => self::JUDGE,
                    'prosecutor'     => self::PROSECUTOR,
                    'indicted'       => self::INDICTED,
                    'plead'          => 'Not guilty',
                    'incarceration_date' => '1944-11-02',
                    'institution_id' => $leavenworth->id,
                ];
                $fields = array_merge($fields, $caseOverrides[$name] ?? []);

                $case = $prisoner->cases()->first();
                if ($case) {
                    $case->update($fields);
                } else {
                    PrisonerCase::create(array_merge(['prisoner_id' => $prisoner->id], $fields));
                }

                $this->info("Enriched: {$name}");
            }
        });

        return self::SUCCESS;
    }
}
