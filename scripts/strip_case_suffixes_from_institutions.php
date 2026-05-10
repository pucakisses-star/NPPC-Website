<?php

declare(strict_types=1);

/**
 * Strip case-context suffixes from institution names and merge
 * the resulting duplicates.
 *
 * Two kinds of suffixes get stripped:
 *
 *   1. Em-dash trailers — anything after " — " or " – " or " - "
 *      (em-dash, en-dash, or hyphen with surrounding spaces).
 *      Examples cleared:
 *        "FCI Danbury — Hollywood Ten" -> "FCI Danbury"
 *        "U.S. Penitentiary Atlanta — Smith Act / Foley Square Trial"
 *           -> "U.S. Penitentiary Atlanta"
 *        "Kilby Prison (released July 24, 1937)" — the parenthetical
 *           is matched separately, not by this rule.
 *
 *   2. Trailing parentheticals that look like case context — i.e.
 *      contain words like "prosecution", "trial", "case", "cohort",
 *      "released", "transferred", "Sedition Act", "Hollywood",
 *      "Vietnam", "Smith Act", "court-martial", "indictment",
 *      "Plowshares cohort", etc. We DON'T strip parentheticals that
 *      look like city/disambiguator clarifiers (e.g. "Hampshire
 *      County House of Corrections (Nashua)" stays — Nashua is
 *      genuinely part of the name).
 *
 * After cleaning, institutions with the same lower-cased cleaned
 * name are merged: the row whose existing name already matches
 * the cleaned name (or, if none, the row with the most cases) is
 * canonical; every other row's cases are reassigned to it, then
 * the duplicates are deleted. The canonical row's name is set to
 * the cleaned form.
 *
 * Idempotent. Run with --dry-run first to inspect.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;
use Illuminate\Support\Facades\DB;

$dryRun = in_array('--dry-run', $argv ?? [], true);

/**
 * Strip case-context trailers. See header.
 */
function cleanName(string $name): string
{
    $orig = $name;

    // 1. Em-dash / en-dash / spaced-hyphen trailer.
    //    " — Hollywood Ten" / " – Vietnam draft resisters" / " - Wells Fargo"
    $name = preg_replace('/\s+(—|–|-)\s+.*$/u', '', $name);

    // 2. Trailing parenthetical with case-context keywords.
    //    Multiple passes so chained suffixes are all stripped.
    $caseWords = '(?:'
        . 'prosecutions?|trial|trials|case|cases|cohort|cohorts|'
        . 'released\s|transferred\s|sentence[ds]?|jumped|acquitted|reversed|'
        . 'sedition\s+act|hollywood\s+ten|vietnam|smith\s+act|huac|'
        . 'court-?martial|indictment|defendants?|arrests?|raid|'
        . 'plowshares|nationalist|first\s+loyalty|patterson|'
        . 'rosenberg|abrams|hiss|remington|garvey|rutherford|'
        . 'foley\s+square|mccarthy|mcgee|orangeburg|wilmington|'
        . 'vieques|dennis|monroe|brooklyn|chicago|wells\s+fargo|fbi'
        . ')';
    $pattern = '/\s+\([^)]*' . $caseWords . '[^)]*\)$/iu';
    for ($i = 0; $i < 3; $i++) {
        $next = preg_replace($pattern, '', $name);
        if ($next === $name) break;
        $name = $next;
    }

    return trim($name);
}

// ---- Build the rename / merge plan ---------------------------------
$all = Institution::query()
    ->orderBy('name')
    ->get(['id', 'name', 'city', 'state', 'security', 'mailing_address', 'physical_address', 'lat', 'lng']);

$plan = []; // cleaned-lower => ['cleaned' => str, 'rows' => Institution[]]
foreach ($all as $inst) {
    $cleaned = cleanName($inst->name);
    if ($cleaned === '') continue; // safety
    $key = mb_strtolower($cleaned);
    if (! isset($plan[$key])) {
        $plan[$key] = ['cleaned' => $cleaned, 'rows' => []];
    }
    $plan[$key]['rows'][] = $inst;
}

