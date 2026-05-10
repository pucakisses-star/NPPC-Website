<?php

declare(strict_types=1);

/**
 * Bulk affiliation/ideology cleanup pass:
 *
 *   1. Rename pairs (canonical -> swap-in):
 *        "Vieques resistance"           -> "Vieques civil disobedience movement"
 *        "SOA watch"                    -> "School of the Americas Watch (SOA Watch)"
 *
 *   2. Collapse every "Occupy *" variant into a single canonical
 *      "Occupy Movement". Catches Occupy Wall Street, Occupy
 *      Oakland, Occupy Chicago, Occupy Denver, Occupy San Diego,
 *      Occupy Seattle, Occupy Cleveland, OWS, etc. Bare "Occupy"
 *      already done in earlier scripts; this is the chapter-level
 *      collapse.
 *
 *   3. Delete (remove from arrays entirely; never replace):
 *        Macheteros / Ejército Popular Boricua
 *        Jeju Naval Base resistance
 *        Gangjeong village
 *        HAMAS
 *        U.S. Army 24th Infantry Regiment, 3rd Battalion
 *        Anti-Draft-Registration Resistance
 *        Atlantic Life Community
 *        Progressive Anti-Abortion Uprising
 *        National Woman's Party
 *        RNC Welcoming Committee
 *        Jonah House
 *        Anti-fascist counter-protest community (Pacific Beach 2021)
 *        Seas of David
 *        Nukewatch
 *
 * Operates on both `ideologies` and `affiliation` arrays so a label
 * stored on the wrong field still gets cleaned. Dedupes after each
 * change. Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

// rename map: lowercased input -> canonical output
$renames = [
    'vieques resistance'                     => 'Vieques civil disobedience movement',
    'soa watch'                              => 'School of the Americas Watch (SOA Watch)',
];

// Anything starting with "occupy " (case-insensitive, with a space)
// or exactly "ows" / "occupy wall street" / "occupy wallstreet" /
// "occupy" goes to the canonical "Occupy Movement". Already-set
// "occupy movement" is a no-op (dedupes).
$occupyCanon = 'Occupy Movement';

$deletes = array_map('mb_strtolower', [
    'Macheteros / Ejército Popular Boricua',
    'Jeju Naval Base resistance',
    'Gangjeong village',
    'HAMAS',
    'U.S. Army 24th Infantry Regiment, 3rd Battalion',
    'Anti-Draft-Registration Resistance',
    'Atlantic Life Community',
    'Progressive Anti-Abortion Uprising',
    "National Woman's Party",
    'RNC Welcoming Committee',
    'Jonah House',
    'Anti-fascist counter-protest community (Pacific Beach 2021)',
    'Seas of David',
    'Nukewatch',
]);

$touched = 0;
foreach (Prisoner::query()->cursor() as $p) {
    $changed = false;
    foreach (['ideologies', 'affiliation'] as $field) {
        $arr = $p->$field ?? [];
        if (! is_array($arr) || empty($arr)) continue;

        $out = [];
        foreach ($arr as $v) {
            $vlc = mb_strtolower(trim((string) $v));

            // delete?
            if (in_array($vlc, $deletes, true)) {
                $changed = true;
                continue;
            }
            // rename pair?
            if (isset($renames[$vlc])) {
                $out[] = $renames[$vlc];
                $changed = true;
                continue;
            }
            // "Occupy *" collapse?
            if ($vlc === 'ows'
                || $vlc === 'occupy'
                || str_starts_with($vlc, 'occupy ')) {
                $out[] = $occupyCanon;
                if ($v !== $occupyCanon) $changed = true;
                continue;
            }
            $out[] = $v;
        }
        if ($changed) {
            $p->$field = array_values(array_unique($out));
        }
    }
    if ($changed) {
        $p->save();
        echo sprintf("[updated] %s\n", $p->name);
        $touched++;
    }
}

echo "\nDone. Updated {$touched} prisoner(s).\n";
