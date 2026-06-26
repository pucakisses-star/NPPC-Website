<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Adds the "other notable" political prisoners from the directory: the Cuban
 * Five's Fernando González, indigenous-solidarity bank robber Byron Chubbuck
 * (Oso Blanco), anarchist Bill Dunne, the Portland Seven's Patrice Lumumba
 * Ford, hacktivist Jeremy Hammond, and Green Scare defendant Eric McDavid.
 * Statuses vary (in custody / released). Researched bios + cases; institutions
 * are attached only where reliably known. Idempotent.
 */
final class AddOtherNotablePps extends Command
{
    protected $signature = 'prisoners:add-other-notable';

    protected $description = 'Add the "other notable" directory political prisoners (Cuban Five, Oso Blanco, Dunne, Ford, Hammond, McDavid)';

    public function handle(): int
    {
        // status: in | released.  end = [Y,M,D] release date.  prison may be null when not reliably known.
        $people = [
            ['name' => 'Fernando González', 'first' => 'Fernando', 'last' => 'González', 'aka' => 'Rubén Campa', 'gender' => 'Male',
                'state' => null, 'inmate' => null, 'era' => '1990s', 'ideologies' => ['Socialism', 'Anti-imperialism'], 'affiliation' => ['Cuban Five'],
                'bday' => [1963, 8, 18], 'status' => 'released', 'prison' => null, 'incarc' => [1998, 9, 12], 'end' => [2014, 2, 27],
                'charges' => 'Conspiracy to act as an unregistered agent of a foreign government and use of false identity, as a member of the Cuban intelligence "Wasp Network" that monitored Miami exile groups.', 'convicted' => 'Yes (2001)', 'sentence' => '19 years (reduced to 17 years 9 months)',
                'desc' => 'Fernando González Llort — known to U.S. authorities under the alias "Rubén Campa" — is one of the Cuban Five, intelligence officers arrested in 1998 and convicted in Miami of acting as unregistered foreign agents. Cuba and an international solidarity campaign held them up as political prisoners who were monitoring violent anti-Castro exile groups, not the U.S. government. Born in Havana on August 18, 1963, González was released on February 27, 2014 and returned to Cuba, where he became president of the Cuban Institute for Friendship with the Peoples (ICAP).'],

            ['name' => 'Byron Chubbuck', 'first' => 'Byron', 'middle' => 'Shane', 'last' => 'Chubbuck', 'aka' => 'Oso Blanco', 'gender' => 'Male',
                'state' => 'New Mexico', 'inmate' => '07909-051', 'era' => '1990s', 'ideologies' => ['Indigenous liberation', 'Anti-capitalism'], 'affiliation' => ['Zapatista solidarity'],
                'bday' => [1967, 2, 26], 'status' => 'in', 'prison' => ['USP Atwater', 'Atwater', 'California'], 'incarc' => [1999, 8, 13],
                'charges' => 'A series of bank robberies, aggravated assault on an FBI agent, escape, and firearms offenses; he said the proceeds funded the Zapatista movement in Chiapas, Mexico.', 'convicted' => 'Yes', 'sentence' => '55 years (reduced from 80 after a 2016 Johnson v. United States appeal)',
                'desc' => 'Byron Shane Chubbuck — "Oso Blanco" (White Bear), of Cherokee descent — is an indigenous activist nicknamed "Robin the Hood" for telling bank tellers their money would fund the Zapatista uprising in southern Mexico. Arrested after a 1999 shootout with the FBI in Albuquerque, he was sentenced to 80 years, later reduced to 55. He remains imprisoned at USP Atwater in California.'],

            ['name' => 'Bill Dunne', 'first' => 'Bill', 'last' => 'Dunne', 'gender' => 'Male',
                'state' => 'Washington', 'inmate' => '10916-086', 'era' => '1970s', 'ideologies' => ['Anarchism'], 'affiliation' => ['Anarchist movement'],
                'bday' => [1954, 8, 3], 'status' => 'in', 'prison' => ['FCI Butner Medium II', 'Butner', 'North Carolina'], 'incarc' => [1979, null, null],
                'charges' => 'Auto theft and aiding and abetting the attempted 1979 escape of a comrade from the King County Jail in Seattle; a 1983 escape attempt from USP Lewisburg added 15 years.', 'convicted' => 'Yes', 'sentence' => '90 years',
                'desc' => 'Bill Dunne is an anarchist political prisoner who has been incarcerated since 1979, when he was sentenced to 90 years for the attempted armed liberation of a comrade from the King County Jail in Seattle. A 1983 escape attempt from USP Lewisburg added 15 years. Repeatedly denied parole — most recently told he will not be reconsidered until 2029 — and denied clemency, he was diagnosed with cancer in 2024.'],

            ['name' => 'Patrice Lumumba Ford', 'first' => 'Patrice', 'middle' => 'Lumumba', 'last' => 'Ford', 'gender' => 'Male',
                'state' => 'Oregon', 'inmate' => '96639-011', 'era' => '2000s', 'ideologies' => [], 'affiliation' => ['Portland Seven'],
                'bday' => null, 'status' => 'released', 'prison' => null, 'incarc' => [2002, 10, 4], 'end' => [2018, null, null],
                'charges' => 'Seditious conspiracy and conspiracy to levy war against the United States and to provide material support to al-Qaeda and the Taliban, as one of the "Portland Seven" who tried to reach Afghanistan after 9/11.', 'convicted' => 'Pleaded guilty (2003)', 'sentence' => '18 years',
                'desc' => 'Patrice Lumumba Ford, a former Portland city intern, was one of the "Portland Seven" — a group that attempted to travel to Afghanistan after the September 11 attacks to aid the Taliban. He refused to cooperate with the government and pleaded guilty in 2003 to seditious conspiracy, drawing an 18-year sentence. He was released in 2018.'],

            ['name' => 'Jeremy Hammond', 'first' => 'Jeremy', 'middle' => 'Alexander', 'last' => 'Hammond', 'gender' => 'Male',
                'state' => 'Illinois', 'inmate' => null, 'era' => '2010s', 'ideologies' => ['Anarchism', 'Hacktivism'], 'affiliation' => ['Anonymous', 'AntiSec'],
                'bday' => [1985, 1, 8], 'status' => 'released', 'prison' => ['FCI Memphis', 'Memphis', 'Tennessee'], 'incarc' => [2012, 3, 5], 'end' => [2020, 11, 17],
                'charges' => 'Hacking the private intelligence firm Stratfor (December 2011) as part of AntiSec/Anonymous, exposing internal emails later published by WikiLeaks; pleaded guilty under the Computer Fraud and Abuse Act.', 'convicted' => 'Pleaded guilty (2013)', 'sentence' => '10 years',
                'desc' => 'Jeremy Alexander Hammond is an anarchist hacker from Chicago. As part of the Anonymous offshoot AntiSec he breached the private intelligence firm Stratfor in December 2011, and the leaked emails were published by WikiLeaks. Arrested in March 2012, he was sentenced in 2013 to the maximum ten years. He was released to a halfway house in November 2020.'],

            ['name' => 'Eric McDavid', 'first' => 'Eric', 'last' => 'McDavid', 'gender' => 'Male',
                'state' => 'California', 'inmate' => null, 'era' => '2000s', 'ideologies' => ['Green anarchism', 'Environmentalism'], 'affiliation' => [],
                'bday' => null, 'status' => 'released', 'prison' => null, 'incarc' => [2005, 1, 13], 'end' => [2015, 1, 8],
                'charges' => 'Conspiracy to damage property by fire or explosives, in an FBI sting driven by a paid informant known as "Anna." His conviction was vacated in 2015 after the government admitted withholding thousands of pages of exculpatory evidence.', 'convicted' => 'Conviction vacated (2015)', 'sentence' => '~20 years (vacated)',
                'desc' => 'Eric McDavid is a green anarchist who became one of the most-cited "Green Scare" entrapment cases. Convicted of conspiring to sabotage targets including the Nimbus Dam, he was sentenced to nearly 20 years — a plot prosecutors built around a young paid FBI informant, "Anna." In 2015, after the government conceded it had withheld exculpatory evidence, his conviction was vacated and he was freed after roughly nine years inside.'],
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
                'released' => $status === 'released',
                'under_review' => false,
                'description' => $p['desc'],
            ];

            $prisoner ? $prisoner->fill($attrs) : $prisoner = new Prisoner($attrs);
            if (! empty($p['bday'])) {
                $prisoner->setPartialDate('birthdate', ...$p['bday']);
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
                if ($status === 'released' && ! empty($p['end'])) {
                    $case->setPartialDate('release_date', ...$p['end']);
                }
                $case->save();
            }
        }

        return self::SUCCESS;
    }
}
