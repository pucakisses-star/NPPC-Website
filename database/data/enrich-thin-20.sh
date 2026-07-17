#!/usr/bin/env bash
#
# Thin-record enrichment (July 2026): 20 randomly sampled records lacking
# photos / birthdates / case detail / substantial descriptions were each
# researched. This script applies everything that could be verified:
#
#  - Merges the phil-frankfeld / philip-frankfeld duplicate pair and attaches
#    caption-certified 1952 portraits of Frankfeld and Maurice Braverman.
#  - Case-field and description enrichments for Edward Sayres (Pearl escape,
#    1848), Margaret McSurely (Pike County sedition, 1967), Sidney Steinberg
#    (Twain Harte cabin capture, 1953 — also corrects a wrong arrest year),
#    Norton Anthony Russell (conviction reversed, Russell v. US 1962),
#    Kolton Krottinger (charge declined Dec 22, 2025), Caroline F. Urie,
#    and the six Baltimore Smith Act defendants' 1952 sentences.
#  - Text repairs: F. Varella (mangled spacing), Minnie Kalnin (stray
#    footnote number), Walter Wolski ("RVW" -> "IWW").
#
# All updates are fill-if-empty or guarded by content checks; idempotent.
#
# Run from the repo root:  bash database/data/enrich-thin-20.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoners:merge-duplicates --only=phil-frankfeld --apply

php artisan prisoners:attach-baltimore-smith-act-photos

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

// --- Edward Sayres (the Pearl, 1848) --------------------------------------
$p = $find("edward-sayres");
$appendOnce($p, "Cornfield Harbor",
    "The Pearl, becalmed on the Potomac, was overtaken at Cornfield Harbor near Point Lookout by the armed steamboat Salem on April 17-18, 1848, and Sayres, Daniel Drayton and the roughly seventy-seven freedom seekers aboard were towed back to Washington past jeering crowds. Convicted of illegally transporting slaves, Sayres was fined $10,060 — a sum he could not pay — and stayed in the Washington jail about four years and four months until President Millard Fillmore, urged by Senator Charles Sumner, pardoned him and Drayton on August 11, 1852.");
if ($p) {
    $jail = \App\Models\Institution::firstOrCreate(["name" => "Washington Jail"], ["city" => "Washington", "state" => "District of Columbia"]);
    $case = $p->cases()->first();
    if ($case && empty($case->institution_id)) { $case->institution_id = $jail->id; $case->save(); }
    $fillCase($p, [
        "arrest_date" => "1848-04-18",
        "release_date" => "1852-08-11",
        "convicted" => "Yes — illegally transporting slaves",
        "sentence" => "Fines totaling $10,060; held in jail for inability to pay",
        "imprisoned_for_days" => 1576,
    ]);
}

// --- Margaret McSurely (Pike County sedition, 1967) -----------------------
$p = $find("margaret-mcsurely");
$appendOnce($p, "McSurely v. Ratliff",
    "Margaret McSurely and her husband Al, field organizers for the Southern Conference Educational Fund, were arrested at their Pike County home on August 11, 1967 and charged under Kentucky\u{2019}s sedition statute after a nighttime raid seized their books and papers. Within weeks a three-judge federal court in McSurely v. Ratliff held the statute unconstitutional and blocked the prosecution. Their seized papers were nonetheless passed to a U.S. Senate investigations subcommittee, launching the decade-long McSurely v. McClellan litigation over the raid and their subsequent contempt-of-Congress convictions (reversed on appeal); in December 1968 their home was dynamited. In 1983 a jury awarded the McSurelys $1.6 million in damages against the former Pike County prosecutor and federal officials.");
if ($p && $p->cases()->count() === 0) {
    $jail = \App\Models\Institution::firstOrCreate(["name" => "Pike County Jail"], ["city" => "Pikeville", "state" => "Kentucky"]);
    $p->cases()->create([
        "institution_id" => $jail->id,
        "charges" => "Sedition (Kentucky statute) — for books and papers seized in the August 1967 raid on their Pike County home",
        "arrest_date" => "1967-08-11",
        "convicted" => "No — statute held unconstitutional (McSurely v. Ratliff, 1967)",
    ]);
    echo "CASE created margaret-mcsurely\n";
}

