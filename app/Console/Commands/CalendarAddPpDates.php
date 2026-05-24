<?php

namespace App\Console\Commands;

use App\Models\CalendarEntry;
use Illuminate\Console\Command;

/**
 * Seeds 28 canonical political-prisoner calendar dates that weren't
 * already on the NPPC calendar:
 *
 *   - Puerto Rican independentista milestones (Jayuya, Lolita Lebrón
 *     Capitol attack, Albizu Campos death, López Rivera commutation
 *     and release)
 *   - Black Liberation Army / Assata Shakur arc (NJ Turnpike, trial,
 *     escape)
 *   - George Jackson killed at San Quentin
 *   - MOVE 9 arrest; Mutulu Shakur captured; Brink's-Nyack arrests
 *   - Green Scare (Operation Backfire, McDavid, Marie Mason)
 *   - Catonsville Nine; Plowshares Eight; Chicago 7 verdict
 *   - IWW founded; Pelican Bay hunger strike begins
 *   - Citizens' Commission FBI burglary (exposed COINTELPRO)
 *   - Guantanamo opens; Tortuguita killed; Mahmoud Khalil ICE arrest;
 *     Peltier release; Patty Hearst kidnapped
 *
 * Each entry is created without an image — run calendar:backfill-photos
 * afterward to pull Wikipedia images.
 *
 * Idempotent — matches on (month, day, year, title).
 */
final class CalendarAddPpDates extends Command {
    protected $signature = 'calendar:add-pp-dates';
    protected $description = 'Add 28 canonical political-prisoner calendar dates';

    public function handle(): int {
        $added = 0;
        $updated = 0;

        foreach ($this->entries() as $e) {
            $existing = CalendarEntry::query()
                ->where('month', $e['month'])
                ->where('day', $e['day'])
                ->where('year', $e['year'])
                ->where('title', $e['title'])
                ->first();

            $payload = $e + ['published' => true];

            if ($existing) {
                $existing->update($payload);
                $updated++;
            } else {
                CalendarEntry::create($payload);
                $this->line(sprintf('  ADD  %02d-%02d %d  %s', $e['month'], $e['day'], $e['year'], $e['title']));
                $added++;
            }
        }

        $this->newLine();
        $this->info("Done — added {$added}, updated {$updated}.");
        return self::SUCCESS;
    }

