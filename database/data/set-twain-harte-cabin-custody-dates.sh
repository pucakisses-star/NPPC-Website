#!/usr/bin/env bash
#
# Custody dates for the other three Twain Harte cabin defendants (Sam Coleman
# was handled separately and is already exact). All four were captured at the
# cabin on August 27, 1953, convicted April 26, 1954 and sentenced May 3, 1954;
# their individual PRETRIAL bail-release dates are unknown, so incarceration is
# recorded from sentencing (May 3, 1954), NOT continuously from the arrest.
# arrest_date is kept as a separate field and does not imply continuous custody.
#
# Post-conviction release, by documented confidence (site owner's research):
#   Shirley Kremen    bounded  — out on appeal bail by July 19, 1954 (exact day
#                               not found) -> release left blank, bound in notes
#   Carl Ross         bounded  — served his 2-year term; released by June 19,
#                               1956 (exact day not found) -> blank, bound noted
#   Sidney Steinberg  probable — could not post the $75,000 bond; released
#                               probably June 1956 after Douglas cut the CA bond
#                               to $10,000 -> recorded month-precision June 1956
#
# All three convictions were reversed by the Supreme Court in Kremen v. United
# States, 353 U.S. 346 (1957). Idempotent. Run from the repo root:
#   bash database/data/set-twain-harte-cabin-custody-dates.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$reversal = " Conviction reversed by the U.S. Supreme Court in Kremen v. United States, 353 U.S. 346 (1957), over the FBI\u{2019}s warrantless seizure of the cabin\u{2019}s contents.";

// slug => [sentence-note, release-parts|null]  (null = leave release blank)
$updates = [
    "shirley-kremen" => [
        "One year. Released on appeal bail sometime between May 3 and July 19, 1954 (a July 19, 1954 defense report listed the other defendants as still jailed for want of an appeal bond but not her); exact date unconfirmed." . $reversal,
        null,
    ],
    "carl-ross" => [
        "Two years. Unable to raise his appeal bond, he served the sentence and was released upon its completion by June 19, 1956 (the Supreme Court petition of that date noted he had finished his term); exact day unconfirmed, and federal good-time credit may have produced an earlier discharge. He was not a petitioner in the 1957 Supreme Court case.",
        null,
    ],
    "sidney-steinberg" => [
        "Three years. Held on a \$75,000 appeal bond (granted May 25, 1954) that he could not post; Justice William O. Douglas cut the California bond to \$10,000 on May 28, 1956, and he was probably released in June 1956 (exact day unconfirmed; latest possible sentence expiration about September 19, 1956)." . $reversal,
        [1956, 6, null],   // probable month
    ],
];

foreach ($updates as $slug => $u) {
    [$note, $rel] = $u;
    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) { echo "MISSING prisoner: {$slug}\n"; continue; }

    // Bio tidy for the two seed records that still carry the vague month and the
    // truncated fugitive name (Steinberg bio was already corrected upstream).
    if (! empty($p->description)) {
        $bio = str_replace(
            ["Sidney Stein ", "harboring in April 1954"],
            ["Sidney Steinberg ", "harboring on April 26, 1954"],
            $p->description
        );
        if ($bio !== $p->description) { $p->description = $bio; }
    }
    $p->in_custody = false;
    $p->released = true;
    $p->save();

    $c = $p->cases()->first();
    if (! $c) {
        $c = new \App\Models\PrisonerCase();
        $c->prisoner_id = $p->id;
        $c->charges = "Harboring Communist Smith Act fugitives";
    }
    $c->convicted = "Yes — jury verdict April 26, 1954";
    $c->sentence = $note;
    $c->setPartialDate("arrest_date", 1953, 8, 27);
    $c->setPartialDate("sentenced_date", 1954, 5, 3);
    $c->setPartialDate("incarceration_date", 1954, 5, 3);
    if ($rel === null) {
        $c->setPartialDate("release_date", null);            // bounded -> leave blank
    } else {
        $c->setPartialDate("release_date", $rel[0], $rel[1] ?? null, $rel[2] ?? null);
    }
    $c->save();

    echo "{$slug}: inc ".($c->partialDateIso("incarceration_date") ?? "-")
        ." -> rel ".($c->partialDateIso("release_date") ?? "(blank)")
        ." (".($c->imprisoned_for_days === null ? "days n/a" : "{$c->imprisoned_for_days} days").")\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Twain Harte cabin custody dates set (Kremen, Ross, Steinberg)."
