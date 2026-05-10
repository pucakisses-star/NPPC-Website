<?php

declare(strict_types=1);

/**
 * 1930s gap-fill: 16 new prisoners across two cohorts.
 *
 *   A. Scottsboro Boys (9) — Charlie Weems, Clarence Norris, Ozie
 *      Powell, Olen Montgomery, Willie Roberson, Eugene Williams,
 *      Andy Wright, Roy Wright, Haywood Patterson. Nine Black
 *      teenagers falsely accused of raping two white women on the
 *      Southern Railway near Scottsboro, AL on March 25, 1931.
 *      Jailed at Scottsboro, then Gadsden, then Kilby Prison
 *      (Montgomery, AL). The defining African-American legal cause
 *      célèbre of the 1930s. Powell v. Alabama (1932) and Norris v.
 *      Alabama (1935) are foundational right-to-counsel and jury-
 *      composition cases.
 *
 *   B. Gastonia 7 — Loray Mill textile strike defendants convicted
 *      October 1929 in Charlotte, NC of conspiracy to murder
 *      Gastonia police chief O. F. Aderholt during the June 7, 1929
 *      raid on the National Textile Workers Union strikers' tent
 *      colony: Fred Beal, Clarence Miller, Joseph Harrison, K. Y.
 *      Hendricks, George Carter, Louis McLaughlin, Robert Allen.
 *      All except Beal jumped bail in early 1930 and fled to the
 *      Soviet Union; Beal returned to the U.S. and ultimately served
 *      ~4 years (1938-1942) at the North Carolina State Penitentiary.
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
        'era'         => '1930s',
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

// ============================================================
// A. Scottsboro Boys
// ============================================================
echo "\n--- A. Scottsboro Boys (March 25, 1931) ---\n";

$scottsboroNarrative = "On March 25, 1931 nine Black teenagers (ages 12 to 19) were pulled off a Southern Railway freight train at Paint Rock, Alabama and accused of raping two white women, Victoria Price and Ruby Bates, who had been riding in the same boxcar. Jailed at Scottsboro and Gadsden, AL, then transferred to Kilby Prison in Montgomery, the nine were tried in groups in Scottsboro, AL beginning April 6, 1931. Eight were convicted and sentenced to death; the case of 12-year-old Roy Wright ended in a hung jury. The Communist Party's International Labor Defense and (later) the NAACP took up their defense. The U.S. Supreme Court intervened twice — first in Powell v. Alabama (1932), holding that the right to counsel applies to capital defendants in state court, and again in Norris v. Alabama (1935), reversing the convictions on grounds of systematic exclusion of African Americans from Alabama jury rolls. Five (Charlie Weems, Clarence Norris, Andy Wright, Haywood Patterson, Ozie Powell) were re-convicted in further trials; four (Olen Montgomery, Willie Roberson, Eugene Williams, Roy Wright) had charges dropped on July 24, 1937. The case remains the defining African-American legal cause célèbre of the 1930s. In 2013 Alabama posthumously pardoned the three (Weems, A. Wright, Patterson) who had not previously been pardoned or had charges dropped.";

$kilby = $inst('Kilby Prison', 'Montgomery', 'Alabama');

$scottsboro = [
    [
        'name' => 'Charlie Weems',
        'birth' => '1911-01-01',
        'death' => '1986-01-01',
        'release' => '1943-11-17',
        'sentence' => 'Convicted at Scottsboro in 1931 (death); reconviction reversed in Norris v. Alabama 1935; tried again 1937 — 75 years. Paroled November 17, 1943.',
    ],
    [
        'name' => 'Clarence Norris',
        'birth' => '1912-07-12',
        'death' => '1989-01-23',
        'release' => '1946-09-26',
        'sentence' => 'Convicted at Scottsboro 1931 (death); his retrial conviction reversed in Norris v. Alabama, 294 U.S. 587 (1935); convicted a third time in 1937 (death); sentence commuted to life. Paroled September 1946. Pardoned by Alabama Governor George Wallace on October 25, 1976 — the only Scottsboro defendant pardoned in his lifetime. Wrote The Last of the Scottsboro Boys (1979).',
    ],
    [
        'name' => 'Ozie Powell',
        'birth' => '1916-01-01',
        'death' => '1974-01-01',
        'release' => '1946-06-15',
        'sentence' => 'Convicted at Scottsboro 1931 (death); his case produced Powell v. Alabama, 287 U.S. 45 (1932), the landmark right-to-counsel decision. Re-tried; on January 24, 1936 a sheriff\'s deputy shot him in the head during a transfer between Birmingham and Kilby, leaving him with permanent brain damage. Sentenced to 20 years on a separate assault charge. Paroled 1946.',
    ],
    [
        'name' => 'Olen Montgomery',
        'birth' => '1914-01-01',
        'death' => '1955-08-31',
        'release' => '1937-07-24',
        'sentence' => 'Convicted at Scottsboro 1931 (death); reversed by Norris v. Alabama 1935. On July 24, 1937 Alabama dropped all charges as part of a deal, and he was released after 6 years 4 months in custody. Largely blind from cataracts; died in obscurity 1955.',
        'institution_override' => 'Released by Alabama 1937',
    ],
    [
        'name' => 'Willie Roberson',
        'birth' => '1915-01-01',
        'death' => '1959-01-01',
        'release' => '1937-07-24',
        'sentence' => 'Convicted at Scottsboro 1931 (death); reversed 1935. Charges dropped July 24, 1937. Suffered from advanced syphilis at the time of his arrest, contradicting the prosecution\'s rape narrative. Died in obscurity ~1959.',
        'institution_override' => 'Released by Alabama 1937',
    ],
    [
        'name' => 'Eugene Williams',
        'birth' => '1918-01-01',
        'death' => null,
        'release' => '1937-07-24',
        'sentence' => 'Convicted at Scottsboro 1931 (death) at age 13. Reversed 1935. Charges dropped July 24, 1937 after 6 years 4 months in custody. Death date and later life unknown.',
        'institution_override' => 'Released by Alabama 1937',
    ],
    [
        'name' => 'Andy Wright',
        'birth' => '1912-01-01',
        'death' => '1969-01-01',
        'release' => '1944-01-08',
        'sentence' => 'Convicted at Scottsboro 1931 (death); reversed 1935; reconvicted 1937 — 99 years. Paroled January 1944; jumped parole; recaptured; finally released 1950. Roy Wright was his younger brother.',
    ],
    [
        'name' => 'Roy Wright',
        'birth' => '1919-01-01',
        'death' => '1959-08-13',
        'release' => '1937-07-24',
        'sentence' => 'Charged at Scottsboro 1931 at age 12; first trial ended in hung jury when jurors split between death penalty and life imprisonment for a child. Held until charges were dropped July 24, 1937 after 6 years 4 months in custody. Joined the Army; killed his wife and himself in 1959.',
        'institution_override' => 'Released by Alabama 1937',
    ],
    [
        'name' => 'Haywood Patterson',
        'birth' => '1913-01-01',
        'death' => '1952-08-24',
        'release' => '1948-07-17',
        'sentence' => 'Convicted at Scottsboro 1931 (death); reversed 1932 in Powell v. Alabama and again 1935 in Norris v. Alabama. Re-tried; 75 years. Escaped from Atmore Prison Farm on July 17, 1948 and fled to Detroit; refused extradition by Michigan Gov. G. Mennen Williams. Wrote Scottsboro Boy (1950) with Earl Conrad. Died of cancer in Michigan custody on a separate manslaughter charge, August 24, 1952.',
    ],
];

foreach ($scottsboro as $c) {
    $p = $createPrisoner($c['name'], [
        'description'  => $scottsboroNarrative,
        'state'        => 'Alabama',
        'race'         => 'Black',
        'gender'       => 'Male',
        'birthdate'    => $c['birth'] ?: null,
        'death_date'   => $c['death'] ?: null,
        'ideologies'   => ['Civil rights','Anti-racism'],
        'affiliation'  => ['International Labor Defense (ILD)','NAACP'],
    ]);
    if (! $p) continue;
    $caseInst = ! empty($c['institution_override'])
        ? $inst('Kilby Prison (released July 24, 1937)', 'Montgomery', 'Alabama')
        : $kilby;

    $ensureCase($p, [
        'institution_id'     => $caseInst->id,
        'charges'            => 'Alabama state rape (capital). The complainant Ruby Bates publicly recanted her testimony in 1933.',
        'arrest_date'        => '1931-03-25',
        'sentenced_date'     => '1931-04-09',
        'incarceration_date' => '1931-03-25',
        'release_date'       => $c['release'],
        'sentence'           => $c['sentence'],
        'convicted'          => 'Yes — Alabama state convictions (most reversed on appeal; charges later dropped or reaffirmed)',
    ]);
}

// ============================================================
// B. Gastonia 7 — Loray Mill (NTW textile strike, June 7, 1929)
// ============================================================
echo "\n--- B. Gastonia 7 (Loray Mill 1929 / convicted Oct 1929) ---\n";

$gastoniaNarrative = "Convicted in Charlotte, North Carolina on October 21, 1929 of conspiracy to murder Gastonia, NC police chief Orville F. Aderholt during the June 7, 1929 night raid on the National Textile Workers Union (a Communist Party-affiliated industrial union) strikers' tent colony at the Loray Mill, Gastonia. The 1929 Loray Mill strike was the most violent textile strike in U.S. history and involved the murder of organizer-balladeer Ella May Wiggins (shot September 14, 1929). After their first trial ended in a mistrial on September 9, 1929, prosecutors brought a second trial focused only on the seven principal NTW organizers — Fred Beal, Clarence Miller, Joseph Harrison, K. Y. Hendricks, George Carter, Louis McLaughlin, and Robert Allen — and won convictions. Sentences: Beal/Harrison/Miller 17-20 years each; Hendricks/Carter/McLaughlin/Allen 5-7 years each. While appeals were pending the seven jumped bail in early 1930 and (with Communist Party assistance) fled to the Soviet Union. Only Fred Beal returned to face his sentence: he surrendered in 1938 and served until parole in 1942.";

$ncpen = $inst('North Carolina State Penitentiary (Central Prison)', 'Raleigh', 'North Carolina');

$gastonia = [
    [
        'name' => 'Fred Beal', 'first' => 'Fred', 'last' => 'Beal', 'aka' => 'Fred Erwin Beal',
        'birth' => '1896-08-13', 'death' => '1954-01-01',
        'min' => 17, 'max' => 20,
        'release' => '1942-12-31',
        'sentence' => '17-20 years for conspiracy to murder. Jumped bail January 1930 and went to the USSR with the other six. Disillusioned with Stalinism, he returned to the U.S., surrendered to North Carolina authorities in 1938, and served ~4 years at the NC State Penitentiary in Raleigh before parole in 1942. Author of Proletarian Journey (1937) and The Red Fraud: An Exposé of Stalinism (1949).',
    ],
    [
        'name' => 'Clarence Miller', 'first' => 'Clarence', 'last' => 'Miller',
        'birth' => null, 'death' => null,
        'min' => 17, 'max' => 20,
        'release' => null,
        'sentence' => '17-20 years for conspiracy to murder. Jumped bail January 1930 with five other Gastonia 7 codefendants and fled to the Soviet Union. Never returned to serve the NC sentence. Last reported in Moscow in the 1930s.',
        'in_exile' => true,
    ],
    [
        'name' => 'Joseph Harrison', 'first' => 'Joseph', 'last' => 'Harrison',
        'birth' => null, 'death' => null,
        'min' => 17, 'max' => 20,
        'release' => null,
        'sentence' => '17-20 years for conspiracy to murder. Jumped bail January 1930 and fled to the USSR; never returned to serve the NC sentence.',
        'in_exile' => true,
    ],
    [
        'name' => 'K. Y. Hendricks', 'first' => 'K. Y.', 'last' => 'Hendricks',
        'birth' => null, 'death' => null,
        'min' => 5, 'max' => 7,
        'release' => null,
        'sentence' => '5-7 years for conspiracy to murder. Jumped bail January 1930 and fled to the USSR; never returned to serve the NC sentence.',
        'in_exile' => true,
    ],
    [
        'name' => 'George Carter', 'first' => 'George', 'last' => 'Carter',
        'birth' => null, 'death' => null,
        'min' => 5, 'max' => 7,
        'release' => null,
        'sentence' => '5-7 years for conspiracy to murder. Jumped bail January 1930 and fled to the USSR; never returned to serve the NC sentence.',
        'in_exile' => true,
    ],
    [
        'name' => 'Louis McLaughlin', 'first' => 'Louis', 'last' => 'McLaughlin',
        'birth' => null, 'death' => null,
        'min' => 5, 'max' => 7,
        'release' => null,
        'sentence' => '5-7 years for conspiracy to murder. Jumped bail January 1930 and fled to the USSR; never returned to serve the NC sentence.',
        'in_exile' => true,
    ],
    [
        'name' => 'Robert Allen', 'first' => 'Robert', 'last' => 'Allen',
        'birth' => null, 'death' => null,
        'min' => 5, 'max' => 7,
        'release' => null,
        'sentence' => '5-7 years for conspiracy to murder. Jumped bail January 1930 and fled to the USSR; never returned to serve the NC sentence.',
        'in_exile' => true,
    ],
];

foreach ($gastonia as $c) {
    $attrs = [
        'description' => $gastoniaNarrative,
        'state'       => 'North Carolina',
        'race'        => 'White',
        'gender'      => 'Male',
        'ideologies'  => ['Communism','Industrial unionism','Labor'],
        'affiliation' => ['National Textile Workers Union (NTW)','Communist Party USA (CPUSA)'],
    ];
    if (! empty($c['aka']))   $attrs['aka']        = $c['aka'];
    if (! empty($c['birth'])) $attrs['birthdate']  = $c['birth'];
    if (! empty($c['death'])) $attrs['death_date'] = $c['death'];
    if (! empty($c['in_exile'])) {
        $attrs['in_exile'] = true;
    }

    $p = $createPrisoner($c['name'], $attrs);
    if (! $p) continue;

    $caseAttrs = [
        'institution_id'     => $ncpen->id,
        'charges'            => 'North Carolina conspiracy to murder Gastonia police chief Orville F. Aderholt — June 7, 1929 raid on the NTW Loray Mill strikers\' tent colony.',
        'arrest_date'        => '1929-06-07',
        'sentenced_date'     => '1929-10-21',
        'sentence'           => $c['sentence'],
        'convicted'          => 'Yes — Mecklenburg County (Charlotte, NC) jury verdict, October 21, 1929',
    ];

    if ($c['name'] === 'Fred Beal') {
        $caseAttrs['incarceration_date'] = '1938-02-21';
        $caseAttrs['release_date']       = '1942-12-31';
    } elseif (! empty($c['in_exile'])) {
        // Bail-jumpers who fled to the USSR. Use exile-window fields
        // rather than incarceration_date (they were never actually
        // imprisoned on the NC sentence).
        $caseAttrs['in_exile_since']  = '1930-01-01';
        $caseAttrs['end_of_exile']    = null;
        $caseAttrs['incarceration_date'] = null;
        $caseAttrs['release_date']    = null;
    }

    $ensureCase($p, $caseAttrs);
}

echo "\nDone. updates={$updates}, creates={$creates}, unchanged={$unchanged}\n";
