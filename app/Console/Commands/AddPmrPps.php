<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds 12 PPs from Port Militarization Resistance (PMR) — the
 * 2006-2010 Olympia / Tacoma anti-war movement that blockaded
 * Stryker-vehicle shipments out of Washington state ports bound
 * for Iraq and Afghanistan, and that became the target of a
 * documented two-year Army CIFA infiltration by John Towery
 * ("John Jacob") of the Force Protection Service at Joint Base
 * Lewis-McChord.
 *
 *   - 7 named plaintiffs in Panagacos v. Towery (W.D. Wash. 2010-2017),
 *     the federal civil suit over Army domestic spying on the movement:
 *     Julianne Panagacos, Brendan Maslauskas Dunn, Jeffrey Berryhill,
 *     Glenn Crespo, Julia Garfield, Chris Grande, Andrea Robbins
 *   - 4 additional PMR arrestees: Phan Nguyen (Olympia + Tacoma 2006-07),
 *     Shyam Prasad Khanna (Nov 2007 Stryker convoy blockade),
 *     Joshua Simpson (Evergreen / Iraq vet, Nov 2007),
 *     Philip "Phil" Chinn (multiple arrests; ultimately won >$400k WSP
 *     false-arrest settlement)
 *   - Jeff Monson — UFC heavyweight champion who joined the 2006 PMR
 *     human chain; later convicted of 1st- and 2nd-degree malicious
 *     mischief for the November 26, 2008 anarchy/peace-sign painting
 *     of the Washington State Capitol columns; 90 days work release +
 *     $21,894 restitution.
 */
final class AddPmrPps extends Command {
    protected $signature = 'archive:add-pmr-pps';
    protected $description = 'Add 12 PPs from Port Militarization Resistance (Olympia/Tacoma 2006-2010)';

    public function handle(): int {
        $added = 0; $skipped = 0;
        foreach ($this->prisoners() as $p) {
            $exit = $this->call('prisoner:add', ['json' => json_encode($p)]);
            if ($exit === self::SUCCESS) { $this->info('ADD: '.$p['name']); $added++; }
            else { $skipped++; }
        }
        $this->info("Done — added {$added}, skipped {$skipped}.");
        return self::SUCCESS;
    }

