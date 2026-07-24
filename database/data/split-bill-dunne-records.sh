#!/usr/bin/env bash
#
# Split the fused "bill-dunne" record back into two distinct people.
#
# The record at slug "bill-dunne" had accidentally merged two unrelated men:
#   * Bill Dunne  — anarchist political prisoner, BOP #10916-086, incarcerated
#     since 1979 (King County Jail liberation attempt), born 1954, still alive
#     and in custody. This record KEEPS the slug bill-dunne.
#   * William F. Dunne — veteran labor journalist and Daily Worker editor
#     (1887-1953), jailed 30 days in 1927 for publishing David Gordon's poem
#     "America". This becomes a NEW record at slug william-f-dunne, and the 1927
#     "The Tombs" case moves to it.
#
# (The bogus "died 1953-09-23" that had produced a death-before-birth on the
#  anarchist record was actually William F. Dunne's death date — the clearest
#  fingerprint of the merge.)
#
# Idempotent. Run from the repo root:
#   bash database/data/split-bill-dunne-records.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$anarch = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "bill-dunne")->first();
if (! $anarch) { echo "bill-dunne not found.\n"; return; }

// --- 1) Ensure the William F. Dunne record exists and is correct. ---
$wf = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "william-f-dunne")->first();
if (! $wf) {
    $wf = new \App\Models\Prisoner();
    $wf->slug = "william-f-dunne";
}
$wf->name = "William F. Dunne";
$wf->first_name = "William";
$wf->last_name = "Dunne";
$wf->aka = null;
$wf->gender = "Male";
$wf->state = "New York";
$wf->era = "1920s";
$wf->ideologies = ["Communism"];
$wf->affiliation = ["Workers (Communist) Party", "Daily Worker"];
$wf->in_custody = false;
$wf->released = true;
$wf->under_review = false;
$wf->description = trim(file_get_contents(base_path("database/data/william-f-dunne-bio.txt")));
$wf->setPartialDate("birthdate", 1887, 10, 15);
$wf->setPartialDate("death_date", 1953, 9, 23);
$wf->save();
echo "william-f-dunne ready (id {$wf->id}).\n";

// --- 2) Move the 1927 Tombs case off the anarchist record onto William F. ---
$moved = false;
foreach ($anarch->cases()->get() as $c) {
    if (str_starts_with((string) $c->incarceration_date, "1927")) {
        $c->prisoner()->associate($wf);
        $c->save();
        $moved = true;
        echo "  moved 1927 case (institution_id {$c->institution_id}) to william-f-dunne\n";
    }
}
if (! $moved) { echo "  no 1927 case on bill-dunne (already split).\n"; }

// --- 3) Clean the anarchist record back to just Bill Dunne. ---
$anarch->name = "Bill Dunne";
$anarch->aka = null;
$anarch->ideologies = ["Anarchism"];
$anarch->affiliation = [];
$anarch->in_custody = true;
$anarch->released = false;
$anarch->description = trim(file_get_contents(base_path("database/data/bill-dunne-anarchist-bio.txt")));
$anarch->save();
echo "bill-dunne cleaned (anarchist only).\n";

$wf->refresh(); $anarch->refresh();
echo "\nWilliam F. Dunne: born ".($wf->partialDateIso("birthdate") ?? "-").", died ".($wf->partialDateIso("death_date") ?? "-").", cases=".$wf->cases()->count()."\n";
echo "Bill Dunne (anarchist): born ".($anarch->partialDateIso("birthdate") ?? "-").", cases=".$anarch->cases()->count().", in_custody=".($anarch->in_custody ? "yes" : "no")."\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Bill Dunne / William F. Dunne split into two records."
