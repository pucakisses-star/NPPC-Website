<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Batch 4 of the historical political-prisoner additions: New Afrikan / Black
 * liberation prisoners. Four are fully documented (Ruchell Magee, Mohaman Koti,
 * Teddy "Jah" Heath, Cecilia "Chui" Ferguson); Kalima Aswad has a partial bio
 * (person documented, underlying case not); and four (Awali Stoneham, Carol
 * Hill, Larry Guy, Richard Thompson-El) are minimal, honest directory entries —
 * listed as PPs in 1980s movement directories but with no reliably-sourced case
 * details, so no charges are invented. Idempotent.
 */
final class AddNewAfrikanPrisoners extends Command
{
    protected $signature = 'prisoners:add-new-afrikan';

    protected $description = 'Add the missing New Afrikan / Black liberation political prisoners';

    public function handle(): int
    {
        $people = [
            [
                'name' => 'Ruchell Magee', 'first' => 'Ruchell', 'last' => 'Magee', 'aka' => 'Ruchell Cinque Magee; Cinque',
                'gender' => 'Male', 'state' => 'California', 'inmate' => 'A92051', 'era' => '1970s',
                'affiliation' => ['Black liberation movement'], 'in_custody' => false, 'released' => true, 'death' => [2023, 10, 17],
                'desc' => "Ruchell \"Cinque\" Magee (born Ruchell Lewis, 1939–2023) was one of the longest-held prisoners in U.S. history, incarcerated almost continuously from the 1960s. He was the sole surviving participant in the August 7, 1970 Marin County Civic Center courthouse incident — Jonathan Jackson's armed attempt to free the Soledad Brothers, in which Judge Harold Haley was killed — and pleaded guilty to aggravated kidnapping. A renowned jailhouse lawyer who took the name \"Cinque,\" he became a symbol of the Black liberation and prison movements. He was freed in August 2023 under California's compassionate-release law and died that October, 81 days after his release.",
                'prison' => ['Folsom State Prison', 'Represa', 'California'],
                'charges' => 'Aggravated kidnapping (guilty plea) in the August 7, 1970 Marin County courthouse incident in which Judge Harold Haley was killed; imprisoned since the 1960s.',
                'convicted' => 'Yes', 'incarc' => [1963, null, null], 'release' => [2023, 8, null],
            ],
            [
                'name' => 'Mohaman Geuka Koti', 'first' => 'Mohaman', 'last' => 'Koti', 'aka' => 'James Johnson',
                'gender' => 'Male', 'state' => 'New York', 'inmate' => '80-A-808', 'era' => '1970s',
                'affiliation' => ['Black Liberation Army'], 'in_custody' => false, 'released' => true, 'death' => [2016, null, null],
                'desc' => 'Mohaman Geuka Koti (s/n James Johnson, c.1927–2016) was a Black Liberation Army-associated revolutionary. In the late 1970s he was convicted of attempted murder after shooting a New York City police officer during a traffic stop — he maintained the officer drew first, and the officer recovered. Offered a seven-and-a-half-year plea, he demanded a trial and was sentenced to 25 years to life. He served decades, was released in early 2016, and died at 89 about two months later.',
                'prison' => ['Great Meadow Correctional Facility', 'Comstock', 'New York'],
                'charges' => 'Attempted murder — shooting of an NYPD officer during a late-1970s traffic stop (officer recovered).',
                'convicted' => 'Yes', 'sentence' => '25 years to life', 'incarc' => [1978, null, null], 'release' => [2016, null, null],
            ],
            [
                'name' => 'Teddy Heath', 'first' => 'Teddy', 'last' => 'Heath', 'aka' => 'Jah',
                'gender' => 'Male', 'state' => 'New York', 'inmate' => '75-A-132', 'era' => '1970s',
                'affiliation' => ['Black Liberation Army', 'Black Panther Party'], 'in_custody' => false, 'released' => false, 'death' => [2001, null, null],
                'desc' => 'Teddy "Jah" Heath (1946–2001) joined the Black Panther Party in 1968 and was associated with the Black Liberation Army. Arrested on May 2, 1973, he was convicted in connection with the kidnapping of a drug dealer — an action he framed as part of the BLA\'s effort to drive the drug trade out of Black communities, in which no one was hurt. He served 28 years and died in prison in 2001.',
                'prison' => ['Attica Correctional Facility', 'Attica', 'New York'],
                'charges' => 'Kidnapping of a drug dealer (1973), carried out in line with Black Liberation Army efforts against the drug trade in Black communities.',
                'convicted' => 'Yes', 'sentence' => 'Served 28 years (died in prison, 2001)', 'arrest' => [1973, 5, 2], 'incarc' => [1973, null, null], 'death_in_custody' => [2001, null, null],
            ],
            [
                'name' => 'Cecilia Ferguson', 'first' => 'Cecilia', 'last' => 'Ferguson', 'aka' => 'Chui Ferguson',
                'gender' => 'Female', 'state' => 'New York', 'inmate' => null, 'era' => '1980s',
                'affiliation' => ['Black Panther Party', 'Black Liberation Army'], 'in_custody' => false, 'released' => true,
                'desc' => "Cecilia \"Chui\" Ferguson is a former Black Panther who was convicted of accessory charges and sentenced to twelve years in connection with the federal RICO/racketeering prosecution surrounding the 1979 escape of Assata Shakur and the 1981 Brink's robbery — the case that also imprisoned Mutulu Shakur and others of the Republic of New Afrika / Black Liberation Army milieu.",
                'prison' => ['FCI Lewisburg', 'Lewisburg', 'Pennsylvania'],
                'charges' => "Accessory / RICO charges connected to the conspiracy that included the 1979 escape of Assata Shakur and the 1981 Brink's robbery.",
                'convicted' => 'Yes (accessory)', 'sentence' => '12 years',
            ],
            [
                'name' => 'Kalima Aswad', 'first' => 'Kalima', 'last' => 'Aswad', 'aka' => 'Robert Duren',
                'gender' => 'Male', 'state' => 'California', 'inmate' => 'B24120', 'era' => '1980s',
                'affiliation' => ['Black liberation movement'], 'in_custody' => false, 'released' => true,
                'desc' => 'Kalima Aswad (s/n Robert Duren) is a New Afrikan political prisoner and writer who was held in the California prison system (California Men\'s Colony, San Luis Obispo) and wrote on prison conditions and the Black liberation struggle. He appears in U.S. political-prisoner directories of the 1980s; detailed, reliably-sourced information about his underlying case was not available.',
                'prison' => ['California Men\'s Colony', 'San Luis Obispo', 'California'],
            ],
            // --- minimal, flagged directory entries (no reliable case details found) ---
            [
                'name' => 'Awali Stoneham', 'first' => 'Awali', 'last' => 'Stoneham', 'gender' => 'Male',
                'state' => 'California', 'inmate' => 'B-98168', 'era' => '1980s', 'affiliation' => ['Black liberation movement'],
                'in_custody' => false, 'released' => true,
                'desc' => 'Awali Stoneham was listed as a New Afrikan / Black-liberation political prisoner, held at the Correctional Training Facility in Soledad, California, in U.S. political-prisoner directories of the 1980s. Detailed, reliably-sourced information about his case was not available; this entry preserves the directory listing.',
                'prison' => ['Correctional Training Facility, Soledad', 'Soledad', 'California'],
            ],
            [
                'name' => 'Carol Hill', 'first' => 'Carol', 'last' => 'Hill', 'gender' => 'Female',
                'state' => 'New York', 'inmate' => null, 'era' => '1980s', 'affiliation' => ['Black liberation movement'],
                'in_custody' => false, 'released' => true,
                'desc' => 'Carol Hill was listed as a Black-liberation political prisoner (held at the Metropolitan Correctional Center, New York, alongside Mutulu Shakur) in U.S. political-prisoner directories of the 1980s. Detailed, reliably-sourced information about her case was not available; this entry preserves the directory listing.',
                'prison' => ['Metropolitan Correctional Center, New York', 'New York', 'New York'],
            ],
            [
                'name' => 'Larry Guy', 'first' => 'Larry', 'last' => 'Guy', 'gender' => 'Male',
                'state' => 'Michigan', 'inmate' => null, 'era' => '1980s', 'affiliation' => ['Black liberation movement'],
                'in_custody' => false, 'released' => true,
                'desc' => 'Larry Guy was listed as a Black-liberation political prisoner held at the State Prison of Southern Michigan (Jackson) in U.S. political-prisoner directories of the 1980s. Detailed, reliably-sourced information about his case was not available; this entry preserves the directory listing.',
                'prison' => ['State Prison of Southern Michigan', 'Jackson', 'Michigan'],
            ],
            [
                'name' => 'Richard Thompson-El', 'first' => 'Richard', 'last' => 'Thompson-El', 'gender' => 'Male',
                'state' => null, 'inmate' => null, 'era' => '1980s', 'affiliation' => ['Black liberation movement'],
                'in_custody' => false, 'released' => true,
                'desc' => 'Richard Thompson-El was listed as a New Afrikan / Black-liberation political prisoner held at the federal penitentiary in Marion, Illinois, in U.S. political-prisoner directories of the 1980s. Detailed, reliably-sourced information about his case was not available; this entry preserves the directory listing.',
                'prison' => ['USP Marion', 'Marion', 'Illinois'],
            ],
        ];

        foreach ($people as $p) {
            $prisoner = Prisoner::withoutGlobalScopes()
                ->where('slug', Str::slug($p['name']))
                ->orWhere('name', $p['name'])
                ->first();

            $attrs = [
                'name' => $p['name'], 'first_name' => $p['first'], 'last_name' => $p['last'], 'aka' => $p['aka'] ?? null,
                'gender' => $p['gender'], 'state' => $p['state'], 'inmate_number' => $p['inmate'] ?? null, 'era' => $p['era'],
                'ideologies' => ['Black liberation'], 'affiliation' => $p['affiliation'],
                'in_custody' => $p['in_custody'], 'released' => $p['released'], 'under_review' => false,
                'description' => $p['desc'],
            ];

            $prisoner ? $prisoner->fill($attrs) : $prisoner = new Prisoner($attrs);
            if (! empty($p['death'])) {
                $prisoner->setPartialDate('death_date', ...$p['death']);
            }
            $prisoner->save();
            $this->info(($prisoner->wasRecentlyCreated ? 'Created: ' : 'Updated: ').$prisoner->name);

            if ($prisoner->cases()->count() === 0) {
                $inst = Institution::firstOrCreate(
                    ['name' => $p['prison'][0]],
                    ['city' => $p['prison'][1], 'state' => $p['prison'][2]],
                );
                $case = $prisoner->cases()->make([
                    'institution_id' => $inst->id,
                    'charges' => $p['charges'] ?? null,
                    'convicted' => $p['convicted'] ?? null,
                    'sentence' => $p['sentence'] ?? null,
                ]);
                foreach (['arrest' => 'arrest_date', 'incarc' => 'incarceration_date', 'release' => 'release_date', 'death_in_custody' => 'death_in_custody_date'] as $k => $field) {
                    if (! empty($p[$k][0])) {
                        $case->setPartialDate($field, ...$p[$k]);
                    }
                }
                $case->save();
            }
        }

        return self::SUCCESS;
    }
}
