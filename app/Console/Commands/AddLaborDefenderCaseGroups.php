<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Batch 4 from the ILD's Labor Defender (1926-27): the documented CASE GROUPS —
 * defendants reported issue after issue with charges, sentences, and prisons.
 *
 *  - The Zeigler, Illinois miners (eight UMW men convicted March 1926 of
 *    assault with intent to murder in the "Zeigler frame-up," 1-14 years,
 *    Chester penitentiary / Pontiac reformatory, bailed pending appeal).
 *  - The Cliftonville miners (six named of ~49 convicted after the July 1922
 *    Cliftonville, W.Va. mine battle; Moundsville; most released October 1926).
 *  - The Passaic "bomb plot" strikers (textile strikers sentenced Feb-Mar 1927
 *    on a firecracker "bombing" frame-up; Trenton State Prison), plus
 *    17-year-old picket Maggie Pitocco.
 *  - Thomas Harty of the Maine seamen's case (Thomaston, with Dirks and Fallon
 *    already recorded).
 *  - The two 1927 Supreme Court criminal-syndicalism defendants: William Burns
 *    (Burns v. United States, upheld) and Harold Fiske (Fiske v. Kansas,
 *    reversed — the first state criminal-syndicalism conviction overturned).
 *
 * Spellings follow the source where no modern record exists. Idempotent:
 * prisoner:add refuses duplicates by name and the variant-name guard skips
 * anyone already recorded.
 */
final class AddLaborDefenderCaseGroups extends Command
{
    protected $signature = 'prisoners:add-labor-defender-case-groups';

    protected $description = 'Add 26 prisoners from Labor Defender case groups (Zeigler, Cliftonville, Passaic, Maine seamen, 1927 SCOTUS syndicalism cases)';

