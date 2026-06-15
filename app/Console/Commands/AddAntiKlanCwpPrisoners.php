<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Batch 7 of the comprehensive-sweep additions: anti-Klan / Communist Workers
 * Party repression cases (1979–81), where the state prosecuted the leftists and
 * let the right-wing attackers walk. Cross-checked as not already in the
 * database:
 *   - Nelson Johnson           (Greensboro Massacre survivor, charged with riot)
 *   - Allen Blitz, Dorothy Blitz (Greensboro demonstrators charged with riot)
 *   - Mark Loo, Rodney Johnson, David Boyd ("NASSCO 3"; FBI bomb-plot frame-up)
 * Sourced to the Greensboro massacre record, UPI/AP, and the CWP/EROL movement
 * archive (defense by attorney Leonard Weinglass). Idempotent (skips by name).
 */
class AddAntiKlanCwpPrisoners extends Command {
    protected $signature = 'prisoners:add-anti-klan-cwp';
    protected $description = 'Add anti-Klan / CWP repression cases (Greensboro defendants + the NASSCO 3)';

    private const NASSCO = "%s was one of the \"NASSCO 3,\" three San Diego shipyard activists convicted in 1981 in what their supporters — and their attorney, Leonard Weinglass — denounced as an FBI and company frame-up. All three were linked to the militant Iron Workers Local 627 at the National Steel and Shipbuilding Company (NASSCO) and to the Communist Workers Party, and were charged with conspiring to pipe-bomb the shipyard's electrical transformers. The supposed plot had been organized and pushed by Ramon Barton, a paid informant and agent provocateur who infiltrated the union for the San Diego police and the FBI — agencies that, evidence at trial showed, had met with NASSCO management weeks before any plot surfaced. On June 6, 1981 the three were convicted of conspiracy and explosives-possession counts and sentenced to six months each.";

    private const NASSCO_CHARGES = 'Conspiracy to bomb the electrical transformers at the NASSCO shipyard in San Diego, plus explosives-possession counts — charges the defendants and their attorney Leonard Weinglass called an FBI and company frame-up engineered by paid provocateur Ramon Barton.';
    private const NASSCO_CONVICTED = 'Yes — convicted on June 6, 1981 of conspiracy and explosives possession (the defense argued entrapment).';
    private const NASSCO_SENTENCE = 'Six months.';

    private const GREENSBORO_RIOT = "%s was one of the Communist Workers Party anti-Klan demonstrators charged with rioting after the November 3, 1979 Greensboro Massacre — the attack in which Ku Klux Klansmen and American Nazis shot and killed five CWP activists at a \"Death to the Klan\" march in Greensboro, North Carolina. In a striking inversion of justice, the gunmen were acquitted by all-white juries while the wounded and surviving demonstrators were the ones prosecuted. The rioting charges against the demonstrators were dropped in November 1980.";

    private const GREENSBORO_CHARGES = 'Rioting — brought against an anti-Klan demonstrator after Klansmen and Nazis killed five Communist Workers Party activists at the November 3, 1979 Greensboro Massacre, while the gunmen themselves were acquitted.';
    private const GREENSBORO_CONVICTED = 'No — the rioting charges against the Greensboro demonstrators were dropped in November 1980.';