    /** @return array<int, array<string, mixed>> */
    private function entries(): array {
        return [
            ['month' => 1, 'day' => 11, 'year' => 2002,
                'title' => 'Guantanamo Bay detention camp opens',
                'description' => 'The first 20 detainees from the U.S. "war on terror" arrive at Naval Station Guantanamo Bay, beginning what would become decades of indefinite detention outside the U.S. legal system. Hundreds of men were held without charge; many were tortured. Detentions continue to this day.'],
            ['month' => 1, 'day' => 13, 'year' => 2006,
                'title' => 'Eric McDavid arrested in Green Scare entrapment',
                'description' => 'Environmental activist Eric McDavid was arrested in Auburn, California, along with two co-defendants, on conspiracy charges built largely by an FBI informant ("Anna") who supplied the group with materials, ideas, and shelter. McDavid was sentenced to nearly 20 years. He was released in 2015 after his attorneys discovered withheld exculpatory evidence; the government agreed to a plea reduction.'],
            ['month' => 1, 'day' => 17, 'year' => 2017,
                'title' => 'Obama commutes Oscar López Rivera\'s sentence',
                'description' => 'President Barack Obama commutes the sentence of Puerto Rican independentista Oscar López Rivera, who had served 35 years — most in maximum-security federal prisons — for seditious conspiracy. López Rivera was released on May 17, 2017.'],
            ['month' => 1, 'day' => 18, 'year' => 2023,
                'title' => 'Tortuguita killed by Georgia State Patrol in Atlanta forest',
                'description' => 'Forest defender Manuel Esteban Paez Terán ("Tortuguita") was shot dead by Georgia State Patrol officers during a multi-agency raid on the Weelaunee Forest encampment opposing the construction of the Atlanta Public Safety Training Center ("Cop City"). An autopsy found at least 57 gunshot wounds; no officers were charged. Tortuguita\'s killing galvanized the Stop Cop City movement.'],
            ['month' => 2, 'day' => 4, 'year' => 1974,
                'title' => 'Patty Hearst kidnapped by Symbionese Liberation Army',
                'description' => 'Newspaper heiress Patricia Hearst was kidnapped from her Berkeley apartment by the Symbionese Liberation Army. Within months she joined the group, taking the name "Tania," and participated in armed actions. Convicted in 1976, sentenced to 7 years; sentence later commuted by Carter and pardoned by Clinton.'],
            ['month' => 2, 'day' => 5, 'year' => 1986,
                'title' => 'Mutulu Shakur captured in Los Angeles',
                'description' => 'Acupuncturist and Black Liberation Army member Mutulu Shakur was arrested in Los Angeles after five years on the FBI\'s Ten Most Wanted Fugitives list. Convicted of RICO conspiracy in the 1981 Brink\'s armored car robbery in Nyack, NY, he was sentenced to 60 years. Released on parole in December 2022; died July 6, 2023.'],
            ['month' => 2, 'day' => 5, 'year' => 2009,
                'title' => 'Marius Mason sentenced to nearly 22 years for ELF actions',
                'description' => 'Earth Liberation Front-associated activist Marius (Marie) Mason was sentenced to 21 years 10 months — the longest sentence ever handed down to an environmental defendant in the United States — for participating in the 1999 arson of a Michigan State University office connected to GMO research, and related actions. Released to a halfway house in May 2026.'],
            ['month' => 2, 'day' => 18, 'year' => 1970,
                'title' => 'Chicago Seven verdict returned',
                'description' => 'After a chaotic five-month trial over the Democratic National Convention protests, the jury acquitted the Chicago Seven (Abbie Hoffman, Jerry Rubin, David Dellinger, Tom Hayden, Rennie Davis, John Froines, Lee Weiner) of conspiracy. Five were convicted of crossing state lines to incite a riot. Judge Hoffman had handed down 175 contempt citations during the trial — most later reversed on appeal.'],
            ['month' => 2, 'day' => 18, 'year' => 2025,
                'title' => 'Leonard Peltier released after 49 years',
                'description' => 'AIM activist Leonard Peltier was released to home confinement at the Turtle Mountain reservation, North Dakota — 49 years after his 1976 arrest for the killing of two FBI agents at Pine Ridge. Peltier had been denied parole repeatedly despite international calls for his release; President Biden commuted his sentence on January 20, 2025.'],
            ['month' => 3, 'day' => 1, 'year' => 1954,
                'title' => 'Lolita Lebrón leads Puerto Rican Nationalist attack on US Capitol',
                'description' => 'Lolita Lebrón, Rafael Cancel Miranda, Andrés Figueroa Cordero, and Irvin Flores opened fire from the visitors\' gallery of the U.S. House of Representatives, wounding five congressmen. They unfurled a Puerto Rican flag and shouted "¡Viva Puerto Rico libre!" Sentenced to decades in prison; their sentences were commuted by President Carter in 1979.'],
            ['month' => 3, 'day' => 8, 'year' => 1971,
                'title' => 'Citizens\' Commission burgles FBI office, exposing COINTELPRO',
                'description' => 'A small group calling itself the Citizens\' Commission to Investigate the FBI broke into the bureau\'s field office in Media, Pennsylvania, and mailed stolen documents to the press. The leak revealed COINTELPRO — the FBI\'s decades-long covert campaign of surveillance, infiltration, and sabotage against the civil rights, Black Power, antiwar, and women\'s liberation movements. The burglars were never identified.'],
            ['month' => 3, 'day' => 8, 'year' => 2025,
                'title' => 'Mahmoud Khalil abducted by ICE for Columbia Gaza organizing',
                'description' => 'Palestinian organizer and Columbia University graduate student Mahmoud Khalil, a lawful permanent resident, was detained by ICE agents at his New York City apartment in retaliation for his prominent role in the Gaza solidarity encampment. The Trump administration invoked a rarely-used Cold War-era statute to seek his deportation. He was transferred to an immigration detention facility in Louisiana.'],
            ['month' => 3, 'day' => 25, 'year' => 1977,
                'title' => 'Assata Shakur convicted in NJ Turnpike case',
                'description' => 'Black Liberation Army member Assata Shakur was convicted by an all-white New Jersey jury of murder, assault, and related charges in the 1973 NJ Turnpike shootout that killed state trooper Werner Foerster and her comrade Zayd Malik Shakur. Sentenced to life plus 33 years. Escaped from prison in 1979; has lived in political asylum in Cuba since 1984.'],
            ['month' => 4, 'day' => 21, 'year' => 1965,
                'title' => 'Pedro Albizu Campos dies in Puerto Rico',
                'description' => 'Pedro Albizu Campos, the foremost twentieth-century Puerto Rican independence leader and president of the Nationalist Party, dies in San Juan from health complications widely attributed to radiation experiments conducted on him during his decades in U.S. federal prisons. Sentenced in 1937 for "seditious conspiracy," he served roughly 25 of his last 30 years in custody.'],
            ['month' => 5, 'day' => 2, 'year' => 1973,
                'title' => 'Assata Shakur shot and captured on NJ Turnpike',
                'description' => 'Black Liberation Army members Assata Shakur, Zayd Malik Shakur, and Sundiata Acoli are stopped by NJ state troopers near East Brunswick. In the shootout that follows, trooper Werner Foerster and Zayd Shakur are killed; Assata is wounded and captured. She would face six separate trials before her 1977 conviction.'],
            ['month' => 5, 'day' => 17, 'year' => 1968,
                'title' => 'Catonsville Nine burn draft files in Maryland',
                'description' => 'Daniel and Philip Berrigan and seven other Catholic peace activists removed 378 draft files from the Catonsville, MD draft board and burned them in the parking lot with homemade napalm. Their trial that October became a landmark of antiwar resistance and Catholic Worker activism. All nine served federal prison sentences.'],
            ['month' => 6, 'day' => 11, 'year' => 2009,
                'title' => 'First International Day of Solidarity with Long-Term Anarchist Prisoners',
                'description' => 'June 11 is observed annually as the International Day of Solidarity with Long-Term Anarchist Prisoners, initiated in 2009 by supporters of imprisoned Earth Liberation Front activist Jeff "Free" Luers. The day is marked by letter writing events, demonstrations, prisoner zines, and fundraising for anarchist political prisoners worldwide.'],
            ['month' => 6, 'day' => 27, 'year' => 1905,
                'title' => 'Industrial Workers of the World founded in Chicago',
                'description' => 'The Industrial Workers of the World ("Wobblies") was founded at a convention in Chicago, uniting socialists, anarchists, and radical unionists into a single revolutionary union open to all workers regardless of skill, race, or gender. IWW members would be among the first major waves of U.S. political prisoners under the 1917 Espionage Act and 1918 Sedition Act.'],
            ['month' => 7, 'day' => 1, 'year' => 2011,
                'title' => 'Pelican Bay hunger strike begins against solitary confinement',
                'description' => 'Prisoners in the Pelican Bay State Prison Security Housing Unit (SHU) in California began a hunger strike against indefinite solitary confinement that had held many for decades. The strike spread across the California prison system and by mid-July involved more than 6,600 prisoners. A second wave in 2013 reached 30,000. The campaign forced major reforms to California\'s use of long-term isolation.'],
            ['month' => 8, 'day' => 8, 'year' => 1978,
                'title' => 'Philadelphia police siege of MOVE results in MOVE 9 arrests',
                'description' => 'Philadelphia police laid siege to the MOVE house at 309 N. 33rd Street, ending in a shootout that killed officer James Ramp. Nine MOVE members — Chuck, Debbie, Delbert, Eddie, Janet, Janine, Merle, Mike, and Phil Africa — were convicted of third-degree murder and sentenced to 30-100 years. Most served four decades; Merle and Phil died in custody.'],
            ['month' => 8, 'day' => 21, 'year' => 1971,
                'title' => 'George Jackson killed at San Quentin',
                'description' => 'Black Panther Party field marshal and Soledad Brother George Jackson was shot dead by guards in the prison yard at San Quentin during what officials called an escape attempt. Three guards and two white inmate informants were also killed. Jackson\'s books "Soledad Brother" and "Blood in My Eye" made him a defining figure in U.S. prison-organizing literature.'],
            ['month' => 9, 'day' => 9, 'year' => 1980,
                'title' => 'Plowshares Eight damage nuclear missile nose cones',
                'description' => 'Eight Catholic peace activists — including Daniel and Philip Berrigan — entered the General Electric nuclear missile plant in King of Prussia, Pennsylvania, hammered on two unarmed Mark 12A nuclear warhead nose cones, and poured blood on documents. They were convicted of burglary and conspiracy. The action launched the international Plowshares anti-nuclear movement.'],
            ['month' => 9, 'day' => 12, 'year' => 2017,
                'title' => 'Oscar López Rivera released after 36 years',
                'description' => 'Puerto Rican independentista Oscar López Rivera was released from home confinement, marking the final end of a 36-year sentence for "seditious conspiracy" — making him one of the longest-held political prisoners in U.S. history. President Obama commuted his sentence in January 2017.'],
            ['month' => 10, 'day' => 20, 'year' => 1981,
                'title' => 'Brink\'s armored car robbery in Nyack, NY',
                'description' => 'Members of the Black Liberation Army and the May 19th Communist Organization attempted to rob a Brink\'s armored car in Nyack, New York. Two police officers and a Brink\'s guard were killed in the ensuing shootouts. The arrests and prosecutions of David Gilbert, Judith Clark, Kuwasi Balagoon, Marilyn Buck, Mutulu Shakur, Sekou Odinga, and others produced some of the longest political-prisoner sentences in U.S. history.'],
            ['month' => 10, 'day' => 30, 'year' => 1950,
                'title' => 'Jayuya Uprising of Puerto Rican Nationalist Party',
                'description' => 'Puerto Rican Nationalist Party members under Blanca Canales seized the town of Jayuya, proclaimed the Republic of Puerto Rico, and burned the local police station, post office, and U.S. selective-service office. The uprising spread to Utuado, Arecibo, and elsewhere. The U.S. responded with aerial bombing and martial law; hundreds of independentistas were arrested.'],
            ['month' => 11, 'day' => 2, 'year' => 1979,
                'title' => 'Assata Shakur escapes from Clinton Correctional Facility',
                'description' => 'Black Liberation Army members and supporters break Assata Shakur out of the Clinton Correctional Facility for Women in Union Township, NJ, where she was serving a life sentence after her 1977 conviction. She remained underground for several years before surfacing in Cuba in 1984, where she has lived under political asylum ever since.'],
            ['month' => 12, 'day' => 7, 'year' => 2005,
                'title' => 'Operation Backfire arrests open the "Green Scare"',
                'description' => 'In a coordinated multi-state action, the FBI arrested six people accused of Earth Liberation Front and Animal Liberation Front actions stretching back to the late 1990s. The investigation, dubbed "Operation Backfire," used an informant who had been a movement participant. Many defendants — Daniel McGowan, Joyanna Zacher, Nathan Block, Stanislas Meyerhoff, Chelsea Gerlach, Kevin Tubbs — received sentences with "terrorism enhancements."'],
        ];
    }
}