// --- Sidney Steinberg (Twain Harte cabin, 1953) ----------------------------
$p = $find("sidney-steinberg");
if ($p && ! str_contains((string) $p->description, "Twain Harte")) {
    $p->description = "Sidney Steinberg was a Communist Party organizer indicted in the 1951 second-string New York Smith Act case who went underground rather than face trial. After about two years as a fugitive he was captured by the FBI on August 27, 1953 at a secluded cabin near Twain Harte, California, together with the fugitive Smith Act leader Robert G. Thompson, Shirley Kremen, Samuel Coleman and Carl Ross. Steinberg was convicted of relieving and harboring Thompson and of conspiracy, but the Supreme Court reversed the convictions in Kremen v. United States, 353 U.S. 346 (1957), holding that the FBI\u{2019}s wholesale seizure of the cabin\u{2019}s entire contents was an illegal search.";
    $p->save();
    echo "DESC sidney-steinberg (corrected: capture was Aug 27, 1953, not early 1955)\n";
}
$fillCase($p, ["arrest_date" => "1953-08-27"]);

// --- Norton Anthony Russell (Russell v. US, 1962) --------------------------
$p = $find("norton-anthony-russell");
$appendOnce($p, "369 U.S. 749",
    "His conviction was ultimately overturned: in Russell v. United States, 369 U.S. 749 (May 21, 1962), the Supreme Court reversed the contempt-of-Congress convictions of Russell and five co-petitioners because their indictments never identified the subject under committee inquiry.");
$fillCase($p, ["convicted" => "Yes — reversed by the Supreme Court (Russell v. United States, 1962)"]);

// --- Kolton Krottinger (Hood County, 2025) ---------------------------------
$p = $find("kolton-krottinger");
$fillCase($p, [
    "arrest_date" => "2025-11-05",
    "charges" => "Online impersonation (Texas Penal Code \u{00a7}33.07), third-degree felony — over a satirical Facebook meme",
    "convicted" => "No — charge formally declined by a special prosecutor (December 22, 2025)",
]);

// --- Caroline F. Urie ------------------------------------------------------
$p = $find("caroline-f-urie");
$appendOnce($p, "Peacemakers",
    "Caroline Foulke Urie, the widow of a Navy medical officer, spent years as a Quaker relief worker in Europe and was once expelled from Italy by the Mussolini government for anti-fascist statements. A founding member of the Peacemakers tax-refusal committee in 1948, she withheld 32.3 percent of her income tax that year — the military share — telling President Truman in an open letter that \u{201c}the atomic bomb has reduced to a final criminal absurdity the whole war system,\u{201d} and declaring herself ready to go to jail rather than pay for war.");

// --- Baltimore Smith Act sentences (April 4, 1952) --------------------------
foreach ([
    "phil-frankfeld"        => "Five years and a $1,000 fine",
    "regina-frankfeld"      => "Two years and a $1,000 fine",
    "george-meyers"         => "Four years and a $1,000 fine, plus 30 days for contempt of court",
    "maurice-braverman"     => "Three years and a $1,000 fine",
    "dorothy-rose-blumberg" => "Three years and a $1,000 fine",
] as $slug => $sentence) {
    $fillCase($find($slug), ["sentence" => $sentence, "sentenced_date" => "1952-04-04", "convicted" => "Yes"]);
}

// --- Text repairs -----------------------------------------------------------
$p = $find("f-varella");
if ($p && str_contains((string) $p->description, "San Quentinbeginningon")) {
    $p->description = str_replace("San Quentinbeginningon", "San Quentin beginning on", $p->description);
    $p->save();
    echo "FIX f-varella spacing\n";
}
$p = $find("minnie-kalnin");
if ($p && str_contains((string) $p->description, "Smith.174")) {
    $p->description = str_replace("Smith.174", "Smith.", $p->description);
    $p->save();
    echo "FIX minnie-kalnin stray footnote\n";
}
$p = $find("walter-wolski");
if ($p && str_contains((string) $p->description, "RVW")) {
    $p->description = str_replace("RVW", "IWW", $p->description);
    $p->save();
    echo "FIX walter-wolski RVW -> IWW\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Thin-record enrichments applied."
