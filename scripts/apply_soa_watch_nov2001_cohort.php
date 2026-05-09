<?php

declare(strict_types=1);

/**
 * Apply 25 entries from the November 2001 School of the Americas Watch
 * (SOA Watch) annual Fort Benning trespass cohort, sentenced and
 * incarcerated September 2002 - April 2003.
 *
 * For each row:
 *   - If the prisoner exists in the DB (matched by exact name, with a
 *     fallback for trivial spelling variants), update missing fields:
 *       * inmate_number (if currently null)
 *       * description (append SOA Watch context if not already present)
 *       * era (1990s -> 2000s if needed; "2000s" if blank)
 *     and on the prisoner's first case (or a newly-created one if they
 *     have none):
 *       * institution_id (if the user-supplied facility name differs)
 *       * incarceration_date / release_date (if currently null)
 *       * charges (if currently empty)
 *
 *   - If they don't exist, create the Prisoner + a single PrisonerCase.
 *
 * All 25 share the same federal charge: 18 U.S.C. § 1382, entering a
 * military reservation (Fort Benning, GA; U.S. Army Western Hemisphere
 * Institute for Security Cooperation / formerly the School of the
 * Americas). The action they were prosecuted for was the November 17-18,
 * 2001 "line crossing" mass civil disobedience.
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;
use Carbon\Carbon;

// name | inmate_number | start (Y-m-d) | end (Y-m-d) | institution name | institution street | institution city | institution state
$rows = [
    ['Charles Booker-Hirsch', '90961-020', '2002-09-10', '2002-12-10', 'FCI McKean',                              'P.O. Box 8000',                              'Bradford',     'Pennsylvania'],
    ['Joanna Cohen',          '90962-020', '2002-09-10', '2002-12-10', 'Federal Prison Camp Phoenix',             '37930 N 45th Ave',                           'Phoenix',      'Arizona'],
    ['Kenneth F. Crowley',    '90963-020', '2002-09-10', '2003-03-10', 'Federal Prison Camp Beaumont',            'PO Box 26010',                               'Beaumont',     'Texas'],
    ['Susan Daniels',         '90964-020', '2002-09-10', '2002-10-12', 'Federal Prison Camp Alderson',            'Box A',                                      'Alderson',     'West Virginia'],
    ['Nancy Gowen',           '90969-020', '2002-09-10', '2002-10-12', 'Federal Prison Camp Alderson',            'Box A',                                      'Alderson',     'West Virginia'],
    ['Abigail Miller',        '90692-020', '2002-09-10', '2002-12-10', 'Federal Prison Camp Alderson',            'Box A',                                      'Alderson',     'West Virginia'],
    ['Kathleen Boylan',       '20047-016', '2002-09-10', '2002-12-10', 'Federal Prison Camp Alderson',            'Box A',                                      'Alderson',     'West Virginia'],
    ['Mary Dean',             '90965-020', '2002-09-10', '2003-03-10', 'Federal Prison Camp Greenville',          'PO Box 6000',                                'Greenville',   'Illinois'],
    ['Kathleen Desautels',    '90966-020', '2002-09-10', '2003-03-10', 'Federal Prison Camp Greenville',          'PO Box 6000',                                'Greenville',   'Illinois'],
    ['Kate Fontanazza',       '90967-020', '2002-09-10', '2003-03-10', 'Federal Prison Camp Greenville',          'PO Box 6000',                                'Greenville',   'Illinois'],
    ['Toni Flynn',            '90960-020', '2002-07-12', '2003-01-01', 'Crisp County Jail',                       '196 South Highway 300',                      'Cordele',      'Georgia'],
    ['Jerry Zawada',          '4995-045',  '2002-07-12', '2003-01-12', 'Crisp County Jail',                       '196 South Highway 300',                      'Cordele',      'Georgia'],
    ['Chantilly Geigle',      '90968-020', '2002-09-10', '2003-03-10', 'Federal Prison Camp Dublin',              '5775 8th Street, Camp Paks',                 'Dublin',       'California'],
    ['Peter Gelderloos',      '90688-202', '2002-07-12', '2003-01-12', 'FCI Cumberland',                          '14601 Burbridge Road, SE',                   'Cumberland',   'Maryland'],
    ['John Heid',             '13815-016', '2002-09-10', '2003-04-10', 'Federal Prison Camp Schuylkill',          'Camp 2, Range B, PO Box 670',                'Minersville',  'Pennsylvania'],
    ['Eric Johnson',          '90971-020', '2002-09-10', '2003-03-10', 'FCI Manchester',                          'PO Box 3000',                                'Manchester',   'Kentucky'],
    ['Janice Sevre-Duszynska','91104-020', '2002-09-10', '2002-12-10', 'FMC Lexington',                           '3301 Leestown Road',                         'Lexington',    'Kentucky'],
    ['Niklan Jones-Lezama',   '0203593',   '2002-09-12', '2003-03-12', 'Sherburne County Jail',                   '13880 Highway 10 NW',                        'Elk River',    'Minnesota'],
    ['Rae Kramer',            '91069-020', '2002-09-10', '2003-03-10', 'FCI Danbury',                             'Route 37',                                   'Danbury',      'Connecticut'],
    ['Palmer Legare',         '91097-020', '2002-09-10', '2002-12-10', 'FMC Devens',                              'PO Box 879',                                 'Devens',       'Massachusetts'],
    ['Tom Mahedy',            '91098-020', '2002-09-10', '2002-12-10', 'FCI Fort Dix',                            'PO Box 38',                                  'Fort Dix',     'New Jersey'],
    ['Bill O\'Donell',        '85713-011', '2002-09-10', '2003-03-10', 'USP Atwater',                             'PO Box 019001',                              'Atwater',      'California'],
    ['Michaele Pasquale',     '91102-020', '2002-09-10', '2003-03-10', 'Federal Prison Camp Allenwood',           'PO Box 1000',                                'Montgomery',   'Pennsylvania'],
    ['Richard M. Ring',       '91099-020', '2002-09-10', '2002-12-10', 'Federal Prison Camp Lewisburg',           'PO Box 2000',                                'Lewisburg',    'Pennsylvania'],
    ['Michael Sobol',         '91105-020', '2002-09-10', '2002-12-10', 'FCI Englewood',                           '9595 W Quincy Ave',                          'Littleton',    'Colorado'],
    ['Louis Vitale',          '25803-048', '2002-10-02', '2003-01-02', 'Federal Bureau of Prisons (institution unspecified)', '',                                '',             ''],
];

$soaContext = "Convicted in U.S. District Court for the Middle District of Georgia under 18 U.S.C. § 1382 (entering a military reservation) for participating in the School of the Americas Watch (SOA Watch) annual nonviolent civil-disobedience action at Fort Benning, Georgia on November 17-18, 2001. The action targeted the U.S. Army Western Hemisphere Institute for Security Cooperation (WHINSEC) — formerly the School of the Americas — which had trained Latin American military officers later linked to assassinations, torture, and massacres including Romero, the four U.S. churchwomen, the Jesuits in El Salvador, El Mozote, and Plan Cóndor. Defendants typically received 3 to 6 months in federal custody plus a fine.";

$updates = 0; $creates = 0; $unchanged = 0;

// Build a name index for fuzzy "missing apostrophe / nn vs n" matches.
$allByExact = Prisoner::whereIn('name', array_column($rows, 0))->get()->keyBy('name');
$loose = function (string $name): ?Prisoner {
    $key = strtolower(preg_replace('/[^a-z0-9]/', '', strtolower($name)));
    foreach (Prisoner::query()->cursor() as $p) {
        if (strtolower(preg_replace('/[^a-z0-9]/', '', strtolower((string) $p->name))) === $key) {
            return $p;
        }
    }
    return null;
};

foreach ($rows as [$name, $reg, $start, $end, $instName, $instStreet, $instCity, $instState]) {
    $p = $allByExact->get($name) ?? $loose($name);

    $instMailing = trim($instStreet . ($instCity ? ', ' . $instCity : '') . ($instState ? ', ' . $instState : ''));
    $inst = Institution::firstOrCreate(
        ['name' => $instName],
        ['city' => $instCity ?: null, 'state' => $instState ?: null, 'mailing_address' => $instMailing ?: null]
    );

    if ($p) {
        $changed = false;

        if (empty($p->inmate_number)) {
            $p->inmate_number = $reg;
            $changed = true;
        }

        if (! $p->description || ! str_contains($p->description, 'School of the Americas')) {
            $p->description = trim((string) $p->description) . ($p->description ? ' ' : '') . $soaContext;
            $changed = true;
        }

        if (! $p->era) {
            $p->era = '2000s';
            $changed = true;
        }

        if ($changed) $p->save();

        // First case (or create one).
        $case = $p->cases()->orderBy('arrest_date')->first();
        if (! $case) {
            $case = new PrisonerCase(['prisoner_id' => $p->id]);
        }
        $caseChanged = false;
        if (empty($case->institution_id))     { $case->institution_id     = $inst->id;            $caseChanged = true; }
        if (empty($case->incarceration_date)) { $case->incarceration_date = $start;               $caseChanged = true; }
        if (empty($case->release_date))       { $case->release_date       = $end;                 $caseChanged = true; }
        if (empty($case->charges))            { $case->charges            = '18 U.S.C. § 1382 — entering a military reservation (Fort Benning, GA / WHINSEC).'; $caseChanged = true; }
        if (empty($case->sentence))           { $case->sentence           = "Federal misdemeanor: incarceration {$start} → release {$end}."; $caseChanged = true; }
        if (empty($case->convicted))          { $case->convicted          = 'Yes — § 1382 federal misdemeanor';                              $caseChanged = true; }

        if ($caseChanged) {
            if (! $case->prisoner_id) $case->prisoner_id = $p->id;
            $case->save();
        }

        if ($changed || $caseChanged) {
            echo "  [update] {$p->name}  reg={$reg}  inst={$instName}  {$start} → {$end}\n";
            $updates++;
        } else {
            echo "  [ok]     {$p->name}  (already complete)\n";
            $unchanged++;
        }
        continue;
    }

    // Brand-new entry.
    $tokens = preg_split('/\s+/', $name);
    $first  = $tokens[0] ?? '';
    $last   = count($tokens) > 1 ? implode(' ', array_slice($tokens, 1)) : '';

    $newP = Prisoner::create([
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
        'prisoner_id'        => $newP->id,
        'institution_id'     => $inst->id,
        'charges'            => '18 U.S.C. § 1382 — entering a military reservation (Fort Benning, GA / WHINSEC).',
        'arrest_date'        => '2001-11-18',
        'incarceration_date' => $start,
        'release_date'       => $end,
        'sentence'           => "Federal misdemeanor: incarceration {$start} → release {$end}.",
        'convicted'          => 'Yes — § 1382 federal misdemeanor',
    ]);

    echo "  [add]    {$name}  reg={$reg}  inst={$instName}  {$start} → {$end}\n";
    $creates++;
}

echo "\nDone. updates={$updates}, creates={$creates}, unchanged={$unchanged}\n";
