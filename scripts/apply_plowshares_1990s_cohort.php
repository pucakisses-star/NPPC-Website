<?php

declare(strict_types=1);

/**
 * Apply three 1990s anti-nuclear/Plowshares actions to the database:
 *
 *   A) Carl Kabat — 1994 hammering of a Minuteman III missile silo in
 *      North Dakota; held at FCC Florence Camp (Teller Unit), April 1,
 *      1994 - August 1998. The court added 18 months for parole
 *      violation tied to a 1984 action.
 *
 *   B) Prince of Peace Plowshares — 6 defendants who hammered and
 *      poured blood on the Tomahawk cruise-missile launching system
 *      aboard USS The Sullivans at Bath Iron Works, Maine on
 *      February 14, 1997. Convicted of conspiracy and damage to
 *      government property ($28,000 alleged damage); sentences:
 *        Susan Crane          27 months
 *        Philip Berrigan      24 months
 *        Steve Kelly S.J.     21 months
 *        Mark Colville        13 months
 *        Steve Baggarly       13 months
 *        Tom Lewis-Borbely     8 months
 *      All 6 also got 2 years supervised release and a $4,000
 *      restitution fine each.
 *
 *   C) Donna and Tom Howard-Hastings — April 21, 1996 "cleansing of
 *      Project ELF" action: cut three transmission-line poles at the
 *      U.S. Navy's Project ELF (Extremely Low Frequency) submarine
 *      antenna at Republic, Michigan / Clam Lake, Wisconsin. Sentenced
 *      to 3 years; served 11 and 7 months respectively before
 *      transitioning to electronically-monitored house arrest.
 *
 * Each existing prisoner gets the new case row only if they don't
 * already have one matching the same arrest date. New prisoners are
 * created with a single PrisonerCase. Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;

$updates = 0; $creates = 0; $unchanged = 0;

// ---- A. Carl Kabat (Minuteman III silo, 1994) ----

$kabat = Prisoner::where('name', 'Rev. Carl Kabat')->first()
       ?? Prisoner::where('name', 'Carl Kabat')->first();

if ($kabat) {
    $kabatNote = "On April 1, 1994 Kabat hammered on a Minuteman III nuclear missile silo in North Dakota — a continuation of the Plowshares disarmament tradition first set by the 1980 Plowshares Eight action. He was held at the federal prison camp at FCC Florence (Teller Unit) until August 1998. The sentencing court added 18 months to his term after determining that the 1994 ND action constituted a parole violation of his prior 1984 Plowshares action sentence.";

    if (! str_contains((string) $kabat->description, 'Minuteman III')) {
        $kabat->description = trim((string) $kabat->description) . ' ' . $kabatNote;
        if (empty($kabat->inmate_number)) $kabat->inmate_number = '03230-045';
        $kabat->save();
        echo "  [update] {$kabat->name} — appended 1994 Minuteman III silo facts\n";
        $updates++;
    }

    $florence = Institution::firstOrCreate(
        ['name' => 'FCC Florence Camp (Teller Unit)'],
        ['city' => 'Florence', 'state' => 'Colorado', 'mailing_address' => 'PO Box 5000, Florence, CO 81226-5000']
    );
    if (! $kabat->cases()->where('incarceration_date', '1994-04-01')->exists()) {
        PrisonerCase::create([
            'prisoner_id'        => $kabat->id,
            'institution_id'     => $florence->id,
            'charges'            => 'Federal sabotage / damage to government property at a Minuteman III nuclear missile silo, North Dakota (April 1, 1994 Plowshares disarmament action). Court additionally found that the action violated the parole conditions of a 1984 Plowshares-era sentence and added 18 months on top of the new sentence.',
            'arrest_date'        => '1994-04-01',
            'incarceration_date' => '1994-04-01',
            'release_date'       => '1998-08-31',
            'sentence'           => 'Approximately 4 years and 4 months at FCC Florence Camp (Teller Unit) — original North Dakota silo sentence plus 18 months added for parole violation of his 1984 action conditions.',
            'convicted'          => 'Yes — federal Plowshares conviction',
        ]);
        echo "  [update] {$kabat->name} — added Minuteman III silo case\n";
        $updates++;
    }
} else {
    echo "  [warn]   Carl Kabat not found; skipping A.\n";
}

// ---- B. Prince of Peace Plowshares (Feb 14, 1997) ----

$popNarrative = "The Prince of Peace Plowshares was a six-person nonviolent direct-disarmament action at Bath Iron Works in Bath, Maine on February 14, 1997. Philip Berrigan, Susan Crane, Stephen Kelly S.J., Mark Colville, Steve Baggarly, and Tom Lewis-Borbely entered the shipyard and hammered on, and poured blood on, the Tomahawk cruise-missile vertical launching system installed on the destroyer USS The Sullivans (DDG-68) under construction. They were charged in U.S. District Court for the District of Maine with conspiracy and damage to government property, with prosecutors alleging \$28,000 in damage. All six were convicted; in addition to prison, each received two years of supervised release and a \$4,000 restitution fine.";

$popDefendants = [
    ['Philip Berrigan',          24, 'federal'],
    ['Susan Crane',              27, 'federal'],
    ['Stephen Michael Kelly',    21, 'federal'],   // canonical DB name
    ['Mark Colville',            13, 'federal'],
    ['Steve Baggarly',           13, 'federal'],
    ['Tom Lewis',                 8, 'federal'],   // canonical DB name (AKA Tom Lewis-Borbely)
];

$popInst = Institution::firstOrCreate(
    ['name' => 'Federal Bureau of Prisons (Prince of Peace Plowshares cohort)'],
    ['city' => '', 'state' => 'Maine']
);

foreach ($popDefendants as [$dbName, $months, $type]) {
    $p = Prisoner::where('name', $dbName)->first();
    if (! $p) {
        echo "  [warn]   {$dbName} not found; skipping PoP add\n";
        continue;
    }

    $changed = false;
    if (! str_contains((string) $p->description, 'Prince of Peace Plowshares')) {
        $p->description = trim((string) $p->description) . ' ' . $popNarrative;
        $changed = true;
    }
    if ($p->name === 'Tom Lewis') {
        $existingAka = (string) ($p->aka ?? '');
        if (! str_contains(mb_strtolower($existingAka), 'tom lewis-borbely')) {
            $p->aka = trim($existingAka . ($existingAka ? ', ' : '') . 'Tom Lewis-Borbely');
            $changed = true;
        }
    }
    if ($changed) $p->save();

    if ($p->cases()->where('arrest_date', '1997-02-14')->exists()) {
        echo "  [ok]     {$p->name} — PoP-Plowshares case already present\n";
        $unchanged++;
        continue;
    }

    $sentenceText = "{$months} months federal prison for the Prince of Peace Plowshares action; plus 2 years supervised release and \$4,000 restitution fine.";
    PrisonerCase::create([
        'prisoner_id'        => $p->id,
        'institution_id'     => $popInst->id,
        'charges'            => 'Conspiracy and damage to government property (~$28,000 alleged) for the February 14, 1997 Prince of Peace Plowshares disarmament of the Tomahawk cruise-missile vertical launching system aboard USS The Sullivans at Bath Iron Works, Maine.',
        'arrest_date'        => '1997-02-14',
        'incarceration_date' => '1997-02-14',
        'release_date'       => \Carbon\Carbon::parse('1997-02-14')->addMonths($months)->format('Y-m-d'),
        'sentence'           => $sentenceText,
        'convicted'          => 'Yes — federal jury verdict (D. Maine)',
    ]);

    echo "  [update] {$p->name} — added Prince of Peace Plowshares case ({$months} mo)\n";
    $updates++;
}

// ---- C. Donna and Tom Howard-Hastings (Project ELF antenna, Apr 21, 1996) ----

$elfNarrative = "On April 21, 1996 Donna and Tom Howard-Hastings cut down three transmission-line poles at the U.S. Navy's Project ELF (Extremely Low Frequency) submarine-communication antenna in northern Wisconsin (Clam Lake) and the Upper Peninsula of Michigan (Republic). Project ELF was used to send one-way 'bell ringer' signals to ballistic-missile submarines on patrol. The action was part of the long-running anti-Project-ELF campaign led by Nukewatch and Catholic Worker affiliates. The Howard-Hastings were each sentenced to 3 years federal prison; Donna served 11 months and Tom served 7 months before transitioning to electronically-monitored house arrest at their home at 12833 E St., H. 13, Maple, WI 54854.";

$elfRows = [
    ['Donna Howard-Hastings', 'Donna', 'Howard-Hastings', 11],
    ['Tom Howard-Hastings',   'Tom',   'Howard-Hastings',  7],
];

foreach ($elfRows as [$name, $first, $last, $monthsServed]) {
    if (Prisoner::where('name', $name)->exists()) {
        echo "  [ok]     {$name} — already in DB\n";
        $unchanged++;
        continue;
    }

    $p = Prisoner::create([
        'name'        => $name,
        'first_name'  => $first,
        'last_name'   => $last,
        'description' => $elfNarrative,
        'state'       => 'Wisconsin',
        'gender'      => $first === 'Donna' ? 'Female' : 'Male',
        'race'        => 'White',
        'ideologies'  => ['Pacifism', 'Anti-nuclear', 'Catholic Worker', 'Plowshares'],
        'affiliation' => ['Nukewatch'],
        'era'         => '1990s',
        'in_custody'  => false,
        'released'    => true,
    ]);

    $inst = Institution::firstOrCreate(
        ['name' => 'Federal Bureau of Prisons (Project ELF cohort)'],
        ['city' => '', 'state' => 'Wisconsin']
    );

    PrisonerCase::create([
        'prisoner_id'        => $p->id,
        'institution_id'     => $inst->id,
        'charges'            => "Federal sabotage / damage to government property — cut three transmission-line poles at the U.S. Navy's Project ELF submarine antenna on April 21, 1996.",
        'arrest_date'        => '1996-04-21',
        'incarceration_date' => '1996-04-21',
        'release_date'       => \Carbon\Carbon::parse('1996-04-21')->addMonths($monthsServed)->format('Y-m-d'),
        'sentence'           => "3-year federal sentence; served {$monthsServed} months in federal custody before transitioning to electronically-monitored house arrest.",
        'convicted'          => 'Yes',
    ]);

    echo "  [add]    {$name} — Project ELF antenna case ({$monthsServed} mo served)\n";
    $creates++;
}

echo "\nDone. updates={$updates}, creates={$creates}, unchanged={$unchanged}\n";
