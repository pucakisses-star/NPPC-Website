<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * The "GEO9" — Fort Lauderdale Food Not Bombs activists arrested for the
 * December 3, 2019 blockade of the Boca Raton headquarters of the GEO Group,
 * the private-prison corporation that runs ICE detention centers (including
 * Florida's Broward Transitional Center). Initially charged with misdemeanor
 * trespassing, several were re-arrested in February 2020 on felony charges of
 * false imprisonment and conspiracy to commit false imprisonment — for
 * allegedly confining a security guard during the blockade — and faced up to
 * 15 years in prison. The felony charges drew criticism from civil-liberties
 * advocates and Rep. Ted Deutch as an attempt to chill protest.
 *
 * Eight named defendants (per their shared counsel, Sabarish Neelakanta) are
 * added here; the case's final disposition is not documented in the available
 * sources, so no conviction/sentence is asserted. Source: Miami Herald,
 * "They protested against private prisons. Now they're facing prison time in
 * South Florida" (Monique O. Madan, Feb 11, 2020). Idempotent (skips by name).
 */
class AddGeoGroupProtesters extends Command
{
    protected $signature = 'prisoners:add-geo-group-protesters';

    protected $description = 'Add the GEO9 — Food Not Bombs activists charged with felony false imprisonment for the 2019 GEO Group private-prison blockade';

    /** First %s = "NAME, N," (name + age fragment). */
    private const BIO = '%s was one of the "GEO9," a group of Fort Lauderdale Food Not Bombs activists arrested for the December 3, 2019 blockade of the Boca Raton, Florida headquarters of the GEO Group — the private-prison corporation that operates immigration detention centers for U.S. Immigration and Customs Enforcement, including Florida\'s Broward Transitional Center. The activists sealed the building\'s entrances, parking garage and elevators with concrete-filled tires and barrels to shut down "business as usual" and protest GEO\'s role in immigrant detention and family separation. Initially charged only with misdemeanor trespassing, the protesters were re-arrested in February 2020 on far more serious felony charges — false imprisonment and conspiracy to commit false imprisonment — after police alleged that a security guard had been confined inside the building during the blockade, exposing them to up to 15 years in prison. The escalation was widely criticized, including by U.S. Rep. Ted Deutch, as an attempt to criminalize and chill political protest against the private-prison industry.';

    private const CHARGES = 'Misdemeanor trespassing and criminal mischief; then felony false imprisonment and conspiracy to commit false imprisonment (added February 2020) — for the December 3, 2019 blockade of the GEO Group headquarters.';

    private const CONVICTED = 'Arrested and charged; the final disposition of the case is not documented in the available sources.';

    private const SENTENCE = 'Faced up to 15 years in prison on the felony false-imprisonment and conspiracy charges. Released pending trial; outcome not documented in available sources.';

    public function handle(): int
    {
        $jail = Institution::firstOrCreate(
            ['name' => 'Palm Beach County Jail'],
            ['city' => 'West Palm Beach', 'state' => 'Florida'],
        );

        // name => [first, last, age-at-arrest fragment]
        $people = [
            ['Carlos Naranjo', 'Carlos', 'Naranjo', '36'],
            ['Mathi Paguth', 'Mathi', 'Paguth', '40'],
            ['Nicholas Vazquez', 'Nicholas', 'Vazquez', '22'],
            ['Alexis Butler', 'Alexis', 'Butler', '25'],
            ['Ellen Vessels', 'Ellen', 'Vessels', '34'],
            ['Christian Minaya', 'Christian', 'Minaya', '39'],
            ['Wendy King', 'Wendy', 'King', '36'],
            ['David Hitchcock', 'David', 'Hitchcock', '30'],
        ];

        DB::transaction(function () use ($people, $jail) {
            foreach ($people as [$name, $first, $last, $age]) {
                if (Prisoner::where('name', $name)->exists()) {
                    $this->warn('Skipped (already exists): '.$name);

                    continue;
                }

                $prisoner = Prisoner::create([
                    'name' => $name,
                    'first_name' => $first,
                    'last_name' => $last,
                    'description' => sprintf(self::BIO, $name.', '.$age.' at the time of the protest,'),
                    'state' => 'Florida',
                    'era' => '2010s',
                    'ideologies' => ['Prison abolition', 'Anti–private prison', 'Immigrant rights'],
                    'affiliation' => ['Fort Lauderdale Food Not Bombs', 'GEO9'],
                    'in_custody' => false,
                    'released' => true,
                    'in_exile' => false,
                    'awaiting_trial' => false,
                ]);

                PrisonerCase::create([
                    'prisoner_id' => $prisoner->id,
                    'institution_id' => $jail->id,
                    'charges' => self::CHARGES,
                    'convicted' => self::CONVICTED,
                    'sentence' => self::SENTENCE,
                    'arrest_date' => '2019-12-03',
                ]);

                $this->info('Added: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
            }
        });

        return self::SUCCESS;
    }
}