    public function handle(): int
    {
        $people = [];

        // --- Zeigler, Illinois miners (Franklin County) ---
        $zeigler = [
            ['Henry Corbishley', 'Henry', 'Corbishley', "Henry Corbishley, a militant United Mine Workers local leader in the Ku Klux Klan-ridden coal town of Zeigler, Illinois, was the central defendant of the \"Zeigler frame-up\": twenty progressive miners indicted after a 1925 union-hall melee provoked by Klansmen. Beaten and, by the defense's account, tortured in custody, he was one of eight convicted in March 1926 of assault with intent to murder and sentenced to one to fourteen years, bailed pending appeal after months inside. The ILD's Labor Defender carried the case on its covers through 1926."],
            ['Frank Corbishley', 'Frank', 'Corbishley', "Frank Corbishley, brother of Zeigler union leader Henry Corbishley, was first held by the grand jury on a murder charge growing out of the 1925 Zeigler mine-town violence — facing the gallows, as the ILD reported — and was then one of the eight miners convicted in March 1926 in the \"Zeigler frame-up\" of assault with intent to murder, sentenced to one to fourteen years at the Chester penitentiary and bailed pending appeal."],
            ['Stanley Paurez', 'Stanley', 'Paurez', null],
            ['Ignatz Simich', 'Ignatz', 'Simich', null],
            ['Martin Simich', 'Martin', 'Simich', "Martin Simich was one of the eight Zeigler, Illinois miners convicted in March 1926 in the \"Zeigler frame-up\" — his alibi witnesses ignored, as the ILD reported — and sentenced to one to fourteen years. His conviction was set aside and a new trial granted in mid-1926, and he was released on bail awaiting retrial."],
            ['Mike Karadich', 'Mike', 'Karadich', null],
            ['Eddie Maleski', 'Eddie', 'Maleski', "Eddie Maleski, not yet nineteen, was the youngest of the eight Zeigler, Illinois miners convicted in March 1926 in the \"Zeigler frame-up\" of assault with intent to murder; because of his age he was sent to the reformatory at Pontiac rather than the penitentiary, and was bailed pending appeal."],
            ['Steve Meanovich', 'Steve', 'Meanovich', null],
        ];
        foreach ($zeigler as [$name, $first, $last, $desc]) {
            $people[] = [
                'payload' => [
                    'name' => $name, 'first_name' => $first, 'last_name' => $last,
                    'description' => $desc ?? "{$name} was one of the eight miners convicted in March 1926 in the \"Zeigler frame-up\" — the prosecution of twenty progressive United Mine Workers members from the Klan-ridden coal town of Zeigler, Illinois, after a 1925 union-hall melee provoked by Klansmen. Convicted of assault with intent to murder and sentenced to one to fourteen years at the Chester penitentiary, he was bailed pending appeal after months inside. The case was a leading ILD defense campaign of 1926, and the spelling of the miners' names follows the ILD's Labor Defender.",
                    'state' => 'Illinois', 'gender' => 'Male',
                    'ideologies' => ['Industrial unionism'],
                    'affiliation' => ['United Mine Workers of America'],
                    'era' => '1920s', 'released' => true,
                    'cases' => [[
                        'charges' => 'Convicted of assault with intent to murder in the March 1926 "Zeigler frame-up" prosecution of twenty progressive UMW miners in Zeigler, Illinois.',
                        'convicted' => 'Convicted, March 1926; bailed pending appeal',
                        'sentence' => 'One to fourteen years; held at the Chester penitentiary (Pontiac reformatory for the youngest defendant) until bailed pending appeal.',
                        'institution_name' => 'Southern Illinois Penitentiary, Chester',
                        'institution_city' => 'Chester', 'institution_state' => 'Illinois',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1926, 3, null]],
            ];
        }
        // Maleski's institution differs
        $people[count($people) - 2]['payload']['cases'][0]['institution_name'] = 'Illinois State Reformatory, Pontiac';
        $people[count($people) - 2]['payload']['cases'][0]['institution_city'] = 'Pontiac';

        // --- Cliftonville, W.Va. miners ---
        $cliftonville = [
            ['John Kaminsky', 'John', 'Kaminsky', '10 years'],
            ['Joseph Tracz', 'Joseph', 'Tracz', '8 years'],
            ['Teddy Arunsky', 'Teddy', 'Arunsky', '10 years'],
            ['Pete Radocowich', 'Pete', 'Radocowich', '10 years'],
            ['Charles Cialia', 'Charles', 'Cialia', '10 years'],
            ['Frank Bodo', 'Frank', 'Bodo', '10 years'],
        ];
        foreach ($cliftonville as [$name, $first, $last, $term]) {
            $people[] = [
                'payload' => [
                    'name' => $name, 'first_name' => $first, 'last_name' => $last,
                    'description' => "{$name} was one of the union miners imprisoned at the West Virginia penitentiary in Moundsville after the July 1922 battle at the Cliftonville mine — a clash during the great coal strike in which striking union miners marched on a scab-run Panhandle mine and the county sheriff was killed. Around forty-nine men were convicted of conspiracy; {$name} was sentenced to {$term}. The ILD's Labor Defender profiled the Cliftonville prisoners in October 1926, shortly before most of the group were released. The spelling of the miners' names follows that source.",
                    'state' => 'West Virginia', 'gender' => 'Male',
                    'ideologies' => ['Industrial unionism'],
                    'affiliation' => ['United Mine Workers of America'],
                    'era' => '1920s', 'released' => true,
                    'cases' => [[
                        'charges' => 'Convicted of conspiracy after the July 1922 Cliftonville, W.Va. mine battle during the great coal strike.',
                        'convicted' => 'Convicted, 1922-23',
                        'sentence' => "{$term} at the West Virginia penitentiary, Moundsville; most of the Cliftonville group were released in October 1926.",
                        'institution_name' => 'West Virginia State Penitentiary',
                        'institution_city' => 'Moundsville', 'institution_state' => 'West Virginia',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1922, null, null], 'release_date' => [1926, 10, null]],
            ];
        }

        // --- Passaic "bomb plot" strikers ---
        $passaic = [
            ['Joseph Bellene', 'Joseph', 'Bellene', 'three years'],
            ['Tony Pochno', 'Tony', 'Pochno', 'three years'],
            ['Alex Kostamacha', 'Alex', 'Kostamacha', 'three years'],
            ['Paul Oznik', 'Paul', 'Oznik', 'three years'],
            ['William Sikora', 'William', 'Sikora', 'three years'],
            ['Adolf Wisnefsky', 'Adolf', 'Wisnefsky', 'five to twenty years'],
            ['Paul Kovac', 'Paul', 'Kovac', 'one to five years'],
            ['Nicholas Schillaci', 'Nicholas', 'Schillaci', 'one year'],
        ];
        foreach ($passaic as [$name, $first, $last, $term]) {
            $people[] = [
                'payload' => [
                    'name' => $name, 'first_name' => $first, 'last_name' => $last,
                    'description' => "{$name} was one of the 1926 Passaic, New Jersey textile strikers prosecuted in the \"bomb plot\" frame-up that followed the great strike of 15,000 mill workers — charges the defense showed rested on firecrackers and paid testimony, brought as the mills' answer to the year-long strike. Convicted in early 1927 and sentenced to {$term}, he was held in the New Jersey State Prison at Trenton and the Bergen County jail. The ILD's Labor Defender carried the defendants' cases and their families' relief through 1927.",
                    'state' => 'New Jersey', 'gender' => 'Male',
                    'ideologies' => ['Labor organizing'],
                    'affiliation' => ['United Front Committee of Textile Workers'],
                    'era' => '1920s', 'released' => true,
                    'cases' => [[
                        'charges' => 'Convicted in the Passaic strike "bomb plot" frame-up of 1926-27, the prosecution that followed the great textile strike.',
                        'convicted' => 'Convicted, early 1927',
                        'sentence' => "{$term}; held at the New Jersey State Prison, Trenton, and the Bergen County jail.",
                        'institution_name' => 'New Jersey State Prison',
                        'institution_city' => 'Trenton', 'institution_state' => 'New Jersey',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1927, null, null]],
            ];
        }
        $people[] = [
            'payload' => [
                'name' => 'Maggie Pitocco',
                'first_name' => 'Maggie', 'last_name' => 'Pitocco',
                'description' => "Maggie Pitocco, a seventeen-year-old picket in the 1926 Passaic textile strike, was sentenced to six months on a disorderly conduct charge for her picket-line activity — one of the young women strikers whose jailings the ILD's Labor Defender reported in 1927. Her case went to appeal.",
                'state' => 'New Jersey', 'gender' => 'Female',
                'ideologies' => ['Labor organizing'],
                'era' => '1920s', 'released' => true,
                'cases' => [[
                    'charges' => 'Sentenced on a disorderly conduct charge for picketing in the 1926 Passaic textile strike, aged seventeen.',
                    'convicted' => 'Convicted, 1927; appealed',
                    'sentence' => 'Six months; case taken to appeal (Labor Defender, May 1927).',
                ]],
            ],
            'dates' => ['incarceration_date' => [1927, null, null]],
        ];

        // --- Maine seamen's case ---
        $people[] = [
            'payload' => [
                'name' => 'Thomas Harty',
                'first_name' => 'Thomas', 'last_name' => 'Harty',
                'description' => "Thomas Harty was one of the marine transport workers imprisoned at the Maine State Prison in Thomaston in the seamen's case of the early 1920s — the same prosecution that held Peter Dirks and Dan Fallon, already recorded in this database. His sentence was raised to seven to eleven years, and his letters appeared in the ILD's Labor Defender through 1927, which listed him on its roster of American class-war prisoners.",
                'state' => 'Maine', 'gender' => 'Male',
                'ideologies' => ['Industrial unionism'],
                'affiliation' => ['Marine Transport Workers (IWW)'],
                'era' => '1920s', 'released' => true,
                'cases' => [[
                    'charges' => "Imprisoned in the Maine seamen's case of the early 1920s, with Peter Dirks and Dan Fallon.",
                    'convicted' => 'Convicted (details per Labor Defender, 1926-27)',
                    'sentence' => 'Sentence raised to seven to eleven years; held at the Maine State Prison, Thomaston.',
                    'institution_name' => 'Maine State Prison',
                    'institution_city' => 'Thomaston', 'institution_state' => 'Maine',
                ]],
            ],
            'dates' => [],
        ];

        // --- 1927 Supreme Court criminal-syndicalism cases ---
        $people[] = [
            'payload' => [
                'name' => 'William Burns',
                'first_name' => 'William', 'last_name' => 'Burns',
                'description' => "William Burns, an IWW organizer, was convicted under the California Criminal Syndicalism Act as applied in Yosemite National Park (federal territory) and sentenced to one to fourteen years. His appeal, Burns v. United States (1927), was decided by the Supreme Court alongside Whitney v. California, and his conviction was upheld — the companion case to Anita Whitney's in the era's defining free-speech losses.",
                'state' => 'California', 'gender' => 'Male',
                'ideologies' => ['Industrial unionism'],
                'affiliation' => ['Industrial Workers of the World'],
                'era' => '1920s', 'released' => true,
                'cases' => [[
                    'charges' => 'Convicted under the California Criminal Syndicalism Act (as federal law in Yosemite National Park) for IWW organizing.',
                    'convicted' => 'Convicted; upheld in Burns v. United States (1927)',
                    'sentence' => 'One to fourteen years.',
                ]],
            ],
            'dates' => [],
        ];
        $people[] = [
            'payload' => [
                'name' => 'Harold Fiske',
                'first_name' => 'Harold', 'last_name' => 'Fiske',
                'description' => "Harold Fiske, a young IWW organizer recruiting farm workers in Kansas, was arrested in 1923 and convicted under the Kansas criminal-syndicalism act on no evidence beyond the preamble of the IWW constitution. In Fiske v. Kansas (1927) a unanimous Supreme Court reversed his conviction — the first time the Court set aside a state criminal-syndicalism conviction, and an early landmark in applying the Fourteenth Amendment to protect speech.",
                'state' => 'Kansas', 'gender' => 'Male',
                'ideologies' => ['Industrial unionism'],
                'affiliation' => ['Industrial Workers of the World'],
                'era' => '1920s', 'released' => true,
                'cases' => [[
                    'charges' => 'Arrested in 1923 and convicted under the Kansas criminal-syndicalism act for recruiting for the IWW.',
                    'convicted' => 'Convicted, 1923; unanimously REVERSED in Fiske v. Kansas (1927)',
                    'sentence' => 'One to ten years, vacated when the Supreme Court set the conviction aside.',
                ]],
            ],
            'dates' => ['arrest_date' => [1923, null, null]],
        ];

        $added = 0;
        foreach ($people as $person) {
            $payload = $person['payload'];
            $payload['in_custody'] = false;
            if (! array_key_exists('released', $payload)) {
                $payload['released'] = true;
            }

            $existing = Prisoner::withoutGlobalScopes()
                ->where('name', 'like', '%'.$payload['first_name'].'%')
                ->where('name', 'like', '%'.$payload['last_name'].'%')
                ->first();
            if ($existing) {
                $this->line("  already in database as \"{$existing->name}\" — skipping {$payload['name']}.");

                continue;
            }

            $this->call('prisoner:add', ['json' => json_encode($payload)]);

            $prisoner = Prisoner::withoutGlobalScopes()->where('name', $payload['name'])->first();
            if (! $prisoner) {
                continue;
            }
            $prisoner->in_custody = false;
            $prisoner->released = $payload['released'];
            $prisoner->save();

            $case = $prisoner->cases()->first();
            if ($case && ! empty($person['dates'])) {
                foreach ($person['dates'] as $field => [$y, $m, $d]) {
                    $case->setPartialDate($field, $y, $m, $d);
                }
                $case->save();
            }
            $added++;
        }

        $this->info("\nDone. Processed {$added} case-group prisoner(s).");

        return self::SUCCESS;
    }
}
