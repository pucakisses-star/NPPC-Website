#!/usr/bin/env bash
#
# Add "Fort Worth Five" as an affiliation and tag the five men who were in it.
#
# The Fort Worth Five were five Irish-American men jailed in Texas in 1972 for
# refusing to testify before a federal grand jury, sitting at Fort Worth, that
# was investigating Irish Republican Army gun-running. All five are already in
# the database, all five carry the ideologies Irish Republicanism and
# Anti-Imperialism, and NONE of them had an affiliation at all -- so nothing on
# the site connected them to each other:
#
#   Daniel Crawford      /prisoner/daniel-crawford
#   Thomas Laffey        /prisoner/thomas-laffey
#   Paschal Morahan      /prisoner/paschal-morahan
#   Matthias Reilly      /prisoner/matthias-reilly
#   Kenneth Tierney      /prisoner/kenneth-tierney
#
# The tag follows the pattern already used for group cases like "Prairieland
# Defendants" -- affiliations are free-form strings on the model, with no
# canonical list to register a new one in.
#
# HOW MEMBERS ARE FOUND. Not from the hardcoded list alone. The script selects
# every prisoner whose biography names the Fort Worth Five, and cross-checks
# that set against the five slugs above. If the two disagree -- someone new
# whose bio mentions the group, or one of the five whose bio has been rewritten
# -- it says so rather than silently tagging its own guess. Existing
# affiliations are preserved; the tag is appended.
#
# Idempotent. Run from the repo root:
#   bash database/data/tag-fort-worth-five.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;

$tag = "Fort Worth Five";

$expected = [
    "daniel-crawford",
    "thomas-laffey",
    "paschal-morahan",
    "matthias-reilly",
    "kenneth-tierney",
];

// Everyone whose biography names the group, however they got there.
$found = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->where("description", "like", "%Fort Worth Five%")
        ->orWhereIn("slug", $expected))
    ->orderBy("name")
    ->get();

$foundSlugs = $found->pluck("slug")->all();
$missing = array_diff($expected, $foundSlugs);
$extra = array_diff($foundSlugs, $expected);

if ($missing) {
    echo "WARNING: expected but not found: ".implode(", ", $missing)."\n\n";
}
if ($extra) {
    echo "NOTE: these also name the Fort Worth Five in their biography and are being tagged too:\n";
    foreach ($extra as $slug) { echo "  {$slug}\n"; }
    echo "\n";
}

$tagged = 0; $already = 0;
foreach ($found as $p) {
    $affs = is_array($p->affiliation) ? $p->affiliation : [];

    if (in_array($tag, $affs, true)) {
        echo "  already tagged  ".str_pad($p->name, 20)." [".implode(", ", $affs)."]\n";
        $already++;
        continue;
    }

    $before = $affs ? implode(", ", $affs) : "(none)";
    $affs[] = $tag;
    $p->affiliation = array_values($affs);
    $p->save();

    echo "  tagged          ".str_pad($p->name, 20)." {$before}  ->  ".implode(", ", $p->affiliation)."\n";
    $tagged++;
}

echo "\n".$found->count()." record(s) matched: {$tagged} newly tagged, {$already} already carried the tag.\n";
if ($found->count() !== 5) {
    echo "That is not five. Check the list above before trusting it.\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