    public function handle(): int {
        $cases = [
            [
                'name' => 'Nelson Johnson', 'first' => 'Nelson', 'last' => 'Johnson',
                'gender' => 'Male', 'race' => 'Black', 'state' => 'North Carolina', 'era' => '1970s',
                'ideologies' => ['Black liberation', 'Communism', 'Anti-racism'],
                'affiliation' => ['Communist Workers Party', 'Greensboro Association of Poor People'],
                'bio' => 'The Reverend Nelson Johnson was a veteran Black freedom organizer in Greensboro, North Carolina — a leader of the student movement at North Carolina A&T in the late 1960s and founder of the Greensboro Association of Poor People — who by 1979 was an organizer with the Communist Workers Party. On November 3, 1979 he helped lead the "Death to the Klan" march when Ku Klux Klansmen and American Nazis opened fire, killing five of his comrades in what became known as the Greensboro Massacre. Johnson was stabbed in the attack — and then arrested at the scene, held overnight, and charged with inciting to riot and resisting arrest; he ultimately faced seven charges arising from the day and was held under a bond twice that of any of the Klansmen, none of whom would be convicted. In November 1980 the rioting charges against Johnson and his fellow demonstrators were dropped. He went on to become a minister and, decades later, a driving force behind the Greensboro Truth and Reconciliation Commission, the first such body in the United States.',
                'charges' => 'Inciting to riot and resisting arrest (seven charges in all) — brought against the wounded march leader after Klansmen and Nazis killed five anti-Klan demonstrators at the November 3, 1979 Greensboro Massacre, even as the killers were acquitted.',
                'convicted' => 'No — the rioting charges against Johnson and his fellow demonstrators were dropped in November 1980 (while the Klan and Nazi shooters were acquitted by all-white juries).',
                'sentence' => 'Jailed at the scene and held under a bond twice that of any Klansman; the charges were dropped in 1980 (no conviction).',
            ],
            [
                'name' => 'Allen Blitz', 'first' => 'Allen', 'last' => 'Blitz',
                'gender' => 'Male', 'race' => null, 'state' => 'Virginia', 'era' => '1970s',
                'ideologies' => ['Communism', 'Anti-racism'],
                'affiliation' => ['Communist Workers Party'],
                'bio' => sprintf(self::GREENSBORO_RIOT, 'Allen Blitz — a marcher who returned fire as the Klansmen and Nazis opened fire —'),
                'charges' => self::GREENSBORO_CHARGES, 'convicted' => self::GREENSBORO_CONVICTED, 'sentence' => null,
            ],
            [
                'name' => 'Dorothy Blitz', 'first' => 'Dorothy', 'last' => 'Blitz',
                'gender' => 'Female', 'race' => null, 'state' => 'Virginia', 'era' => '1970s',
                'ideologies' => ['Communism', 'Anti-racism'],
                'affiliation' => ['Communist Workers Party'],
                'bio' => sprintf(self::GREENSBORO_RIOT, 'Dorothy Blitz, of Martinsville, Virginia,'),
                'charges' => self::GREENSBORO_CHARGES, 'convicted' => self::GREENSBORO_CONVICTED, 'sentence' => null,
            ],
            [
                'name' => 'Mark Loo', 'first' => 'Mark', 'last' => 'Loo',
                'gender' => 'Male', 'race' => 'Asian', 'state' => 'California', 'era' => '1980s',
                'ideologies' => ['Communism', 'Labor'],
                'affiliation' => ['Communist Workers Party'],
                'bio' => sprintf(self::NASSCO, 'Mark Loo, a Chinese-American member of the Communist Workers Party,'),
                'charges' => self::NASSCO_CHARGES, 'convicted' => self::NASSCO_CONVICTED, 'sentence' => self::NASSCO_SENTENCE,
            ],
            [
                'name' => 'Rodney Johnson', 'first' => 'Rodney', 'last' => 'Johnson',
                'gender' => 'Male', 'race' => null, 'state' => 'California', 'era' => '1980s',
                'ideologies' => ['Communism', 'Labor'],
                'affiliation' => ['Communist Workers Party'],
                'bio' => sprintf(self::NASSCO, 'Rodney Johnson, a member of the Communist Workers Party,'),
                'charges' => self::NASSCO_CHARGES, 'convicted' => self::NASSCO_CONVICTED, 'sentence' => self::NASSCO_SENTENCE,
            ],
            [
                'name' => 'David Boyd', 'first' => 'David', 'last' => 'Boyd',
                'gender' => 'Male', 'race' => null, 'state' => 'California', 'era' => '1980s',
                'ideologies' => ['Communism', 'Labor'],
                'affiliation' => ['Communist Workers Party', 'Iron Workers Local 627 (NASSCO)'],
                'bio' => sprintf(self::NASSCO, 'David Boyd, an Iron Workers union member at the NASSCO shipyard,'),
                'charges' => self::NASSCO_CHARGES, 'convicted' => self::NASSCO_CONVICTED, 'sentence' => self::NASSCO_SENTENCE,
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
