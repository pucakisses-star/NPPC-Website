<?php

declare(strict_types=1);

/**
 * Set the May 16, 2015 release date and append the Sixth Circuit
 * appellate-vacatur context on the three Transform Now Plowshares
 * defendants — Sr. Megan Rice, Michael Walli, and Greg Boertje-Obed
 * — for their July 28, 2012 action at the Y-12 National Security
 * Complex (Oak Ridge, TN). All three were released from federal
 * custody on May 16, 2015 after the U.S. Court of Appeals for the
 * Sixth Circuit overturned their sabotage convictions, finding the
 * action "had zero effect... on the nation's ability to wage war
 * or defend against attack" (Kethledge, J.).
 *
 * Sources:
 *   https://www.nukeresister.org/2015/05/16/freedom-for-sr-megan-rice-michael-walli-greg-boertje-obed/
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use App\Models\Institution;
use App\Models\PrisonerCase;

$context = "On May 16, 2015 the U.S. Court of Appeals for the Sixth Circuit overturned the sabotage conviction (18 U.S.C. § 2155) for the Transform Now Plowshares action — Sr. Megan Rice, Michael Walli, and Greg Boertje-Obed had cut through three perimeter fences at the Y-12 National Security Complex in Oak Ridge, TN on July 28, 2012, hung crime-scene tape and banners, splashed human blood, and hammered on the wall of the highly enriched uranium storage facility. Judge Raymond Kethledge wrote that the action \"had zero effect, at the time of their actions or anytime afterwards, on the nation's ability to wage war or defend against attack.\" The remaining conviction (depredation of government property) was already covered by time served, so all three were released from federal custody the same day pending resentencing.";

$rows = [
    ['name' => 'Megan Rice',          'inst' => 'MDC Brooklyn',                    'instCity' => 'Brooklyn',     'instState' => 'New York'],
    ['name' => 'Michael Walli',       'inst' => 'FCI Schuylkill',                   'instCity' => 'Minersville',  'instState' => 'Pennsylvania'],
    ['name' => 'Greg Boertje-Obed',   'inst' => 'USP Leavenworth',                  'instCity' => 'Leavenworth',  'instState' => 'Kansas'],
];

$updates = 0;
foreach ($rows as $row) {
    $p = Prisoner::where('name', $row['name'])->first();
    if (! $p) {
        echo "  [warn]   {$row['name']} not in DB; skipping\n";
        continue;
    }

    $changed = false;
    if (! str_contains((string) $p->description, 'Transform Now Plowshares')) {
        $p->description = trim((string) $p->description) . ($p->description ? ' ' : '') . $context;
        $changed = true;
    }
    if ($changed) $p->save();

    $inst = Institution::firstOrCreate(['name' => $row['inst']], ['city' => $row['instCity'], 'state' => $row['instState']]);

    // Find a Transform Now Plowshares case (arrest_date 2012-07-28
    // or charges mentioning Y-12) to update; otherwise pick the
    // existing case that covers the release window or create a new one.
    $case = $p->cases()
        ->where(function ($q) {
            $q->where('arrest_date', '2012-07-28')
              ->orWhere('charges', 'like', '%Y-12%')
              ->orWhere('charges', 'like', '%Transform Now Plowshares%');
        })
        ->first()
        ?? $p->cases()->orderByDesc('arrest_date')->first()
        ?? new PrisonerCase(['prisoner_id' => $p->id]);

    $caseChanged = false;
    if (empty($case->institution_id))           { $case->institution_id     = $inst->id;       $caseChanged = true; }
    if ((string) $case->release_date !== '2015-05-16') {
        $case->release_date = '2015-05-16';
        $caseChanged = true;
    }
    if (empty($case->charges) || ! str_contains((string) $case->charges, 'Y-12')) {
        $case->charges = '18 U.S.C. § 2155 (sabotage — vacated by 6th Cir., May 8, 2015) and 18 U.S.C. § 1361 (depredation of government property — affirmed). Transform Now Plowshares action at the Y-12 National Security Complex, Oak Ridge, TN, July 28, 2012.';
        $caseChanged = true;
    }
    if (empty($case->arrest_date))             { $case->arrest_date        = '2012-07-28';     $caseChanged = true; }
    if (empty($case->incarceration_date))      { $case->incarceration_date = '2014-02-08';     $caseChanged = true; }
    if (! str_contains((string) $case->sentence, '6th Cir')) {
        $case->sentence = "Federal prison until May 16, 2015, when the 6th Circuit vacated the sabotage count and the remaining property-damage count was deemed time-served. Held at " . $row['inst'] . ".";
        $caseChanged = true;
    }
    if (empty($case->convicted)) {
        $case->convicted = 'Yes — federal jury verdict (E.D. Tenn.); sabotage count later vacated on appeal';
        $caseChanged = true;
    }

    if ($caseChanged) {
        if (! $case->prisoner_id) $case->prisoner_id = $p->id;
        $case->save();
        echo "  [update] {$row['name']} — release_date 2015-05-16 + Y-12 / 6th Cir context\n";
        $updates++;
    } else {
        echo "  [ok]     {$row['name']} — already complete\n";
    }
}

echo "\nDone. updates={$updates}\n";
