#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/backfill_cases_from_add_scripts.sh
#
# Many prisoners on the site have zero attached case rows because
# their original prisoner:add command was a no-op - the prisoner
# already existed in the database via the Airtable import, so
# prisoner:add hit its duplicate-name guard and the case data
# inside the JSON never landed in the cases table.
#
# This script scans every scripts/add_*.sh file, extracts the
# prisoner:add JSON for each defendant, indexes by canonical name,
# and for any prisoner currently in the database with zero case
# rows whose name matches an entry in the index, creates the case
# rows defined in that JSON. Existing institutions are matched (or
# created) by name + city + state.
#
# Idempotent: skips any prisoner that already has at least one case
# row attached.
set -e

php artisan tinker --execute='
$scriptDir = base_path("scripts");
$files = glob($scriptDir . "/add_*.sh");
sort($files);

// Build a name -> [array of cases-arrays] index.
// One name may map to multiple add invocations across files; we
// merge their case arrays together.
$index = [];
foreach ($files as $f) {
    $contents = @file_get_contents($f);
    if (!$contents) continue;

    // Extract every single-quoted JSON payload after prisoner:add.
    // The shell scripts use double quotes inside the JSON and
    // single quotes to wrap the JSON, with apostrophes pre-stripped
    // from the content.
    if (!preg_match_all("/prisoner:add\s+\x27([^\x27]+)\x27/m", $contents, $m)) {
        continue;
    }
    foreach ($m[1] as $jsonStr) {
        $data = json_decode($jsonStr, true);
        if (!is_array($data) || empty($data["name"])) continue;
        $name = trim($data["name"]);
        $cases = $data["cases"] ?? [];
        if (!is_array($cases) || empty($cases)) continue;
        if (!isset($index[$name])) {
            $index[$name] = [];
        }
        foreach ($cases as $c) {
            $index[$name][] = $c;
        }
    }
}
echo "Indexed " . count($index) . " distinct prisoner names with case data across " . count($files) . " scripts.\n";

$attached = 0;
$skippedHasCases = 0;
$noEntryInIndex  = 0;

$caseless = App\Models\Prisoner::whereDoesntHave("cases")->get();
echo "Caseless prisoners in DB: " . $caseless->count() . "\n";

foreach ($caseless as $p) {
    if (!isset($index[$p->name])) {
        $noEntryInIndex++;
        continue;
    }
    $cases = $index[$p->name];
    foreach ($cases as $c) {
        // Resolve / create the institution by name + city + state.
        $instName  = $c["institution_name"]  ?? null;
        $instCity  = $c["institution_city"]  ?? null;
        $instState = $c["institution_state"] ?? null;

        $inst = null;
        if ($instName) {
            $q = App\Models\Institution::where("name", $instName);
            if ($instCity)  $q->where("city",  $instCity);
            if ($instState) $q->where("state", $instState);
            $inst = $q->first();
            if (!$inst) {
                $inst = App\Models\Institution::create(array_filter([
                    "name"  => $instName,
                    "city"  => $instCity,
                    "state" => $instState,
                ]));
            }
        }

        App\Models\PrisonerCase::create([
            "prisoner_id"           => $p->id,
            "institution_id"        => $inst?->id,
            "charges"               => $c["charges"]               ?? null,
            "arrest_date"           => $c["arrest_date"]           ?? null,
            "incarceration_date"    => $c["incarceration_date"]    ?? null,
            "sentenced_date"        => $c["sentenced_date"]        ?? null,
            "release_date"          => $c["release_date"]          ?? null,
            "death_in_custody_date" => $c["death_in_custody_date"] ?? null,
            "in_exile_since"        => $c["in_exile_since"]        ?? null,
            "end_of_exile"          => $c["end_of_exile"]          ?? null,
            "sentence"              => $c["sentence"]              ?? null,
            "convicted"             => $c["convicted"]             ?? null,
            "judge"                 => $c["judge"]                 ?? null,
            "prosecutor"            => $c["prosecutor"]            ?? null,
            "indicted"              => $c["indicted"]              ?? null,
            "plead"                 => $c["plead"]                 ?? null,
        ]);
        $attached++;
    }
    echo "  attached " . count($cases) . " case(s) to {$p->name}\n";
}

echo "\nSummary:\n";
echo "  cases attached:                 {$attached}\n";
echo "  caseless prisoners skipped:     {$noEntryInIndex} (no add-script entry found)\n";
echo "  prisoners with cases (skipped): " . App\Models\Prisoner::whereHas("cases")->count() . " total in DB now have cases\n";
'

echo
echo "Case backfill from add-scripts complete."
