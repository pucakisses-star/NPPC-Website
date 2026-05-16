<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Add Daniel "Dan" Duggan — Australian-American former U.S. Marine
 * Corps pilot detained in Australia since October 2022, fighting a
 * U.S. extradition request on a 2017 ITAR indictment over alleged
 * training of Chinese pilots in South Africa. His support site is
 * https://freedanduggan.org/.
 *
 * Idempotent: if he already exists, only the website field is
 * back-filled (so re-runs after manual edits don't clobber data).
 */
final class AddDanDuggan extends Command {
    protected $signature = 'prisoners:add-dan-duggan';
    protected $description = 'Add Daniel Duggan and link his support website (freedanduggan.org)';

    public function handle(): int {
        $existing = Prisoner::where('slug', 'dan-duggan')
            ->orWhere('slug', 'daniel-duggan')
            ->orWhere('name', 'like', '%Duggan%')
            ->where(function ($q) {
                $q->where('first_name', 'Daniel')
                  ->orWhere('first_name', 'Dan');
            })
            ->first();

        if ($existing) {
            if (empty($existing->website)) {
                $existing->website = 'https://freedanduggan.org/';
                $existing->save();
                $this->info('Linked freedanduggan.org to existing record: '.$existing->name);
            } else {
                $this->line('Already on file: '.$existing->name.' — website: '.$existing->website);
            }
            return self::SUCCESS;
        }

        DB::transaction(function () {
            $institution = Institution::firstOrCreate(
                ['name' => 'Lithgow Correctional Centre'],
                ['city' => 'Lithgow', 'state' => 'New South Wales']
            );

            $prisoner = Prisoner::create([
                'name'         => 'Daniel Duggan',
                'first_name'   => 'Daniel',
                'last_name'    => 'Duggan',
                'aka'          => 'Dan Duggan',
                'gender'       => 'Male',
                'race'         => 'White',
                'state'        => 'Australia',
                'era'          => '2020s',
                'ideologies'   => ['Anti-War'],
                'affiliation'  => [],
                'in_custody'   => true,
                'released'     => false,
                'website'      => 'https://freedanduggan.org/',
                'description'  => "Daniel \"Dan\" Duggan is an Australian-American former U.S. Marine Corps fighter pilot, father of six, who has been detained in maximum-security custody in New South Wales, Australia since October 21, 2022, fighting a U.S. extradition request. The 2017 sealed U.S. indictment alleges that, while living in South Africa, he violated the U.S. International Traffic in Arms Regulations (ITAR) by helping train Chinese military pilots at the South African Test Flying Academy in 2009–2012. Duggan, who became an Australian citizen in January 2012, denies the charges and his legal team argues the prosecution is politically motivated, fails dual-criminality requirements under the U.S.–Australia extradition treaty, and exploits a fundamentally unfair extradition framework.\n\nHe spent more than nineteen months in solitary confinement before being moved to general population at Lithgow Correctional Centre in June 2024. On December 20, 2024 the Australian Attorney-General formally notified him that he would be surrendered to U.S. custody. His family, legal team, and a broad civil-liberties campaign continue to challenge the extradition; the case has drawn comparisons to the prosecutions of Julian Assange and other extradition fights framed by supporters as politically driven national-security prosecutions.\n\nSupport campaign: https://freedanduggan.org/",
            ]);

            PrisonerCase::create([
                'prisoner_id'        => $prisoner->id,
                'institution_id'     => $institution->id,
                'charges'            => 'U.S. ITAR indictment (filed 2017, unsealed 2022) — conspiracy to violate the Arms Export Control Act over alleged training of Chinese military pilots in South Africa, 2009–2012',
                'arrest_date'        => '2022-10-21',
                'incarceration_date' => '2022-10-21',
            ]);
        });

        $this->info('Added Daniel Duggan and linked freedanduggan.org.');

        return self::SUCCESS;
    }
}