$renames = [];   // [Institution, oldName, newName]
$merges  = [];   // [canonInstitution, dupesArray, cleanedName]
foreach ($plan as $key => $entry) {
    $cleaned = $entry['cleaned'];
    $rows    = $entry['rows'];

    if (count($rows) === 1) {
        $r = $rows[0];
        if ($r->name !== $cleaned) {
            $renames[] = [$r, $r->name, $cleaned];
        }
        continue;
    }

    // multiple rows share this cleaned name -> merge
    // canon: prefer row that already matches cleaned name; tie-break
    // by case_count desc so the busiest row stays.
    usort($rows, function ($a, $b) use ($cleaned) {
        $aMatch = (int) ($a->name === $cleaned);
        $bMatch = (int) ($b->name === $cleaned);
        if ($aMatch !== $bMatch) return $bMatch - $aMatch; // matching name first
        $aCount = DB::table('prisoner_cases')->where('institution_id', $a->id)->count();
        $bCount = DB::table('prisoner_cases')->where('institution_id', $b->id)->count();
        return $bCount - $aCount;
    });
    $canon = $rows[0];
    $dupes = array_slice($rows, 1);
    $merges[] = [$canon, $dupes, $cleaned];
}

// ---- Print plan -----------------------------------------------------
echo "==== Renames (single-row, name needs cleaning) ====\n";
foreach ($renames as [$r, $old, $new]) {
    echo sprintf("  %s  :: %s\n         -> %s\n", substr($r->id, 0, 8), $old, $new);
}

echo "\n==== Merges (multiple rows collapse to one cleaned name) ====\n";
$mergeCases = 0; $mergeDeletes = 0;
foreach ($merges as [$canon, $dupes, $cleaned]) {
    echo sprintf("\n  CANON %s  :: %s\n", substr($canon->id, 0, 8), $canon->name);
    if ($canon->name !== $cleaned) {
        echo sprintf("       (rename canon -> %s)\n", $cleaned);
    }
    foreach ($dupes as $d) {
        $caseCount = DB::table('prisoner_cases')->where('institution_id', $d->id)->count();
        echo sprintf("    + %s  cases=%d  :: %s\n", substr($d->id, 0, 8), $caseCount, $d->name);
        $mergeCases   += $caseCount;
        $mergeDeletes++;
    }
}

echo "\n==== Summary ====\n";
echo "  rows to rename in place: " . count($renames) . "\n";
echo "  merge groups:            " . count($merges) . "\n";
echo "  rows to delete (dupes):  {$mergeDeletes}\n";
echo "  cases reassigned:        {$mergeCases}\n";

if ($dryRun) {
    echo "\nDry run — nothing written. Re-run without --dry-run to execute.\n";
    return;
}

// ---- Execute ---------------------------------------------------------
echo "\nExecuting...\n";
DB::transaction(function () use ($renames, $merges) {
    foreach ($renames as [$r, $old, $new]) {
        $r->name = $new;
        $r->save();
    }
    foreach ($merges as [$canon, $dupes, $cleaned]) {
        if ($canon->name !== $cleaned) {
            $canon->name = $cleaned;
        }
        foreach ($dupes as $d) {
            DB::table('prisoner_cases')
                ->where('institution_id', $d->id)
                ->update(['institution_id' => $canon->id]);

            // Fill blank fields on canon from dupe
            foreach (['city', 'state', 'security', 'mailing_address', 'physical_address', 'lat', 'lng'] as $col) {
                $canonVal = trim((string) $canon->getAttribute($col));
                $dupeVal  = trim((string) $d->getAttribute($col));
                if ($canonVal === '' && $dupeVal !== '') {
                    $canon->setAttribute($col, $d->getAttribute($col));
                }
            }
            $d->delete();
        }
        $canon->save();
    }
});
echo "Done.\n";
