<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Adds the missing Black / New Afrikan liberation prisoners from the directory.
 * Statuses vary: still in custody, released, died in custody, or released-then-
 * died. Researched bios + cases. Gary Watson is included per the directory with
 * a measured note (his political-prisoner status is asserted but not widely
 * documented). Idempotent.
 */
final class AddBlackNewAfrikan extends Command
{
    protected $signature = 'prisoners:add-black-new-afrikan';

    protected $description = 'Add the missing Black / New Afrikan liberation political prisoners';

    public function handle(): int
    {
        // status: in | released | died_custody | died_free.  end = [Y,M,D] release-or-death date.
        $people = [
            ['name' => 'Jamil Al-Amin', 'first' => 'Jamil', 'middle' => 'Abdullah', 'last' => 'Al-Amin', 'aka' => 'H. Rap Brown', 'gender' => 'Male',
                'state' => 'Georgia', 'inmate' => '99974-555', 'era' => '2000s', 'ideologies' => ['Black liberation', 'Black nationalism'], 'affiliation' => ['SNCC'],
                'bday' => [1943, 10, 4], 'status' => 'in', 'prison' => ['USP Tucson', 'Tucson', 'Arizona'], 'incarc' => [2000, null, null],
                'charges' => 'Convicted in 2002 of the March 2000 killing of Fulton County (Atlanta) sheriff\'s deputy Ricky Kinchen and the wounding of another deputy.', 'convicted' => 'Yes', 'sentence' => 'Life without parole',
                'desc' => 'Jamil Abdullah Al-Amin — formerly known as H. Rap Brown — was a 1960s chairman of the Student Nonviolent Coordinating Committee (SNCC) and a justice minister of the Black Panther Party, who later became a Muslim imam in Atlanta. In 2002 he was convicted of the March 2000 shooting that killed Fulton County sheriff\'s deputy Ricky Kinchen and wounded another, and sentenced to life without parole. Supporters consider him a target of decades of state surveillance dating to COINTELPRO.'],

            ['name' => 'Zolo Azania', 'first' => 'Zolo', 'last' => 'Azania', 'gender' => 'Male',
                'state' => 'Indiana', 'inmate' => '4969', 'era' => '1980s', 'ideologies' => ['Black liberation', 'New Afrikan independence'], 'affiliation' => ['New Afrikan independence movement'],
                'bday' => [1954, 12, 12], 'status' => 'released', 'prison' => ['Indiana State Prison', 'Michigan City', 'Indiana'], 'incarc' => [1981, null, null], 'end' => [2017, null, null],
                'charges' => 'Convicted of the 1981 killing of Gary, Indiana police lieutenant George Yaros during a bank robbery. Twice sentenced to death (both sentences overturned), he was later resentenced.', 'convicted' => 'Yes', 'sentence' => 'Death (overturned), later a term of years',
                'desc' => 'Zolo Agona Azania is a New Afrikan activist and artist convicted of the 1981 killing of a Gary, Indiana police lieutenant during a bank robbery. He spent years on death row before his death sentences were twice overturned for misconduct; he was resentenced and ultimately paroled around 2017 after more than three decades inside.'],

            ['name' => 'Joseph Bowen', 'first' => 'Joseph', 'last' => 'Bowen', 'aka' => 'Joe-Joe Bowen', 'gender' => 'Male',
                'state' => 'Pennsylvania', 'inmate' => 'AM-4272', 'era' => '1970s', 'ideologies' => ['Black liberation'], 'affiliation' => ['Black liberation movement'],
                'bday' => [1948, 1, 15], 'status' => 'in', 'prison' => ['SCI Coal Township', 'Coal Township', 'Pennsylvania'], 'incarc' => [1971, null, null],
                'charges' => 'Convicted of the 1973 killings of Holmesburg Prison warden Patrick Curran and deputy warden Robert Fromhold (while already imprisoned).', 'convicted' => 'Yes', 'sentence' => 'Life',
                'desc' => 'Joseph "Joe-Joe" Bowen is a Pennsylvania prisoner and longtime jailhouse organizer. Already imprisoned, he was convicted of the 1973 killings of Holmesburg Prison warden Patrick Curran and deputy warden Robert Fromhold, and has spent the decades since as a prison-movement figure.'],

            ['name' => 'Fred Burton', 'first' => 'Fred', 'last' => 'Burton', 'aka' => 'Muhammad Burton', 'gender' => 'Male',
                'state' => 'Pennsylvania', 'inmate' => 'AF-3896', 'era' => '1970s', 'ideologies' => ['Black liberation'], 'affiliation' => ['Black Unity Council'],
                'bday' => [1946, 12, 15], 'status' => 'in', 'prison' => ['SCI Somerset', 'Somerset', 'Pennsylvania'], 'incarc' => [1970, null, null],
                'charges' => 'Convicted in the 1970 killing of Philadelphia police sergeant Frank Von Colln, attributed to the Black Unity Council.', 'convicted' => 'Yes', 'sentence' => 'Life',
                'desc' => 'Fred "Muhammad" Burton was associated with the Black Unity Council and was convicted in the 1970 killing of a Philadelphia police sergeant, Frank Von Colln. He has been imprisoned in Pennsylvania since the early 1970s.'],

            ['name' => 'Kamau Sadiki', 'first' => 'Kamau', 'last' => 'Sadiki', 'aka' => 'Freddie Hilton', 'gender' => 'Male',
                'state' => 'Georgia', 'inmate' => '0001150688', 'era' => '2000s', 'ideologies' => ['Black liberation'], 'affiliation' => ['Black Liberation Army'],
                'bday' => null, 'status' => 'in', 'prison' => ['Augusta State Medical Prison', 'Grovetown', 'Georgia'], 'incarc' => [2002, null, null],
                'charges' => 'Convicted in 2002 of the 1971 murder of Atlanta police officer James Greer — a cold case revived decades later.', 'convicted' => 'Yes', 'sentence' => 'Life',
                'desc' => 'Kamau Sadiki (Freddie Hilton) is a former Black Panther and Black Liberation Army member. In 2002 he was convicted of the 1971 killing of Atlanta police officer James Greer, a cold case supporters say was revived to pressure him to inform on Assata Shakur. Gravely ill, he remains held at the Augusta State Medical Prison in Georgia.'],

            ['name' => 'Larry Hoover', 'first' => 'Larry', 'last' => 'Hoover', 'gender' => 'Male',
                'state' => 'Illinois', 'inmate' => '86063-024', 'era' => '1970s', 'ideologies' => [], 'affiliation' => ['Gangster Disciples'],
                'bday' => [1950, 11, 30], 'status' => 'in', 'prison' => ['USP Florence ADMAX', 'Florence', 'Colorado'], 'incarc' => [1973, null, null],
                'charges' => 'Serving a 150–200 year Illinois sentence for a 1973 murder, plus a federal life term for running a continuing criminal enterprise.', 'convicted' => 'Yes', 'sentence' => '150–200 years (Illinois) + federal life (commuted 2025)',
                'desc' => 'Larry Hoover is the founder of the Gangster Disciples. He has been imprisoned since 1973 on an Illinois murder conviction (150–200 years) and also received a federal life sentence in 1997 for running a continuing criminal enterprise. President Trump commuted the federal sentence in May 2025, but Hoover remains imprisoned on the Illinois term.'],

            ['name' => 'Jeff Fort', 'first' => 'Jeff', 'last' => 'Fort', 'aka' => 'Abdullah Malik Ka\'bah', 'gender' => 'Male',
                'state' => 'Illinois', 'inmate' => '92298-024', 'era' => '1980s', 'ideologies' => [], 'affiliation' => ['Black P. Stone Nation', 'El Rukn'],
                'bday' => [1947, 2, 20], 'status' => 'in', 'prison' => ['USP Florence ADMAX', 'Florence', 'Colorado'], 'incarc' => [1983, null, null],
                'charges' => 'Convicted in 1987 of conspiring with Libya to carry out terrorist acts in the United States for payment, and of ordering a 1981 murder.', 'convicted' => 'Yes', 'sentence' => 'Life (held at ADX Florence)',
                'desc' => 'Jeff Fort (Abdullah Malik Ka\'bah) founded the Chicago street organization the Black P. Stone Nation, later the El Rukn. In 1987 he was convicted of conspiring with the Libyan government to carry out terrorist acts in the U.S. in exchange for payment, and of ordering a murder; he has been held under extreme isolation at ADX Florence.'],

            ['name' => 'Sekou Kambui', 'first' => 'Sekou', 'last' => 'Kambui', 'aka' => 'William Turk', 'gender' => 'Male',
                'state' => 'Alabama', 'inmate' => '113058', 'era' => '1970s', 'ideologies' => ['Black liberation'], 'affiliation' => ['Black Panther Party'],
                'bday' => [1948, 9, 6], 'status' => 'released', 'prison' => ['Bibb County Correctional Facility', 'Brent', 'Alabama'], 'incarc' => [1975, null, null], 'end' => [2014, 6, 18],
                'charges' => 'Accused of two 1975 killings of white men in Alabama, amid allegations that witnesses were coerced; supporters tie the prosecution to his civil-rights organizing.', 'convicted' => 'Yes', 'sentence' => 'Life (paroled 2014)',
                'desc' => 'Sekou Cinque Kambui (William Turk) was a civil-rights and Black Panther organizer in Alabama. Accused of two 1975 killings in cases marked by allegations of coerced witnesses, he served about 40 years and was paroled on June 18, 2014.'],

            ['name' => 'Maumin Khabir', 'first' => 'Maumin', 'last' => 'Khabir', 'aka' => 'Melvin Mayes', 'gender' => 'Male',
                'state' => 'Illinois', 'inmate' => '09891-000', 'era' => '1980s', 'ideologies' => [], 'affiliation' => ['El Rukn'],
                'bday' => null, 'status' => 'released', 'prison' => ['MCFP Springfield', 'Springfield', 'Missouri'], 'incarc' => [1980, null, null], 'end' => [2022, 2, 15],
                'charges' => 'Convicted under RICO as an El Rukn member in connection with the group\'s $2.5 million conspiracy with Libya; sentenced to life.', 'convicted' => 'Yes', 'sentence' => 'Life (released 2022)',
                'desc' => 'Maumin Khabir (Melvin Mayes) was identified by the U.S. government as an El Rukn "general" and convicted under RICO in connection with the group\'s conspiracy with Libya. After years as a fugitive and then decades imprisoned, he was released on February 15, 2022.'],

            ['name' => 'Mondo we Langa', 'first' => 'Mondo', 'last' => 'we Langa', 'aka' => 'David Rice', 'gender' => 'Male',
                'state' => 'Nebraska', 'inmate' => '27768', 'era' => '1970s', 'ideologies' => ['Black liberation'], 'affiliation' => ['Black Panther Party'],
                'bday' => [1947, 5, 21], 'status' => 'died_custody', 'prison' => ['Nebraska State Penitentiary', 'Lincoln', 'Nebraska'], 'incarc' => [1970, null, null], 'end' => [2016, 3, 11],
                'charges' => 'Convicted with Ed Poindexter (the "Omaha Two") of the 1970 bombing murder of Omaha police officer Larry Minard, in a COINTELPRO-tainted case widely regarded as a frame-up.', 'convicted' => 'Yes', 'sentence' => 'Life',
                'desc' => 'Wopashitwe Mondo Eyen we Langa (David Rice) was a leader of Omaha\'s Black Panther affiliate. With Ed Poindexter — the "Omaha Two" — he was convicted of the 1970 bombing murder of Omaha police officer Larry Minard, in a case tainted by COINTELPRO and widely considered a frame-up. He died in prison on March 11, 2016 after 44 years.'],

            ['name' => 'Ed Poindexter', 'first' => 'Ed', 'last' => 'Poindexter', 'gender' => 'Male',
                'state' => 'Nebraska', 'inmate' => '27767', 'era' => '1970s', 'ideologies' => ['Black liberation'], 'affiliation' => ['Black Panther Party'],
                'bday' => [1944, 11, 1], 'status' => 'died_custody', 'prison' => ['Nebraska State Penitentiary', 'Lincoln', 'Nebraska'], 'incarc' => [1970, null, null], 'end' => [2023, 12, 7],
                'charges' => 'Convicted with David Rice / Mondo we Langa (the "Omaha Two") of the 1970 bombing murder of Omaha police officer Larry Minard, in a COINTELPRO-tainted case.', 'convicted' => 'Yes', 'sentence' => 'Life',
                'desc' => 'Edward Poindexter, with David Rice (Mondo we Langa), made up the "Omaha Two," convicted of the 1970 bombing murder of Omaha police officer Larry Minard in a COINTELPRO-tainted case that supporters long called a frame-up. He spent 52 years in prison and died in custody on December 7, 2023.'],

            ['name' => 'Maliki Shakur Latine', 'first' => 'Maliki', 'middle' => 'Shakur', 'last' => 'Latine', 'gender' => 'Male',
                'state' => 'New York', 'inmate' => '81-A-4469', 'era' => '1980s', 'ideologies' => ['Black liberation'], 'affiliation' => ['Black Panther Party', 'Black Liberation Army'],
                'bday' => [1953, 8, 23], 'status' => 'released', 'prison' => ['Clinton Correctional Facility', 'Dannemora', 'New York'], 'incarc' => [1979, null, null], 'end' => [2016, null, null],
                'charges' => 'Sentenced in 1981 to 25 years to life for the attempted murder of a New York City police officer.', 'convicted' => 'Yes', 'sentence' => '25 years to life',
                'desc' => 'Maliki Shakur Latine, born in the Bronx in 1953, was a member of the Black Panther Party and the Black Liberation Army. Sentenced in 1981 to 25 years to life for the attempted murder of a New York City police officer, he was freed in 2016 after 37 years.'],

            ['name' => 'Gary Watson', 'first' => 'Gary', 'last' => 'Watson', 'gender' => 'Male',
                'state' => 'Delaware', 'inmate' => '098990', 'era' => '2010s', 'ideologies' => [], 'affiliation' => ['Black liberation movement'],
                'bday' => null, 'status' => 'in', 'prison' => ['James T. Vaughn Correctional Center', 'Smyrna', 'Delaware'], 'incarc' => [2012, null, null],
                'charges' => 'Convicted in Delaware of attempted murder for shooting into a police officer\'s home in 2012; sentenced to 106 years.', 'convicted' => 'Yes', 'sentence' => '106 years',
                'desc' => 'Gary Watson is held in Delaware, where he was convicted of attempted murder for a 2012 shooting into a police officer\'s home and sentenced to 106 years. He is listed among Black-liberation prisoners in movement directories, though that framing of his case is not widely documented in other sources.'],

            ['name' => 'Albert Woodfox', 'first' => 'Albert', 'last' => 'Woodfox', 'gender' => 'Male',
                'state' => 'Louisiana', 'inmate' => '72148', 'era' => '1970s', 'ideologies' => ['Black liberation'], 'affiliation' => ['Black Panther Party', 'Angola 3'],
                'bday' => [1947, 2, 19], 'status' => 'died_free', 'prison' => ['Louisiana State Penitentiary (Angola)', 'Angola', 'Louisiana'], 'incarc' => [1972, null, null], 'end' => [2016, 2, 19], 'death' => [2022, 8, 4],
                'charges' => 'Convicted of the 1972 murder of Louisiana State Penitentiary guard Brent Miller — a conviction repeatedly overturned. He spent ~43 years in solitary confinement.', 'convicted' => 'Conviction overturned; freed 2016 on a plea to lesser charges', 'sentence' => 'Life',
                'desc' => 'Albert Woodfox was one of the "Angola 3." Convicted of the 1972 murder of a Louisiana State Penitentiary guard — a conviction overturned several times — he spent roughly 43 years in solitary confinement, the longest such stretch in U.S. history, before being freed on his 69th birthday in 2016 on a plea to lesser charges. His memoir "Solitary" was a Pulitzer Prize and National Book Award finalist. He died in 2022.'],
        ];

        foreach ($people as $p) {
            $prisoner = Prisoner::withoutGlobalScopes()
                ->where('slug', Str::slug($p['name']))
                ->orWhere('name', $p['name'])
                ->first();

            $status = $p['status'];
            $attrs = [
                'name' => $p['name'], 'first_name' => $p['first'], 'middle_name' => $p['middle'] ?? null,
                'last_name' => $p['last'], 'aka' => $p['aka'] ?? null, 'gender' => $p['gender'],
                'state' => $p['state'], 'inmate_number' => $p['inmate'], 'era' => $p['era'],
                'ideologies' => $p['ideologies'], 'affiliation' => $p['affiliation'],
                'in_custody' => $status === 'in',
                'released' => in_array($status, ['released', 'died_free'], true),
                'under_review' => false,
                'description' => $p['desc'],
            ];

            $prisoner ? $prisoner->fill($attrs) : $prisoner = new Prisoner($attrs);
            if (! empty($p['bday'])) {
                $prisoner->setPartialDate('birthdate', ...$p['bday']);
            }
            if ($status === 'died_custody') {
                $prisoner->setPartialDate('death_date', ...$p['end']);
            } elseif ($status === 'died_free' && ! empty($p['death'])) {
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
                    'charges' => $p['charges'],
                    'convicted' => $p['convicted'],
                    'sentence' => $p['sentence'] ?? null,
                ]);
                if (! empty($p['incarc'])) {
                    $case->setPartialDate('incarceration_date', ...$p['incarc']);
                }
                if ($status === 'died_custody') {
                    $case->setPartialDate('death_in_custody_date', ...$p['end']);
                } elseif (in_array($status, ['released', 'died_free'], true) && ! empty($p['end'])) {
                    $case->setPartialDate('release_date', ...$p['end']);
                }
                $case->save();
            }
        }

        return self::SUCCESS;
    }
}
