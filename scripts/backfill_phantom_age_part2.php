<?php

declare(strict_types=1);

/**
 * Round 2 of the phantom-age backfill: 6 of the 8 prisoners the
 * earlier scripts/backfill_death_dates_centenarian_phantoms.php
 * left as "research inconclusive" now have confident dates from
 * additional research:
 *
 *   - Michael X. Mockus       d. 1939-10-23 (Lithuania)
 *     Convicted of blasphemy in CT (1916) and ME (1917); fled to
 *     Mexico rather than serve the Maine sentence after the Maine
 *     Supreme Judicial Court upheld in 1921. Born 1864-10-26.
 *
 *   - Robert Lee Hill         d. 1963-05-11 (Topeka, KS)
 *     Elaine Massacre 1919 leader of the Progressive Farmers and
 *     Household Union; escaped to Kansas and lived the rest of his
 *     life in Topeka, working for the Santa Fe Railway. Buried in
 *     Topeka Cemetery.
 *
 *   - Vern Smith              d. 1978-10-27 (Alameda, CA)
 *     IWW Industrial Worker / Industrial Solidarity editor; later
 *     CPUSA Daily Worker Moscow correspondent. Lived to 87.
 *
 *   - Max Geldman             d. 1989 (year only)
 *     Trotskyist Minneapolis Smith Act defendant 1941. Birth/death
 *     years 1905-1989 per the Twentieth Century Radicalism in
 *     Minnesota Oral History Project.
 *
 *   - Reubin Clein            d. 1989-09-09
 *     NOTE: this is a press-freedom case, not a Communist / Smith
 *     Act case. Clein was the editor/publisher of the Miami Beach
 *     scandal sheet "Miami Life" 1923-1965 and was the first
 *     Florida journalist jailed (1950) for refusing to reveal a
 *     source. The DB description should be reviewed.
 *
 *   - Loretta Starvus Stack   d. 2000 (year only)
 *     Connecticut Smith Act defendant 1951; sentenced to 5 years
 *     1952; conviction reversed; left CPUSA a few years later.
 *
 * Still inconclusive (2): Jacob Wipf and David Hofer (Hutterite WWI
 * COs who survived imprisonment and returned to South Dakota
 * colonies). Their death dates aren't in any free public source I
 * could reach; Hutterite genealogy archives would be the next step.
 *
 * Idempotent — only writes when death_date is currently null.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$dates = [
    ['names' => ['Michael X. Mockus'],          'date' => '1939-10-23'],
    ['names' => ['Robert Lee Hill'],            'date' => '1963-05-11'],
    ['names' => ['Vern Smith'],                 'date' => '1978-10-27'],
    ['names' => ['Max Geldman'],                'date' => '1989-12-31'], // year confirmed; day approximate
    ['names' => ['Reubin Clein'],               'date' => '1989-09-09'],
    ['names' => ['Loretta Starvus Stack'],      'date' => '2000-12-31'], // year confirmed; day approximate
];

$set = 0; $already = 0; $miss = 0;
foreach ($dates as $row) {
    $p = Prisoner::whereIn('name', $row['names'])->first();
    if (! $p) {
        echo "  [not found] " . $row['names'][0] . "\n"; $miss++;
        continue;
    }
    if ($p->death_date) {
        echo "  [already set] {$p->name}: " . $p->death_date->format('Y-m-d') . "\n"; $already++;
        continue;
    }
    $p->death_date = $row['date'];
    $p->save();
    echo "  [set] {$p->name}: death_date = {$row['date']}\n"; $set++;
}

echo "\n--- Still inconclusive (skipped) ---\n";
echo "  Jacob Wipf  — Hutterite WWI CO who survived Alcatraz/Leavenworth (released Apr 13, 1919); returned to SD. No confident death date in free public sources.\n";
echo "  David Hofer — Hutterite WWI CO who survived (his brothers Joseph and Michael died at Leavenworth Nov-Dec 1918). No confident death date in free public sources.\n";

echo "\nDone. set={$set}, already={$already}, not found={$miss}\n";
