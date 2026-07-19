#!/usr/bin/env bash
#
# Audit of three supplied documents (July 2026):
#
#  1. The Militant, July 1, 1977 — archived; its US custody names (Morton
#     Sobell, the grand-jury resisters) are already present, and its other
#     prisoner coverage is international (Soweto, Spain) or out of scope
#     (Watergate figures).
#  2. Amnesty International Report 1978 excerpt — archived; its USA
#     section's people are all present, but the audit surfaced three
#     duplicate clusters, merged here: T.J. Reddy (two records), Anne
#     Sheppard Turner (two records), and Johnny Imani Harris (three
#     records). The AI observation detail is appended to the Skyhorse,
#     Mohawk and Harris records.
#  3. The CIA reading-room document could not be retrieved (cia.gov
#     redirects the direct PDF away) and the readkong page re-hosts the
#     NASC Skyhorse-Mohawk pamphlet already in the archive.
#
# Idempotent throughout.
#
# Run from the repo root:  bash database/data/add-militant-amnesty-1978.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoners:merge-duplicates --only=thomas-james-reddy,anne-sheppard-turner,johnny-imani-harris --apply

php artisan tinker --execute='
$find = fn (string $slug) => \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
$appendOnce = function ($p, string $marker, string $paragraph): void {
    if (! $p || str_contains((string) $p->description, $marker)) { return; }
    $p->description = trim((string) $p->description) . "\n\n" . $paragraph;
    $p->save();
    echo "DESC {$p->slug}\n";
};

$aiSkyhorse = "Amnesty International monitored the case throughout: Chilean lawyer Dr. Eugenio Velasco observed the trial on the organization'"'"'s behalf, and in July and October 1977 Amnesty wrote to Governor Jerry Brown, Senators Cranston and Hayakawa, and the Los Angeles County Jail warden over allegations that the two prisoners were denied adequate medical treatment and ill-treated in the Ventura and Los Angeles county jails (Amnesty International Report 1978).";
$appendOnce($find("paul-skyhorse"), "Eugenio Velasco", $aiSkyhorse);
$appendOnce($find("richard-mohawk"), "Eugenio Velasco", $aiSkyhorse);

$appendOnce($find("johnny-imani-harris"), "Urgent Action", "Amnesty International launched Urgent Actions in January and February 1978 against his scheduled March 10, 1978 execution — which would have been only the second in the United States since 1967 — and sent British lawyer Brian Wrobel to Alabama, where he met Harris on death row in Holman Prison, his lawyers, and Governor Wallace'"'"'s legal adviser (Amnesty International Report 1978).");

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

php artisan archive:add-militant-amnesty-1978

echo
echo "Done. Militant 1977 + Amnesty 1978 applied."
