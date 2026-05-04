<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SeedPoliticalPrisonerEvents extends Command
{
    protected $signature = 'events:seed-political-prisoner';
    protected $description = 'Seed the Events page with past political-prisoner-related commemorations, anniversaries, and historical events.';

    public function handle(): int
    {
        // Each entry: [title, event_date, location, description, series]
        $events = [
            // ─── 2025 commemorations / anniversaries ────────────────
            [
                'Leonard Peltier Homecoming Celebration',
                '2025-02-18', 'Belcourt, North Dakota',
                "After 49 years in federal prison, Leonard Peltier was released to home confinement on February 18, 2025 by order of President Joe Biden's commutation. The American Indian Movement leader returned to the Turtle Mountain Indian Reservation in Belcourt, North Dakota where the Indigenous community welcomed him home with a public homecoming celebration. The longest-running political-prisoner-support campaign in U.S. history finally won its goal.",
                'Anniversaries & Releases',
            ],
            [
                'Mumia Abu-Jamal Birthday Solidarity Vigil',
                '2025-04-24', 'Philadelphia, Pennsylvania',
                "Each April 24, supporters of Mumia Abu-Jamal gather outside the Pennsylvania Department of Corrections offices in Philadelphia and at SCI Mahanoy to mark his birthday and renew the call for his release. Mumia turned 71 on April 24, 2025, marking 43 years and 4 months since his arrest on December 9, 1981.",
                'Anniversaries & Releases',
            ],
            [
                'MOVE Bombing 40th Anniversary Commemoration',
                '2025-05-13', 'Philadelphia, Pennsylvania',
                "On May 13, 1985, Philadelphia police dropped a bomb from a state police helicopter onto the home of the MOVE organization at 6221 Osage Avenue, killing 11 people including five children, and burning down 61 homes in the surrounding Black neighborhood. The 40th anniversary commemoration in 2025 brought together MOVE survivors, Ramona Africa, neighborhood residents, and supporters of the MOVE 9 (the nine MOVE members imprisoned after the 1978 Philadelphia police siege, all of whom have now been released; two died in prison).",
                'Anniversaries & Releases',
            ],
            [
                'Hiroshima Day Plowshares Vigil at the Pentagon',
                '2025-08-06', 'Arlington, Virginia',
                "Each year on August 6 — the anniversary of the U.S. atomic bombing of Hiroshima — Catholic Worker, Plowshares, and other anti-nuclear activists hold a vigil at the Pentagon and at U.S. nuclear weapons facilities. The 2025 vigil marked 80 years since the bombings of Hiroshima and Nagasaki and called attention to the dozens of U.S. anti-nuclear activists who have served federal prison sentences for symbolic disarmament actions over four decades.",
                'Anti-Nuclear & Plowshares',
            ],
            [
                'Black August Commemoration',
                '2025-08-21', 'Oakland, California',
                "Black August originated in the California prison system in 1979 to commemorate the August 21, 1971 killing of revolutionary George Jackson at San Quentin and the deaths of Jonathan Jackson, William Christmas, and James McClain in the August 7, 1970 Marin County courthouse incident. Each year, Black August is observed by political prisoners and their supporters with fasting, study, training, and remembrance of fallen comrades. The 2025 commemoration centered on the recently released — Mutulu Shakur, Russell Maroon Shoatz, Sundiata Acoli — and those still inside.",
                'Black August',
            ],
            [
                'Attica Uprising 54th Anniversary',
                '2025-09-09', 'Attica, New York',
                "On September 9, 1971, 1,281 prisoners at the Attica Correctional Facility in upstate New York seized control of the prison and held it for four days, demanding humane conditions and political recognition. On September 13, Governor Nelson Rockefeller ordered state troopers to retake the prison, killing 29 prisoners and 10 hostages — all of whom were killed by state-trooper fire. The 54th anniversary was observed at Attica with a memorial gathering of survivors, families of those killed, and prison-abolition organizers.",
                'Prison Uprisings',
            ],
            [
                'September 9 Prisoner Strike Solidarity Day',
                '2025-09-09', 'Multiple U.S. cities',
                "Coordinated by Jailhouse Lawyers Speak and other inside organizers, the annual September 9 Prisoner Strike Solidarity Day calls on outside supporters to phone-zap prisons, organize at gates, and amplify demands from prisoners on strike. The date was chosen to honor the 1971 Attica Uprising. Solidarity actions in 2025 took place in roughly 30 U.S. cities.",
                'Prison Uprisings',
            ],
            [
                'World Day Against the Death Penalty',
                '2025-10-10', 'International / virtual',
                "October 10 is the World Day Against the Death Penalty, established in 2003 by the World Coalition Against the Death Penalty. NPPC participated in the 2025 international day of action by highlighting the cases of U.S. political prisoners who have faced the death penalty — Mumia Abu-Jamal (death sentence overturned 2011), Sundiata Acoli's co-defendants, and the eight Plowshares Eight defendants who faced death-eligible federal counts in the early 1980s.",
                'Anniversaries & Releases',
            ],
            [
                'International Day of Solidarity with Political Prisoners',
                '2025-12-10', 'International / virtual',
                "December 10 is Human Rights Day, marking the 1948 adoption of the Universal Declaration of Human Rights. NPPC observes the date as the International Day of Solidarity with U.S. Political Prisoners, with letter-writing nights at bookstores and infoshops nationwide and a rolling read-aloud of every name in the database.",
                'Letter-Writing & Solidarity',
            ],
            [
                'New Year\'s Eve Noise Demo at Federal Prisons',
                '2025-12-31', 'Multiple U.S. federal facilities',
                "An anarchist tradition that began in the 1990s in Mexico and spread internationally, the New Year's Eve noise demonstration brings outside supporters to the gates of jails and prisons at midnight to make noise so that the people inside know they are not forgotten. NPPC supported actions in 2025 at FCI Danbury, USP Atlanta, ADX Florence, FCI Coleman, USP Allenwood, and roughly two dozen other facilities.",
                'Letter-Writing & Solidarity',
            ],

            // ─── 2024 / earlier ───────────────────────────────────
            [
                'Free Mumia Day of Action — 70th Birthday',
                '2024-04-24', 'Philadelphia, Pennsylvania',
                "Mumia Abu-Jamal turned 70 on April 24, 2024. Supporters in Philadelphia and at SCI Mahanoy held a day of action including a march from City Hall, a press conference at the Pennsylvania Department of Corrections, and a public reading of his prison writings.",
                'Anniversaries & Releases',
            ],
            [
                'Walk to Free Leonard Peltier (final cross-country march)',
                '2024-09-12', 'Pittsburgh, Pennsylvania → Washington, D.C.',
                "In September–October 2024, AIM organizers and supporters of Leonard Peltier walked from Pittsburgh to Washington, D.C. — roughly 250 miles — to deliver clemency petitions to the Biden White House. The walk gathered tribal nation endorsements at every stop. Biden issued the commutation that led to Peltier's home confinement four months later, on January 20, 2025.",
                'Anniversaries & Releases',
            ],
            [
                'Stop Cop City: National Day of Action',
                '2024-11-09', 'Atlanta, Georgia',
                "On the second anniversary of the December 14, 2022 launch of Defend the Atlanta Forest, organizers and forest defenders held a national day of action with rallies in over 50 U.S. cities and a march in Atlanta to the South River Forest. The day centered the cases of the 61 Stop Cop City RICO defendants and the memory of Manuel \"Tortuguita\" Terán, killed by Georgia state troopers on January 18, 2023.",
                'Anti-Repression',
            ],
            [
                'Anniversary of the killing of Tortuguita',
                '2024-01-18', 'Atlanta, Georgia',
                "Manuel Esteban Paez Terán — Tortuguita — was killed by Georgia state troopers on January 18, 2023 during a multi-agency raid on the Atlanta forest encampment opposing construction of the Atlanta Public Safety Training Center (\"Cop City\"). The 2024 first-anniversary commemoration brought hundreds to the South River Forest and to the spot where Tortuguita was killed, sitting in lotus position with hands raised when troopers shot 57 rounds into them.",
                'Anti-Repression',
            ],
            [
                'Plowshares Eight 44th Anniversary',
                '2024-09-09', 'King of Prussia, Pennsylvania',
                "On September 9, 1980, the Plowshares Eight — Daniel Berrigan, Philip Berrigan, Carl Kabat, Anne Montgomery, Molly Rush, Elmer Maas, John Schuchardt, and Dean Hammer — entered the General Electric Mark 12A nuclear missile-warhead plant in King of Prussia, Pennsylvania, hammered on Mark 12A nose cones, poured blood on documents, and prayed. The 44th-anniversary commemoration in 2024 brought together surviving Plowshares activists from four decades of subsequent actions.",
                'Anti-Nuclear & Plowshares',
            ],
            [
                'MOVE 9 Final Release Anniversary',
                '2024-02-25', 'Philadelphia, Pennsylvania',
                "Delbert Africa, the last living MOVE 9 prisoner still incarcerated, was released on parole in January 2020 after 41 years; he died eight months later. The 2024 commemoration on February 25 (the anniversary of the 1981 conviction at the conclusion of the trial) marked the full release of all surviving MOVE 9 members and remembered Phil Africa and Merle Africa, who died in prison.",
                'Anniversaries & Releases',
            ],
            [
                '50th Anniversary of the Pine Ridge Shootout',
                '2025-06-26', 'Pine Ridge Indian Reservation, South Dakota',
                "On June 26, 1975, two FBI agents and AIM activist Joe Stuntz were killed in a confrontation at Jumping Bull Compound on the Pine Ridge Indian Reservation. Leonard Peltier was eventually convicted of the agents' deaths in a prosecution that has been challenged for five decades. The 50th-anniversary commemoration in 2025, held at Oglala on the reservation, marked the conflict in the context of Peltier's release four months earlier.",
                'Anniversaries & Releases',
            ],
            [
                'Sacco-Vanzetti Memorial — 98th Anniversary',
                '2025-08-23', 'Boston, Massachusetts',
                "Italian anarchist immigrants Nicola Sacco and Bartolomeo Vanzetti were executed at the Charlestown State Prison on August 23, 1927 after a seven-year campaign that drew worldwide attention to the framing of two anarchist working men for a 1920 Massachusetts payroll robbery and double murder. The 98th-anniversary memorial in 2025 was held at the site of the former Charlestown prison and at the Sacco-Vanzetti memorial in the North End.",
                'Historical Commemorations',
            ],
            [
                'Joe Hill 110th Anniversary Memorial',
                '2025-11-19', 'Salt Lake City, Utah',
                "Swedish-American IWW union member and songwriter Joe Hill was executed by firing squad at the Utah State Penitentiary on November 19, 1915 for a murder he did not commit. The 110th-anniversary memorial in 2025 was held at the site of the former Utah State Penitentiary, with IWW members and labor historians from across the country reading from Hill's prison letters and singing his songs.",
                'Historical Commemorations',
            ],
            [
                'Haymarket Martyrs 138th Anniversary',
                '2025-11-11', 'Forest Park, Illinois',
                "On November 11, 1887, four Chicago anarchists — Albert Parsons, August Spies, Adolph Fischer, and George Engel — were hanged in the Cook County Jail for the May 4, 1886 Haymarket Square bombing. The annual commemoration at the Haymarket Martyrs Monument in Waldheim Cemetery (now Forest Home Cemetery, Forest Park) is one of the longest-running political-prisoner memorials in the world. The 138th-anniversary commemoration drew labor and anarchist organizers from across the U.S. and Europe.",
                'Historical Commemorations',
            ],
            [
                'Anti-Nuclear Plowshares Action Anniversary at Y-12',
                '2024-07-28', 'Oak Ridge, Tennessee',
                "On July 28, 2012 — 80-year-old Sister Megan Rice SHCJ, Vietnam veteran Catholic Worker Michael Walli, and former U.S. Army officer Greg Boertje-Obed cut through three perimeter fences and walked into the Y-12 National Security Complex in Oak Ridge, Tennessee. They reached the Highly Enriched Uranium Materials Facility — the storage site for the U.S. weapons-grade uranium stockpile — hung peace banners, splashed human blood, and waited for security. The Transform Now Plowshares 12th-anniversary commemoration in 2024 was held at the Y-12 perimeter fence.",
                'Anti-Nuclear & Plowshares',
            ],
            [
                'Pelican Bay Hunger Strike 12th Anniversary',
                '2025-07-08', 'Crescent City, California',
                "On July 8, 2013, roughly 30,000 California state prisoners began the third and largest of the Pelican Bay hunger strikes, demanding an end to long-term solitary confinement. The strike lasted 60 days and was the largest prison hunger strike in U.S. history. The 12th anniversary in 2025 was marked by the original Pelican Bay strike representatives — Sitawa Nantambu Jamaa, Todd Ashker, Arturo Castellanos, Antonio Guillen — and surviving family members of those who died.",
                'Prison Uprisings',
            ],
            [
                'Day of Solidarity with Palestinian Political Prisoners',
                '2025-04-17', 'Multiple U.S. cities',
                "April 17 is observed internationally as the Day of Solidarity with Palestinian Political Prisoners. The 2025 observance, falling at the height of the U.S. campus encampment crackdowns, drew attention to the convergence of U.S. political-prisoner cases (Khalil, Öztürk, Suri, Taal, Srinivasan, Lelo Juarez) with the broader Israeli detention regime documented by Addameer.",
                'Anti-Repression',
            ],
            [
                'May Day International Workers\' Day',
                '2025-05-01', 'Multiple U.S. cities',
                "May 1 — International Workers' Day — has its origin in the 1886 Haymarket events in Chicago and the U.S. eight-hour-day general strike of that year. NPPC participated in 2025 May Day rallies in roughly 40 U.S. cities, with a particular focus this year on the federal prosecutions of Stop Cop City defendants and the ongoing ICE detentions of farmworker organizers including Lelo (Alfredo Juarez) of Familias Unidas por la Justicia.",
                'Historical Commemorations',
            ],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($events as [$title, $date, $location, $description, $series]) {
            if (Event::where('title', $title)->exists()) {
                $this->line("Skipping (exists): {$title}");
                $skipped++;
                continue;
            }

            Event::create([
                'title'       => $title,
                'description' => $description,
                'event_date'  => Carbon::parse($date),
                'location'    => $location,
                'series'      => $series,
                'published'   => true,
            ]);

            $this->info("Added: {$title} ({$date})");
            $created++;
        }

        $this->info("\nDone. Created {$created}, skipped {$skipped}.");

        return self::SUCCESS;
    }
}
