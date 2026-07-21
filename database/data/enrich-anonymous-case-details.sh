#!/usr/bin/env bash
#
# Two follow-ups for the Anonymous cases, both from documented sources:
#
# 1. Correct James E. Robinson's incarceration date. The stored 2018-10-23
#    matches no documented event; his FBI arrest with remand-without-bail
#    (continuous federal custody) was 2018-05-10.
#
# 2. Fill the empty sentence / sentenced-date / convicted fields and upgrade
#    the bare "Hacking" charge placeholders to accurate charges, for the
#    cases whose details the research pinned down:
#      Mettenbrink, Guzner, Salinas, Ochoa III, Borell III.
#
# Idempotent: each field is only written when it is still empty (or, for
# charges, still the bare "Hacking"/"Hacking Fraud" placeholder), and the
# Robinson incarceration date is only changed when it is still 2018-10-23.
#
# Run from the repo root:
#   bash database/data/enrich-anonymous-case-details.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$fld = function ($c, $col, $val, $mode, $old = null) {
    $cur = $c->{$col};
    if ($cur instanceof \Carbon\Carbon) { $cur = $cur->format("Y-m-d"); }
    $cur = $cur === null ? "" : (string) $cur;
    $do = false;
    if ($mode === "empty")       { $do = ($cur === ""); }
    elseif ($mode === "placeholder") { $do = ($cur === "" || in_array($cur, ["Hacking", "Hacking Fraud"], true)); }
    elseif ($mode === "from")    { $do = ($cur === $old); }
    if ($do && $cur !== (string) $val) { $c->{$col} = $val; echo "    {$col} <- " . mb_substr($val, 0, 44) . "\n"; }
};

$one = function ($slug, callable $apply) {
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "{$slug}: NOT FOUND\n"; return; }
    $c = $p->cases()->first();
    if (! $c) { echo "{$slug}: no case\n"; return; }
    echo "{$slug}:\n";
    $apply($c);
    if ($c->isDirty()) { $c->save(); echo "    saved\n"; } else { echo "    nothing to do\n"; }
};

$one("james-e-robinson", function ($c) use ($fld) {
    $fld($c, "incarceration_date", "2018-05-10", "from", "2018-10-23");
    $fld($c, "sentenced_date", "2019-10-03", "empty");
});

$one("brian-thomas-mettenbrink", function ($c) use ($fld) {
    $fld($c, "charges", "Misdemeanor unauthorized access of a protected computer — 2008 Project Chanology DDoS on Church of Scientology websites", "placeholder");
    $fld($c, "sentence", "12 months (one year) in federal prison plus \$20,000 restitution and one year of supervised release", "empty");
    $fld($c, "convicted", "Yes — guilty plea", "empty");
    $fld($c, "sentenced_date", "2010-05-24", "empty");
});

$one("dmitriy-guzner", function ($c) use ($fld) {
    $fld($c, "charges", "Unauthorized impairment of a protected computer — January 2008 Project Chanology DDoS on Church of Scientology websites", "placeholder");
    $fld($c, "sentence", "366 days (a year and a day) in federal prison plus two years probation and \$37,500 restitution", "empty");
    $fld($c, "convicted", "Yes — guilty plea", "empty");
    $fld($c, "sentenced_date", "2009-11-18", "empty");
});

$one("fidel-salinas", function ($c) use ($fld) {
    $fld($c, "charges", "Misdemeanor computer fraud (18 U.S.C. § 1030), reduced from 44 felony counts — hacking and cyberstalking tied to the Hidalgo County website", "placeholder");
    $fld($c, "sentence", "Six months in federal prison plus a \$10,600 fine and restitution to Hidalgo County", "empty");
    $fld($c, "convicted", "Yes — guilty plea (single misdemeanor count, reduced from 44 felony counts)", "empty");
    $fld($c, "sentenced_date", "2015-02-02", "empty");
});

$one("higinio-ochoa-iii", function ($c) use ($fld) {
    $fld($c, "charges", "Accessing a protected computer without authorization (18 U.S.C. § 1030) — February 2012 breaches of Texas DPS, Alabama DPS, Houston County (AL), and the West Virginia Chiefs of Police Association", "placeholder");
    $fld($c, "sentence", "27 months in federal prison plus three years supervised release and \$14,062.17 restitution", "empty");
    $fld($c, "convicted", "Yes — guilty plea", "empty");
    $fld($c, "sentenced_date", "2012-08-24", "empty");
});

$one("john-anthony-borell-iii", function ($c) use ($fld) {
    $fld($c, "charges", "Five counts of computer intrusion (18 U.S.C. § 1030) — 2011–2012 DDoS and hacks of the Utah Chiefs of Police Association, Salt Lake City PD, Syracuse (NY), Springfield (MO), and pendletonunderground.com", "placeholder");
    $fld($c, "sentence", "36 months (three years) in federal prison plus three years supervised release and over \$227,000 restitution", "empty");
    $fld($c, "convicted", "Yes — guilty plea to five counts", "empty");
    $fld($c, "sentenced_date", "2013-09-11", "empty");
});

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Anonymous case details enriched."
