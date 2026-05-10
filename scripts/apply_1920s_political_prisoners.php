<?php

declare(strict_types=1);

/**
 * 1920s gap-fill: 10 new political prisoners.
 *
 *   A. John Scopes (1925 Tennessee anti-evolution trial)
 *   B. Wesley Everest (lynched November 11, 1919, Centralia, WA — IWW
 *      veteran taken from the city jail by a mob and hanged)
 *   C. Centralia 8 — eight IWW members convicted in March 1920 for
 *      the deaths of four American Legionnaires during the November 11,
 *      1919 Armistice Day attack on the Centralia, WA IWW hall:
 *        Eugene Barnett, Britt Smith, John Lamb, Oliver Charles "O.C."
 *        Bland, James Bertie "Bert" Bland, James McInerney, Ray Becker
 *        (all 2nd-degree murder, 25-40 years at Walla Walla), and
 *        Loren Roberts (same verdict but found insane; committed to
 *        Western State Hospital, Steilacoom, then transferred to
 *        Walla Walla in 1925).
 *
 * The big-name 1920s figures (Marcus Garvey, Anita Whitney, Benjamin
 * Gitlow, Charles Ruthenberg) are already in the DB with thorough
 * descriptions of their 1920s cases, so no update needed for them.
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;

$updates = 0; $creates = 0; $unchanged = 0;

$inst = function (string $name, ?string $city = null, ?string $state = null): Institution {
    return Institution::firstOrCreate(['name' => $name], ['city' => $city, 'state' => $state]);
};

$createPrisoner = function (string $name, array $attrs) use (&$creates): ?Prisoner {
    if (Prisoner::where('name', $name)->exists()) {
        echo "  [skip]   {$name} — already exists\n";
        return Prisoner::where('name', $name)->first();
    }
    $tokens = preg_split('/\s+/', $name);
    $first  = $tokens[0] ?? '';
    $last   = count($tokens) > 1 ? implode(' ', array_slice($tokens, 1)) : '';
    $defaults = [
        'name'        => $name,
        'first_name'  => $first,
        'last_name'   => $last,
        'era'         => '1920s',
        'in_custody'  => false,
        'released'    => true,
    ];
    $p = Prisoner::create(array_merge($defaults, $attrs));
    echo "  [add]    {$name}\n";
    $creates++;
    return $p;
};

$ensureCase = function (Prisoner $p, array $attrs) use (&$updates, &$unchanged): void {
    $key = $attrs['arrest_date'] ?? null;
    if ($key && $p->cases()->where('arrest_date', $key)->exists()) {
        echo "  [ok]     {$p->name} — case already present for {$key}\n";
        $unchanged++;
        return;
    }
    PrisonerCase::create(array_merge(['prisoner_id' => $p->id], $attrs));
    echo "  [update] {$p->name} — added case ({$key})\n";
    $updates++;
};

// =====================================================
// A. John Scopes
// =====================================================
echo "\n--- A. John Scopes (1925 Tennessee) ---\n";

$scopes = $createPrisoner('John Scopes', [
    'description' => "John Thomas Scopes (1900-1970) was a 24-year-old high-school teacher and football coach in Dayton, Tennessee when he was indicted on May 25, 1925 for violating the state's Butler Act, which banned the teaching of human evolution in public schools. The trial — orchestrated by the ACLU as a test case and known as the \"Scopes Monkey Trial\" — was held July 10-21, 1925 in Dayton. Clarence Darrow led the defense; William Jennings Bryan led the prosecution and famously took the stand to defend a literal reading of Genesis. Scopes was convicted and fined \$100 by the trial judge. The Tennessee Supreme Court reversed in 1927 on the technical ground that the fine should have been imposed by the jury, leaving the Butler Act on the books (until its 1967 repeal) but vacating Scopes's conviction.",
    'birthdate'   => '1900-08-03',
    'death_date'  => '1970-10-21',
    'race'        => 'White',
    'gender'      => 'Male',
    'state'       => 'Tennessee',
    'ideologies'  => ['Free Exercise','Civil libertarian','Science education'],
    'affiliation' => ['American Civil Liberties Union (ACLU) — test-case defendant'],
]);
if ($scopes) {
    $i = $inst('Rhea County Courthouse / no incarceration', 'Dayton', 'Tennessee');
    $ensureCase($scopes, [
        'institution_id'     => $i->id,
        'charges'            => 'Tennessee Butler Act of 1925 — teaching the theory of evolution in a public school. Test case organized by the ACLU.',
        'arrest_date'        => '1925-05-25',
        'sentenced_date'     => '1925-07-21',
        'incarceration_date' => null,
        'release_date'       => null,
        'sentence'           => "\$100 fine imposed by Judge John T. Raulston. Conviction reversed by the Tennessee Supreme Court on January 17, 1927 on a procedural ground (the fine should have been imposed by the jury, not the judge). No incarceration.",
        'convicted'          => 'Yes — Rhea County jury verdict (vacated on appeal 1927)',
    ]);
}

// =====================================================
// B. Wesley Everest — lynched in custody, Nov 11, 1919
// =====================================================
echo "\n--- B. Wesley Everest (lynched Nov 11, 1919) ---\n";

$everest = $createPrisoner('Wesley Everest', [
    'description' => "Wesley Everest (1890-1919) was an IWW Wobbly, lumber worker, and World War I infantry veteran. On Armistice Day, November 11, 1919, American Legion marchers attacked the Centralia, Washington IWW union hall. Everest was inside; in the resulting gunfight four Legionnaires were killed. Everest fled, was captured, and was held in the Centralia city jail. That night a mob took him from the jail, castrated him in the back of a car en route to the Chehalis River, and hanged him from the Mellen Street railroad bridge. The mob then fired on the body. The official coroner's verdict was that Everest \"jumped from the bridge with a rope around his neck\" — no one was ever prosecuted for his murder. Everest's body was buried by IWW prisoners (the Centralia 8) in an unmarked grave.",
    'birthdate'   => '1890-04-05',
    'death_date'  => '1919-11-11',
    'race'        => 'White',
    'gender'      => 'Male',
    'state'       => 'Washington',
    'ideologies'  => ['Anarchism','Industrial unionism','Anti-militarism'],
    'affiliation' => ['Industrial Workers of the World (IWW)'],
    'era'         => '1910s', // action and death are 1910s; Centralia 8 cohort is 1920s
]);
if ($everest) {
    $i = $inst('Centralia City Jail (lynched in custody)', 'Centralia', 'Washington');
    $ensureCase($everest, [
        'institution_id'        => $i->id,
        'charges'               => 'Held in the Centralia city jail after the November 11, 1919 IWW hall gunfight; lynched the same night by a mob who took him from the jail.',
        'arrest_date'           => '1919-11-11',
        'incarceration_date'    => '1919-11-11',
        'death_in_custody_date' => '1919-11-11',
        'release_date'          => '1919-11-11',
        'sentence'              => 'Murdered by mob action while in police custody; no prosecution of the perpetrators. Coroner ruled "jumped from a bridge with a rope around his neck."',
        'convicted'             => 'No — never tried; lynched in custody',
    ]);
}

// =====================================================
// C. Centralia 8 — convicted March 13, 1920
// =====================================================
echo "\n--- C. Centralia 8 (March 13, 1920 verdicts) ---\n";

$centraliaContext = "One of the eight IWW defendants tried at Montesano (Grays Harbor County), WA from January 26 to March 13, 1920 for the deaths of four American Legionnaires (Warren Grimm, Arthur McElfresh, Ben Casagranda, Dale Hubbard) during the Armistice Day, November 11, 1919 attack on the Centralia, WA IWW union hall. Convicted of 2nd-degree murder despite the IWW's defense that they were defending the union hall from an armed mob. Sentenced to 25-40 years at the Washington State Penitentiary at Walla Walla. The case became a long-running cause célèbre of the IWW and the labor left, with the Centralia Publicity Committee and later the Free Ray Becker Committee organizing for parole and pardon over two decades.";

$walla = $inst('Washington State Penitentiary', 'Walla Walla', 'Washington');

$centralia = [
    [
        'name' => 'Eugene Barnett', 'first' => 'Eugene', 'last' => 'Barnett',
        'birth' => '1888-12-01', 'death' => '1948-02-02',
        'release' => '1931-06-01',
        'sentence' => "25-40 years 2nd-degree murder; paroled June 1931 after 11 years 3 months at Walla Walla.",
    ],
    [
        'name' => 'Britt Smith', 'first' => 'Britt', 'last' => 'Smith',
        'birth' => null, 'death' => null,
        'release' => '1933-05-01',
        'sentence' => "25-40 years 2nd-degree murder; paroled May 1933 after 13 years at Walla Walla. Smith was the secretary of the Centralia IWW local at the time of the November 1919 attack.",
    ],
    [
        'name' => 'John Lamb', 'first' => 'John', 'last' => 'Lamb',
        'birth' => null, 'death' => '1922-06-06',
        'release' => '1922-06-06',
        'sentence' => "25-40 years 2nd-degree murder; died of tuberculosis at Walla Walla on June 6, 1922 — about 2 years 3 months into his sentence.",
        'death_in_custody' => '1922-06-06',
    ],
    [
        'name' => 'Oliver Charles Bland', 'first' => 'Oliver Charles', 'last' => 'Bland', 'aka' => 'O. C. Bland',
        'birth' => '1882-01-01', 'death' => '1936-01-01',
        'release' => '1933-05-01',
        'sentence' => "25-40 years 2nd-degree murder; paroled May 1933 with his brother Bert Bland. Died ~1936.",
    ],
    [
        'name' => 'James Bertie Bland', 'first' => 'James Bertie', 'last' => 'Bland', 'aka' => 'Bert Bland',
        'birth' => null, 'death' => '1953-01-01',
        'release' => '1933-05-01',
        'sentence' => "25-40 years 2nd-degree murder; paroled May 1933 with his brother O.C. Bland. Died ~1953.",
    ],
    [
        'name' => 'James McInerney', 'first' => 'James', 'last' => 'McInerney',
        'birth' => null, 'death' => '1930-10-19',
        'release' => '1930-10-19',
        'sentence' => "25-40 years 2nd-degree murder; died of tuberculosis at Walla Walla on October 19, 1930 — about 10 years 7 months into his sentence.",
        'death_in_custody' => '1930-10-19',
    ],
    [
        'name' => 'Ray Becker', 'first' => 'Ray', 'last' => 'Becker',
        'birth' => '1894-01-01', 'death' => '1950-01-01',
        'release' => '1939-09-22',
        'sentence' => "25-40 years 2nd-degree murder; refused to seek parole, demanding a pardon. Held longer than any other Centralia defendant. Released September 22, 1939 after 19 years 6 months — over the strenuous objection of his lawyers and the Free Ray Becker Committee, who wanted full vindication. Died ~1950.",
    ],
    [
        'name' => 'Loren Roberts', 'first' => 'Loren', 'last' => 'Roberts',
        'birth' => null, 'death' => null,
        'release' => '1930-01-01',
        'sentence' => "25-40 years 2nd-degree murder, with the jury attaching a finding of insanity. Committed to Western State Hospital, Steilacoom, WA in 1920; declared sane and transferred to the Washington State Penitentiary at Walla Walla in 1925. Paroled around 1930.",
        'institution_override' => 'Western State Hospital (then Walla Walla)',
        'institution_city' => 'Steilacoom',
    ],
];

foreach ($centralia as $c) {
    $attrs = [
        'description' => $centraliaContext,
        'state'       => 'Washington',
        'race'        => 'White',
        'gender'      => 'Male',
        'ideologies'  => ['Industrial unionism','Anarchism'],
        'affiliation' => ['Industrial Workers of the World (IWW)'],
        'era'         => '1920s',
    ];
    if (! empty($c['aka']))   $attrs['aka']        = $c['aka'];
    if (! empty($c['birth'])) $attrs['birthdate']  = $c['birth'];
    if (! empty($c['death'])) $attrs['death_date'] = $c['death'];

    $p = $createPrisoner($c['name'], $attrs);
    if (! $p) continue;

    $caseInst = $walla;
    if (! empty($c['institution_override'])) {
        $caseInst = $inst($c['institution_override'], $c['institution_city'] ?? 'Walla Walla', 'Washington');
    }

    $caseAttrs = [
        'institution_id'     => $caseInst->id,
        'charges'            => '2nd-degree murder of four American Legionnaires (Warren Grimm, Arthur McElfresh, Ben Casagranda, Dale Hubbard) during the November 11, 1919 Armistice Day attack on the Centralia, Washington IWW union hall. Tried in Grays Harbor County Superior Court at Montesano, WA, January 26 - March 13, 1920.',
        'arrest_date'        => '1919-11-11',
        'sentenced_date'     => '1920-03-13',
        'incarceration_date' => '1920-04-05',
        'release_date'       => $c['release'],
        'sentence'           => $c['sentence'],
        'convicted'          => 'Yes — Grays Harbor County jury verdict, March 13, 1920',
    ];
    if (! empty($c['death_in_custody'])) {
        $caseAttrs['death_in_custody_date'] = $c['death_in_custody'];
    }
    $ensureCase($p, $caseAttrs);
}

echo "\nDone. updates={$updates}, creates={$creates}, unchanged={$unchanged}\n";
