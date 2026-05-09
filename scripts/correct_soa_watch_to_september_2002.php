<?php

declare(strict_types=1);

/**
 * Correct the SOA Watch cohort previously applied as "November 2001"
 * (in scripts/apply_soa_watch_nov2001_cohort.php) to its actual
 * timing per the user: a September 2002 action at the School of the
 * Americas / WHINSEC at Fort Benning, GA.
 *
 * For each of the 25 prisoners in the user-supplied list:
 *   - Find by exact name; create if missing (Joanna Cohen, Nancy
 *     Gowen, Jerry Zawada, Eric Johnson, Tom Mahedy, Bill O'Donell
 *     are the cohort's six "new" entries).
 *   - Set inmate_number if blank.
 *   - Append the September 2002 framing to the description if not
 *     already present, and rewrite any "November 17-18, 2001"
 *     wording to "September 2002" so prior runs of the Nov-2001
 *     script are corrected in place.
 *   - On the case row matching this stint, force the institution,
 *     incarceration_date, release_date, charges, and sentence to the
 *     user-supplied values; if a prior case row used arrest_date
 *     "2001-11-18", update its arrest_date to the user's start date.
 *
 * This script is meant to be safe to run after, instead of, or
 * without the prior November 2001 script — it will produce the
 * correct end state in any of those cases. Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;

// name | reg | start | end | institution name | city | state
$rows = [
    ['Charles Booker-Hirsch', '90962-020', '2002-09-10', '2002-12-10', 'FCI McKean',                               'Bradford',     'Pennsylvania'],
    ['Joanna Cohen',          '90962-020', '2002-09-10', '2002-12-10', 'Federal Prison Camp Phoenix',              'Phoenix',      'Arizona'],
    ['Kenneth F. Crowley',    '90963-020', '2002-09-10', '2003-03-10', 'Federal Prison Camp Beaumont',             'Beaumont',     'Texas'],
    ['Susan Daniels',         '90964-020', '2002-09-10', '2002-10-12', 'Federal Prison Camp Alderson',             'Alderson',     'West Virginia'],
    ['Nancy Gowen',           '90969-020', '2002-09-10', '2002-10-12', 'Federal Prison Camp Alderson',             'Alderson',     'West Virginia'],
    ['Abigail Miller',        '90692-020', '2002-09-10', '2002-12-10', 'Federal Prison Camp Alderson',             'Alderson',     'West Virginia'],
    ['Kathleen Boylan',       '20047-016', '2002-09-10', '2002-12-10', 'Federal Prison Camp Alderson',             'Alderson',     'West Virginia'],
    ['Mary Dean',             '90965-020', '2002-09-10', '2003-03-10', 'Federal Prison Camp Greenville',           'Greenville',   'Illinois'],
    ['Kathleen Desautels',    '90966-020', '2002-09-10', '2003-03-10', 'Federal Prison Camp Greenville',           'Greenville',   'Illinois'],
    ['Kate Fontanazza',       '90967-020', '2002-09-10', '2003-03-10', 'Federal Prison Camp Greenville',           'Greenville',   'Illinois'],
    ['Toni Flynn',            '90960-020', '2002-07-12', '2003-01-01', 'Crisp County Jail',                        'Cordele',      'Georgia'],
    ['Jerry Zawada',          '4995-045',  '2002-07-12', '2003-01-12', 'Crisp County Jail',                        'Cordele',      'Georgia'],
    ['Chantilly Geigle',      '90968-020', '2002-09-10', '2003-03-10', 'Federal Prison Camp Dublin',               'Dublin',       'California'],
    ['Peter Gelderloos',      '90688-202', '2002-07-12', '2003-01-12', 'FCI Cumberland',                           'Cumberland',   'Maryland'],
    ['John Heid',             '13815-016', '2002-09-10', '2003-04-10', 'Federal Prison Camp Schuylkill',           'Minersville',  'Pennsylvania'],
    ['Eric Johnson',          '90971-020', '2002-09-10', '2003-03-10', 'FCI Manchester',                           'Manchester',   'Kentucky'],
    ['Janice Sevre-Duszynska','91104-020', '2002-09-10', '2002-12-10', 'FMC Lexington',                            'Lexington',    'Kentucky'],
    ['Niklan Jones-Lezama',   '0203593',   '2002-09-12', '2003-03-12', 'Sherburne County Jail',                    'Elk River',    'Minnesota'],
    ['Rae Kramer',            '91069-020', '2002-09-10', '2003-03-10', 'FCI Danbury',                              'Danbury',      'Connecticut'],
    ['Palmer Legare',         '91097-020', '2002-09-10', '2002-12-10', 'FMC Devens',                               'Devens',       'Massachusetts'],
    ['Tom Mahedy',            '91098-020', '2002-09-10', '2002-12-10', 'FCI Fort Dix',                             'Fort Dix',     'New Jersey'],
    ['Bill O\'Donell',        '85713-011', '2002-09-10', '2003-03-10', 'USP Atwater',                              'Atwater',      'California'],
    ['Michaele Pasquale',     '91102-020', '2002-09-10', '2003-03-10', 'Federal Prison Camp Allenwood',            'Montgomery',   'Pennsylvania'],
    ['Richard M. Ring',       '91099-020', '2002-09-10', '2002-12-10', 'Federal Prison Camp Lewisburg',            'Lewisburg',    'Pennsylvania'],
    ['Michael Sobol',         '91105-020', '2002-09-10', '2002-12-10', 'FCI Englewood',                            'Littleton',    'Colorado'],
    ['Louis Vitale',          '25803-048', '2002-10-02', '2003-01-02', 'Federal Bureau of Prisons (institution unspecified)', '', ''],
];

$soaContext = "Convicted in U.S. District Court for the Middle District of Georgia under 18 U.S.C. § 1382 (entering a military reservation) for participating in a September 2002 nonviolent civil-disobedience action at Fort Benning, Georgia. The action targeted the U.S. Army Western Hemisphere Institute for Security Cooperation (WHINSEC) — formerly the School of the Americas — which had trained Latin American military officers later linked to assassinations, torture, and massacres including Romero, the four U.S. churchwomen, the Jesuits in El Salvador, El Mozote, and Plan Cóndor. Defendants typically received 3 to 6 months in federal custody plus a fine.";

$updates = 0; $creates = 0; $unchanged = 0;

foreach ($rows as [$name, $reg, $start, $end, $instName, $instCity, $instState]) {
    $p = Prisoner::where('name', $name)->first();

    $inst = Institution::firstOrCreate(
        ['name' => $instName],
        ['city' => $instCity ?: null, 'state' => $instState ?: null]
    );

    if (! $p) {
        $tokens = preg_split('/\s+/', $name);
        $first  = $tokens[0] ?? '';
        $last   = count($tokens) > 1 ? implode(' ', array_slice($tokens, 1)) : '';

        $p = Prisoner::create([
            'name'          => $name,
            'first_name'    => $first,
            'last_name'     => $last,
            'inmate_number' => $reg,
            'description'   => $soaContext,
            'ideologies'    => ['Pacifism', 'Anti-militarism', 'Anti-imperialism', 'Catholic Worker'],
            'affiliation'   => ['School of the Americas Watch (SOA Watch)'],
            'era'           => '2000s',
            'in_custody'    => false,
            'released'      => true,
        ]);

        PrisonerCase::create([
            'prisoner_id'        => $p->id,
            'institution_id'     => $inst->id,
            'charges'            => '18 U.S.C. § 1382 — entering a military reservation (Fort Benning, GA / WHINSEC).',
            'arrest_date'        => $start,
            'incarceration_date' => $start,
            'release_date'       => $end,
            'sentence'           => "Federal misdemeanor: incarceration {$start} → release {$end} for September 2002 SOA Watch action.",
            'convicted'          => 'Yes — § 1382 federal misdemeanor',
        ]);

        echo "  [add]    {$name}  reg={$reg}  inst={$instName}  {$start} → {$end}\n";
        $creates++;
        continue;
    }

    // ---- Existing prisoner: correct any wrong data ----
    $changed = false;

    if (empty($p->inmate_number)) {
        $p->inmate_number = $reg;
        $changed = true;
    }

    $desc = (string) $p->description;
    // Normalize any "November 17-18, 2001" / "November 2001" wording from
    // the prior script to "September 2002".
    $newDesc = $desc;
    $newDesc = str_replace('November 17-18, 2001', 'September 2002', $newDesc);
    $newDesc = str_replace('November 2001',         'September 2002', $newDesc);
    if ($newDesc !== $desc) {
        $p->description = $newDesc;
        $changed = true;
    }
    if (! str_contains((string) $p->description, 'September 2002') &&
        ! str_contains((string) $p->description, 'School of the Americas')) {
        $p->description = trim((string) $p->description) . ($p->description ? ' ' : '') . $soaContext;
        $changed = true;
    }

    if (! $p->era) { $p->era = '2000s'; $changed = true; }

    if ($changed) $p->save();

    // ---- Find or build the case for this stint ----
    // Prefer a case whose incarceration_date matches; otherwise look
    // for one with the prior-script's arrest_date 2001-11-18; otherwise
    // create a new one.
    $case = $p->cases()->where('incarceration_date', $start)->first()
         ?? $p->cases()->where('arrest_date', '2001-11-18')->first()
         ?? new PrisonerCase(['prisoner_id' => $p->id]);

    $caseChanged = false;
    if ($case->institution_id     !== $inst->id) { $case->institution_id     = $inst->id; $caseChanged = true; }
    if ((string) $case->arrest_date        !== $start) { $case->arrest_date        = $start; $caseChanged = true; }
    if ((string) $case->incarceration_date !== $start) { $case->incarceration_date = $start; $caseChanged = true; }
    if ((string) $case->release_date       !== $end)   { $case->release_date       = $end;   $caseChanged = true; }
    if (empty($case->charges))   { $case->charges   = '18 U.S.C. § 1382 — entering a military reservation (Fort Benning, GA / WHINSEC); September 2002 SOA Watch action.'; $caseChanged = true; }
    if (empty($case->sentence))  { $case->sentence  = "Federal misdemeanor: incarceration {$start} → release {$end}.";                                                  $caseChanged = true; }
    if (empty($case->convicted)) { $case->convicted = 'Yes — § 1382 federal misdemeanor';                                                                                $caseChanged = true; }

    if ($caseChanged) {
        if (! $case->prisoner_id) $case->prisoner_id = $p->id;
        $case->save();
    }

    if ($changed || $caseChanged) {
        echo "  [update] {$p->name}  reg={$reg}  inst={$instName}  {$start} → {$end}\n";
        $updates++;
    } else {
        echo "  [ok]     {$p->name}  (already correct)\n";
        $unchanged++;
    }
}

echo "\nDone. updates={$updates}, creates={$creates}, unchanged={$unchanged}\n";
echo "Note: the action timing is now recorded as September 2002 per the user.\n";
