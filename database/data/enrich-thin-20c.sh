#!/usr/bin/env bash
#
# Thin-record enrichment, third batch (July 2026): 20 more randomly sampled
# records lacking photos / birthdates / case detail / substantial
# descriptions, each researched. Verified additions:
#
#  - Mark Lane: birth/death dates (Feb 24, 1927 - May 10, 2016), a public-
#    domain 1967 portrait, and his later career (Rush to Judgment etc.).
#  - Maia Scherrer: alias Maia Turchin, the seven Colorado Smith Act
#    co-defendants, and the Bary v. United States appeals.
#  - Marcus A. Murphy: full name, the five St. Louis co-defendants, the
#    Sept 24, 1952 indictment, June 4, 1954 sentencing, 1958 reversal.
#  - Benjamin Moye and Conrad Fahnestock: the October 1799 Sedition Act
#    indictment over the Morgenrothe and its dismissal — description
#    outcome + first case rows; Mayer/Moyer aliases for Moye.
#  - Case structuring from facts already in descriptions: Errick Steven
#    Toa, Joseph Davenport (Fort Leavenworth, Nov 12, 1918), Thomas
#    O'Mara (San Quentin, Aug 25, 1923), Bret G. Walton.
#  - Text repairs: O'Mara stray footnote "92"; the OCR-garbled sentence
#    line on Lndwelk Evanickl; the fragmentary Solares Herrera entry
#    rewritten as a sentence.
#
# All updates fill-if-empty or guarded by content checks; idempotent.
#
# Run from the repo root:  bash database/data/enrich-thin-20c.sh

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

// --- Mark Lane --------------------------------------------------------------
$p = $find("mark-lane");
if ($p) {
    $changed = false;
    if (empty($p->birthdate)) { $p->birthdate = "1927-02-24"; $changed = true; }
    if (empty($p->death_date)) { $p->death_date = "2016-05-10"; $changed = true; }
    if ($changed) { $p->save(); echo "SET mark-lane dates\n"; }
    if (empty($p->photo) && is_file(database_path("data/photos/mark-lane.jpg"))) {
        \Illuminate\Support\Facades\Storage::disk("public")->makeDirectory("prisoners");
        \Illuminate\Support\Facades\Storage::disk("public")->put("prisoners/mark-lane.jpg", (string) file_get_contents(database_path("data/photos/mark-lane.jpg")));
        $p->photo = "prisoners/mark-lane.jpg";
        $p->save();
        echo "PHOTO mark-lane\n";
    }
}
$appendOnce($p, "Rush to Judgment",
    "Lane went on to become one of the most prominent critics of the Warren Commission: his 1966 best-seller Rush to Judgment was the first major book challenging the official account of the Kennedy assassination. A lawyer to the end of his life, he later represented James Earl Ray before the House Select Committee on Assassinations, was in Jonestown as an attorney for the Peoples Temple when the 1978 massacre began and escaped into the jungle, and continued writing and litigating civil-rights cases until his death in Charlottesville, Virginia in 2016.");

// --- Maia Scherrer (Colorado Smith Act, 1954) -------------------------------
$p = $find("maia-scherrer");
if ($p && empty($p->aka)) { $p->aka = "Maia Turchin"; $p->save(); echo "AKA maia-scherrer\n"; }
$appendOnce($p, "Bary v. United States",
    "She was one of seven Colorado defendants — with Arthur Bary, Anna Bary, Harold Zepelin, Lewis Martin Johnson, Patricia Julia Blau and her husband Joseph William Scherrer — indicted in 1954 for conspiring to advocate the overthrow of the government under the Smith Act. All were convicted, and the convictions were fought through years of appeals in Bary v. United States (10th Cir. 1957 and 1961) as the Supreme Court\u{2019}s Yates decision dismantled the Smith Act membership prosecutions.");

// --- Marcus A. Murphy (St. Louis Smith Act) ---------------------------------
$p = $find("marcus-a-murphy");
$appendOnce($p, "Manewitz",
    "Marcus Alphonse Murphy was indicted on September 24, 1952 with the four other St. Louis defendants — UE district president William Sentner, James Frederick Forest, Dorothy Rose Forest and Robert Manewitz. All five were convicted and sentenced on June 4, 1954; the Eighth Circuit reversed the convictions in 1958 in the wake of the Supreme Court\u{2019}s Yates decision, and the prosecution was abandoned.");
