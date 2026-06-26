<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Batch 2 of the historical political-prisoner additions: Irish republicans held
 * in / fought over by the U.S. in the 1980s — Joseph Doherty (PIRA, the famous
 * NY extradition case), William Quinn (first IRA member extradited from the U.S.
 * to Britain), and James "Jim" Barr (INLA/IRSP; U.S. court denied his
 * extradition). All long since released. Idempotent; matches by slug/exact name
 * (exact, to avoid colliding with the unrelated "James Barrett").
 */
final class AddIrishPrisoners extends Command
{
    protected $signature = 'prisoners:add-irish';

    protected $description = 'Add the Irish republican political prisoners (Doherty, Quinn, Barr)';

    public function handle(): int
    {
        $people = [
            [
                'name' => 'Joseph Doherty', 'first' => 'Joseph', 'middle' => 'Patrick', 'last' => 'Doherty',
                'gender' => 'Male', 'state' => 'Northern Ireland', 'inmate' => '07792-054',
                'ideologies' => ['Irish republicanism'], 'affiliation' => ['Provisional IRA'],
                'desc' => 'Joseph Patrick Thomas Doherty was a volunteer in the Belfast Brigade of the Provisional IRA. He was charged with the 1980 killing of British SAS Captain Herbert Westmacott during a gun battle in Belfast and escaped from Crumlin Road Gaol during his 1981 trial. He fled to the United States, was arrested in New York in 1983, and held at the Metropolitan Correctional Center. His nine-year fight against extradition and deportation became an international cause célèbre — a Manhattan street corner was named for him and the case reached the U.S. Supreme Court — but he was deported to Northern Ireland on February 19, 1992. He was released on November 6, 1998 under the Good Friday Agreement.',
                'prison' => ['Metropolitan Correctional Center, New York', 'New York', 'New York'],
                'charges' => 'Killing of British SAS Captain Herbert Westmacott (Belfast, 1980); escaped custody during his 1981 trial. Fought a nine-year U.S. extradition/deportation battle.',
                'convicted' => 'Convicted in Northern Ireland', 'arrest' => [1983, null, null], 'incarc' => [1983, null, null], 'release' => [1992, 2, 19],
            ],
            [
                'name' => 'William Quinn', 'first' => 'William', 'middle' => 'Joseph', 'last' => 'Quinn',
                'gender' => 'Male', 'state' => 'California', 'inmate' => null,
                'ideologies' => ['Irish republicanism'], 'affiliation' => ['Provisional IRA'],
                'desc' => 'William Joseph Quinn, an American-born member of the Provisional IRA, was sought by the United Kingdom for the 1975 killing of London police constable Stephen Tibble and a letter-bomb campaign. Arrested in San Francisco on September 30, 1981, he became the first IRA member extradited from the United States to Britain, in October 1986, after a landmark legal battle (Quinn v. Robinson) over the "political offense" exception to extradition. He was convicted in the U.K. and later released under the Good Friday Agreement.',
                'prison' => ['San Francisco County Jail', 'San Francisco', 'California'],
                'charges' => 'Sought by the UK for the 1975 killing of London police constable Stephen Tibble and a letter-bomb campaign.',
                'convicted' => 'Extradited to the UK (1986) and convicted there', 'arrest' => [1981, 9, 30], 'incarc' => [1981, 9, 30], 'release' => [1986, 10, null],
            ],
            [
                'name' => 'James Barr', 'first' => 'James', 'middle' => 'Gerard', 'last' => 'Barr', 'aka' => 'Jim Barr',
                'gender' => 'Male', 'state' => 'Northern Ireland', 'inmate' => null,
                'ideologies' => ['Irish republican socialism'], 'affiliation' => ['INLA', 'IRSP'],
                'desc' => 'James Gerard Barr was a member of the Irish National Liberation Army (INLA) and the Irish Republican Socialist Party (IRSP). Named by a supergrass informer, he fled to the United States, where the U.K. sought his extradition on an attempted-murder charge from Northern Ireland. He was held for some seventeen months at the Philadelphia Detention Center until a U.S. federal court denied the extradition request in 1985 for insufficient evidence — a rare such denial. He was later granted political asylum in the United States in 1993.',
                'prison' => ['Philadelphia Detention Center', 'Philadelphia', 'Pennsylvania'],
                'charges' => 'UK sought extradition on an attempted-murder charge in Northern Ireland; a U.S. federal court denied the request in 1985 for insufficient evidence.',
                'convicted' => 'No — U.S. court denied extradition (1985); granted asylum 1993', 'arrest' => [1984, null, null], 'incarc' => [1984, null, null], 'release' => [1985, null, null],
            ],
        ];

        foreach ($people as $p) {
            $prisoner = Prisoner::withoutGlobalScopes()
                ->where('slug', Str::slug($p['name']))
                ->orWhere('name', $p['name'])
                ->first();

            $attrs = [
                'name' => $p['name'], 'first_name' => $p['first'], 'middle_name' => $p['middle'] ?? null,
                'last_name' => $p['last'], 'aka' => $p['aka'] ?? null, 'gender' => $p['gender'],
                'state' => $p['state'], 'inmate_number' => $p['inmate'], 'era' => '1980s',
                'ideologies' => $p['ideologies'], 'affiliation' => $p['affiliation'],
                'in_custody' => false, 'released' => true, 'under_review' => false,
                'description' => $p['desc'],
            ];

            if ($prisoner) {
                $prisoner->fill($attrs)->save();
                $this->info("Updated: {$prisoner->name}");
            } else {
                $prisoner = Prisoner::create($attrs);
                $this->info("Created: {$prisoner->name}");
            }

            if ($prisoner->cases()->count() === 0) {
                $inst = Institution::firstOrCreate(
                    ['name' => $p['prison'][0]],
                    ['city' => $p['prison'][1], 'state' => $p['prison'][2]],
                );
                $case = $prisoner->cases()->make([
                    'institution_id' => $inst->id,
                    'charges' => $p['charges'],
                    'convicted' => $p['convicted'],
                ]);
                $case->setPartialDate('arrest_date', ...$p['arrest']);
                $case->setPartialDate('incarceration_date', ...$p['incarc']);
                $case->setPartialDate('release_date', ...$p['release']);
                $case->save();
                $this->line("  + case at {$inst->name}");
            }
        }

        return self::SUCCESS;
    }
}
