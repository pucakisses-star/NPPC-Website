<?php

declare(strict_types=1);

/**
 * Delete institutions that aren't real physical facilities.
 *
 * Categories targeted:
 *   A) Parenthetical placeholder text — names like
 *      "(brief detentions; no extended incarceration)" that were
 *      stuffed into the institution column to record case context.
 *   B) Federal Bureau of Prisons agency rollups — "Federal Bureau
 *      of Prisons (federal custody)", "...(California)", "...
 *      (location varied)" etc. The BOP is an agency; the actual
 *      facility belongs in the institution row, not the agency.
 *   C) State Department-of-Corrections agency entries — same logic
 *      as the BOP rollups, one tier down.
 *   D) Generic "State prison (state)" wrappers.
 *   E) Federal courts — "U.S. District Court for the X District",
 *      "Federal courthouse, Y", etc. Courts aren't where prisoners
 *      are *held*, they're where they were tried.
 *   F) Exile / non-incarceration markers — Hollywood-blacklist
 *      "exile to France/UK/Mexico", "Soviet Russia (political
 *      exile)", "No detention — left U.S. before ICE custody".
 *
 * Cascading behavior: prisoner_cases.institution_id has
 * onDelete('set null'), so every affected case keeps its charges,
 * sentence, dates, etc. — only the (fake) institution link is
 * cleared. Idempotent: rows that no longer exist are skipped.
 *
 * Run with --dry-run first to inspect the list. Without --dry-run
 * it deletes.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;
use Illuminate\Support\Facades\DB;

$dryRun = in_array('--dry-run', $argv ?? [], true);

// SQL LIKE patterns. SQLite LIKE is case-insensitive by default
// for ASCII; we normalize via LOWER() to be safe.
$patternGroups = [
    'A: parenthetical placeholders' => [
        "name LIKE '(%' AND name LIKE '%)'",
    ],
    'B: BOP agency rollups' => [
        "name LIKE 'Federal Bureau of Prisons (%'",
    ],
    'C: State DOC agency entries (no specific facility)' => [
        // Bare state DOC names without a parenthetical specifying a facility
        "LOWER(name) IN ("
          . "'california department of corrections',"
          . "'california department of corrections and rehabilitation',"
          . "'california department of corrections (location varied)',"
          . "'california department of corrections (pretrial detention on sq6 charges)',"
          . "'california department of corrections (pretrial detention only on sq6 charges)',"
          . "'california department of corrections (san quentin, pelican bay, new folsom)',"
          . "'california state probation supervision',"
          . "'colorado department of corrections',"
          . "'georgia department of corrections (location varied)',"
          . "'illinois department of corrections',"
          . "'indiana department of correction',"
          . "'indiana department of corrections',"
          . "'indiana state probation supervision',"
          . "'massachusetts department of corrections',"
          . "'michigan state probation supervision',"
          . "'new jersey department of corrections',"
          . "'new york state department of corrections',"
          . "'north carolina state court',"
          . "'north carolina state prison (location varied)',"
          . "'oklahoma department of corrections',"
          . "'pennsylvania department of corrections',"
          . "'pennsylvania doc death row',"
          . "'south carolina department of corrections (location varied)',"
          . "'tennessee department of correction',"
          . "'texas department of criminal justice',"
          . "'utah department of corrections',"
          . "'virginia department of corrections (location varied)',"
          . "'washington state department of corrections',"
          . "'washington state department of corrections',"
          . "'wisconsin department of corrections',"
          . "'wyoming department of corrections',"
          . "'georgia state court'"
          . ")",
    ],
    'D: generic "State prison (state)"' => [
        "name LIKE 'State prison (%'",
    ],
    'E: federal courts and courthouses' => [
        // 'Federal Court' / 'Federal courts' (with optional trailing
        // text)
        "name LIKE 'Federal Court (%'",
        "name LIKE 'Federal Court —%'",
        "name LIKE 'Federal courts %'",
        "name LIKE 'Federal court, %'",
        "name LIKE 'Federal courthouse, %'",
        "name LIKE 'U.S. District Court%'",
        "name LIKE 'United States District Court%'",
        "name LIKE 'US District Court%'",
        // Specific known cases that got caught here as institutions:
        "LOWER(name) IN ("
          . "'fort benning federal court (soa watch prosecutions)',"
          . "'federal indictments (never tried)',"
          . "'federal jail (sedition act prosecutions)',"
          . "'federal prison (wwi-era prosecution)',"
          . "'federal prison — selective service act prosecutions',"
          . "'federal custody (u.s. virgin islands prosecution; transferred bop)'"
          . ")",
    ],
    'F: exile / non-incarceration markers' => [
        "name LIKE 'Hollywood blacklist exile %'",
        "name LIKE '%political exile%'",
        "LOWER(name) IN ("
          . "'no detention — left u.s. before ice custody',"
          . "'deported to haiti as a terrorism-related removal',"
          . "'federal fugitive / fbi most wanted terrorists list - in hiding (uk)',"
          . "'soviet russia (political exile)',"
          . "'republic of cuba (political asylum)',"
          . "'mccarthy-era political exile — switzerland',"
          . "'huac-era political exile — german democratic republic'"
          . ")",
    ],
];

$totalToDelete = 0;
$totalCasesAffected = 0;
$rowsByCategory = [];

foreach ($patternGroups as $label => $patterns) {
    $where = '(' . implode(' OR ', $patterns) . ')';
    $rows = DB::select("SELECT id, name, (SELECT COUNT(*) FROM prisoner_cases pc WHERE pc.institution_id = institutions.id) AS case_count FROM institutions WHERE {$where} ORDER BY name");
    if (! $rows) continue;

    echo "\n==== {$label} ====\n";
    foreach ($rows as $r) {
        echo sprintf("  %s  [cases=%d]  %s\n", substr($r->id, 0, 8), $r->case_count, $r->name);
        $totalCasesAffected += (int) $r->case_count;
    }
    $rowsByCategory[$label] = $rows;
    $totalToDelete += count($rows);
}

echo "\n==== Summary ====\n";
echo "  institutions to delete: {$totalToDelete}\n";
echo "  cases that will lose institution_id (data preserved): {$totalCasesAffected}\n";

if ($dryRun) {
    echo "\nDry run — nothing deleted. Re-run without --dry-run to execute.\n";
    return;
}

echo "\nExecuting deletes...\n";
$deleted = 0;
foreach ($rowsByCategory as $label => $rows) {
    foreach ($rows as $r) {
        // Belt-and-suspenders: re-fetch to confirm the row still
        // exists, in case something raced.
        $inst = Institution::find($r->id);
        if ($inst) {
            $inst->delete();
            $deleted++;
        }
    }
}
echo "Deleted: {$deleted}\n";
echo "Done.\n";