$fillCase($p, [
    "sentenced_date" => "1954-06-04",
    "convicted" => "Yes — reversed on appeal (8th Cir. 1958)",
]);

// --- The Morgenrothe editors (Sedition Act, 1799) ---------------------------
$p = $find("benjamin-moye");
if ($p && empty($p->aka)) { $p->aka = "Benjamin Mayer / Benjamin Moyer"; $p->save(); echo "AKA benjamin-moye\n"; }
foreach (["benjamin-moye", "conrad-fahnestock"] as $slug) {
    $p = $find($slug);
    $appendOnce($p, "October 1799 session",
        "The two editors were indicted at the October 1799 session of the U.S. Circuit Court for the Pennsylvania district under the Sedition Act of 1798, accused of intending to \u{201c}vilify and defame the government of the United States and the administration of justice\u{201d} over an article of May 21, 1799. The charges were ultimately dismissed — making them part of the minority of Sedition Act defendants who escaped conviction and punishment.");
    if ($p && $p->cases()->count() === 0) {
        $case = $p->cases()->create([
            "charges" => "Sedition Act of 1798 — indicted October 1799 in the U.S. Circuit Court for the Pennsylvania district as co-editor of the Harrisburger Morgenrothe",
            "arrest_date" => "1799-01-01",
            "convicted" => "No — charges dismissed",
        ]);
        $case->date_precision = ["arrest_date" => "year"];
        $case->save();
        echo "CASE created {$slug}\n";
    }
}

// --- Case structuring from facts already in the descriptions ----------------
$fillCase($find("errick-steven-toa"), [
    "convicted" => "Yes — pled guilty",
    "sentence" => "Time served (about 10.6 months), two years of supervised release, and $390 restitution",
]);
$fillCase($find("bret-g-walton"), [
    "convicted" => "Yes",
    "sentence" => "30 days of home confinement",
]);

$p = $find("joseph-davenport");
if ($p) {
    $case = $p->cases()->first();
    if ($case && empty($case->institution_id)) {
        $fl = \App\Models\Institution::where("name", "like", "%Disciplinary Barracks%")->first()
            ?? \App\Models\Institution::where("name", "like", "%Leavenworth%")->first()
            ?? \App\Models\Institution::create(["name" => "Fort Leavenworth Disciplinary Barracks", "city" => "Fort Leavenworth", "state" => "Kansas"]);
        $case->institution_id = $fl->id;
        $case->save();
        echo "CASE joseph-davenport institution\n";
    }
    $fillCase($p, ["incarceration_date" => "1918-11-12", "convicted" => "Yes — court-martialed"]);
}

$p = $find("thomas-omara");
if ($p && str_contains((string) $p->description, "Casdorf.)92")) {
    $p->description = str_replace("Casdorf.)92", "Casdorf.)", $p->description);
    $p->save();
    echo "FIX thomas-omara stray footnote\n";
}
if ($p) {
    $case = $p->cases()->first();
    if ($case && empty($case->institution_id)) {
        $sq = \App\Models\Institution::where("name", "like", "%San Quentin%")->first();
        if ($sq) { $case->institution_id = $sq->id; $case->save(); echo "CASE thomas-omara institution\n"; }
    }
    $fillCase($p, [
        "incarceration_date" => "1923-08-25",
        "sentence" => "Four years",
        "convicted" => "Yes — criminal syndicalism (California)",
    ]);
}

// --- Text repairs -----------------------------------------------------------
$p = $find("lndwelk-evanickl");
if ($p && str_contains((string) $p->description, "Sept.24,1018, Oneyearandt")) {
    $p->description = str_replace(
        "Sentence: Sept.24,1018, Oneyearandt.",
        "Sentenced September 24, 1918; sentence: one year. (The name and sentence line are OCR-garbled in the digitized hearing record.)",
        $p->description
    );
    $p->save();
    echo "FIX lndwelk-evanickl OCR garble\n";
}
$p = $find("oliver-edu-solares-herrera");
if ($p && str_contains((string) $p->description, "green-handprint defendant, July 30 2022.")) {
    $p->description = "Oliver Edu Solares Herrera, 24, was one of the Rise Up 4 Abortion Rights defendants charged after green handprints were left on the Riverside Historic Courthouse in California during a July 30, 2022 protest against the overturning of Roe v. Wade.";
    $p->save();
    echo "FIX oliver-edu-solares-herrera fragment\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Third thin-record enrichment batch applied."
