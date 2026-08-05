#!/usr/bin/env bash
#
# BATCH 164 -- a biography for Mike McCorkle on the staff page.
#
#   Written from his professional profile at the Cadiz Law Firm, after
#   the curator confirmed the identification. That confirmation was
#   necessary: the name returns at least four people — a candidate for
#   office in Kansas, this attorney, the founder of Capitol Hill Law,
#   and several unconnected social accounts — and none of them is
#   linked to NPPC in any public source.
#
#   WHAT IS DELIBERATELY LEFT OUT. His firm page ends with the
#   neighbourhood he lives in and his wife's name. Neither belongs on a
#   staff page, and least of all on the staff page of an organisation
#   whose subject is political prosecution: a home neighbourhood and a
#   spouse's name are exactly the details not to volunteer about
#   someone who represents political defendants. Only professional
#   facts are used.
#
#   HIS OWN WORDS WOULD BE BETTER. This is assembled from a law-firm
#   profile written for a different purpose, so it describes his
#   commercial practice rather than what he does for NPPC — which is
#   what a reader of this page actually wants. Show it to him and
#   replace it with whatever he would rather say.
#
#   NOTHING IS WRITTEN FOR BRIAN MULHEARN. No public professional
#   footprint for him could be found, and inventing one is not an
#   option. His entry keeps its empty biography until he supplies
#   something.
#
#   Idempotent, and it will not overwrite a biography that already
#   exists — if someone has written one in the meantime, this reports
#   and leaves it.
#
# Run from the repo root, after git pull (after batch 163):
#   bash database/data/run-batch-164.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"; shift
    echo; echo "--- ${label}"
    if "$@"; then return 0; fi
    echo "  !! FAILED: ${label}"; FAILED+=("${label}"); return 0
}

echo "==================================================================="
echo "  Batch 164 — staff biography: Mike McCorkle"
echo "==================================================================="

set_bio() {
    php artisan tinker --execute='
use App\Models\Staff;
use Illuminate\Support\Facades\File;

$payload = json_decode(File::get(base_path("database/data/fixes/batch164.json")), true);

if (! $payload) { echo "Could not read the payload — nothing changed.\n"; return; }

$s = Staff::where("name", $payload["name"])->first();

if (! $s) { echo "  ", $payload["name"], " NOT FOUND on the staff list — nothing changed.\n"; return; }

echo "  ", $s->name, "  (", ($s->position ?: "no position"), ", group ", ($s->group ?: "-"), ")\n";
echo "  biography before: ", (trim((string) $s->about) === "" ? "(empty)" : strlen($s->about)." chars"), "\n";

if (trim((string) $s->about) !== "" && $s->about !== $payload["about"]) {
    echo "  a biography already exists and is not the one in this payload — left alone.\n";
    echo "  ", wordwrap($s->about, 84, "\n  "), "\n";

    return;
}

$s->about = $payload["about"];
if (empty($s->position)) { $s->position = $payload["position"]; }
$s->save();
$s->refresh();

echo "  biography after:  ", strlen($s->about), " chars\n\n";
echo "  ", wordwrap($s->about, 84, "\n  "), "\n";

echo "\n  STAFF ENTRIES STILL WITHOUT A BIOGRAPHY\n";

$blank = Staff::where("published", true)
    ->get()
    ->filter(fn ($x) => trim((string) $x->about) === "");

echo "  ", $blank->count(), "\n";

foreach ($blank as $b) {
    echo "    ", str_pad($b->name, 26), ($b->position ?: "-"), "   group ", ($b->group ?: "-"), "\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'
}

run "mccorkle-bio" set_bio

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then echo "  Batch 164 applied. No failures."
else echo "  Finished with ${#FAILED[@]} failed step(s):"; for f in "${FAILED[@]}"; do echo "    - ${f}"; done; fi
echo "==================================================================="
