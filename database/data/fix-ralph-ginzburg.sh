#!/usr/bin/env bash
#
# Ralph Ginzburg -- corrected release date and facility.
#
# The record said he was paroled on OCTOBER 11, 1972, giving 237 days.
# The release was OCTOBER 10: February 17 to October 10 is 236 days,
# and it is also exactly the seven months and twenty-three days the
# corrected figure gives, which the 11th does not.
#
# THE FACILITY WAS TWO PLACES, not one. He was RECEIVED at the federal
# institution at Lewisburg, Pennsylvania and was SUBSEQUENTLY CONFINED
# at the Allenwood Federal Prison Camp -- Allenwood being the satellite
# camp of the Lewisburg penitentiary, which is why a single reference to
# either turns up in accounts of the case. The institution field holds
# Allenwood, where he served the sentence; the Lewisburg reception is
# recorded in the case text, since the field takes one place and the
# reception is not where he did the time.
#
# RELEASE TYPE: parole, already recorded and unchanged.
#
# This script corrects a record that already exists. The payload in
# add-free-expression-five.sh, which is what CREATES him, carries the
# same corrected dates, so a database that has not run that script yet
# gets it right on creation and this script then finds nothing to
# change. Both paths land in the same place.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-ralph-ginzburg.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;

$p = Prisoner::withoutGlobalScopes()->where("slug", "ralph-ginzburg")->with("cases")->first();
if (! $p) {
    echo "NOT FOUND: ralph-ginzburg\n";
    echo "Run database/data/add-free-expression-five.sh first -- that is what creates him,\n";
    echo "and its payload already carries these corrected dates.\n";
    exit(1);
}

$p->description = str_replace(
    [
        "served at the federal prison camp at Allenwood, Pennsylvania, and was paroled on October 11, 1972, after about eight months.",
        "and was paroled on October 11, 1972, after about eight months.",
    ],
    "was received at the federal institution at Lewisburg, Pennsylvania and then held at the Allenwood Federal Prison Camp, and was paroled on October 10, 1972, after seven months and twenty-three days.",
    (string) $p->description,
);
$p->in_custody = false;
$p->released = true;
$p->save();

$allenwood = Institution::firstOrCreate(
    ["name" => "Federal Prison Camp, Allenwood"],
    ["city" => "Allenwood", "state" => "Pennsylvania"],
);

$case = $p->cases->first() ?? $p->cases()->create([]);
$case->institution_id = $allenwood->id;
$case->sentence = "Five years and a \$42,000 fine, reduced to three years shortly before he surrendered. He entered custody on February 17, 1972, was RECEIVED at the federal institution at Lewisburg, Pennsylvania and subsequently confined at the Allenwood Federal Prison Camp — the satellite camp of the Lewisburg penitentiary, which is why accounts of the case name one or the other. He was released on parole on October 10, 1972 after 236 days, seven months and twenty-three days. The institution field records Allenwood, where the sentence was actually served. Nearly nine years passed between conviction and imprisonment while the appeals ran. An earlier version of this record gave the release as October 11 and the term as 237 days.";
$case->setPartialDate("incarceration_date", 1972, 2, 17);
$case->setPartialDate("release_date", 1972, 10, 10);
$case->save();

$p->refresh()->load("cases");
$c = $p->cases->first();
echo "Ralph Ginzburg  [{$p->slug}]\n";
echo "  incarcerated ".optional($c->incarceration_date)->toDateString()."   released ".optional($c->release_date)->toDateString()."   (expect 1972-02-17, 1972-10-10)\n";
echo "  imprisoned_for_days = ".($c->imprisoned_for_days ?? "null")."  (expect 236, was 237)\n";
echo "  institution ".($c->institution->name ?? "-")."\n";
echo "  bio still says October 11: ".(str_contains((string) $p->description, "October 11, 1972") ? "YES -- not fixed" : "no")."\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