    /** @return array<int, array<string, mixed>> */
    private function prisoners(): array {
        $out = [];

        $panagacosDesc = 'Named plaintiff in Panagacos v. Towery (W.D. Wash. No. 3:10-cv-05018-RBL), the federal civil-rights lawsuit filed in January 2010 by seven Port Militarization Resistance (PMR) activists against the U.S. Army, U.S. Air Force, Pierce County, the City of Lakewood, and Army intelligence operative John Towery. Towery, working under the alias "John Jacob" out of the Force Protection Service (CIFA successor) at Joint Base Lewis-McChord, spent more than two years (2007-2009) inside the PMR/SDS scene in Olympia attending meetings, hosting parties, befriending members, and feeding their identities, license plates, and political analyses to the Army, FBI Joint Terrorism Task Force, and local fusion-center police. The infiltration was exposed in July 2009 when Olympia activists Brendan Maslauskas Dunn and Drew Hendricks obtained Towery\'s identity through a Washington Public Records Act request. The case survived motions to dismiss (782 F. Supp. 2d 1183, 2011; 9th Cir. aff\'d 2012) but was dismissed on summary judgment by Judge Ronald B. Leighton in 2014, with the 9th Circuit hearing the appeal in 2017.';
        $panagacosBase = [
            'state' => 'Washington',
            'race' => 'White',
            'ideologies' => ['Anti-war', 'Anti-imperialist', 'Anarchist'],
            'affiliation' => ['Port Militarization Resistance', 'SDS'],
            'era' => '2000s',
            'in_custody' => false,
            'released' => true,
        ];

        $out[] = [
            'name' => 'Julianne Panagacos',
            'first_name' => 'Julianne',
            'last_name' => 'Panagacos',
            'description' => $panagacosDesc.' Panagacos — the lead-named plaintiff — was a PMR organizer in Olympia and was arrested at the November 2007 Port of Olympia blockades against the USNS Brittin Stryker offload.',
            'gender' => 'Female',
            'cases' => [[
                'institution_state' => 'Washington',
                'charges' => 'Misdemeanor obstruction / pedestrian-in-roadway (Port of Olympia, November 2007); Panagacos v. Towery civil plaintiff.',
                'arrest_date' => '2007-11-09',
                'sentence' => 'PMR criminal charges generally pled out or dismissed; Panagacos v. Towery summary judgment for government 2014.',
            ]],
        ] + $panagacosBase;
        $out[] = [
            'name' => 'Brendan Maslauskas Dunn',
            'first_name' => 'Brendan',
            'middle_name' => 'Maslauskas',
            'last_name' => 'Dunn',
            'description' => $panagacosDesc.' Dunn — an Olympia IWW organizer and PMR activist — filed the 2009 Washington Public Records Act request that exposed John Towery\'s true identity. The Army\'s own surveillance file on Dunn included a "domestic terrorist" designation. Panagacos plaintiff.',
            'gender' => 'Male',
            'cases' => [[
                'institution_state' => 'Washington',
                'charges' => 'Multiple PMR-related arrests for misdemeanor obstruction; Panagacos v. Towery civil plaintiff; subject of Army "domestic terrorist" dossier.',
                'arrest_date' => '2007-11-09',
                'sentence' => 'PMR criminal charges generally pled out or dismissed; civil case lost on summary judgment 2014.',
            ]],
        ] + $panagacosBase;
        $out[] = [
            'name' => 'Jeffrey Berryhill',
            'first_name' => 'Jeffrey',
            'last_name' => 'Berryhill',
            'description' => $panagacosDesc.' Berryhill, a PMR activist, was the subject of an Army "domestic terrorist" dossier produced by Towery and shared with multiple federal and local police agencies. Panagacos plaintiff.',
            'gender' => 'Male',
            'cases' => [[
                'institution_state' => 'Washington',
                'charges' => 'PMR-related arrests; subject of Army "domestic terrorist" dossier; Panagacos v. Towery civil plaintiff.',
                'arrest_date' => '2007-11-09',
                'sentence' => 'PMR criminal charges generally pled out or dismissed; civil case lost on summary judgment 2014.',
            ]],
        ] + $panagacosBase;
        $out[] = [
            'name' => 'Glenn Crespo',
            'first_name' => 'Glenn',
            'last_name' => 'Crespo',
            'description' => $panagacosDesc.' Crespo — a PMR/SDS organizer — was specifically targeted by Towery, who befriended him, offered him rides, and (per the federal complaint) attempted to entrap him into criminal conduct. Panagacos plaintiff.',
            'gender' => 'Male',
            'cases' => [[
                'institution_state' => 'Washington',
                'charges' => 'PMR-related arrests; targeted for entrapment by Army informant John Towery; Panagacos v. Towery civil plaintiff.',
                'arrest_date' => '2007-11-09',
                'sentence' => 'PMR criminal charges generally pled out or dismissed; civil case lost on summary judgment 2014.',
            ]],
        ] + $panagacosBase;
        $out[] = [
            'name' => 'Julia Garfield',
            'first_name' => 'Julia',
            'last_name' => 'Garfield',
            'description' => $panagacosDesc.' Garfield was arrested at the November 2007 Port of Olympia blockades. Panagacos plaintiff.',
            'gender' => 'Female',
            'cases' => [[
                'institution_state' => 'Washington',
                'charges' => 'Misdemeanor obstruction (Port of Olympia, November 2007); Panagacos v. Towery civil plaintiff.',
                'arrest_date' => '2007-11-09',
                'sentence' => 'PMR criminal charges generally pled out or dismissed; civil case lost on summary judgment 2014.',
            ]],
        ] + $panagacosBase;
        $out[] = [
            'name' => 'Chris Grande',
            'first_name' => 'Chris',
            'last_name' => 'Grande',
            'description' => $panagacosDesc.' Grande was arrested at the November 2007 Port of Olympia blockades. Panagacos plaintiff.',
            'gender' => 'Male',
            'cases' => [[
                'institution_state' => 'Washington',
                'charges' => 'Misdemeanor obstruction (Port of Olympia, November 2007); Panagacos v. Towery civil plaintiff.',
                'arrest_date' => '2007-11-09',
                'sentence' => 'PMR criminal charges generally pled out or dismissed; civil case lost on summary judgment 2014.',
            ]],
        ] + $panagacosBase;
        $out[] = [
            'name' => 'Andrea Robbins',
            'first_name' => 'Andrea',
            'last_name' => 'Robbins',
            'description' => $panagacosDesc.' Robbins was arrested at the November 2007 Port of Olympia blockades. Panagacos plaintiff.',
            'gender' => 'Female',
            'cases' => [[
                'institution_state' => 'Washington',
                'charges' => 'Misdemeanor obstruction (Port of Olympia, November 2007); Panagacos v. Towery civil plaintiff.',
                'arrest_date' => '2007-11-09',
                'sentence' => 'PMR criminal charges generally pled out or dismissed; civil case lost on summary judgment 2014.',
            ]],
        ] + $panagacosBase;

        // === Other named PMR arrestees ===
        $pmrBase = [
            'state' => 'Washington',
            'race' => 'White',
            'gender' => 'Male',
            'ideologies' => ['Anti-war', 'Anti-imperialist'],
            'affiliation' => ['Port Militarization Resistance'],
            'era' => '2000s',
            'in_custody' => false,
            'released' => true,
        ];

        $out[] = [
            'name' => 'Phan Nguyen',
            'first_name' => 'Phan',
            'last_name' => 'Nguyen',
            'description' => 'Vietnamese-American Port Militarization Resistance organizer in Olympia, Washington. Arrested at the November 2006 Port of Olympia blockade against the Stryker offload and again at the March 2007 Port of Tacoma blockade against the USNS Pomeroy — two of the highest-profile dock actions of the PMR campaign.',
            'race' => 'Asian',
            'cases' => [[
                'institution_state' => 'Washington',
                'charges' => 'Misdemeanor obstruction / pedestrian-in-roadway (Port of Olympia Nov 2006; Port of Tacoma Mar 2007).',
                'arrest_date' => '2006-11-13',
                'sentence' => 'Misdemeanor charges generally pled out or dismissed; movement-defense outcome.',
            ]],
        ] + $pmrBase;

        $out[] = [
            'name' => 'Shyam Prasad Khanna',
            'first_name' => 'Shyam',
            'middle_name' => 'Prasad',
            'last_name' => 'Khanna',
            'description' => 'South Asian-American PMR activist arrested for blocking a Stryker convoy departing the Port of Olympia in November 2007 — the same wave of confrontations that produced the Olympia 22 prosecutions and the surveillance later exposed in Panagacos v. Towery.',
            'race' => 'Asian',
            'cases' => [[
                'institution_state' => 'Washington',
                'charges' => 'Misdemeanor obstruction / pedestrian-in-roadway (Port of Olympia, November 2007 Stryker convoy blockade).',
                'arrest_date' => '2007-11-09',
                'sentence' => 'Misdemeanor charges generally pled out or dismissed.',
            ]],
        ] + $pmrBase;

        $out[] = [
            'name' => 'Joshua Simpson',
            'first_name' => 'Joshua',
            'last_name' => 'Simpson',
            'description' => 'Iraq War combat veteran and Evergreen State College student arrested at the November 2007 Port of Olympia blockades against the USNS Brittin Stryker offload. Simpson was one of several veteran-resisters whose participation made PMR a flashpoint between Joint Base Lewis-McChord soldiers and the civilian anti-war movement.',
            'cases' => [[
                'institution_state' => 'Washington',
                'charges' => 'Misdemeanor obstruction (Port of Olympia, November 2007).',
                'arrest_date' => '2007-11-09',
                'sentence' => 'Misdemeanor charges generally pled out or dismissed.',
            ]],
        ] + $pmrBase;

        $out[] = [
            'name' => 'Philip Chinn',
            'aka' => 'Phil Chinn',
            'first_name' => 'Philip',
            'last_name' => 'Chinn',
            'description' => 'Evergreen State College student and PMR organizer in Olympia. Arrested multiple times during the 2006-2008 PMR campaign. The defining incident: on May 30, 2007 the Washington State Patrol pulled Chinn over near the Port of Aberdeen, falsely arrested him for DUI, and held him 36 hours despite a blood-alcohol level of 0.000. He sued the WSP under 42 U.S.C. §1983; the State of Washington settled for in excess of $400,000 — one of the largest false-arrest settlements ever obtained by a Washington anti-war organizer. Chinn was not a named plaintiff in Panagacos v. Towery but was likewise a target of John Towery\'s surveillance operation.',
            'cases' => [[
                'institution_state' => 'Washington',
                'charges' => 'Falsely arrested for DUI by WSP near Port of Aberdeen, May 30, 2007 (BAC: 0.000); held 36 hours.',
                'arrest_date' => '2007-05-30',
                'release_date' => '2007-05-31',
                'sentenced_date' => '2009-06-01',
                'convicted' => 'No — civil settlement with WSP in excess of $400,000.',
                'sentence' => 'Falsely arrested; sued and won >$400,000 civil settlement against Washington State Patrol.',
            ]],
        ] + $pmrBase;

        $out[] = [
            'name' => 'Jeff Monson',
            'first_name' => 'Jeff',
            'last_name' => 'Monson',
            'description' => 'Former UFC heavyweight title challenger and self-identified anarchist. Joined the May 23-24, 2006 PMR human-chain blockade at the Port of Olympia (his refusal to be moved forced the Stryker convoy to detour) and subsequent PMR actions through 2008. On November 26, 2008 Monson spray-painted an anarchy symbol, peace sign, and "No War / No Poverty" on the marble columns of the Washington State Capitol in Olympia; the action only came to police attention after ESPN The Magazine published photos of it in January 2009. Charged with first- and second-degree malicious mischief; pled guilty; sentenced to 90 days of work-release / electronic home monitoring and $21,894 restitution.',
            'birthdate' => '1971-01-18',
            'cases' => [[
                'institution_state' => 'Washington',
                'charges' => 'First- and second-degree malicious mischief (Washington State Capitol column painting, November 26, 2008).',
                'arrest_date' => '2009-01-30',
                'sentenced_date' => '2009-10-14',
                'convicted' => 'Pled guilty.',
                'sentence' => '90 days work release / electronic home monitoring + $21,894 restitution.',
            ]],
        ] + $pmrBase;

        return $out;
    }
}
