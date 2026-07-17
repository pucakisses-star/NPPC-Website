#!/usr/bin/env bash
#
# Thin-record enrichment, second batch (July 2026): 20 more randomly sampled
# records lacking photos / birthdates / case detail / substantial
# descriptions, each researched. Verified additions:
#
#  - James Thornwell: full account of the 1961 Operation Third Chance LSD
#    interrogation, the $625,000 congressional award, and his June 1984
#    death (month-precision death date); first case row created.
#  - Reuben Ship: birth/death dates (Oct 18, 1915 - Aug 23, 1975) and the
#    HUAC appearance / Jan 12, 1953 deportation / "The Investigator" story.
#  - Adam Blackwell: co-defendants, the 2002 Richmond ELF actions, and the
#    January 12, 2004 guilty plea.
#  - Jane Moses: the July 12, 1957 Operation Alert arrest date.
#  - Jessica Lynn White: first case row (charges + dismissal).
#  - Case-field structuring from facts already in the descriptions: Dale
#    Bartell, Louis Murray, William Minton and F. Varella (the last two are
#    the same July 12, 1923 San Quentin criminal-syndicalism cohort).
#
# All updates fill-if-empty or guarded by content checks; idempotent.
#
# Run from the repo root:  bash database/data/enrich-thin-20b.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$find = fn (string $slug) => \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
$appendOnce = function ($p, string $marker, string $paragraph): bool {
    if (! $p || str_contains((string) $p->description, $marker)) { return false; }
    $p->description = trim((string) $p->description) . "\n\n" . $paragraph;
    $p->save();
    echo "DESC {$p->slug}\n";
    return true;
};
$fillCase = function ($p, array $fill): void {
    if (! $p) { return; }
    $case = $p->cases()->first();
    if (! $case) { return; }
    $changed = false;
    foreach ($fill as $f => $v) {
        if (! empty($case->{$f})) { continue; }
        $case->{$f} = $v;
        $changed = true;
    }
    if ($changed) { $case->save(); echo "CASE {$p->slug}\n"; }
};

// --- James Thornwell (Operation Third Chance, 1961) ------------------------
$p = $find("james-thornwell");
$appendOnce($p, "Operation Third Chance",
    "Thornwell, a private stationed in France, was held in solitary confinement in 1961 during an Army investigation into missing classified documents — kept without food, light or toilet facilities in an abandoned mill near Orl\u{00e9}ans — and, unknown to him, was secretly dosed with LSD by Army intelligence as part of the interrogation experiment Operation Third Chance. He was never charged and received an honorable discharge, and only learned of the drugging sixteen years later through the 1977 disclosures and his own Freedom of Information Act requests. After suing the government for $10 million, he received a private-bill award of $625,000 from Congress in 1980. He drowned in a swimming pool in June 1984, at 46, after years of seizures and depression he attributed to the drugging.");
if ($p) {
    if (empty($p->death_date)) {
        $p->death_date = "1984-06-01";
        $p->date_precision = array_merge($p->date_precision ?? [], ["death_date" => "month"]);
        $p->save();
        echo "SET james-thornwell death_date = June 1984 (month precision)\n";
    }
    if ($p->cases()->count() === 0) {
        $case = $p->cases()->create([
            "charges" => "Suspected of removing classified documents — never charged; held in solitary military confinement during the 1961 Army intelligence investigation in Orl\u{00e9}ans, France, during which he was secretly dosed with LSD (Operation Third Chance)",
            "arrest_date" => "1961-01-01",
            "convicted" => "No — never charged; honorably discharged",
        ]);
        $case->date_precision = ["arrest_date" => "year"];
        $case->save();
        echo "CASE created james-thornwell\n";
    }
}

// --- Reuben Ship ------------------------------------------------------------
$p = $find("reuben-ship");
if ($p) {
    $changed = false;
    if (empty($p->birthdate)) { $p->birthdate = "1915-10-18"; $changed = true; }
    if (empty($p->death_date)) { $p->death_date = "1975-08-23"; $changed = true; }
    if ($changed) { $p->save(); echo "SET reuben-ship dates\n"; }
}
$appendOnce($p, "The Investigator",
    "Ship, a radio writer best known for his work on The Life of Riley, was called before the House Un-American Activities Committee on September 24, 1951, where he invoked the Fifth Amendment and accused the committee of jailing people who wanted peace. Seized by immigration agents, he was deported in handcuffs to his native Canada on January 12, 1953. The next year he answered with his celebrated CBC radio satire The Investigator (1954), a thinly veiled portrait of Joseph McCarthy that circulated widely in bootleg copies inside the United States; he moved to England in 1956 and continued writing for television and film until his death in 1975.");
$fillCase($p, ["convicted" => "Deported to Canada under the McCarran-Walter Act (January 12, 1953)"]);

// --- Adam Blackwell (ELF Richmond, 2002-04) ---------------------------------
$p = $find("adam-blackwell");
$appendOnce($p, "Freeman High",
    "Blackwell and his co-defendants John Wade and Aaron Linas were Douglas S. Freeman High School students in Henrico County who had belonged to the school\u{2019}s Friends of Earth Club before adopting Earth Liberation Front tactics. Between July and October 2002 the three set kerosene-soaked wicks in the fuel tanks of construction equipment at the Short Pump Town Center mall site, etched slogans into 25 SUVs at a dealership, and vandalized fast-food restaurants, leaving ELF graffiti. Blackwell pled guilty in federal court in Richmond on January 12, 2004.");
$fillCase($p, ["convicted" => "Yes — pled guilty (January 12, 2004)"]);

// --- Jane Moses (Operation Alert, 1957) -------------------------------------
$fillCase($find("jane-moses"), [
    "arrest_date" => "1957-07-12",
    "sentence" => "30 days",
    "convicted" => "Yes",
]);

// --- Jessica Lynn White (St. Paul, 2020) ------------------------------------
$p = $find("jessica-lynn-white");
if ($p && $p->cases()->count() === 0) {
    $p->cases()->create([
        "charges" => "Federal arson charge over the May 28, 2020 fire at the Enterprise Rent-A-Car building in St. Paul during the George Floyd uprising",
        "convicted" => "No — case dismissed",
    ]);
    echo "CASE created jessica-lynn-white\n";
}

// --- Case structuring from facts already in the descriptions ----------------
$fillCase($find("dale-bartell"), [
    "convicted" => "Yes — pled guilty to facts of desertion",
    "sentence" => "Four months of military confinement",
]);
$fillCase($find("louis-murray"), [
    "sentence" => "100 days",
    "convicted" => "Yes",
]);

// William Minton and F. Varella: the same San Quentin criminal-syndicalism
// cohort, both serving four-year terms from July 12, 1923.
$sq = \App\Models\Institution::where("name", "like", "%San Quentin%")->first()
    ?? \App\Models\Institution::create(["name" => "San Quentin State Prison", "city" => "San Quentin", "state" => "California"]);
foreach (["william-minton", "f-varella"] as $slug) {
    $p = $find($slug);
    if (! $p) { echo "MISS {$slug}\n"; continue; }
    $case = $p->cases()->first();
    if ($case && empty($case->institution_id)) { $case->institution_id = $sq->id; $case->save(); }
    $fillCase($p, [
        "incarceration_date" => "1923-07-12",
        "sentence" => "Four years",
        "convicted" => "Yes — criminal syndicalism (California)",
    ]);
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Second thin-record enrichment batch applied."
