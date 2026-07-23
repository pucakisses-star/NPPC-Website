#!/usr/bin/env bash
#
# Re-space the "Third World at Home" article (/publications/beyond-bars). Its
# whole body was stored as ONE <p>, so every paragraph, the epigraph, the
# section headings, and the endnotes ran together as a wall of text.
#
# This does NOT change any wording. It reads the article body already in the
# database, flattens it to plain text, and re-imposes the original structure
# using a map of short opening-phrase locators in:
#
#   database/data/articles/beyond-bars.structure.json
#
# It rebuilds the body with section <h2> headings, the Debs epigraph as a
# <blockquote>, the three common-denominators as a <ul>, proper <p> paragraphs,
# and the endnotes split into separate <p class="note"> entries.
#
# Safety: if any locator cannot be found, or the located offsets are not in
# order, the script prints what failed and leaves the article UNCHANGED.
# Idempotent: re-running reproduces the same result. Run from the repo root:
#   bash database/data/reformat-beyond-bars.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$S = json_decode(file_get_contents(base_path("database/data/articles/beyond-bars.structure.json")), true);
if (! is_array($S)) { echo "Could not read structure JSON.\n"; return; }

$article = \App\Models\Article::where("slug", $S["slug"])->first();
if (! $article) { echo "Article not found: " . $S["slug"] . "\n"; return; }

// Flatten current body to plain text (no wording change).
$text = html_entity_decode(strip_tags((string) $article->body), ENT_QUOTES | ENT_HTML5, "UTF-8");
$text = preg_replace("/\s+/u", " ", $text);
$text = trim($text);
// Neutralize the synthetic Notes heading if this was already reformatted once.
$text = str_replace("Notes Quoted from Jackie Lyden", "Quoted from Jackie Lyden", $text);

// Resolve offsets for every block that carries a locator.
$reals = [];
foreach ($S["blocks"] as $b) { if (isset($b["at"])) { $reals[] = $b; } }
$offs = []; $frm = 0;
foreach ($reals as $b) {
    $i = mb_strpos($text, $b["at"], $frm);
    if ($i === false) { echo "LOCATOR NOT FOUND, leaving article unchanged:\n  " . $b["at"] . "\n"; return; }
    $offs[] = $i; $frm = $i + mb_strlen($b["at"]);
}
for ($x = 1; $x < count($offs); $x++) {
    if ($offs[$x] <= $offs[$x - 1]) { echo "Locators out of order, leaving article unchanged.\n"; return; }
}

// Slice the plain text into one segment per located block.
$bounds = $offs; $bounds[] = mb_strlen($text);
$segs = [];
for ($k = 0; $k < count($reals); $k++) { $segs[] = trim(mb_substr($text, $bounds[$k], $bounds[$k + 1] - $bounds[$k])); }

$esc = function ($s) { return htmlspecialchars($s, ENT_NOQUOTES, "UTF-8"); };
$trimHead = function ($s) { return trim($s, " \t\r\n\"" . chr(0)); };

$out = []; $k = 0; $ul = [];
$flush = function () use (&$ul, &$out, $esc) {
    if ($ul) {
        $li = "";
        foreach ($ul as $x) { $li .= "<li>" . $esc($x) . "</li>"; }
        $out[] = "<ul>" . $li . "</ul>";
        $ul = [];
    }
};

foreach ($S["blocks"] as $b) {
    $t = $b["type"];
    if (isset($b["literal"])) { $flush(); $out[] = "<h2>" . $esc($b["literal"]) . "</h2>"; continue; }
    $seg = $segs[$k]; $k++;
    if ($t === "li") { $ul[] = $seg; continue; }
    $flush();
    if ($t === "h2") {
        // strip a stray leading/trailing quote mark picked up at a boundary
        $h = $trimHead($seg);
        $h = trim($h, mb_chr(0x201C, "UTF-8") . mb_chr(0x201D, "UTF-8"));
        $out[] = "<h2>" . $esc(trim($h)) . "</h2>";
    } elseif ($t === "bq") {
        $parts = preg_split("/(?=\x{2014}\s*Eugene)/u", $seg, 2);
        if (count($parts) === 2) {
            $out[] = "<blockquote><p>" . $esc(trim($parts[0], " \"" . mb_chr(0x201C, "UTF-8"))) . "</p><cite>" . $esc(trim($parts[1])) . "</cite></blockquote>";
        } else {
            $out[] = "<blockquote><p>" . $esc($seg) . "</p></blockquote>";
        }
    } elseif ($t === "notes") {
        $marks = [0]; $pos = 0;
        foreach ($S["note_starts"] as $s) {
            $j = mb_strpos($seg, $s, $pos);
            if ($j !== false) { $marks[] = $j; $pos = $j + mb_strlen($s); }
        }
        $marks = array_values(array_unique($marks)); sort($marks);
        $html = "";
        for ($x = 0; $x < count($marks); $x++) {
            $start = $marks[$x];
            $end = ($x + 1 < count($marks)) ? $marks[$x + 1] : mb_strlen($seg);
            $p = trim(mb_substr($seg, $start, $end - $start));
            if ($p !== "") { $html .= "<p class=\"note\">" . $esc($p) . "</p>\n"; }
        }
        $out[] = $html;
    } else {
        $out[] = "<p>" . $esc($seg) . "</p>";
    }
}
$flush();

$article->body = implode("\n", $out);
$article->save();
echo "Reformatted body saved (" . count($out) . " top-level blocks).\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Beyond Bars article re-spaced."
