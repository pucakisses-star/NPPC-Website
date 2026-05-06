#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/fix_smith_act_dates.sh
#
# Two Smith Act-era prisoners had open-ended cases in the database
# (incarceration_date set but no release_date), so the booted hook
# computed imprisoned_for_days from their arrest to today, producing
# absurd 70+-year totals on the public site:
#   - Walter Lowenfels: showed ~72 years 9 months 29 days
#   - John Gates:        showed ~74 years 10 months 7 days
#
# This script sets the correct release_date on each and adds
# John Gatess death date (which was missing, leading the page to
# compute his age as 112).
#
# Walter Lowenfels (Philadelphia Smith Act 9):
#   - Arrested July 28, 1953 in Philadelphia; held briefly, released
#     on bond. Convicted July 31, 1954, sentenced to 20 years and
#     $10,000 fine; remained on bond pending appeal. Third Circuit
#     reversed the conviction December 28, 1956 in light of Yates-
#     era retreat from Smith Act prosecutions; charges dismissed.
#   - Died July 7, 1976.
#   - Source: Lowenfels v. United States, 240 F.2d 632 (3d Cir. 1956)
#
# John Gates (Foley Square 11):
#   - Born September 28, 1913 (real name Solomon Regenstreif). Editor
#     of the Daily Worker; member of the CPUSA national board.
#   - Indicted July 20, 1948 with the other 11 CPUSA leaders in the
#     original Smith Act prosecution. Convicted October 14, 1949
#     (Foley Square trial). Sentenced to 5 years federal prison and
#     a $10,000 fine. Conviction affirmed in Dennis v. United States,
#     341 U.S. 494 (1951).
#   - Reported to federal prison on July 2, 1951; paroled March 1,
#     1955 after 3 years 8 months served.
#   - Resigned from the CPUSA in 1958 over the Khrushchev secret
#     speech and the Soviet invasion of Hungary.
#   - Died May 23, 1992 in New York at age 78.
set -e

php artisan tinker --execute='
// ─── Walter Lowenfels ──────────────────────────────────────────
$wl = App\Models\Prisoner::where("name", "Walter Lowenfels")->first();
if ($wl) {
    if (!$wl->death_date) {
        $wl->death_date = "1976-07-07";
        $wl->in_custody = false;
        $wl->released   = true;
        $wl->save();
        echo "Walter Lowenfels: set death_date=1976-07-07.\n";
    }
    foreach ($wl->cases as $case) {
        if (!$case->release_date) {
            $case->release_date = "1956-12-28";
            $case->save();
            echo "  case id={$case->id}: release_date=1956-12-28; imprisoned_for_days={$case->imprisoned_for_days}\n";
        }
    }
} else {
    echo "Walter Lowenfels not found.\n";
}

// ─── John Gates ────────────────────────────────────────────────
$jg = App\Models\Prisoner::where("name", "John Gates")
    ->orWhere("name", "like", "%John Gates%")
    ->orWhere("name", "like", "%Solomon Regenstreif%")
    ->first();
if ($jg) {
    $dirty = false;
    if (!$jg->birthdate)   { $jg->birthdate   = "1913-09-28"; $dirty = true; }
    if (!$jg->death_date)  { $jg->death_date  = "1992-05-23"; $dirty = true; }
    if ($jg->in_custody)   { $jg->in_custody  = false;        $dirty = true; }
    if (!$jg->released)    { $jg->released    = true;         $dirty = true; }
    if ($dirty) {
        $jg->save();
        echo "John Gates: set birthdate=1913-09-28, death_date=1992-05-23.\n";
    }
    foreach ($jg->cases as $case) {
        $changed = false;
        if (!$case->incarceration_date || $case->incarceration_date->toDateString() !== "1951-07-02") {
            $case->incarceration_date = "1951-07-02";
            $changed = true;
        }
        if (!$case->release_date) {
            $case->release_date = "1955-03-01";
            $changed = true;
        }
        if ($changed) {
            $case->save();
            echo "  case id={$case->id}: incarceration_date=1951-07-02, release_date=1955-03-01; imprisoned_for_days={$case->imprisoned_for_days}\n";
        }
    }
} else {
    echo "John Gates not found.\n";
}
'

echo
echo "Smith Act date fix complete."
