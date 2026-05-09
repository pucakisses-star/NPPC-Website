<?php

declare(strict_types=1);

/**
 * Apply two overlapping 2000-2001 cohorts:
 *
 *   A) Prince of Peace Plowshares / Plowshares vs Depleted Uranium
 *      (Berrigan, Crane, Kelly): probation-violation stints starting
 *      Feb 2, 2001 connected to the Feb 14, 1997 PoP Plowshares
 *      direct-disarmament action and the Dec 19, 1999 "Plowshares vs
 *      Depleted Uranium" action against A-10 anti-tank warplanes.
 *
 *   B) November 2000 SOA Watch line-crossing (~21 defendants):
 *      Federal misdemeanor 18 U.S.C. § 1382 trespass at Fort Benning,
 *      GA / WHINSEC. Sentences typically 6 months federal, mostly
 *      served July 2001 - January/February 2002.
 *
 *   Plus three standalone 2001 actions named in the user's list:
 *      - Alberto de Jesús ("Tito Kayak") — Empire State Building,
 *        June 21, 2001; 1-year sentence at MCC New York.
 *      - Scott Kenji Warren — Pentagon blood-marking, April 2001;
 *        July 2001 - January 2002 at Northern Neck Regional Jail.
 *        (Distinct from the No More Deaths "Scott Warren" already in
 *        the DB — different person.)
 *      - Scott Galindez — Vandenberg AFB Star Wars protest, May 2001;
 *        October 2001 - January 2002 at MDC Los Angeles.
 *
 * For each row, the script updates existing prisoners (filling blank
 * fields) and adds missing ones with full Prisoner + PrisonerCase.
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;

$soaContext = "Convicted in U.S. District Court for the Middle District of Georgia under 18 U.S.C. § 1382 (entering a military reservation) for participating in the School of the Americas Watch (SOA Watch) annual nonviolent civil-disobedience action at Fort Benning, Georgia in November 2000. The action targeted the U.S. Army Western Hemisphere Institute for Security Cooperation (WHINSEC) — formerly the School of the Americas — which had trained Latin American military officers later linked to assassinations, torture, and massacres. Defendants typically received 3 to 6 months in federal custody.";

$plowsharesContext = "Imprisoned February 2, 2001 for probation violation tied to the 'Plowshares vs Depleted Uranium' direct-disarmament action of December 19, 1999, in which Catholic Worker / Plowshares activists hammered on A-10 anti-tank warplanes (which fire depleted-uranium rounds) at the Maryland Air National Guard's Warfield ANG Base. The probation itself stemmed from the Prince of Peace Plowshares action of February 14, 1997, when six activists hammered and poured blood on a Tomahawk cruise-missile launching system aboard the USS The Sullivans at Bath Iron Works in Maine.";

// SOA Nov-2000 cohort: name | reg | start | end | inst name | city | state
$soaRows = [
    ['David Corcoran (or Corcoren)',  'David', 'Corcoran', 'Corcoren', '90282-020', '2001-07-17', '2002-01-17', 'Federal Prison Camp Oxford',     'Oxford',      'Wisconsin'],
    ['John Alfred Hunt, Jr.',          'John',  'Hunt',     'John Alfred Hunt Jr', '90277-020', '2001-07-17', '2002-01-17', 'FCI Beckley',                'Beaver',      'West Virginia'],
    ['Steve Jacobs',                   'Steve', 'Jacobs',   '',         '88110-020', '2001-07-17', '2002-07-17', 'Federal Prison Camp Leavenworth','Leavenworth', 'Kansas'],
    ['Richard John Kinane',            'Richard','Kinane',  '',         '90279-020', '2001-07-17', '2002-01-17', 'FCI Englewood',                'Littleton',   'Colorado'],
    ['Mary Lou Benson',                'Mary Lou','Benson', '',         '90281-020', '2001-07-17', '2002-01-17', 'Federal Prison Camp Pekin',    'Pekin',       'Illinois'],
    ['Rachel Louise Hayward',          'Rachel','Hayward',  '',         '90286-020', '2001-07-17', '2002-01-17', 'Federal Prison Camp Pekin',    'Pekin',       'Illinois'],
    ['Gwen Hennessey',                 'Gwen',  'Hennessey','Gwen Henessey','90288-020', '2001-07-17', '2002-01-17', 'Federal Prison Camp Pekin', 'Pekin',       'Illinois'],
    ['Rebecca Kanner',                 'Rebecca','Kanner',  '',         '90278-020', '2001-07-17', '2002-01-17', 'Federal Prison Camp Pekin',    'Pekin',       'Illinois'],
    ['Elizabeth Anne McKenzie',        'Elizabeth','McKenzie','',       '90291-020', '2001-07-17', '2002-01-17', 'Federal Prison Camp Pekin',    'Pekin',       'Illinois'],
    ['Miriam Spencer',                 'Miriam','Spencer',  '',         '90294-020', '2001-08-10', '2002-02-10', 'Federal Prison Camp Pekin',    'Pekin',       'Illinois'],
    ['Mary Alice Vaughan',             'Mary Alice','Vaughan','Mary Alice Shemo','90296-020', '2001-07-17', '2002-01-17', 'Federal Prison Camp Pekin','Pekin','Illinois'],
    ['Dorothy M. Hennessey',           'Dorothy','Hennessey','Dorothee M. Hennessey','90287-020', '2001-07-17', '2002-01-17', '',                'Dubuque',     'Iowa'],
    ['Lois Putzier',                   'Lois',  'Putzier',  '',         '90292-020', '2001-07-17', '2002-01-17', 'Federal Prison Camp Phoenix',  'Phoenix',     'Arizona'],
    ['John Ewers',                     'John',  'Ewers',    '',         '90284-020', '2001-07-17', '2002-01-17', 'Federal Prison Camp Ashland',  'Ashland',     'Kentucky'],
    ['William Houston, Jr.',           'William','Houston Jr.','',      '90289-020', '2001-07-17', '2002-01-17', 'Federal Prison Camp Ashland',  'Ashland',     'Kentucky'],
    ['Clare Marie Hanrahan',           'Clare', 'Hanrahan', 'Claire Marie Hanrahan','90285-020','2001-07-17','2002-01-17','Federal Prison Camp Alderson','Alderson','West Virginia'],
    ['Eric Robison',                   'Eric',  'Robison',  '',         '90293-020', '2001-07-30', '2002-01-30', 'Federal Prison Camp Sheridan', 'Sheridan',    'Oregon'],
    ['Josh Raisler Cohn',              'Josh',  'Raisler Cohn','',      '90274-020', '2001-05-23', '2001-11-23', 'Federal Prison Camp Sheridan', 'Sheridan',    'Oregon'],
];

$updates = 0; $creates = 0; $unchanged = 0;

foreach ($soaRows as $row) {
    [$displayName, $first, $last, $aka, $reg, $start, $end, $instName, $instCity, $instState] = $row;

    // Try exact match, then any AKA variant (e.g. "Dorothee M. Hennessey" vs "Dorothy M. Hennessey").
    $candidates = array_filter(array_unique([$displayName, $aka]));
    $p = null;
    foreach ($candidates as $cand) {
        $hit = Prisoner::where('name', $cand)->first();
        if ($hit) { $p = $hit; break; }
    }

    $inst = null;
    if ($instName !== '') {
        $inst = Institution::firstOrCreate(
            ['name' => $instName],
            ['city' => $instCity ?: null, 'state' => $instState ?: null]
        );
    }

    if ($p) {
        $changed = false;
        if (empty($p->inmate_number)) { $p->inmate_number = $reg; $changed = true; }
        if (! $p->description || ! str_contains($p->description, 'School of the Americas')) {
            $p->description = trim((string) $p->description) . ($p->description ? ' ' : '') . $soaContext;
            $changed = true;
        }
        if (! $p->era) { $p->era = '2000s'; $changed = true; }
        if ($aka && $p->name !== $aka) {
            $existingAka = (string) ($p->aka ?? '');
            if (! str_contains(mb_strtolower($existingAka), mb_strtolower($aka))) {
                $p->aka = trim($existingAka . ($existingAka ? ', ' : '') . $aka);
                $changed = true;
            }
        }
        if ($changed) $p->save();

        $case = $p->cases()->orderBy('arrest_date')->first() ?? new PrisonerCase(['prisoner_id' => $p->id]);
        $caseChanged = false;
        if ($inst && empty($case->institution_id))    { $case->institution_id     = $inst->id; $caseChanged = true; }
        if (empty($case->incarceration_date))         { $case->incarceration_date = $start;    $caseChanged = true; }
        if (empty($case->release_date))               { $case->release_date       = $end;      $caseChanged = true; }
        if (empty($case->charges))                    { $case->charges            = '18 U.S.C. § 1382 — entering a military reservation (Fort Benning, GA / WHINSEC).'; $caseChanged = true; }
        if (empty($case->sentence))                   { $case->sentence           = "Federal misdemeanor: incarceration {$start} → release {$end}."; $caseChanged = true; }
        if (empty($case->convicted))                  { $case->convicted          = 'Yes — § 1382 federal misdemeanor'; $caseChanged = true; }
        if ($caseChanged) {
            if (! $case->prisoner_id) $case->prisoner_id = $p->id;
            $case->save();
        }

        echo ($changed || $caseChanged ? '  [update] ' : '  [ok]     ') . "{$p->name}  reg={$reg}\n";
        ($changed || $caseChanged) ? $updates++ : $unchanged++;
        continue;
    }

    // New prisoner.
    $newP = Prisoner::create([
        'name'          => $displayName,
        'first_name'    => $first,
        'last_name'     => $last,
        'aka'           => $aka ?: null,
        'inmate_number' => $reg,
        'description'   => $soaContext,
        'ideologies'    => ['Pacifism', 'Anti-militarism', 'Anti-imperialism', 'Catholic Worker'],
        'affiliation'   => ['School of the Americas Watch (SOA Watch)'],
        'era'           => '2000s',
        'in_custody'    => false,
        'released'      => true,
    ]);
    PrisonerCase::create([
        'prisoner_id'        => $newP->id,
        'institution_id'     => $inst?->id,
        'charges'            => '18 U.S.C. § 1382 — entering a military reservation (Fort Benning, GA / WHINSEC).',
        'arrest_date'        => '2000-11-19',
        'incarceration_date' => $start,
        'release_date'       => $end,
        'sentence'           => "Federal misdemeanor: incarceration {$start} → release {$end}.",
        'convicted'          => 'Yes — § 1382 federal misdemeanor',
    ]);
    echo "  [add]    {$displayName}  reg={$reg}\n";
    $creates++;
}

// ---- Plowshares vs DU stints (Berrigan, Crane, Kelly) ----

$plowsharesRows = [
    ['Philip Berrigan',         '14850-056', '2001-02-02', '2002-02-01', '',                              '',           ''],
    ['Susan Crane',              '87783-011', '2001-02-02', '2002-02-01', 'FCI Dublin',                     'Dublin',     'California'],
    ['Stephen Michael Kelly',    '292-140',   '1999-12-19', '2002-03-18', 'Roxbury Correctional Institution','Hagerstown', 'Maryland'],
];
foreach ($plowsharesRows as [$name, $reg, $start, $end, $instName, $instCity, $instState]) {
    $p = Prisoner::where('name', $name)->first();
    if (! $p) { echo "  [warn]   {$name} — not found, skipping Plowshares update\n"; continue; }

    $changed = false;
    // Existing inmate_number is from a different stint; don't overwrite.
    if (empty($p->inmate_number)) { $p->inmate_number = $reg; $changed = true; }
    if (! $p->description || ! str_contains($p->description, 'Plowshares vs Depleted Uranium')) {
        $p->description = trim((string) $p->description) . ($p->description ? ' ' : '') . $plowsharesContext;
        $changed = true;
    }
    if ($changed) $p->save();

    if ($instName) {
        $inst = Institution::firstOrCreate(['name' => $instName], ['city' => $instCity, 'state' => $instState]);
    } else {
        $inst = null;
    }

    // Add a SECOND case row for this Plowshares-vs-DU stint (the
    // existing case probably tracks a different action). Idempotent
    // by inmate_number on the case-level matching here is messy,
    // so we just check whether any case for this prisoner already
    // covers the start date.
    $exists = $p->cases()->where('incarceration_date', $start)->exists();
    if (! $exists) {
        PrisonerCase::create([
            'prisoner_id'        => $p->id,
            'institution_id'     => $inst?->id,
            'charges'            => 'Probation violation (originating action: "Plowshares vs Depleted Uranium" December 19, 1999 hammering of A-10 anti-tank warplanes; Prince of Peace Plowshares February 14, 1997 disarmament of USS The Sullivans Tomahawk launcher).',
            'arrest_date'        => $start,
            'incarceration_date' => $start,
            'release_date'       => $end,
            'sentence'           => "Federal probation-violation incarceration: {$start} → {$end}.",
            'convicted'          => 'Yes — probation violation',
        ]);
        echo "  [update] {$p->name} — added Plowshares-vs-DU case ({$start} → {$end})\n";
        $updates++;
    } else {
        echo "  [ok]     {$p->name} — Plowshares-vs-DU case already present\n";
        $unchanged++;
    }
}

// ---- Standalone additions ----

// Alberto de Jesús (Tito Kayak) — Empire State Building action 2001.
// If the Vieques script has already been run he'll exist; this just
// adds a NEW case row for the Empire State Building action.
$tito = Prisoner::where('name', 'Alberto de Jesús')->first();
if ($tito) {
    $mcc = Institution::firstOrCreate(['name' => 'MCC New York'], ['city' => 'New York', 'state' => 'New York']);
    $exists = $tito->cases()->where('incarceration_date', '2001-06-21')->exists();
    if (! $exists) {
        PrisonerCase::create([
            'prisoner_id'        => $tito->id,
            'institution_id'     => $mcc->id,
            'charges'            => 'New York criminal trespass and related charges. Climbed the Empire State Building on June 21, 2001 and placed a Vieques + Puerto Rican flag and a "No more bombing" sign on the crown of the building. At the time of the action he was on probation for earlier Vieques civil-disobedience cases.',
            'arrest_date'        => '2001-06-21',
            'incarceration_date' => '2001-06-21',
            'release_date'       => '2002-06-21',
            'sentence'           => '1 year at MCC New York for the Empire State Building action.',
            'convicted'          => 'Yes',
        ]);
        echo "  [update] Alberto de Jesús — added Empire State Building (June 21, 2001) case\n";
        $updates++;
    } else {
        echo "  [ok]     Alberto de Jesús — Empire State Building case already present\n";
        $unchanged++;
    }
} else {
    echo "  [warn]   Alberto de Jesús not yet in DB. Run scripts/add_nuke_resister_vieques_prisoners.php first, then re-run this script to attach the Empire State Building case.\n";
}

// Scott Kenji Warren — Pentagon blood-marking April 2001 (NOT the
// No More Deaths Arizona Scott Warren). Create as a separate prisoner.
if (! Prisoner::where('name', 'Scott Kenji Warren')->exists()) {
    $jail = Institution::firstOrCreate(['name' => 'Northern Neck Regional Jail'], ['city' => 'Warsaw', 'state' => 'Virginia']);
    $p = Prisoner::create([
        'name'          => 'Scott Kenji Warren',
        'first_name'    => 'Scott',
        'middle_name'   => 'Kenji',
        'last_name'     => 'Warren',
        'inmate_number' => 'A0101453',
        'description'   => "Scott Kenji Warren participated in an April 2001 nonviolent direct action against the Pentagon, marking the building with blood as protest. He was charged with throwing an object at a building. Listed in the Nuclear Resister Inside & Out roster. (Distinct from Scott Warren the No More Deaths / Arizona migrant-aid activist who is a different person.)",
        'ideologies'    => ['Pacifism', 'Anti-militarism', 'Catholic Worker'],
        'affiliation'   => null,
        'era'           => '2000s',
        'in_custody'    => false,
        'released'      => true,
    ]);
    PrisonerCase::create([
        'prisoner_id'        => $p->id,
        'institution_id'     => $jail->id,
        'charges'            => 'Throwing an object at a building (Pentagon, April 2001 — marked with blood as anti-war protest).',
        'arrest_date'        => '2001-04-01',
        'incarceration_date' => '2001-07-20',
        'release_date'       => '2002-01-20',
        'sentence'           => 'Approximately 6 months at Northern Neck Regional Jail (incarceration July 20, 2001 - January 20, 2002).',
        'convicted'          => 'Yes',
    ]);
    echo "  [add]    Scott Kenji Warren  reg=A0101453  (Pentagon blood action)\n";
    $creates++;
} else {
    echo "  [ok]     Scott Kenji Warren — already in DB\n";
    $unchanged++;
}

// Scott Galindez — Vandenberg AFB Star Wars protest May 2001.
if (! Prisoner::where('name', 'Scott Galindez')->exists()) {
    $mdc = Institution::firstOrCreate(['name' => 'Metropolitan Detention Center, Los Angeles'], ['city' => 'Los Angeles', 'state' => 'California']);
    $p = Prisoner::create([
        'name'          => 'Scott Galindez',
        'first_name'    => 'Scott',
        'last_name'     => 'Galindez',
        'inmate_number' => '87333-012',
        'description'   => "Scott Galindez was convicted of trespass at Vandenberg Air Force Base for backcountry occupation of the base in May 2001 to protest U.S. 'Star Wars' (National Missile Defense) ballistic-missile-defense tests. He served approximately 3 months at MDC Los Angeles.",
        'ideologies'    => ['Pacifism', 'Anti-militarism', 'Anti-nuclear'],
        'affiliation'   => null,
        'era'           => '2000s',
        'in_custody'    => false,
        'released'      => true,
    ]);
    PrisonerCase::create([
        'prisoner_id'        => $p->id,
        'institution_id'     => $mdc->id,
        'charges'            => 'Federal trespass — backcountry occupation of Vandenberg AFB to protest "Star Wars" missile-defense tests, May 2001.',
        'arrest_date'        => '2001-05-01',
        'incarceration_date' => '2001-10-29',
        'release_date'       => '2002-01-28',
        'sentence'           => '~3 months at MDC Los Angeles (October 29, 2001 - January 28, 2002).',
        'convicted'          => 'Yes',
    ]);
    echo "  [add]    Scott Galindez  reg=87333-012  (Vandenberg AFB Star Wars protest)\n";
    $creates++;
} else {
    echo "  [ok]     Scott Galindez — already in DB\n";
    $unchanged++;
}

echo "\nDone. updates={$updates}, creates={$creates}, unchanged={$unchanged}\n";
