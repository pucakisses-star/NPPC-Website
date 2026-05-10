<?php

declare(strict_types=1);

/**
 * Normalize institution city/state fields:
 *   - city "BIG SPRING" -> "Big Spring" (Title Case)
 *   - state "TX" -> "Texas" (full state name)
 *
 * Idempotent: only writes rows whose normalized form differs from
 * what's stored. --dry-run shows every change without writing.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;

$dryRun = in_array('--dry-run', $argv ?? [], true);

// US state abbreviation -> full name (50 + DC + common territories)
$states = [
    'AL' => 'Alabama','AK' => 'Alaska','AZ' => 'Arizona','AR' => 'Arkansas',
    'CA' => 'California','CO' => 'Colorado','CT' => 'Connecticut','DE' => 'Delaware',
    'FL' => 'Florida','GA' => 'Georgia','HI' => 'Hawaii','ID' => 'Idaho',
    'IL' => 'Illinois','IN' => 'Indiana','IA' => 'Iowa','KS' => 'Kansas',
    'KY' => 'Kentucky','LA' => 'Louisiana','ME' => 'Maine','MD' => 'Maryland',
    'MA' => 'Massachusetts','MI' => 'Michigan','MN' => 'Minnesota','MS' => 'Mississippi',
    'MO' => 'Missouri','MT' => 'Montana','NE' => 'Nebraska','NV' => 'Nevada',
    'NH' => 'New Hampshire','NJ' => 'New Jersey','NM' => 'New Mexico','NY' => 'New York',
    'NC' => 'North Carolina','ND' => 'North Dakota','OH' => 'Ohio','OK' => 'Oklahoma',
    'OR' => 'Oregon','PA' => 'Pennsylvania','RI' => 'Rhode Island','SC' => 'South Carolina',
    'SD' => 'South Dakota','TN' => 'Tennessee','TX' => 'Texas','UT' => 'Utah',
    'VT' => 'Vermont','VA' => 'Virginia','WA' => 'Washington','WV' => 'West Virginia',
    'WI' => 'Wisconsin','WY' => 'Wyoming','DC' => 'District of Columbia',
    'PR' => 'Puerto Rico','VI' => 'U.S. Virgin Islands','GU' => 'Guam',
    'AS' => 'American Samoa','MP' => 'Northern Mariana Islands',
];

/**
 * Title Case for city names. ucwords handles most cases. We then
 * fix common Mc-prefix cases (McAlester, McKean) by hand and
 * preserve dotted abbreviations (St., Ft.).
 */
function titleCaseCity(string $s): string
{
    $s = trim($s);
    if ($s === '') return $s;
    // Lowercase first, then ucwords
    $out = ucwords(mb_strtolower($s));
    // Fix Mc-prefix cities: "Mcalester" -> "McAlester"
    $out = preg_replace_callback('/\bMc([a-z])/u', fn ($m) => 'Mc' . mb_strtoupper($m[1]), $out);
    // "St" / "Ft" should keep their period if present in original, but
    // also treat "St" / "Ft" not followed by period as fine
    return $out;
}

$rows = Institution::query()->orderBy('name')->get(['id','name','city','state']);

$cityChanges = 0;
$stateChanges = 0;
$writes = 0;

echo "Walking " . $rows->count() . " institutions...\n\n";

foreach ($rows as $inst) {
    $newCity  = $inst->city;
    $newState = $inst->state;

    // City: if all-caps (no lowercase letters), Title Case it.
    if ($inst->city !== null && $inst->city !== '') {
        $hasLower = (bool) preg_match('/[a-z]/u', $inst->city);
        if (! $hasLower) {
            $newCity = titleCaseCity($inst->city);
        }
    }

    // State: if exactly 2 uppercase letters and in our map, expand it.
    if ($inst->state !== null && $inst->state !== '' && preg_match('/^[A-Z]{2}$/', trim($inst->state))) {
        $abbr = trim($inst->state);
        if (isset($states[$abbr])) {
            $newState = $states[$abbr];
        }
    }

    if ($newCity !== $inst->city || $newState !== $inst->state) {
        echo sprintf("  %s  %s\n", substr($inst->id, 0, 8), $inst->name);
        if ($newCity !== $inst->city) {
            echo sprintf("       city:  %-25s -> %s\n", (string) $inst->city, $newCity);
            $cityChanges++;
        }
        if ($newState !== $inst->state) {
            echo sprintf("       state: %-25s -> %s\n", (string) $inst->state, $newState);
            $stateChanges++;
        }
        if (! $dryRun) {
            $inst->city  = $newCity;
            $inst->state = $newState;
            $inst->save();
        }
        $writes++;
    }
}

echo "\nSummary:\n";
echo "  rows touched:  {$writes}\n";
echo "  city renames:  {$cityChanges}\n";
echo "  state renames: {$stateChanges}\n";
if ($dryRun) echo "\nDry run — nothing written. Re-run without --dry-run to execute.\n";
