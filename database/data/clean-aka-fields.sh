#!/usr/bin/env bash
#
# AKA-field cleanup (July 2026). Hundreds of records carry an aka that is
# just the person's own name repeated — exactly ("Christina L. Reid /
# Christina Reid"), with a middle name/initial added or removed, with
# diacritics or punctuation varied, or with a title/rank prefixed ("Father
# ...", "Sgt. ..."). Most bled in from duplicate-record merges that folded
# the dup's near-identical name into aka.
#
# LOSSLESS: before an alias is dropped, any name material it carries that the
# display name lacks is preserved —
#   - an extra middle name or initial moves into the middle_name field when
#     that field is empty ("Branden Michael Wolfe" -> middle_name "Michael");
#   - the full form of an initial does the same ("Christina Lee Reid" on
#     "Christina L. Reid" -> middle_name "Lee");
#   - if middle_name is already occupied by something different, the alias is
#     KEPT rather than lost.
# Aliases carrying a leading given name ("Fred Ahmed Evans"), a trailing
# surname ("Blanca Canales Torresola"), a hyphenated compound surname the
# name lacks ("Joan Andrews-Bell"), nicknames, goes-by middle names ("Dawn")
# and social handles are all kept.
#
# Prints every change. Idempotent — a second run changes nothing.
#
# Run from the repo root:  bash database/data/clean-aka-fields.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$TITLES = ["father","fr","rev","reverend","dr","sister","imam","mr","mrs","ms","miss","sgt","spc","pfc","cpl","capt","lt","col","maj","pvt"];
$SUFFIX = ["jr","sr","ii","iii","iv"];

// One normalized token per word: ascii, lowercase, alphanumerics only.
$nw = fn (string $w): string => preg_replace("/[^a-z0-9]+/", "", mb_strtolower(\Illuminate\Support\Str::ascii($w)));

// Original-cased words, split on whitespace, hyphens and periods so that
// "C.E." becomes two initials and "Espinosa-Villegas" aligns with the name.
$words = function (string $s, bool $stripTitles = false) use ($nw, $TITLES): array {
    $out = [];
    foreach (preg_split("/\s+/", trim($s)) as $chunk) {
        foreach (preg_split("/[-.]/", $chunk) as $w) {
            $w = trim($w, "\"\x27()“”‘’[],");
            if ($w !== "" && $nw($w) !== "") { $out[] = $w; }
        }
    }
    if ($stripTitles) {
        while ($out && in_array($nw($out[0]), $TITLES, true)) { array_shift($out); }
    }
    return $out;
};
$toks = fn (array $ws): array => array_map($nw, $ws);

$tokMatch = fn (string $a, string $b): bool =>
    $a === $b
    || (strlen($a) === 1 && str_starts_with($b, $a))
    || (strlen($b) === 1 && str_starts_with($a, $b));

$subseq = function (array $small, array $big) use ($tokMatch): bool {
    $i = 0;
    foreach ($big as $t) {
        if ($i < count($small) && $tokMatch($small[$i], $t)) { $i++; }
    }
    return $i === count($small);
};

$splitAka = function (string $aka) use ($words, $toks, $SUFFIX): array {
    $parts = array_values(array_filter(array_map("trim", preg_split("/\s*[\/;,]\s*/", $aka)), fn ($p) => $p !== ""));
    $out = [];
    foreach ($parts as $p) {
        $t = $toks($words($p));
        if ($out && $t && ! array_diff($t, $SUFFIX)) {
            $out[count($out) - 1] .= ", " . $p;
        } else {
            $out[] = $p;
        }
    }
    return $out;
};

// A single-letter gain is stored as an initial with its period.
$dressGain = function (string $gain): string {
    $ws = array_map(fn ($w) => strlen($w) === 1 ? mb_strtoupper($w) . "." : $w, array_filter(explode(" ", $gain)));
    return implode(" ", $ws);
};

/**
 * Classify one alias against the display name.
 * Returns [action, gain]: action "keep" | "drop" | "keepfull"; gain is the
 * original-cased name material the alias carries that the name lacks and
 * that must be preserved for a lossless drop ("" when nothing).
 */
