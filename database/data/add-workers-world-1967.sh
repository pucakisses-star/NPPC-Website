#!/usr/bin/env bash
#
# Mines Workers World vol. 9 no. 23 (November 16, 1967) — also registered
# in the site archive by archive:add-workers-world-1967.
#
# Already present: Martin Sostre, Amiri Baraka (the LeRoi Jones Newark
# conviction), H. Rap Brown, Huey Newton. Not added: Edward R. Lynn (the
# San Diego Naval Hospital corpsman facing UCMJ charges for organizing
# the 35-signature discrimination statement — no confinement could be
# verified) and John Prince (Black draft refuser whose trials ended in
# hung juries — never convicted or jailed per the issue).
#
# Adds Edward Oquendo, with his captioned Workers World photo.
#
# Idempotent: prisoner:add refuses duplicates; photo attach fill-if-empty.
#
# Run from the repo root:  bash database/data/add-workers-world-1967.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{"name":"Edward Oquendo","first_name":"Edward","last_name":"Oquendo","aka":"Ed Oquendo","description":"Edward Oquendo was a young Black anti-war activist, a member of Blacks Against Negative Dying and of Youth Against War & Fascism, who publicly refused to serve in what he argued was an unconstitutional war of aggression in Vietnam. Tried in Brooklyn federal court before Judge J.C. Zavatt for refusing to report for the draft, he was convicted on November 6, 1967 by a jury of eleven grey-haired whites and one middle-aged Black juror — empaneled at twenty minutes to three and back with a guilty verdict in fifteen minutes. His attorney Conrad Lynn'"'"'s legal brief, which the court rejected, argued that using the draft to raise armies of aggression violates the Constitution, and quoted Ho Chi Minh'"'"'s 1966 New Year message to the American people. Supporters picketed the courthouse throughout the trial, and the judge barred courthouse employees from the picket-line area, locked the courtroom doors and hammered the jury with a directive charge. Sentencing was set for December 15, 1967, with the penalty up to five years; the sentence he received has not been located. Reconstructed from Workers World, November 16, 1967.","state":"New York","race":"Black","gender":"Male","ideologies":["Anti-war","Black liberation"],"affiliation":["Youth Against War & Fascism"],"era":"1960s","released":true,"cases":[{"charges":"Refusing to report for the draft (Selective Service Act) — tried in Brooklyn federal court before Judge J.C. Zavatt","convicted":"Yes — convicted November 6, 1967; the jury deliberated fifteen minutes","sentence":"Faced up to five years; sentencing was set for December 15, 1967, and the sentence imposed has not been located"}]}' || true

php artisan tinker --execute='
\Illuminate\Support\Facades\Storage::disk("public")->makeDirectory("prisoners");
$p = \App\Models\Prisoner::withUnderReview()->where("slug", "edward-oquendo")->first();
if ($p && empty($p->photo) && is_file(database_path("data/photos/nonfree/edward-oquendo.jpg"))) {
    \Illuminate\Support\Facades\Storage::disk("public")->put("prisoners/edward-oquendo.jpg", (string) file_get_contents(database_path("data/photos/nonfree/edward-oquendo.jpg")));
    $p->photo = "prisoners/edward-oquendo.jpg";
    $p->save();
    echo "PHOTO edward-oquendo\n";
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

php artisan archive:add-workers-world-1967

echo
echo "Done. Workers World November 1967 applied."
