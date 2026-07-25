#!/usr/bin/env bash
#
# Cap the imprisonment length of every "open" prisoner case -- one with an
# incarceration date but no release date, on a prisoner who is NOT currently in
# custody -- at its real end, instead of the two wrong extremes it has swung
# between (counting to today, or 0 after the release date was nulled).
#
# Two layers, tried in order per case:
#   1. Sentence term: a short, definite, fully-served sentence in the case text
#      ("6 months", "1 year 1 day", "reduced to 2 years") -> release = the
#      incarceration date plus that term. Long / ranged / commuted / bail /
#      probation phrasings are deliberately NOT parsed here.
#   2. Documented cohort release: for the mass-imprisonment groups whose imposed
#      sentences were mostly commuted / paroled early, apply the researched
#      release date keyed on institution + incarceration year:
#        - Arkansas State Penitentiary 1919-1921  -> 1925-01-13  (Elaine massacre defendants, freed by Jan 1925)
#        - San Quentin 1919-1927                  -> 1925        (CA Criminal Syndicalism Act paroles/pardons, mid-1920s)
#        - US Disciplinary Barracks Leavenworth / Fort Douglas / McNeil 1917-1921 -> 1920 (WWI conscientious objectors)
#        - USP Leavenworth / Leavenworth Pen / federal 1917-1923 -> 1923-06 (IWW & Espionage Act, 1923 amnesty)
#        - Fort Delaware / Warren / McHenry <=1862 -> 1862-11-27 ; 1863-1865 -> 1865-06 (Civil War detainees)
#        - Boston Jail / Sedition Act era 1798-1802 -> 1801
#        - WWI-era (any) with espionage/sedition/syndicalism/CO wording 1917-1921 -> 1923-06
#
# Every computed release is capped at the person death date (recorded as a death
# in custody when they died before that release) and never runs past today.
# Cases that neither layer resolves are left null and REPORTED, not guessed.
#
# Idempotent: only touches cases whose release_date is still null; once capped
# they are skipped. Prints every change. Run from the repo root:
#   bash database/data/cap-open-case-lengths.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Carbon\Carbon;

$today = Carbon::today();

$wordToNum = function (string $w): ?int {
    $map = ["one"=>1,"two"=>2,"three"=>3,"four"=>4,"five"=>5,"six"=>6,"seven"=>7,
        "eight"=>8,"nine"=>9,"ten"=>10,"eleven"=>11,"twelve"=>12,"thirteen"=>13,
        "fourteen"=>14,"fifteen"=>15,"eighteen"=>18,"twenty"=>20,"thirty"=>30,
        "sixty"=>60,"ninety"=>90];
    $w = strtolower(trim($w));
    if (ctype_digit($w)) { return (int) $w; }
    return $map[$w] ?? null;
};

// Returns ["years"=>int,"months"=>int,"days"=>int] or null.
$parseTerm = function (string $sentence) use ($wordToNum): ?array {
    $s = strtolower(trim($sentence));
    if ($s === "") { return null; }

    // "reduced to N years/months" -> the actual served term; trust it first.
    if (preg_match("/reduced to (\\d+|[a-z]+)\\s+(year|month|day)/", $s, $m)) {
        $n = $wordToNum($m[1]);
        if ($n !== null) {
            if ($m[2] === "year")  { return ["years"=>$n,"months"=>0,"days"=>0]; }
            if ($m[2] === "month") { return ["years"=>0,"months"=>$n,"days"=>0]; }
            return ["years"=>0,"months"=>0,"days"=>$n];
        }
    }

    // Disqualify vague / range / early-release / non-custodial phrasings.
    foreach ([" to ","-","up to","suspended","probation","pending","appeal",
        "bail","commut","parole","not specified","not available","not stated",
        "typical","within the documented","same","multiple","brief","cite",
        "no conviction","no prison","denied","awaiting","death","at least",
        "roughly","about","several","data not"] as $bad) {
        if (str_contains($s, $bad)) { return null; }
    }

    if (preg_match("/(\\d+|[a-z]+)\\s+year/", $s, $m)) {
        $y = $wordToNum($m[1]);
        if ($y !== null && $y <= 3) {
            $days = preg_match("/(1|one)\\s+day/", $s) ? 1 : 0;
            return ["years"=>$y,"months"=>0,"days"=>$days];
        }
        return null;
    }
    if (preg_match("/(\\d+|[a-z]+)\\s+month/", $s, $m)) {
        $mo = $wordToNum($m[1]);
        if ($mo !== null && $mo <= 30) { return ["years"=>0,"months"=>$mo,"days"=>0]; }
        return null;
    }
    if (preg_match("/(\\d+|[a-z]+)\\s+day/", $s, $m)) {
        $d = $wordToNum($m[1]);
        if ($d !== null && $d <= 400) { return ["years"=>0,"months"=>0,"days"=>$d]; }
    }
    return null;
};

