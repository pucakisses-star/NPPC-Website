#!/usr/bin/env bash
#
# AKA-field cleanup (July 2026). Hundreds of records carry an aka that is
# just the person's own name repeated — exactly ("Christina L. Reid (Christina
# Reid)"), with a middle name/initial added or removed, with diacritics or
# punctuation varied, or with a title prefixed ("Father ..."). Most bled in
# from duplicate-record merges that folded the dup's near-identical name into
# aka. This pass drops every such trivial variant while keeping real aliases:
#
#   - kept:   nicknames ("Ant", "Tortuguita"), different names ("H. Rap
#             Brown" on Jamil al-Amin), goes-by middle names ("Dawn"),
#             hyphenated compound surnames absent from the display name
#             ("Joan Andrews-Bell"), social handles.
#   - dropped: aliases whose tokens are a subsequence of the name (or vice
#             versa) allowing initial-to-name matching and title stripping,
#             single tokens that merely repeat the first or last name, and
#             exact duplicates within the same field.
#
# Prints every change. Idempotent — a second run changes nothing.
#
# Run from the repo root:  bash database/data/clean-aka-fields.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$TITLES = ["father","fr","rev","reverend","dr","sister","imam","mr","mrs","ms","miss"];
$SUFFIX = ["jr","sr","ii","iii","iv"];

$norm = function (string $s, bool $stripTitles = false) use ($TITLES): array {
    $s = mb_strtolower(\Illuminate\Support\Str::ascii($s));
    $s = preg_replace("/[^a-z0-9]+/", " ", $s);
    $t = array_values(array_filter(explode(" ", $s), fn ($x) => $x !== ""));
    if ($stripTitles) {
        while ($t && in_array($t[0], $TITLES, true)) { array_shift($t); }
    }
    return $t;
};

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

// Split an aka string into candidate aliases; "Jr."-style fragments produced
// by comma-splitting are rejoined to the alias before them.
$splitAka = function (string $aka) use ($norm, $SUFFIX): array {
    $parts = array_values(array_filter(array_map("trim", preg_split("/\s*[\/;,]\s*/", $aka)), fn ($p) => $p !== ""));
    $out = [];
    foreach ($parts as $p) {
        $t = $norm($p);
        if ($out && $t && ! array_diff($t, $SUFFIX)) {
            $out[count($out) - 1] .= ", " . $p;
        } else {
            $out[] = $p;
        }
    }
    return $out;
};

$trivial = function (string $alias, string $name) use ($norm, $tokMatch, $subseq): bool {
    $a = $norm($alias, true);
    $n = $norm($name);
    if (! $a) { return true; }
    if (count($a) === 1) {
        // A lone token is noise only when it repeats the first or last name;
        // a goes-by middle name or fresh nickname is kept.
        return $n && ($tokMatch($a[0], $n[0]) || (count($n) > 1 && $tokMatch($a[0], end($n))));
    }
    if ($subseq($a, $n)) { return true; }          // alias adds nothing
    if ($subseq($n, $a)) {
        // Fuller variant of the same name. Keep it only if it contributes a
        // hyphenated compound surname the display name lacks.
        preg_match_all("/\p{L}+-\p{L}+/u", $alias, $m);
        $nameAscii = mb_strtolower(\Illuminate\Support\Str::ascii($name));
        foreach ($m[0] as $compound) {
            if (! str_contains($nameAscii, mb_strtolower(\Illuminate\Support\Str::ascii($compound)))) {
                return false;
            }
        }
        return true;
    }
    return false;
};

$changed = 0; $dropped = 0; $cleared = 0;
foreach (\App\Models\Prisoner::withoutGlobalScopes()->whereNotNull("aka")->where("aka", "!=", "")->orderBy("slug")->get() as $p) {
    $parts = $splitAka($p->aka);
    $kept = []; $seen = [];
    foreach ($parts as $part) {
        $key = implode(" ", $norm($part));
        if ($trivial($part, $p->name) || isset($seen[$key])) { $dropped++; continue; }
        $kept[] = $part;
        $seen[$key] = true;
    }
    // Only rewrite when something was actually dropped — do not churn
    // records purely to normalise separators.
    if (count($kept) === count($parts)) { continue; }
    $new = $kept ? implode(" / ", $kept) : null;
    echo "{$p->slug}: \"{$p->aka}\"  ->  " . ($new === null ? "(cleared)" : "\"{$new}\"") . "\n";
    $p->aka = $new;
    $p->save();
    $changed++;
    if ($new === null) { $cleared++; }
}
if ($changed > 0) {
    \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
}
echo "\nDone. {$changed} record(s) cleaned ({$cleared} aka fields cleared entirely, {$dropped} trivial alias(es) dropped).\n";
'
