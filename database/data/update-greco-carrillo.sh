#!/usr/bin/env bash
#
# Greco–Carrillo case — full research update for Donato Carrillo and
# Calogero Greco, the two Italian-American anti-fascists tried and
# acquitted for the May 30, 1927 killing of two Fascist League members,
# Giuseppe Carisi and Michele Amoroso, in the Bronx.
#
# Replaces the vague case dates (arrest "1927-06", release "1927-12")
# with the documented record:
#
#   - Both arrested in Brooklyn July 11, 1927 (one Italian political-
#     police summary says July 12 for Carrillo; noted in his description)
#     and held in the Bronx County Jail as material witnesses.
#   - Formally indicted for premeditated murder July 26, 1927; bail
#     $50,000 each, which they could not furnish — jailed all pretrial.
#   - Trial began in Bronx County Court December 9, 1927; both acquitted
#     December 23, 1927 after nearly eight hours of deliberation
#     (about five and a half months / 165 days detained).
#
# Biography:
#   - Carrillo: b. August 4, 1894, Sant'Agata di Puglia, Foggia, Italy;
#     d. August 26, 1965, Los Angeles (heart attack while visiting a
#     hospitalized comrade; obituary in L'Adunata dei Refrattari).
#     Surname also appears as "Carillo" — stored as aka.
#   - Greco: b. approx. 1893–1894 in Sicily (a 1927 report gives his age
#     as 33) — too imprecise for the birthdate field, so it stays unset
#     and the approximation lives in the description. WWI veteran,
#     immigrated 1920, tailor, Amalgamated Clothing Workers Local 63.
#
# Idempotent (marker-guarded on "Carisi" in the rewritten description).
#
# Run from the repo root:  bash database/data/update-greco-carrillo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$find = fn ($slug) => \App\Models\Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();

$jail = \App\Models\Institution::firstOrCreate(
    ["name" => "Bronx County Jail"],
    ["city" => "Bronx", "state" => "New York"]
);

$fixCase = function ($p) use ($jail) {
    $c = $p->cases()->first();
    if (! $c) {
        echo "  (no case found for {$p->slug} — case not updated)\n";
        return;
    }
    $c->update([
        "institution_id"      => $jail->id,
        "charges"             => "First-degree (premeditated) murder — killing of two Fascist League members, Giuseppe Carisi and Michele Amoroso, May 30, 1927, the Bronx (Greco–Carrillo case; acquitted)",
        "arrest_date"         => "1927-07-11",
        "incarceration_date"  => "1927-07-11",
        "release_date"        => "1927-12-23",
        "convicted"           => "No — acquitted December 23, 1927 (Bronx County Court)",
        "sentence"            => "Held about five and a half months in the Bronx County Jail pending trial; acquitted",
        "imprisoned_for_days" => 165,
    ]);
};

// ---- Donato Carrillo ----
$p = $find("donato-carrillo");
if (! $p) {
    echo "donato-carrillo not found — nothing done.\n";
} elseif (str_contains((string) $p->description, "Carisi")) {
    echo "donato-carrillo already updated — nothing done.\n";
} else {
    $p->aka = "Donato Carillo";
    $p->birthdate = "1894-08-04";
    $p->death_date = "1965-08-26";
    $p->description = "Donato Carrillo, born August 4, 1894 in Sant'"'"'Agata di Puglia, Foggia, Italy (his Italian political-police file names his parents as Michele Carrillo and Saveria Soldo), was an Italian-American anti-fascist and one of the two defendants in the celebrated Greco–Carrillo case. He was arrested in Brooklyn on July 11, 1927 — one Italian political-police summary gives July 12 — and held with fellow anti-fascist Calogero Greco in the Bronx County Jail as a material witness in the investigation of the May 30, 1927 Memorial Day killing of two Italian Fascists, Giuseppe Carisi and Michele Amoroso, in the Bronx. On July 26, 1927 the two were formally indicted for premeditated murder, with bail set at fifty thousand dollars each; unable to furnish it, they remained jailed throughout the pretrial period. Their trial opened in Bronx County Court on December 9, 1927, with Clarence Darrow leading the defense in a celebrated International Labor Defense campaign that showed the prosecution to be a frame-up built on fascist testimony. After nearly eight hours of deliberation the jury acquitted both men on December 23, 1927, ending about five and a half months of detention, and a victory celebration was held that same day. His surname appears in sources as both Carrillo and Carillo. He died of a heart attack on August 26, 1965 in Los Angeles, California, while visiting a hospitalized comrade; his obituary appeared in L'"'"'Adunata dei Refrattari.";
    $p->save();
    $fixCase($p);
    echo "UPDATED donato-carrillo: bio + case dates/institution\n";
}

// ---- Calogero Greco ----
$p = $find("calogero-greco");
if (! $p) {
    echo "calogero-greco not found — nothing done.\n";
} elseif (str_contains((string) $p->description, "Carisi")) {
    echo "calogero-greco already updated — nothing done.\n";
} else {
    $p->description = "Calogero Greco, born in Sicily around 1893–1894 (a 1927 report gives his age as 33), was a Sicilian-born veteran of the First World War who immigrated to the United States in 1920 and worked as a tailor in New York, a member of Amalgamated Clothing Workers Local 63. An anti-fascist, he was arrested in Brooklyn on July 11, 1927 and held with fellow anti-fascist Donato Carrillo in the Bronx County Jail as a material witness in the investigation of the May 30, 1927 Memorial Day killing of two Italian Fascists, Giuseppe Carisi and Michele Amoroso, in the Bronx. On July 26, 1927 the two were formally indicted for premeditated murder, with bail set at fifty thousand dollars each; unable to furnish it, they remained jailed throughout the pretrial period. Their trial — the celebrated Greco–Carrillo case — opened in Bronx County Court on December 9, 1927, with Clarence Darrow leading the defense in a celebrated International Labor Defense campaign that showed the prosecution to be a frame-up built on fascist testimony. After nearly eight hours of deliberation the jury acquitted both men on December 23, 1927, ending about five and a half months of detention, and a victory celebration was held that same day.";
    $aff = $p->affiliation ?? [];
    if (! in_array("Amalgamated Clothing Workers of America", $aff, true)) {
        $aff[] = "Amalgamated Clothing Workers of America";
        $p->affiliation = $aff;
    }
    $p->save();
    $fixCase($p);
    echo "UPDATED calogero-greco: bio + affiliation + case dates/institution\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Greco–Carrillo records updated (Donato Carrillo, Calogero Greco)."
