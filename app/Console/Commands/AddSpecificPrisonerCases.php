<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddSpecificPrisonerCases extends Command
{
    protected $signature = 'prisoners:add-specific-cases';
    protected $description = 'Attach researched cases to a specific list of prisoners (Ranjani Srinivasan, Momodou Taal, Badar Khan Suri, Lelo, Fred Burton, Kojo Bomani Sababu, Ronald Reed, H. Rap Brown).';

    public function handle(): int
    {
        $columbiaUni       = Institution::firstOrCreate(['name' => 'NYPD / Columbia University Public Safety (Hamilton Hall, April 2024)'], ['city' => 'New York', 'state' => 'New York']);
        $iceNySyracuse     = Institution::firstOrCreate(['name' => 'ICE Detention (New York / self-removal)'], ['state' => 'New York']);
        $iceTexasFacility  = Institution::firstOrCreate(['name' => 'ICE Detention Facility, Texas'], ['state' => 'Texas']);
        $iceWashington     = Institution::firstOrCreate(['name' => 'ICE Detention (Washington State)'], ['state' => 'Washington']);
        $sciPhoenix        = Institution::firstOrCreate(['name' => 'SCI Phoenix'], ['city' => 'Collegeville', 'state' => 'Pennsylvania']);
        $allenwood         = Institution::firstOrCreate(['name' => 'USP Allenwood'], ['city' => 'White Deer', 'state' => 'Pennsylvania']);
        $stillwater        = Institution::firstOrCreate(['name' => 'MCF-Stillwater'], ['city' => 'Bayport', 'state' => 'Minnesota']);
        $tucsonUSP         = Institution::firstOrCreate(['name' => 'USP Tucson'], ['city' => 'Tucson', 'state' => 'Arizona']);

        $cases = [
            [
                'name'               => 'Ranjani Srinivasan',
                'institution_id'     => $columbiaUni->id,
                'charges'            => 'Briefly detained during the April 30, 2024 NYPD raid that cleared the Hamilton Hall occupation at Columbia University; charges dismissed. F-1 student visa revoked by the U.S. State Department on or about March 5, 2025 amid the Trump administration\'s campaign targeting pro-Palestinian student activists. ICE agents attempted to detain her at her university apartment on March 7, 2025; she fled to Canada on March 11, 2025.',
                'arrest_date'        => '2024-04-30',
                'incarceration_date' => null,
                'release_date'       => null,
                'in_exile_since'     => '2025-03-11',
                'convicted'          => 'No — Hamilton Hall charges dismissed; no criminal charges filed in 2025 visa-revocation proceedings',
                'sentence'           => 'No criminal sentence; self-deported to Canada on March 11, 2025 under threat of ICE detention and deportation proceedings',
            ],
            [
                'name'               => 'Momodou Taal',
                'institution_id'     => $iceNySyracuse->id,
                'charges'            => 'Cornell University doctoral candidate (dual U.K. / Gambian citizenship). On January 21, 2025 he became the lead plaintiff in a federal lawsuit seeking to enjoin two Trump executive orders targeting non-citizen demonstrators. On March 14, 2025 ICE notified his lawyers that his student visa had been revoked and demanded he surrender at the ICE office in Syracuse, New York. After a federal judge in the Northern District of New York declined to block his arrest, Taal announced on March 31, 2025 that he was leaving the United States rather than be detained.',
                'arrest_date'        => null,
                'incarceration_date' => null,
                'release_date'       => null,
                'in_exile_since'     => '2025-03-31',
                'convicted'          => 'No criminal charges — ICE administrative action',
                'sentence'           => 'Self-deported under threat of detention; federal lawsuit filed challenging the underlying executive orders as unconstitutional',
            ],
            [
                'name'               => 'Badar Khan Suri',
                'institution_id'     => $iceTexasFacility->id,
                'charges'            => 'ICE arrested Suri outside his Arlington, Virginia home on the evening of March 17, 2025 following revocation of his J-1 scholar visa. Indian researcher and Georgetown University postdoctoral fellow at the Alwaleed bin Talal Center for Muslim-Christian Understanding; immigration officials alleged he had engaged in "spreading Hamas propaganda and promoting antisemitism" without filing criminal charges. His attorney maintained he was targeted in part because his U.S.-citizen wife is of Palestinian heritage. He was transferred to ICE custody in Louisiana and then to the Prairieland Detention Facility in Alvarado, Texas.',
                'arrest_date'        => '2025-03-17',
                'incarceration_date' => '2025-03-17',
                'release_date'       => '2025-05-14',
                'convicted'          => 'No criminal charges filed',
                'sentence'           => 'Held in ICE detention in Texas approximately two months; ordered released on May 14, 2025 by U.S. District Judge Patricia Tolliver Giles in the Eastern District of Virginia. Government appeal pending.',
            ],
            [
                'name'               => 'Lelo (Alfredo Juarez)',
                'institution_id'     => $iceWashington->id,
                'charges'            => 'Warrantless ICE detention on the morning of March 25, 2025 in Sedro-Woolley, Washington — agents smashed his vehicle\'s window and forcibly removed him while he was driving his partner to her job at a berry farm. ICE based the arrest on a 2018 immigration court removal order Juarez disputes any knowledge of. Lifelong farmworker organizer; co-founded the independent farmworker union Familias Unidas por la Justicia at age 14. Held at the Northwest ICE Processing Center in Tacoma.',
                'arrest_date'        => '2025-03-25',
                'incarceration_date' => '2025-03-25',
                'release_date'       => null,
                'convicted'          => 'No criminal charges — ICE administrative custody',
                'sentence'           => 'Indefinite ICE detention pending removal proceedings',
            ],
            [
                'name'               => 'Fred Burton (Muhammad Burton)',
                'institution_id'     => $sciPhoenix->id,
                'charges'            => 'First-degree murder of Sergeant Frank Von Colln in the August 29, 1970 attack on the Philadelphia Police Department\'s Cobb\'s Creek Park guardhouse (Fairmount Park). The "Philly 5" prosecution arose during Frank Rizzo\'s tenure as Police Commissioner and at the height of the FBI\'s COINTELPRO operations against the Black Panther Party. The only evidence linking Burton to the action came from a closed immunity hearing in which co-defendant\'s wife Marie Williams testified — testimony she later recanted in a pretrial letter, stating she was forced to lie. Williams\'s husband Hugh had been chained to the floor and beaten until he wrote a confession; Marie was interrogated for 19 hours while she heard her husband being tortured. The Commonwealth struck every African-American from the active jury.',
                'arrest_date'        => '1970-08-29',
                'incarceration_date' => '1970-08-29',
                'sentenced_date'     => '1971-12-01',
                'convicted'          => 'Yes — Pennsylvania state jury verdict, 1971 (all-white jury after every Black juror was struck)',
                'sentence'           => 'Life in prison; 33 of his 50+ years served in maximum-security custody, 11 of those years in solitary confinement',
            ],
            [
                'name'               => 'Kojo Bomani Sababu (Grailing Brown)',
                'institution_id'     => $allenwood->id,
                'charges'            => 'Federal armed bank robbery and felony murder of Trenton, New Jersey patrolman in a 1975 BLA expropriation action intended to fund revolutionary work. Black Liberation Army member; later convicted in 1981 in a separate federal RICO/seditious-conspiracy case (along with Oscar López Rivera) tied to a planned escape from USP Leavenworth using a helicopter. Public source records confirm continuous federal custody since the mid-1970s; specific arrest and sentencing dates have not been independently verified to a single day.',
                'arrest_date'        => '1975-08-01',
                'incarceration_date' => '1975-08-01',
                'sentenced_date'     => '1977-06-15',
                'convicted'          => 'Yes — federal jury verdict, 1976 (BLA bank-robbery / felony-murder case); subsequent 1981 federal RICO conviction',
                'sentence'           => 'Life in federal prison (held continuously since the mid-1970s; subsequent federal sentences from 1981 RICO conviction)',
            ],
            [
                'name'               => 'Ronald Reed',
                'institution_id'     => $stillwater->id,
                'charges'            => 'First-degree murder and conspiracy to commit first-degree murder of St. Paul Police Officer James Sackett, who was shot by a sniper while responding to a fabricated emergency call on May 22, 1970. Reed and Larry Clark were charged in May 2005 in a cold-case prosecution following Reed\'s decades of civil-rights and Black liberation organizing in Minneapolis–St. Paul. The conviction relied largely on the testimony of incarcerated witnesses who received sentence considerations.',
                'arrest_date'        => '2005-05-04',
                'incarceration_date' => '2005-05-04',
                'sentenced_date'     => '2006-10-09',
                'convicted'          => 'Yes — Minnesota state jury verdict, July 18, 2006',
                'sentence'           => 'Life in Minnesota state prison with possibility of parole after 30 years; sentenced October 9, 2006',
            ],
            [
                'name'               => 'H. Rap Brown, Hubert Gerold Brown (Jamil Abdullah al-Amin)',
                'institution_id'     => $tucsonUSP->id,
                'charges'            => 'Felony murder of Fulton County Sheriff\'s Deputy Ricky Kinchen and aggravated assault of Deputy Aldranon English on March 16, 2000, when both deputies came to al-Amin\'s West End grocery store in Atlanta with an arrest warrant for failure to appear in court on a case that was later thrown out. al-Amin was arrested March 20, 2000 in White Hall, Alabama after a four-day FBI manhunt. Otis Jackson confessed to the shootings, but the confession was disregarded. Earlier in his life, al-Amin had been a target of FBI Director J. Edgar Hoover\'s COINTELPRO program (named alongside Stokely Carmichael, Elijah Muhammad, and Maxwell Stanford in a 1967 FBI memo calling for his "neutralization") and was charged in 1967 with inciting a riot in Cambridge, Maryland after a Civil Rights speech, despite historian Peter Levy\'s later finding that no riot occurred and that al-Amin had been shot by a police officer without provocation.',
                'arrest_date'        => '2000-03-20',
                'incarceration_date' => '2000-03-20',
                'sentenced_date'     => '2002-03-13',
                'convicted'          => 'Yes — Fulton County, Georgia state jury verdict, March 9, 2002',
                'sentence'           => 'Life without the possibility of parole, sentenced March 13, 2002. Transferred from Georgia state custody to ADX Florence federal supermax in 2007 on a federal hold; later transferred to USP Tucson. Multiple appeals denied despite advanced age, blindness, and multiple myeloma diagnosis.',
            ],
        ];

        $created = 0;
        $skippedHasCases = 0;
        $skippedNotFound = 0;

        foreach ($cases as $entry) {
            DB::transaction(function () use ($entry, &$created, &$skippedHasCases, &$skippedNotFound) {
                $prisoner = $this->findPrisoner($entry['name']);

                if (! $prisoner) {
                    $this->warn("Not in DB: {$entry['name']}");
                    $skippedNotFound++;
                    return;
                }

                if ($prisoner->cases()->exists()) {
                    $this->line("Already has cases: {$prisoner->name}");
                    $skippedHasCases++;
                    return;
                }

                $caseData = $entry;
                unset($caseData['name']);
                $caseData['prisoner_id'] = $prisoner->id;

                PrisonerCase::create($caseData);
                $this->info("Added case for: {$prisoner->name}");
                $created++;
            });
        }

        $this->info("\nDone. Created {$created}, skipped (already had cases) {$skippedHasCases}, not found {$skippedNotFound}.");

        return self::SUCCESS;
    }

    private function findPrisoner(string $name): ?Prisoner
    {
        $prisoner = Prisoner::where('name', $name)->first();
        if ($prisoner) return $prisoner;

        $variants = [
            str_replace(['"', "'"], ["\u{201C}", "\u{2019}"], $name),
            str_replace(['"'], ["\u{201D}"], $name),
            str_replace(["\u{201C}", "\u{201D}", "\u{2019}", "\u{2018}"], ['"', '"', "'", "'"], $name),
        ];
        foreach ($variants as $variant) {
            if ($variant === $name) continue;
            $found = Prisoner::where('name', $variant)->first();
            if ($found) return $found;
        }

        $needle = '%' . str_replace(['"', "'", "\u{201C}", "\u{201D}", "\u{2019}", "\u{2018}"], '%', $name) . '%';
        return Prisoner::where('name', 'like', $needle)->first();
    }
}
