#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/audit_in_custody_status_4.sh
#
# Audit batch #4:
#   - Luke O'Donovan: released July 25, 2016 from Washington State Prison
#     (Georgia, 2-yr sentence served + 8 yrs probation; banished from
#     Georgia except Scriven County during probation, which has now
#     ended).
#   - Sonia Anayibe Rojas Valderrama (FARC): released August 17, 2018
#     from US federal prison after 11 years served on a 16-year drug
#     trafficking sentence (200-mo sentence imposed 2007 in D.D.C.,
#     reduced to 16 yrs with credits); deported to Colombia.
set -e

php artisan tinker --execute='
$released = [
    "Luke O Donovan"            => "2016-07-25",
    "Sonia Anayibe Rojas"       => "2018-08-17",
];

foreach ($released as $name => $releaseDate) {
    $p = App\Models\Prisoner::where("name", $name)
        ->orWhere("name", "like", "%{$name}%")
        ->first();
    if (!$p) {
        echo "skip: prisoner {$name} not found\n";
        continue;
    }
    $p->in_custody          = false;
    $p->released            = true;
    $p->imprisoned_or_exiled = false;
    $p->saveQuietly();

    foreach ($p->cases as $case) {
        if (! $case->release_date) {
            $case->release_date = $releaseDate;
            $case->save();
            echo "fixed: {$name} (case id={$case->id}) release_date={$releaseDate}; imprisoned_for_days={$case->imprisoned_for_days}\n";
        }
    }
}
echo "\nBatch 4 done.\n";
'
echo
echo "Audit batch #4 complete."