// Returns [isoDate, basis] or null.
$cohortRelease = function (?string $inst, int $year, string $sl): ?array {
    $inst = (string) $inst;
    if ($inst === "Arkansas State Penitentiary" && $year >= 1918 && $year <= 1922) {
        return ["1925-01-13", "Elaine massacre defendants freed by Jan 1925 (Gov. McRae furlough)"];
    }
    if ($inst === "San Quentin State Prison" && $year >= 1919 && $year <= 1927) {
        return ["1925", "CA Criminal Syndicalism Act prisoners paroled/pardoned mid-1920s (approx.)"];
    }
    if (in_array($inst, ["United States Disciplinary Barracks, Fort Leavenworth","Fort Douglas War Prison Barracks","USP McNeil Island"], true) && $year >= 1917 && $year <= 1921) {
        return ["1920", "WWI conscientious objectors released by 1920 (approx.)"];
    }
    if (in_array($inst, ["USP Leavenworth","Leavenworth Penitentiary","Federal prison","Federal Bureau of Prisons"], true) && $year >= 1917 && $year <= 1923) {
        return ["1923-06", "IWW / Espionage Act prisoners freed by the 1923 amnesty (approx.)"];
    }
    if (in_array($inst, ["Fort Delaware","Fort Warren","Fort McHenry"], true)) {
        if ($year <= 1862) { return ["1862-11-27", "Maryland civilian detainees released Nov 1862 on loyalty oath"]; }
        if ($year >= 1863 && $year <= 1866) { return ["1865-06", "Civil War detainees released at war end, 1865 (approx.)"]; }
    }
    if ($inst === "Boston Jail" && $year >= 1798 && $year <= 1802) {
        return ["1801", "Sedition Act prisoners freed by the Act expiry, Mar 1801 (approx.)"];
    }
    // Era fallbacks (any institution, incl. none).
    if ($year >= 1798 && $year <= 1802) {
        return ["1801", "Sedition Act era release, ~1801 (approx.)"];
    }
    if ($year >= 1917 && $year <= 1921 && preg_match("/espionage|sedition|syndicalism|iww|wobbl|conscientious|draft/", $sl)) {
        return ["1923-06", "WWI political prisoners freed by the 1923 amnesty (approx.)"];
    }
    return null;
};

$isoParts = function (string $iso): array {
    // returns [year, month|null, day|null]
    $len = strlen($iso);
    $y = (int) substr($iso, 0, 4);
    if ($len === 4) { return [$y, null, null]; }
    if ($len === 7) { return [$y, (int) substr($iso, 5, 2), null]; }
    return [$y, (int) substr($iso, 5, 2), (int) substr($iso, 8, 2)];
};

$rank = ["year"=>1, "month"=>2, "day"=>3];

$capped = 0; $bySentence = 0; $byCohort = 0; $diedInCustody = 0; $unresolved = 0;
$unresolvedList = [];