$classify = function (string $alias, string $name) use ($words, $toks, $nw, $tokMatch, $subseq, $SUFFIX) {
    $aw = $words($alias, true);
    $nwds = $words($name);
    $a = $toks($aw);
    $n = $toks($nwds);
    if (! $a) { return ["drop", ""]; }
    if (count($a) === 1) {
        if ($n && ($tokMatch($a[0], $n[0]) || (count($n) > 1 && $tokMatch($a[0], end($n))))) {
            foreach ($n as $t) {
                if (strlen($t) === 1 && strlen($a[0]) > 1 && str_starts_with($a[0], $t)) {
                    return ["keepfull", $aw[0]];   // lone full form of a name initial
                }
            }
            return ["drop", ""];
        }
        return ["keep", ""];
    }
    $ais = $subseq($a, $n);
    $nis = $subseq($n, $a);
    if (! $ais && ! $nis) { return ["keep", ""]; }
    if ($ais) {
        // The alias adds nothing beyond, possibly, full forms of initials.
        $gain = []; $i = 0;
        foreach ($n as $t) {
            if ($i < count($a) && $tokMatch($a[$i], $t)) {
                if (strlen($a[$i]) > 1 && strlen($t) === 1) { $gain[] = $aw[$i]; }
                $i++;
            }
        }
        return ["drop", implode(" ", $gain)];
    }
    // name ⊆ alias: a fuller variant. Hyphenated compound surnames the name
    // lacks stay as aliases (they matter for search).
    preg_match_all("/\p{L}+-\p{L}+/u", $alias, $m);
    $nameSquash = $nw(str_replace(" ", "", $name));
    foreach ($m[0] as $compound) {
        if (! str_contains($nameSquash, $nw($compound))) { return ["keep", ""]; }
    }
    $gain = []; $i = 0; $matched = []; $extras = [];
    foreach ($aw as $idx => $w) {
        $t = $nw($w);
        if ($i < count($n) && $tokMatch($t, $n[$i])) {
            if (strlen($t) > 1 && strlen($n[$i]) === 1) { $gain[$idx] = $w; }
            $matched[] = $idx;
            $i++;
        } elseif (in_array($t, $SUFFIX, true)) {
            // droppable suffix (Jr./Sr./III)
        } else {
            $extras[$idx] = $w;
        }
    }
    if ($i < count($n)) { return ["keep", ""]; }   // reordered name — keep
    if ($extras) {
        // Only extras strictly INSIDE the matched span are middle names; a
        // leading given name or trailing surname keeps the alias instead.
        $first = $matched ? min($matched) : 0;
        $last  = $matched ? max($matched) : 0;
        foreach ($extras as $idx => $w) {
            if ($idx <= $first || $idx >= $last) { return ["keep", ""]; }
            $gain[$idx] = $w;
        }
    }
    ksort($gain);
    return ["drop", implode(" ", $gain)];
};

$compatMiddle = function (string $existing, string $gain) use ($words, $toks, $tokMatch): bool {
    $e = $toks($words($existing));
    $g = $toks($words($gain));
    if ($e === $g) { return true; }
    if (count($e) === 1 && count($g) === 1) { return $tokMatch($e[0], $g[0]); }
    return false;
};

$changed = 0; $droppedTotal = 0; $cleared = 0; $midSet = 0; $keptForInfo = 0;
foreach (\App\Models\Prisoner::withoutGlobalScopes()->whereNotNull("aka")->where("aka", "!=", "")->orderBy("slug")->get() as $p) {
    $parts = $splitAka($p->aka);
    $kept = []; $seen = []; $drops = 0; $newMiddle = null;
    foreach ($parts as $part) {
        $key = implode(" ", $toks($words($part)));
        if (isset($seen[$key])) { $drops++; continue; }
        [$action, $gain] = $classify($part, $p->name);
        if ($action === "keepfull") {
            if (empty($p->middle_name) && $newMiddle === null) { $newMiddle = $gain; $action = "drop"; }
            else { $action = "keep"; }
            $gain = "";
        }
        if ($action === "drop" && $gain !== "") {
            $dressed = $dressGain($gain);
            if (empty($p->middle_name) && $newMiddle === null) {
                $newMiddle = $dressed;
            } elseif (! $compatMiddle($newMiddle ?? $p->middle_name, $dressed)) {
                $action = "keep";   // cannot store the extra material — keep the alias
                $keptForInfo++;
            }
        }
        if ($action === "drop") { $drops++; continue; }
        $kept[] = $part;
        $seen[$key] = true;
    }
    if ($drops === 0) { continue; }
    $new = $kept ? implode(" / ", $kept) : null;
    echo "{$p->slug}: \"{$p->aka}\"  ->  " . ($new === null ? "(cleared)" : "\"{$new}\"");
    $p->aka = $new;
    if ($newMiddle !== null) {
        $p->middle_name = $newMiddle;
        $midSet++;
        echo "   [middle_name <- \"{$newMiddle}\"]";
    }
    echo "\n";
    $p->save();
    $changed++;
    $droppedTotal += $drops;
    if ($new === null) { $cleared++; }
}
if ($changed > 0) {
    \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
}
echo "\nDone. {$changed} record(s) cleaned, {$droppedTotal} trivial alias(es) dropped, {$cleared} aka fields cleared, {$midSet} middle_name(s) backfilled, {$keptForInfo} alias(es) kept because their extra name material could not be stored.\n";
'
