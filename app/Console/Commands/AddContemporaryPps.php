<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Adds the contemporary-cluster political prisoners from the directory: the
 * NATO 3 / NATO 5, the Cleveland 4, the Holy Land Foundation Five, anti-war
 * defendants (Ashqar, Dhafir), Christopher Monfort, and assorted anarchist /
 * eco / indigenous / grand-jury-resistance cases. Statuses vary (in custody /
 * released / died in custody / released-then-died). Institutions and birthdays
 * are attached only where reliably known. Researched bios + cases. Idempotent.
 *
 * Note: "Michael Sykes" from the directory is intentionally omitted — his
 * political-prisoner status could not be reliably sourced, so rather than
 * fabricate a record he is flagged for manual review.
 */
final class AddContemporaryPps extends Command
{
    protected $signature = 'prisoners:add-contemporary';

    protected $description = 'Add the contemporary-cluster directory political prisoners (NATO 3, Cleveland 4, Holy Land 5, etc.)';

    public function handle(): int
    {
        // status: in | released | died_custody | died_free.
        // incarc/end/death = [Y,M,D] (month/day may be null). prison may be null when not reliably known.
        $people = [
            // --- NATO 3 (anarchists swept up before the 2012 Chicago NATO summit) ---
            ['name' => 'Jared Chase', 'first' => 'Jared', 'last' => 'Chase', 'aka' => 'Jay Chase', 'gender' => 'Male',
                'state' => 'Illinois', 'era' => '2010s', 'ideologies' => ['Anarchism', 'Anti-war'], 'affiliation' => ['NATO 3'],
                'status' => 'released', 'prison' => null, 'incarc' => [2012, 5, 16], 'end' => [2020, 11, 6],
                'charges' => 'Acquitted of terrorism; convicted of misdemeanor mob action and possession of an incendiary device, over Molotov cocktails in an alleged plot at the 2012 NATO summit (an undercover-police entrapment, supporters say). A later prison-assault charge added a year.', 'convicted' => 'Yes (lesser charges, 2014)', 'sentence' => '8 years (+1 year for a later assault)',
                'desc' => 'Jared "Jay" Chase was one of the "NATO 3," anarchists arrested before the 2012 NATO summit in Chicago in what was the first use of Illinois state terrorism charges. A jury acquitted the three of terrorism, convicting them only of mob action and possession of an incendiary device. Diagnosed with Huntington\'s disease in 2013, Chase served the longest term of the three and was released in November 2020.'],

            ['name' => 'Brian Church', 'first' => 'Brian', 'middle' => 'Jacob', 'last' => 'Church', 'gender' => 'Male',
                'state' => 'Illinois', 'era' => '2010s', 'ideologies' => ['Anarchism', 'Anti-war'], 'affiliation' => ['NATO 3'],
                'status' => 'released', 'prison' => null, 'incarc' => [2012, 5, 16], 'end' => [2016, null, null],
                'charges' => 'Acquitted of terrorism; convicted of misdemeanor mob action and possession of an incendiary device over the 2012 NATO summit case.', 'convicted' => 'Yes (lesser charges, 2014)', 'sentence' => '5 years',
                'desc' => 'Brian Jacob Church was one of the "NATO 3." Arrested in a Chicago police entrapment operation before the 2012 NATO summit and charged with terrorism, he and his co-defendants were acquitted of the terrorism counts and convicted only of mob action and possessing an incendiary device. He was sentenced to five years and has since completed his sentence.'],

            ['name' => 'Brent Betterly', 'first' => 'Brent', 'last' => 'Betterly', 'gender' => 'Male',
                'state' => 'Illinois', 'era' => '2010s', 'ideologies' => ['Anarchism', 'Anti-war'], 'affiliation' => ['NATO 3'],
                'status' => 'released', 'prison' => null, 'incarc' => [2012, 5, 16], 'end' => [2017, null, null],
                'charges' => 'Acquitted of terrorism; convicted of misdemeanor mob action and possession of an incendiary device over the 2012 NATO summit case.', 'convicted' => 'Yes (lesser charges, 2014)', 'sentence' => '6 years',
                'desc' => 'Brent Betterly was one of the "NATO 3," arrested before the 2012 Chicago NATO summit and acquitted of terrorism after a jury found the case amounted to mob action and possession of an incendiary device rather than a terror plot. Sentenced to six years, he has since completed his sentence.'],

            // --- Cleveland 4 (Occupy-affiliated anarchists entrapped in an FBI bridge-bomb sting) ---
            ['name' => 'Douglas Wright', 'first' => 'Douglas', 'last' => 'Wright', 'aka' => 'Doug Wright', 'gender' => 'Male',
                'state' => 'Ohio', 'era' => '2010s', 'ideologies' => ['Anarchism'], 'affiliation' => ['Occupy', 'Cleveland 4'],
                'status' => 'released', 'prison' => null, 'incarc' => [2012, 5, 1], 'end' => [2022, null, null],
                'charges' => 'Conspiracy and attempted use of weapons of mass destruction in an FBI sting in which an informant supplied fake explosives for a plot to blow up the Brecksville-Northfield High Level Bridge near Cleveland.', 'convicted' => 'Pleaded guilty (2012)', 'sentence' => '11 years 6 months + lifetime supervised release',
                'desc' => 'Douglas "Doug" Wright was one of the "Cleveland 4," young Occupy-affiliated anarchists entrapped by an FBI informant who supplied fake explosives and pushed a 2012 plot to bomb a bridge near Cleveland. He received the longest sentence of the four — 11½ years plus lifetime supervised release — and has since been released.'],

            ['name' => 'Brandon Baxter', 'first' => 'Brandon', 'last' => 'Baxter', 'gender' => 'Male',
                'state' => 'Ohio', 'era' => '2010s', 'ideologies' => ['Anarchism'], 'affiliation' => ['Occupy', 'Cleveland 4'],
                'status' => 'released', 'prison' => null, 'incarc' => [2012, 5, 1], 'end' => [2020, null, null],
                'charges' => 'Conspiracy and attempted use of weapons of mass destruction in the FBI-orchestrated Cleveland bridge-bomb sting.', 'convicted' => 'Pleaded guilty (2012)', 'sentence' => '9 years 9 months + lifetime supervised release',
                'desc' => 'Brandon Baxter was one of the "Cleveland 4," Occupy activists drawn by a paid FBI informant into a manufactured 2012 plot to bomb a bridge near Cleveland with explosives the government itself supplied. Sentenced to nine years and nine months plus lifetime supervised release, he has since been released.'],

            ['name' => 'Connor Stevens', 'first' => 'Connor', 'last' => 'Stevens', 'gender' => 'Male',
                'state' => 'Ohio', 'era' => '2010s', 'ideologies' => ['Anarchism'], 'affiliation' => ['Occupy', 'Cleveland 4'],
                'status' => 'released', 'prison' => null, 'incarc' => [2012, 5, 1], 'end' => [2019, 7, null],
                'charges' => 'Conspiracy and attempted use of weapons of mass destruction in the FBI-orchestrated Cleveland bridge-bomb sting.', 'convicted' => 'Pleaded guilty (2012)', 'sentence' => '8 years 1 month + lifetime supervised release',
                'desc' => 'Connor Stevens was one of the "Cleveland 4," entrapped by an FBI informant in a 2012 bridge-bombing sting. He was sentenced to eight years and one month plus lifetime supervised release, and was released in July 2019.'],

            ['name' => 'Joshua Stafford', 'first' => 'Joshua', 'last' => 'Stafford', 'gender' => 'Male',
                'state' => 'Ohio', 'era' => '2010s', 'ideologies' => ['Anarchism'], 'affiliation' => ['Occupy', 'Cleveland 4'],
                'status' => 'released', 'prison' => null, 'incarc' => [2012, 5, 1], 'end' => [2022, null, null],
                'charges' => 'Conspiracy and attempted use of weapons of mass destruction in the FBI-orchestrated Cleveland bridge-bomb sting; convicted at a 2015 trial after earlier competency proceedings.', 'convicted' => 'Yes (2015)', 'sentence' => '10 years + lifetime supervised release',
                'desc' => 'Joshua Stafford was one of the "Cleveland 4." Unlike his co-defendants he went to trial, after lengthy questions about his mental competency, and was convicted in 2015 and sentenced to ten years plus lifetime supervised release. He has since been released.'],

            // --- Holy Land Foundation Five (Palestinian charity material-support case) ---
            ['name' => 'Ghassan Elashi', 'first' => 'Ghassan', 'last' => 'Elashi', 'gender' => 'Male',
                'state' => 'Texas', 'era' => '2000s', 'ideologies' => [], 'affiliation' => ['Holy Land Foundation'],
                'status' => 'in', 'prison' => null, 'incarc' => [2008, 11, null],
                'charges' => 'Conspiracy to provide, and providing, material support to Hamas through the Holy Land Foundation, the largest Muslim charity in the U.S., by funding Palestinian charitable committees the government tied to Hamas.', 'convicted' => 'Yes (2008)', 'sentence' => '65 years',
                'desc' => 'Ghassan Elashi was a founder and chairman of the Holy Land Foundation for Relief and Development, once the largest Muslim charity in the United States. In a case civil-liberties groups consider a landmark overreach, he was convicted in 2008 of funneling charitable donations to Palestinian committees the government linked to Hamas, and sentenced to 65 years. He remains imprisoned.'],

            ['name' => 'Shukri Abu-Baker', 'first' => 'Shukri', 'last' => 'Abu-Baker', 'gender' => 'Male',
                'state' => 'Texas', 'era' => '2000s', 'ideologies' => [], 'affiliation' => ['Holy Land Foundation'],
                'status' => 'in', 'prison' => null, 'incarc' => [2008, 11, null],
                'charges' => 'Conspiracy to provide, and providing, material support to Hamas as CEO of the Holy Land Foundation.', 'convicted' => 'Yes (2008)', 'sentence' => '65 years',
                'desc' => 'Shukri Abu-Baker was the chief executive of the Holy Land Foundation. Convicted in 2008 in the Holy Land Foundation prosecution — the largest terrorism-financing case the U.S. had then brought, built on charitable giving to Palestinians — he was sentenced to 65 years and remains imprisoned.'],

            ['name' => 'Mufid Abdulqader', 'first' => 'Mufid', 'last' => 'Abdulqader', 'gender' => 'Male',
                'state' => 'Texas', 'era' => '2000s', 'ideologies' => [], 'affiliation' => ['Holy Land Foundation'],
                'status' => 'released', 'prison' => null, 'incarc' => [2008, 11, null], 'end' => [2024, 12, 12],
                'charges' => 'Conspiracy to provide material support to Hamas through fundraising performances for the Holy Land Foundation.', 'convicted' => 'Yes (2008)', 'sentence' => '20 years',
                'desc' => 'Mufid Abdulqader was a volunteer performer and fundraiser for the Holy Land Foundation, convicted in 2008 in the foundation\'s material-support case and sentenced to 20 years. He was released on December 12, 2024 after 16 years.'],

            ['name' => 'Abdulrahman Odeh', 'first' => 'Abdulrahman', 'last' => 'Odeh', 'gender' => 'Male',
                'state' => 'New Jersey', 'era' => '2000s', 'ideologies' => [], 'affiliation' => ['Holy Land Foundation'],
                'status' => 'released', 'prison' => null, 'incarc' => [2008, 11, null], 'end' => [2020, null, null],
                'charges' => 'Conspiracy to provide material support to Hamas as the Holy Land Foundation\'s New Jersey representative.', 'convicted' => 'Yes (2008)', 'sentence' => '15 years',
                'desc' => 'Abdulrahman Odeh was the New Jersey representative of the Holy Land Foundation, convicted in the 2008 material-support case and sentenced to 15 years. He was released in 2020.'],

            ['name' => 'Mohammad El-Mezain', 'first' => 'Mohammad', 'last' => 'El-Mezain', 'gender' => 'Male',
                'state' => 'Texas', 'era' => '2000s', 'ideologies' => [], 'affiliation' => ['Holy Land Foundation'],
                'status' => 'released', 'prison' => null, 'incarc' => [2008, 11, null], 'end' => [2022, null, null],
                'charges' => 'Conspiracy to provide material support to Hamas in connection with the Holy Land Foundation (convicted on one conspiracy count).', 'convicted' => 'Yes (2008)', 'sentence' => '15 years',
                'desc' => 'Mohammad El-Mezain was a longtime Holy Land Foundation figure and its former chairman. Convicted on a conspiracy count in the 2008 material-support case, he was sentenced to 15 years. He was released around 2022 and faced immigration proceedings on release.'],

            // --- Anti-war / civil-liberties defendants ---
            ['name' => 'Abdelhaleem Ashqar', 'first' => 'Abdelhaleem', 'last' => 'Ashqar', 'gender' => 'Male',
                'state' => 'Virginia', 'era' => '2000s', 'ideologies' => [], 'affiliation' => ['Palestinian rights movement'],
                'status' => 'released', 'prison' => null, 'incarc' => [2007, null, null], 'end' => [2017, null, null],
                'charges' => 'Criminal contempt and obstruction of justice for refusing — even under a grant of immunity — to testify before a grand jury investigating Hamas. Acquitted of racketeering.', 'convicted' => 'Yes (contempt/obstruction, 2007)', 'sentence' => '11 years 3 months',
                'desc' => 'Abdelhaleem Ashqar, a Palestinian-born former Howard University business professor, was acquitted of racketeering but convicted of criminal contempt and obstruction for refusing to testify before a grand jury investigating Hamas, saying he would not "live as a traitor or a collaborator." Sentenced in 2007 to more than eleven years (sharply enhanced as terrorism), he was released around 2017 and faced deportation.'],

            ['name' => 'Rafil Dhafir', 'first' => 'Rafil', 'last' => 'Dhafir', 'gender' => 'Male',
                'state' => 'New York', 'era' => '2000s', 'ideologies' => ['Anti-war'], 'affiliation' => ['Help the Needy'],
                'status' => 'released', 'prison' => null, 'incarc' => [2003, 2, null], 'end' => [2020, null, null],
                'charges' => 'Violating the U.S. embargo on Iraq by sending humanitarian aid through his charity Help the Needy, plus money laundering, fraud, and tax counts.', 'convicted' => 'Yes (2005)', 'sentence' => '22 years',
                'desc' => 'Dr. Rafil Dhafir, an Iraqi-American oncologist near Syracuse, ran the charity Help the Needy, which sent food and medicine to Iraqis suffering under sanctions. Arrested in 2003 and publicly branded a "suspected terrorist" by officials (a charge never brought), he was convicted of sanctions-violation, fraud, and tax offenses and sentenced to 22 years. He was released in 2020.'],

            // --- Anti-police-brutality (died in custody) ---
            ['name' => 'Christopher Monfort', 'first' => 'Christopher', 'last' => 'Monfort', 'gender' => 'Male',
                'state' => 'Washington', 'era' => '2000s', 'ideologies' => ['Anti-police brutality'], 'affiliation' => [],
                'status' => 'died_custody', 'prison' => ['Washington State Penitentiary', 'Walla Walla', 'Washington'], 'incarc' => [2009, 11, null], 'end' => [2017, 1, null],
                'charges' => 'Aggravated murder of Seattle police officer Timothy Brenton (October 31, 2009), attempted murder, and arson — attacks he framed as retaliation against police brutality.', 'convicted' => 'Yes (2015)', 'sentence' => 'Life without parole',
                'desc' => 'Christopher Monfort shot and killed Seattle police officer Timothy Brenton on October 31, 2009, in attacks he cast as retaliation for police brutality; he was paralyzed when arrested days later. Convicted in 2015 and sentenced to life without parole, he died in custody at the Washington State Penitentiary in January 2017.'],

            // --- Japanese Red Army (US-imprisoned, deported, later died in Japan) ---
            ['name' => 'Tsutomu Shirosaki', 'first' => 'Tsutomu', 'last' => 'Shirosaki', 'gender' => 'Male',
                'state' => null, 'era' => '1980s', 'ideologies' => ['Communism', 'Anti-imperialism'], 'affiliation' => ['Japanese Red Army'],
                'status' => 'died_free', 'prison' => null, 'incarc' => [1996, null, null], 'end' => [2015, 1, null], 'death' => [2024, 7, 20],
                'charges' => 'Attempted murder of internationally protected persons, attempted destruction of buildings, and violent attack on official premises, for the May 1986 rocket attack on the U.S. embassy in Jakarta.', 'convicted' => 'Yes (1998)', 'sentence' => '30 years',
                'desc' => 'Tsutomu Shirosaki was a member of the Japanese Red Army convicted in U.S. federal court in 1998 over the 1986 rocket attack on the U.S. embassy in Jakarta, and sentenced to 30 years. Released for good behavior in January 2015, he was deported to Japan, where he was tried and imprisoned again. He died at Fuchu Prison in Tokyo on July 20, 2024, at age 76.'],

            // --- American Indian Movement ---
            ['name' => 'John Graham', 'first' => 'John', 'last' => 'Graham', 'aka' => 'John Boy Patton', 'gender' => 'Male',
                'state' => 'South Dakota', 'era' => '1970s', 'ideologies' => ['Indigenous liberation'], 'affiliation' => ['American Indian Movement'],
                'status' => 'in', 'prison' => ['South Dakota State Penitentiary', 'Sioux Falls', 'South Dakota'], 'incarc' => [2010, null, null],
                'charges' => 'Felony murder in the 1975 killing of fellow AIM activist Anna Mae Pictou Aquash on the Pine Ridge Reservation.', 'convicted' => 'Yes (2010)', 'sentence' => 'Life',
                'desc' => 'John Graham (John Boy Patton), a Southern Tutchone member of the American Indian Movement from the Yukon, was extradited from Canada and convicted in 2010 of the 1975 murder of AIM activist Anna Mae Aquash, drawing a mandatory life sentence. He maintains his innocence; the case remains bitterly contested within the movement. He is held at the South Dakota State Penitentiary.'],

            // --- Earth Liberation Front ---
            ['name' => 'Steve Murphy', 'first' => 'Steve', 'middle' => 'James', 'last' => 'Murphy', 'gender' => 'Male',
                'state' => 'California', 'inmate' => '39013-177', 'era' => '2000s', 'ideologies' => ['Environmentalism', 'Eco-anarchism'], 'affiliation' => ['Earth Liberation Front'],
                'status' => 'released', 'prison' => null, 'incarc' => [2009, null, null], 'end' => [2014, 2, 25],
                'charges' => 'Attempted arson of a townhouse construction site in Pasadena (2006) using a delayed-ignition incendiary device, to inflict economic harm in the name of the environment (Earth Liberation Front).', 'convicted' => 'Pleaded guilty', 'sentence' => '5 years',
                'desc' => 'Steven James Murphy was prosecuted as an Earth Liberation Front "eco-arsonist" for a 2006 attempt to set fire to a Pasadena construction site with a homemade incendiary device. He served a five-year federal sentence and was released in February 2014.'],

            // --- Eco / animal-rights (self-defense killing) ---
            ['name' => 'Fran Thompson', 'first' => 'Fran', 'last' => 'Thompson', 'gender' => 'Female',
                'state' => 'Missouri', 'inmate' => '1090915', 'era' => null, 'ideologies' => ['Environmentalism', 'Animal liberation'], 'affiliation' => [],
                'status' => 'in', 'prison' => ['Women\'s Eastern Reception, Diagnostic and Correctional Center', 'Vandalia', 'Missouri'], 'incarc' => null,
                'charges' => 'First-degree murder for shooting a man who had stalked, threatened, and broken into her home; she was barred from presenting a self-defense argument at trial.', 'convicted' => 'Yes', 'sentence' => 'Life (plus 10 years)',
                'desc' => 'Fran Thompson was an environmental, animal-rights, and anti-nuclear activist who shot and killed a man who had stalked and threatened her and broke into her home. Convicted of first-degree murder after being barred from presenting a self-defense argument — bias her supporters attribute to her activism — she is serving a life sentence (plus ten years) and remains imprisoned at the Women\'s Eastern Reception, Diagnostic and Correctional Center in Missouri.'],

            // --- Anarchist (attack during a planned governor's visit) ---
            ['name' => 'Casey Brezik', 'first' => 'Casey', 'last' => 'Brezik', 'gender' => 'Male',
                'state' => 'Missouri', 'era' => '2010s', 'ideologies' => ['Anarchism'], 'affiliation' => ['Anarchist movement'],
                'status' => 'released', 'prison' => null, 'incarc' => [2010, null, null], 'end' => [2026, 1, null],
                'charges' => 'Assault and armed criminal action for slashing a dean at Metropolitan Community College–Penn Valley in 2010, during a visit at which Missouri Governor Jay Nixon was scheduled to speak.', 'convicted' => 'Pleaded guilty (2013)', 'sentence' => '12 years',
                'desc' => 'Casey Brezik is a Kansas City–area anarchist who slashed a community-college dean in 2010 at an event where Missouri\'s governor was due to appear. After years held as incompetent to stand trial, he pleaded guilty in 2013 and was sentenced to 12 years. He was released in January 2026.'],

            // NOTE: Mark "Migs" Neiweem (NATO 5) is intentionally omitted — he is
            // already in the database with a curated record, so adding him here
            // would risk overwriting existing data.

            // --- Anarchist (Santa Cruz arson) ---
            ['name' => 'Miguel Balderos', 'first' => 'Miguel', 'last' => 'Balderos', 'gender' => 'Male',
                'state' => 'California', 'era' => '2010s', 'ideologies' => ['Anarchism'], 'affiliation' => ['Anarchist movement'],
                'status' => 'released', 'prison' => null, 'incarc' => null, 'end' => [2016, 1, 9],
                'charges' => 'Arson of the Santa Cruz, California city prosecutor\'s office; he reportedly told police he was a homeless anarchist protesting the city\'s ban on camping.', 'convicted' => 'Yes', 'sentence' => '10 years 8 months',
                'desc' => 'Miguel Balderos is an anarchist convicted of an arson at the Santa Cruz, California city prosecutor\'s office, which he tied to protest against the city\'s ban on sleeping outdoors. He was released on January 9, 2016.'],

            // --- Seattle grand jury resisters (civil contempt, no charges) ---
            ['name' => 'Matt Duran', 'first' => 'Matt', 'last' => 'Duran', 'gender' => 'Male',
                'state' => 'Washington', 'era' => '2010s', 'ideologies' => ['Anarchism'], 'affiliation' => ['Seattle grand jury resisters'],
                'status' => 'released', 'prison' => ['SeaTac Federal Detention Center', 'SeaTac', 'Washington'], 'incarc' => [2012, 9, 14], 'end' => [2013, 2, 28],
                'charges' => null, 'convicted' => 'Held in civil contempt (no charges filed)', 'sentence' => 'Civil contempt — jailed for refusing to testify before a grand jury',
                'desc' => 'Matt Duran was one of the Pacific Northwest anarchists jailed in 2012 for civil contempt after refusing to testify before a federal grand jury investigating May Day protests in Seattle. He was never charged with a crime. Held for more than five months — including stretches in solitary — he was released in February 2013.'],

            ['name' => 'Katherine Olejnik', 'first' => 'Katherine', 'last' => 'Olejnik', 'aka' => 'KteeO', 'gender' => 'Female',
                'state' => 'Washington', 'era' => '2010s', 'ideologies' => ['Anarchism'], 'affiliation' => ['Seattle grand jury resisters'],
                'status' => 'released', 'prison' => ['SeaTac Federal Detention Center', 'SeaTac', 'Washington'], 'incarc' => [2012, 9, 27], 'end' => [2013, 2, 28],
                'charges' => null, 'convicted' => 'Held in civil contempt (no charges filed)', 'sentence' => 'Civil contempt — jailed for refusing to testify before a grand jury',
                'desc' => 'Katherine "KteeO" Olejnik was one of the Pacific Northwest anarchists jailed in 2012 for civil contempt for refusing to testify before a federal grand jury probing Seattle May Day protests. Never charged with any crime, she spent more than five months in detention, including time in solitary, and was released in February 2013.'],

            ['name' => 'Matthew Pfeiffer', 'first' => 'Matthew', 'last' => 'Pfeiffer', 'aka' => 'Maddy Pfeiffer', 'gender' => 'Male',
                'state' => 'Washington', 'era' => '2010s', 'ideologies' => ['Anarchism'], 'affiliation' => ['Seattle grand jury resisters'],
                'status' => 'released', 'prison' => ['SeaTac Federal Detention Center', 'SeaTac', 'Washington'], 'incarc' => [2012, 12, 26], 'end' => [2013, 4, 11],
                'charges' => null, 'convicted' => 'Held in civil contempt (no charges filed)', 'sentence' => 'Civil contempt — jailed for refusing to testify before a grand jury',
                'desc' => 'Matthew "Maddy" Pfeiffer was one of the Pacific Northwest grand-jury resisters, jailed in December 2012 for civil contempt after refusing to cooperate with a federal grand jury investigating Seattle May Day protests. Never charged with a crime, Pfeiffer was released in April 2013.'],
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
                'state' => $p['state'] ?? null, 'inmate_number' => $p['inmate'] ?? null, 'era' => $p['era'] ?? null,
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
                $institutionId = null;
                if (! empty($p['prison'])) {
                    $institutionId = Institution::firstOrCreate(
                        ['name' => $p['prison'][0]],
                        ['city' => $p['prison'][1], 'state' => $p['prison'][2]],
                    )->id;
                }
                $case = $prisoner->cases()->make([
                    'institution_id' => $institutionId,
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

        $this->newLine();
        $this->warn('Flagged (not added — could not reliably source as a political prisoner): Michael Sykes.');
        $this->line('Skipped (already in the database): Mark Neiweem.');

        return self::SUCCESS;
    }
}
