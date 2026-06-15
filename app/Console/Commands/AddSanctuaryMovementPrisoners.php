<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Batch 5 of the comprehensive-sweep additions: the 1980s Sanctuary Movement —
 * U.S. religious and lay workers criminally prosecuted for sheltering and
 * transporting Central American refugees fleeing the U.S.-backed wars in El
 * Salvador and Guatemala. Cross-checked as not already in the database:
 *   - Stacey Merkt    (first sanctuary worker convicted; 179 days)
 *   - Jack Elder      (Casa Oscar Romero director; 150 days, halfway house)
 *   - John Fife       (movement co-founder; Tucson trial; probation)
 *   - Darlene Nicgorski (Franciscan sister; Tucson trial; probation)
 * Sourced to UPI/AP, the Washington Post, the Christian Science Monitor, and
 * the court records (United States v. Elder, 601 F. Supp. 1574). Idempotent.
 */
class AddSanctuaryMovementPrisoners extends Command {
    protected $signature = 'prisoners:add-sanctuary-movement';
    protected $description = 'Add Sanctuary Movement defendants (Merkt, Elder, Fife, Nicgorski)';

    public function handle(): int {
        $cases = [
            [
                'name' => 'Stacey Merkt', 'first' => 'Stacey', 'last' => 'Merkt',
                'gender' => 'Female', 'state' => 'Texas', 'era' => '1980s',
                'ideologies' => ['Sanctuary movement', 'Human rights'],
                'affiliation' => ['Casa Óscar Romero'],
                'bio' => 'Stacey Lynn Merkt was a lay Methodist volunteer at Casa Óscar Romero, a Catholic refugee shelter in San Benito, Texas, and the first member of the 1980s Sanctuary Movement convicted of an immigration crime for helping Central Americans fleeing the wars in El Salvador and Guatemala. On February 17, 1984 she was stopped while driving undocumented Salvadorans — together with a Catholic sister and a newspaper reporter — and charged with felony alien transportation; convicted that year, she received a 90-day suspended sentence and two years\' probation from U.S. District Judge Filemón Vela. On February 21, 1985 she was convicted again, alongside shelter director Jack Elder, of conspiring to transport Salvadoran refugees from Brownsville to a Harlingen bus station, and Vela sentenced her to 179 days in prison. Pregnant during the prosecution and supported by churches across the country, Merkt became a national symbol of the movement; after her appeals failed she entered prison in January 1987.',
                'charges' => 'Felony alien transportation and conspiracy to transport undocumented Salvadoran refugees — for her work, as a lay volunteer at the Casa Óscar Romero shelter, helping Central Americans fleeing civil war seek asylum.',
                'convicted' => 'Yes — convicted in 1984 (felony transportation) and again on February 21, 1985 (conspiracy, with Jack Elder); the first Sanctuary Movement worker convicted of an immigration offense.',
                'sentence' => 'A 90-day suspended sentence and probation in 1984; 179 days in prison on the 1985 conspiracy conviction.',
            ],
            [
                'name' => 'Jack Elder', 'first' => 'Jack', 'last' => 'Elder',
                'gender' => 'Male', 'state' => 'Texas', 'era' => '1980s',
                'ideologies' => ['Sanctuary movement', 'Human rights'],
                'affiliation' => ['Casa Óscar Romero'],
                'bio' => 'Jack Elder was the director of Casa Óscar Romero, the Catholic refugee shelter in San Benito, Texas sponsored by the Diocese of Brownsville, and one of the most prominent Sanctuary Movement workers prosecuted for aiding Central American refugees. On March 12, 1984 he drove three undocumented Salvadorans the few miles from the shelter to a bus station in Harlingen, an act that led to federal charges of conspiracy and illegal transportation. Convicted on multiple counts in 1985 (United States v. Elder, 601 F. Supp. 1574), Elder rejected as "unacceptable" an initial sentence of probation, and U.S. District Judge Filemón Vela ultimately ordered him to serve 150 days in a halfway house in San Antonio. Elder maintained that giving refuge to those fleeing death squads was both a religious duty and protected by U.S. refugee law.',
                'charges' => 'Conspiracy and illegal transportation of undocumented immigrants — for driving Salvadoran refugees from the Casa Óscar Romero shelter, which he directed, to a Texas bus station (United States v. Elder, 601 F. Supp. 1574).',
                'convicted' => 'Yes — convicted on multiple counts in 1985.',
                'sentence' => 'After he rejected a probation-only sentence, 150 days in a halfway house in San Antonio.',
            ],
            [
                'name' => 'John Fife', 'first' => 'John', 'last' => 'Fife',
                'gender' => 'Male', 'state' => 'Arizona', 'era' => '1980s',
                'ideologies' => ['Sanctuary movement', 'Human rights'],
                'affiliation' => ['Southside Presbyterian Church', 'Sanctuary movement'],
                'bio' => 'The Reverend John Fife was the pastor of Southside Presbyterian Church in Tucson, Arizona and a principal founder of the Sanctuary Movement: on March 24, 1982 his congregation publicly declared itself a sanctuary for Central American refugees, launching a national network of churches that sheltered those fleeing the U.S.-backed wars in El Salvador and Guatemala. In January 1985 the federal government, which had infiltrated the movement with paid informants, indicted Fife and fifteen others in Tucson on dozens of counts of conspiracy and harboring and transporting "illegal aliens." After a trial running from late 1985 into 1986, Fife was convicted on May 1, 1986, and U.S. District Judge Earl H. Carroll sentenced him and the other convicted defendants to five years\' probation rather than prison. Fife went on to be elected Moderator of the General Assembly of the Presbyterian Church (USA) and to co-found the migrant-aid group No More Deaths.',
                'charges' => 'Conspiracy and harboring and transporting undocumented immigrants — for co-founding and leading the Sanctuary Movement, which sheltered Central American refugees in defiance of federal immigration law.',
                'convicted' => 'Yes — convicted on May 1, 1986 in the Tucson sanctuary trial, one of eight defendants found guilty.',
                'sentence' => 'Five years\' probation (no prison).',
            ],
            [
                'name' => 'Darlene Nicgorski', 'first' => 'Darlene', 'last' => 'Nicgorski',
                'gender' => 'Female', 'state' => 'Arizona', 'era' => '1980s',
                'ideologies' => ['Sanctuary movement', 'Human rights'],
                'affiliation' => ['School Sisters of St. Francis', 'Sanctuary movement'],
                'bio' => 'Sister Darlene Nicgorski was a School Sister of St. Francis who became a leading organizer of the Sanctuary Movement after the 1980 murder of a priest she had worked with in Guatemala convinced her of the dangers facing the people she helped. A central defendant in the 1985–86 Tucson sanctuary trial — singled out, her supporters charged, for especially aggressive treatment by the prosecution — she was arrested after a search of her home in January 1985 and convicted on May 1, 1986 of conspiracy and alien-smuggling charges for her work aiding Central American refugees. Like her co-defendants, she was sentenced by U.S. District Judge Earl H. Carroll to probation rather than prison. Nicgorski remained an outspoken advocate for refugees and a critic of U.S. policy in Central America.',
                'charges' => 'Conspiracy and aiding and transporting undocumented immigrants — for her organizing work in the Sanctuary Movement on behalf of Central American refugees.',
                'convicted' => 'Yes — convicted on May 1, 1986 in the Tucson sanctuary trial.',
                'sentence' => 'Probation (no prison).',
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
