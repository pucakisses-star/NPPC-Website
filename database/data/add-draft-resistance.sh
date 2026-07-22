#!/usr/bin/env bash
#
# Add Vietnam-era draft-card burners and draft-board-raid defendants who were
# missing from the prisoner database.
#
# The database already held the famous Catholic Left actions (Catonsville Nine,
# Baltimore Four, Milwaukee 14, Harrisburg Seven, and part of the Camden 28).
# This fills in the groups and individuals that were absent, all drawn from
# court records, contemporary press, MNopedia, the Camden 28 documentary press
# kit, and standard histories:
#
#   Camden 28 (remaining defendants), DC Nine, Chicago 15, Minnesota Eight,
#   Beaver 55, Flower City Conspiracy, Silver Spring Three, the Union Square
#   draft-card burners, and other individually-prosecuted burners (David Paul
#   O Brien of United States v. O Brien, Barry Bondhus, Bruce Dancis, Gary
#   Rader, Stephen L. Smith).
#
# Deliberately EXCLUDED as not fitting the prisoner list or as unverifiable:
#   the FBI informant Robert Hardy; Women Against Daddy Warbucks and the Boston
#   Eight (never prosecuted); the single-source Pasadena Three; and people
#   already in the database (Tom Cornell, Ted Glick, the Harrisburg Seven,
#   David J. Miller, David McReynolds, Michael Doyle, John Grady). William
#   Anderson and David Williams are namesakes of unrelated existing records and
#   are added as distinct people (the slug trait auto-suffixes on collision).
#
# The roster with per-person facts is in
# database/data/draft-resistance-additions.json.
#
# Idempotent: a person is skipped if a prisoner with the same name already
# shares one of their group affiliations, so re-running adds nothing new. Run
# from the repo root:
#   bash database/data/add-draft-resistance.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$rows = json_decode(file_get_contents(base_path("database/data/draft-resistance-additions.json")), true);
if (! is_array($rows)) { echo "Could not read additions JSON\n"; return; }

$created = 0; $skipped = 0;
foreach ($rows as $r) {
    // Dedup: skip only if a same-named prisoner already shares an affiliation.
    $matches = \App\Models\Prisoner::withoutGlobalScopes()
        ->whereRaw("LOWER(name) = ?", [strtolower($r["name"])])->get();
    $dup = false;
    foreach ($matches as $m) {
        $maff = array_map("strtolower", (array) $m->affiliation);
        foreach ((array) $r["affiliation"] as $a) {
            if (in_array(strtolower($a), $maff, true)) { $dup = true; break 2; }
        }
    }
    if ($dup) { $skipped++; continue; }

    $p = new \App\Models\Prisoner();
    $p->name = $r["name"];
    $p->first_name = $r["first_name"] ?? null;
    $p->last_name = $r["last_name"] ?? null;
    $p->description = $r["description"] ?? null;
    if (! empty($r["state"]))   { $p->state = $r["state"]; }
    if (! empty($r["gender"]))  { $p->gender = $r["gender"]; }
    $p->era = $r["era"] ?? null;
    $p->ideologies = $r["ideologies"] ?? [];
    $p->affiliation = $r["affiliation"] ?? [];
    $p->in_custody = false;
    $p->released = true;
    if (! empty($r["birth"])) {
        $b = $r["birth"];
        $p->setPartialDate("birthdate", $b[0] ?? null, $b[1] ?? null, $b[2] ?? null);
    }
    if (! empty($r["death"])) {
        $d = $r["death"];
        $p->setPartialDate("death_date", $d[0] ?? null, $d[1] ?? null, $d[2] ?? null);
    }
    $p->save();

    if (! empty($r["case"])) {
        $c = new \App\Models\PrisonerCase();
        $c->prisoner_id = $p->id;
        if (! empty($r["case"]["charges"]))   { $c->charges = $r["case"]["charges"]; }
        if (! empty($r["case"]["convicted"])) { $c->convicted = $r["case"]["convicted"]; }
        if (! empty($r["case"]["sentence"]))  { $c->sentence = $r["case"]["sentence"]; }
        $c->save();
    }

    echo "  + " . $p->name . " (" . $p->slug . ") [" . implode(", ", (array) $r["affiliation"]) . "]\n";
    $created++;
}

echo "\nCreated {$created}; skipped {$skipped} already present.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Draft-resistance prisoners added."
