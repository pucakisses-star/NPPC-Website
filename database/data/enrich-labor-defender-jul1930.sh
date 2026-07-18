#!/usr/bin/env bash
#
# Labor Defender July 1930 audit (July 2026). Every named prisoner in the
# issue's main groups is already in the database: the Atlanta Six, all
# eight Imperial Valley criminal-syndicalism prisoners, the four New York
# unemployment-demonstration prisoners, Guido Serio, Dominick Flaiani, the
# six disputed cases (Mooney, Billings, Cornelison, Bonita, McNamara,
# Doherty) and the seven Gastonia defendants.
#
#  1. Adds the five Detroit workers sentenced to 90 days for holding a
#     meeting, known only by surname from the issue's photograph caption
#     (Coperean, Conn, Raymond, Caravas, Powers) — each record states its
#     surname-only provenance plainly.
#  2. Fills custody detail on the existing records (fill-if-empty with a
#     single-case guard): the Atlanta Six's ~six weeks in Fulton Tower and
#     the 1939 dismissal, the Imperial Valley eight's sentence ranges and
#     documented release dates (per Kohn), and the New York four's
#     ~six-month terms ending October 1930.
#
# Gus C. Mansen and John M. Lynch (San Quentin correspondents with no
# established charges) are deliberately NOT added, per the review's own
# assessment that they cannot be classified as political prisoners.
#
# Idempotent throughout.
#
# Run from the repo root:  bash database/data/enrich-labor-defender-jul1930.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

DETROIT_DESC="was one of five Detroit workers sentenced to 90 days in jail for holding a workers' meeting, pictured and named in the July 1930 Labor Defender. The magazine supplies only surnames, so this record is deliberately kept to what the source documents."

for SURNAME in Coperean Conn Raymond Caravas Powers; do
php artisan prisoner:add '{"name":"'"$SURNAME"' (Detroit, 1930)","last_name":"'"$SURNAME"'","description":"'"$SURNAME"' '"$DETROIT_DESC"'","state":"Michigan","era":"1930s","affiliation":["Unemployed Councils"],"released":true,"cases":[{"charges":"Holding a workers meeting in Detroit (1930)","convicted":"Yes","sentence":"90 days","imprisoned_for_days":90}]}' || true
done

php artisan tinker --execute='
$find = fn (string $slug) => \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
$fillCase = function ($p, array $fill): void {
    if (! $p || $p->cases()->count() !== 1) { if ($p) { echo "SKIP {$p->slug}\n"; } return; }
    $case = $p->cases()->first();
    $changed = false;
    foreach ($fill as $f => $v) {
        if (! empty($case->{$f})) { continue; }
        $case->{$f} = $v;
        $changed = true;
    }
    if ($changed) { $case->save(); echo "CASE {$p->slug}\n"; }
};

// --- Atlanta Six: ~six weeks in Fulton Tower; never tried ------------------
foreach (["m-h-powers", "joe-carr", "anna-burlak", "herbert-newton", "henry-storey", "mary-dalton"] as $slug) {
    $fillCase($find($slug), [
        "convicted" => "No — never tried; the insurrection indictments were tabled in 1932 and dismissed in 1939",
        "imprisoned_for_days" => 42,
    ]);
}

// --- Imperial Valley eight: sentence ranges and documented releases --------
$iv = [
    "frank-spector"    => ["3 to 42 years; released 1932", null],
    "lawrence-emery"   => ["3 to 42 years; served June 1930 to February 1933", null],
    "oscar-erickson"   => ["3 to 42 years", null],
    "carl-sklar"       => ["3 to 33 years; served June 1930 to October 3, 1932", "1932-10-03"],
    "tetsuji-horiuchi" => ["3 to 33 years; served June 1930 to January 1932", null],
    "danny-roxas"      => ["3 to 42 years; served June 1930 to July 1932", null],
    "eduardo-herrera"  => ["2 to 28 years; released April 28, 1932 and deported to Mexico", "1932-04-28"],
    "braulio-orosco"   => ["2 to 28 years; released November 28, 1931 and deported to Mexico", "1931-11-28"],
];
foreach ($iv as $slug => [$sentence, $release]) {
    $fill = ["sentence" => $sentence, "convicted" => "Yes — criminal syndicalism (California); all eight paroled or released by 1933"];
    if ($release) { $fill["release_date"] = $release; }
    $fillCase($find($slug), $fill);
}

// --- New York unemployment four: ~six months, released October 1930 --------
foreach (["william-z-foster", "robert-minor", "israel-amter", "harry-raymond"] as $slug) {
    $fillCase($find($slug), [
        "arrest_date" => "1930-03-06",
        "sentence" => "Six months to three years (indeterminate); served about six months, released October 1930",
        "imprisoned_for_days" => 180,
    ]);
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Labor Defender July 1930 enrichments applied."
