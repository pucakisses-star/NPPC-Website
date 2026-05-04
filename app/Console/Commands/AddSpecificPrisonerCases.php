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
                'charges'            => 'Briefly detained during the April 2024 protests at Hamilton Hall, Columbia University; charges dismissed. F-1 student visa revoked by U.S. Immigration and Customs Enforcement in March 2025 amid the Trump administration\'s campaign targeting pro-Palestinian student activists.',
                'arrest_date'        => '2024-04-30',
                'incarceration_date' => null,
                'release_date'       => null,
                'in_exile_since'     => '2025-03-15',
                'convicted'          => 'No — Hamilton Hall charges dismissed; no criminal charges filed in 2025 visa-revocation proceedings',
                'sentence'           => 'No criminal sentence; self-deported to Canada in March 2025 under threat of ICE detention and deportation proceedings',
            ],
            [
                'name'               => 'Momodou Taal',
                'institution_id'     => $iceNySyracuse->id,
                'charges'            => 'Ordered to surrender to ICE in Syracuse, New York in March 2025 amid the Trump administration\'s enforcement of executive orders targeting international students and scholars for alleged protest involvement. Cornell University graduate student with dual U.K. / Gambian citizenship; no criminal charges filed.',
                'arrest_date'        => null,
                'incarceration_date' => null,
                'release_date'       => null,
                'in_exile_since'     => '2025-03-20',
                'convicted'          => 'No criminal charges — ICE administrative action',
                'sentence'           => 'Self-deported under threat of detention; federal lawsuit filed challenging the underlying executive orders as unconstitutional',
            ],
            [
                'name'               => 'Badar Khan Suri',
                'institution_id'     => $iceTexasFacility->id,
                'charges'            => 'ICE administrative detention in March 2025 following revocation of his student visa. Indian researcher and Georgetown University scholar; immigration officials alleged he had engaged in "spreading Hamas propaganda and promoting antisemitism" without filing criminal charges. His attorney maintained he was targeted in part because his U.S.-citizen wife is of Palestinian heritage.',
                'arrest_date'        => '2025-03-17',
                'incarceration_date' => '2025-03-17',
                'release_date'       => '2025-05-14',
                'convicted'          => 'No criminal charges filed',
                'sentence'           => 'Held in ICE detention in Texas approximately two months; released in May 2025 by federal court order. Government appeal pending.',
            ],
            [
                'name'               => 'Lelo (Alfredo Juarez)',
                'institution_id'     => $iceWashington->id,
                'charges'            => 'Warrantless ICE detention on March 25, 2025 — agents smashed his vehicle\'s window and forcibly removed him while he was driving his partner to work. ICE based the arrest on a 2018 court deportation order Juarez disputes any knowledge of. Lifelong farmworker organizer and a co-founder, at age 14, of the independent union Familias Unidas por la Justicia.',
                'arrest_date'        => '2025-03-25',
                'incarceration_date' => '2025-03-25',
                'release_date'       => null,
                'convicted'          => 'No criminal charges — ICE administrative custody',
                'sentence'           => 'Indefinite ICE detention pending removal proceedings',
            ],
            [
                'name'               => 'Fred Burton (Muhammad Burton)',
                'institution_id'     => $sciPhoenix->id,
                'charges'            => 'First-degree murder — alleged 1970 attack on the Philadelphia Police Department\'s Cobb Creek Guardhouse that killed one officer. The "Philly 5" prosecution arose during Frank Rizzo\'s tenure as Police Commissioner and at the height of the FBI\'s COINTELPRO operations against the Black Panther Party. The only evidence linking Burton to the action came from a closed immunity hearing in which co-defendant\'s wife Marie Williams testified — testimony she later recanted in a pretrial letter, stating she was forced to lie. Williams\'s husband Hugh had been chained to the floor and beaten until he wrote a confession; Marie was interrogated for 19 hours while she heard her husband being tortured. The Commonwealth struck every African-American from the active jury.',
                'arrest_date'        => '1970-08-28',
                'incarceration_date' => '1971-01-15',
                'convicted'          => 'Yes — Pennsylvania state jury verdict, 1971 (all-white jury after every Black juror was struck)',
                'sentence'           => 'Life in prison; 33 of his 50+ years served in maximum-security custody, 11 of those years in solitary confinement',
            ],
            [
                'name'               => 'Kojo Bomani Sababu (Grailing Brown)',
                'institution_id'     => $allenwood->id,
                'charges'            => 'Federal armed robbery and felony murder — attempted bank robbery to fund revolutionary causes. Black Liberation Army member.',
                'arrest_date'        => '1975-08-15',
                'incarceration_date' => '1975-12-01',
                'convicted'          => 'Yes — federal jury verdict',
                'sentence'           => 'Life in federal prison',
            ],
            [
                'name'               => 'Ronald Reed',
                'institution_id'     => $stillwater->id,
                'charges'            => 'First-degree murder and conspiracy to commit first-degree murder — 1970 killing of St. Paul police officer James Sackett. Reed was charged 25 years after the killing in a cold-case prosecution following his decades of civil-rights and Black liberation organizing in Minneapolis–St. Paul. He had previously been convicted in 1970 of shooting a different St. Paul police officer.',
                'arrest_date'        => '2005-05-04',
                'incarceration_date' => '2006-10-09',
                'convicted'          => 'Yes — Minnesota state jury verdict, 2006',
                'sentence'           => 'Life in Minnesota state prison',
            ],
            [
                'name'               => 'H. Rap Brown, Hubert Gerold Brown (Jamil Abdullah al-Amin)',
                'institution_id'     => $tucsonUSP->id,
                'charges'            => 'Felony murder of Fulton County Sheriff\'s Deputy Ricky Kinchen and aggravated assault of Deputy Aldranon English on March 16, 2000, when both deputies came to al-Amin\'s West End grocery store with an arrest warrant for failure to appear in court on a case that was later thrown out. Otis Jackson confessed to the shootings, but the confession was disregarded. Earlier in his life, al-Amin had been a target of FBI Director J. Edgar Hoover\'s COINTELPRO program (named alongside Stokely Carmichael, Elijah Muhammad, and Maxwell Stanford in a 1967 FBI memo calling for his "neutralization") and was charged in 1967 with inciting a riot in Cambridge, Maryland after a Civil Rights speech, despite historian Peter Levy\'s later finding that no riot occurred and that al-Amin had been shot by a police officer without provocation.',
                'arrest_date'        => '2000-03-20',
                'incarceration_date' => '2002-03-13',
                'convicted'          => 'Yes — Georgia state jury verdict, March 2002',
                'sentence'           => 'Life without the possibility of parole; transferred from Georgia state custody to federal custody (USP Tucson via ADX Florence) on a federal hold; multiple appeals denied despite advanced age, blindness, and cancer diagnosis',
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
