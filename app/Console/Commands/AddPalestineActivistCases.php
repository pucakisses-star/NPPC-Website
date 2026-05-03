<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddPalestineActivistCases extends Command
{
    protected $signature = 'prisoners:add-palestine-cases';
    protected $description = 'Attach case records to Ranjani Srinivasan, Momodou Taal, Badar Khan Suri, and Alfredo "Lelo" Juarez (existing prisoners with no cases yet).';

    public function handle(): int
    {
        $created = 0;
        $skipped = 0;

        $nyJail   = Institution::firstOrCreate(['name' => 'NYPD custody (Hamilton Hall protest)'], ['city' => 'New York', 'state' => 'New York']);
        $cornell  = Institution::firstOrCreate(['name' => 'No detention — left U.S. before ICE custody'], ['city' => null, 'state' => null]);
        $prairieland = Institution::firstOrCreate(['name' => 'Prairieland Detention Center (ICE)'], ['city' => 'Alvarado', 'state' => 'Texas']);
        $tacoma   = Institution::firstOrCreate(['name' => 'Northwest ICE Processing Center'], ['city' => 'Tacoma', 'state' => 'Washington']);

        $entries = [
            [
                'slug'       => 'ranjani-srinivasan',
                'released'   => true,
                'in_custody' => false,
                'in_exile'   => true,
                'currently_in_exile' => true,
                'case'       => [
                    'institution_id' => $nyJail->id,
                    'charges'        => "Failure to disperse and blocking the sidewalk (NYPD summons, April 30, 2024 Hamilton Hall protest at Columbia — both charges dismissed); F-1 student visa subsequently revoked by the U.S. State Department under 8 U.S.C. § 1182(a)(3)(C)(i) (the foreign-policy revocation provision)",
                    'arrest_date'    => '2024-04-30',
                    'release_date'   => '2025-03-11',
                    'convicted'      => 'No — NYPD charges dismissed; visa revoked administratively without criminal proceedings',
                    'sentence'       => 'No criminal sentence; F-1 visa revoked, ICE attempted to detain her at her Columbia apartment in March 2025; she self-deported to Canada via the CBP Home App on or about March 11, 2025 rather than face detention',
                ],
            ],
            [
                'slug'       => 'momodou-taal',
                'released'   => true,
                'in_custody' => false,
                'in_exile'   => true,
                'currently_in_exile' => true,
                'case'       => [
                    'institution_id' => $cornell->id,
                    'charges'        => "F-1 student visa revoked by the U.S. State Department on March 14, 2025; ordered to surrender to U.S. Immigration and Customs Enforcement in Syracuse, New York on March 21, 2025 to begin removal proceedings",
                    'arrest_date'    => '2025-03-14',
                    'release_date'   => '2025-03-31',
                    'convicted'      => 'No criminal charges filed; visa revoked and Notice to Appear (deportation proceedings) issued; never taken into ICE custody',
                    'sentence'       => "Co-plaintiff in March 15, 2025 federal lawsuit (Taal v. Trump) challenging the Trump administration's executive orders targeting pro-Palestinian student activists for deportation; left the United States voluntarily on March 31, 2025 after a federal judge declined to immediately block the deportation",
                ],
            ],
            [
                'slug'       => 'badar-khan-suri',
                'released'   => true,
                'in_custody' => false,
                'in_exile'   => false,
                'currently_in_exile' => false,
                'case'       => [
                    'institution_id' => $prairieland->id,
                    'charges'        => "No criminal charges; J-1 scholar visa revoked by the U.S. State Department; detained by federal immigration agents on allegations of 'spreading Hamas propaganda and promoting antisemitism' (allegations strongly disputed by Suri, his attorneys, and his employer Georgetown University)",
                    'arrest_date'    => '2025-03-17',
                    'release_date'   => '2025-05-14',
                    'convicted'      => 'No criminal conviction; ordered released by U.S. District Court Judge Patricia Tolliver Giles on May 14, 2025 after the court found the government had not produced any evidence justifying continued detention',
                    'sentence'       => "Held in immigration detention at the Prairieland Detention Center in Alvarado, Texas for approximately two months. After release, returned to Virginia under conditions requiring continued appearance at immigration removal proceedings. Reported harsh detention conditions including denial of appropriate food and proper sleeping arrangements",
                ],
            ],
            [
                'slug'       => 'alfredo-juarez',
                'released'   => true,
                'in_custody' => false,
                'in_exile'   => true,
                'currently_in_exile' => true,
                'case'       => [
                    'institution_id' => $tacoma->id,
                    'charges'        => "Removal proceedings under a 2018 in absentia deportation order; no criminal charges. Detained without warrant when ICE plainclothes agents broke the window of his vehicle at 7:21 a.m. on March 25, 2025 in Sedro-Woolley, Washington while he was driving his partner to work",
                    'arrest_date'    => '2025-03-25',
                    'release_date'   => '2025-07-21',
                    'convicted'      => 'No criminal conviction; immigration court accepted his motion for voluntary departure to Mexico in July 2025',
                    'sentence'       => "Held approximately four months at the Northwest ICE Processing Center in Tacoma, Washington (a privately operated GEO Group facility); voluntarily departed to Mexico in late July 2025 in lieu of formal deportation. He had been a co-founder of Familias Unidas por la Justicia, the independent Indigenous farmworkers union in Washington state, since 2013",
                ],
            ],
        ];

        foreach ($entries as $e) {
            DB::transaction(function () use ($e, &$created, &$skipped) {
                $prisoner = Prisoner::where('slug', $e['slug'])->first();

                if (! $prisoner) {
                    $this->error("  not found: {$e['slug']}");
                    $skipped++;
                    return;
                }

                if ($prisoner->cases()->count() > 0) {
                    $this->warn("  skipped: {$e['slug']} already has {$prisoner->cases()->count()} case(s)");
                    $skipped++;
                    return;
                }

                $prisoner->update([
                    'in_custody'         => $e['in_custody'],
                    'released'           => $e['released'],
                    'in_exile'           => $e['in_exile'] ?? false,
                    'currently_in_exile' => $e['currently_in_exile'] ?? false,
                ]);

                PrisonerCase::create(array_merge(
                    ['prisoner_id' => $prisoner->id],
                    $e['case']
                ));

                $this->info("  added case for {$prisoner->name}");
                $created++;
            });
        }

        $this->info("\nDone. {$created} created, {$skipped} skipped.");

        return self::SUCCESS;
    }
}
