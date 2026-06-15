<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Fulani Sunni-Ali (born Cynthia Boston), the Republic of New Afrika leader
 * cleared of the 1981 Brink's robbery (via a New Orleans alibi) but then jailed
 * about a year and a half for grand-jury contempt in the dragnet against the
 * Black-liberation movement. Surfaced from the Workers Vanguard "Free Fulani
 * Sunni-Ali" coverage; sourced to UPI, the Library of Congress, and the Jericho
 * Movement. Idempotent.
 */
class AddFulaniSunniAli extends Command {
    protected $signature = 'prisoners:add-fulani-sunni-ali';
    protected $description = 'Add Fulani Sunni-Ali (Cynthia Boston), RNA grand-jury resister in the 1981 Brink\'s case';

    private const BIO = <<<'TXT'
Fulani Sunni-Ali (born Cynthia Boston, 1948–2016) was a leading member of the Republic of New Afrika, the Black-nationalist organization that called for an independent Black republic in the Deep South. In the dragnet that followed the October 20, 1981 Brink's armored-car robbery in Rockland County, New York — carried out by members of the Black Liberation Army and the Weather Underground — federal authorities named her a suspect. On October 27, 1981, more than 200 law-enforcement officers, backed by helicopters and two Army tanks, raided her Mississippi farmhouse and arrested her along with Jerry Gaines and fourteen children.

After a witness established that she had been in New Orleans at the time of the robbery, the government dropped the robbery charge — and on the same day served her with a grand-jury subpoena. Sunni-Ali refused to testify, treating silence as resistance to what supporters called a political inquisition against the Black-liberation movement, and was jailed for civil contempt. She was released about a year and a half later, on October 19, 1983, from the Metropolitan Correctional Center in New York. Represented by William Kunstler, she remained an organizer until her death in 2016.
TXT;

    public function handle(): int {
        if (Prisoner::where('name', 'Fulani Sunni-Ali')->exists()) {
            $this->error('Fulani Sunni-Ali already exists.');
            return self::FAILURE;
        }

        DB::transaction(function () {
            $mcc = Institution::firstOrCreate(
                ['name' => 'Metropolitan Correctional Center, New York'],
                ['city' => 'New York', 'state' => 'New York']
            );

            $prisoner = Prisoner::create([
                'name'           => 'Fulani Sunni-Ali',
                'first_name'     => 'Fulani',
                'last_name'      => 'Sunni-Ali',
                'description'    => self::BIO,
                'gender'         => 'Female',
                'race'           => 'Black',
                'state'          => 'New York',
                'era'            => '1980s',
                'ideologies'     => ['Black nationalism', 'New Afrikan independence'],
                'affiliation'    => ['Republic of New Afrika'],
                'in_custody'     => false,
                'released'       => true,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id'        => $prisoner->id,
                'institution_id'     => $mcc->id,
                'charges'            => 'Initially named a suspect in the October 20, 1981 Brink\'s armored-car robbery in Rockland County, New York (arrested October 27, 1981 in a military-style raid on her Mississippi farmhouse), but cleared after she proved she had been in New Orleans; she was then jailed for civil contempt for refusing to testify before the federal grand jury investigating the case — part of a sweeping inquisition against the Black-liberation movement.',
                'incarceration_date' => '1981-10-27',
                'release_date'       => '1983-10-19',
                'convicted'          => 'Cleared of the robbery charge; jailed for grand-jury contempt (not a criminal conviction).',
                'sentence'           => 'Held about a year and a half for grand-jury contempt; released October 19, 1983 from the Metropolitan Correctional Center in New York.',
            ]);

            $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
        });

        return self::SUCCESS;
    }
}