foreach (Prisoner::withoutGlobalScopes()->with(["cases.institution"])->get() as $p) {
    if ($p->in_custody || $p->awaiting_trial) { continue; }

    foreach ($p->cases as $c) {
        if (! $c->incarceration_date || $c->release_date) { continue; }

        $incIso = $c->partialDateIso("incarceration_date");
        $year = (int) substr((string) $incIso, 0, 4);
        $sentence = (string) $c->sentence;
        $sl = strtolower($sentence);
        $incPrecision = $c->datePrecisionFor("incarceration_date");

        $relY = null; $relM = null; $relD = null; $basis = null; $method = null;

        $term = $parseTerm($sentence);
        if ($term !== null) {
            $inc = Carbon::parse($c->incarceration_date);
            $rel = $inc->copy()->addYears($term["years"])->addMonths($term["months"])->addDays($term["days"]);
            $termGran = $term["days"] > 0 ? "day" : ($term["months"] > 0 ? "month" : "year");
            $gran = $rank[$incPrecision] >= $rank[$termGran] ? $incPrecision : $termGran;
            $relY = (int) $rel->year;
            $relM = $rank[$gran] >= 2 ? (int) $rel->month : null;
            $relD = $rank[$gran] >= 3 ? (int) $rel->day : null;
            $basis = "sentence term"; $method = "sentence"; $bySentence++;
        } else {
            $co = $cohortRelease($c->institution?->name, $year, $sl);
            if ($co !== null) {
                [$ry, $rm, $rd] = $isoParts($co[0]);
                $relY = $ry; $relM = $rm; $relD = $rd;
                $basis = $co[1]; $method = "cohort"; $byCohort++;
            }
        }

        if ($relY === null) {
            $unresolved++;
            $unresolvedList[] = "  ".$p->slug." | ".$p->name." | inc ".($incIso ?? "?")." | ".($sentence !== "" ? substr($sentence, 0, 70) : "(no sentence)");
            continue;
        }

        // Guard against a release at or before incarceration.
        $relCarbon = Carbon::parse(sprintf("%04d-%02d-%02d", $relY, $relM ?: 1, $relD ?: 1));
        if ($relCarbon->lessThanOrEqualTo(Carbon::parse($c->incarceration_date))) {
            $unresolved++;
            $unresolvedList[] = "  ".$p->slug." | ".$p->name." | computed release not after incarceration -- skipped";
            continue;
        }

        // Death cap: if the person died before the computed release, they died in custody.
        if (! empty($p->death_date)) {
            $death = Carbon::parse($p->death_date);
            if ($relCarbon->greaterThan($death)) {
                $dp = $isoParts($p->death_date);
                $c->setPartialDate("death_in_custody_date", $dp[0], $dp[1], $dp[2]);
                $c->save(); // hook mirrors death_in_custody_date -> release_date and caps days
                $diedInCustody++; $capped++;
                echo "DIED   ".$p->slug." | ".$p->name." | died in custody ".$p->death_date." (".$method.")\n";
                continue;
            }
        }

        // A computed release in the future means the record is likely still
        // being served or is a data gap -- do not assert a release, report it.
        if ($relCarbon->greaterThan($today)) {
            $unresolved++;
            $unresolvedList[] = "  ".$p->slug." | ".$p->name." | computed release ".sprintf("%04d", $relY)." is in the future -- left null";
            continue;
        }

        $c->setPartialDate("release_date", $relY, $relM, $relD);
        if (! $p->released) { $p->released = true; }
        $c->save();
        $capped++;
        $newDays = $c->imprisoned_for_days;
        $yrs = $newDays !== null ? round($newDays / 365.25, 1) : 0;
        echo "CAP    ".$p->slug." | ".$p->name." | -> ".$c->partialDateIso("release_date")." (".$yrs." yrs) [".$method.": ".$basis."]\n";
    }

    if ($p->released && $p->isDirty()) { $p->save(); }
}

echo "\n=== Summary ===\n";
echo "Capped total: {$capped}\n";
echo "  from sentence term: {$bySentence}\n";
echo "  from cohort release: {$byCohort}\n";
echo "  recorded as died in custody: {$diedInCustody}\n";
echo "Unresolved (left null, need manual review): {$unresolved}\n";
if ($unresolvedList) {
    echo "\nUnresolved cases:\n".implode("\n", array_slice($unresolvedList, 0, 400))."\n";
    if (count($unresolvedList) > 400) { echo "  ... and ".(count($unresolvedList) - 400)." more\n"; }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done. Open-case imprisonment lengths capped."
