<?php

declare(strict_types=1);

/**
 * Two changes in one script:
 *
 * 1. Rename "Occupy Wall Street" -> "Occupy Movement" everywhere
 *    it appears in any prisoner's ideologies or affiliation array.
 *    Also catches case/space variants (occupy wallstreet, occupy
 *    wall st, etc.). Dedupes if the canonical value was already
 *    present.
 *
 * 2. Ensure "Occupy Movement" is present in both the ideologies
 *    and affiliation arrays of Cecily McMillan and Roberto Rivera
 *    specifically (adds if missing; idempotent if already set).
 *
 * Same idempotent pattern as the other ideology/affiliation
 * normalize scripts.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$canon = 'Occupy Movement';
$variants = [
    'occupy wall street',
    'occupy wallstreet',
    'occupy wall st',
    'occupy wall st.',
    'ows',
];

// ---- 1. Global rename ------------------------------------------
$renamed = 0;
foreach (Prisoner::query()->cursor() as $p) {
    $changed = false;

    foreach (['ideologies', 'affiliation'] as $field) {
        $arr = $p->$field ?? [];
        if (! is_array($arr) || empty($arr)) continue;

        $out = [];
        foreach ($arr as $v) {
            $vlc = mb_strtolower(trim((string) $v));
            if (in_array($vlc, $variants, true)) {
                $out[] = $canon;
                $changed = true;
            } else {
                $out[] = $v;
            }
        }
        if ($changed) {
            $p->$field = array_values(array_unique($out));
        }
    }

    if ($changed) {
        $p->save();
        echo "[rename]  {$p->name}\n";
        $renamed++;
    }
}

echo "\nTotal prisoners renamed: {$renamed}\n";

// ---- 2. Ensure Cecily McMillan + Roberto Rivera have it --------
$targets = ['cecily mcmillan', 'roberto rivera'];
$ensured = 0;

foreach ($targets as $name) {
    $p = Prisoner::whereRaw('LOWER(name) = ?', [$name])->first();
    if (! $p) {
        echo "[missing] target prisoner '{$name}' not in DB\n";
        continue;
    }

    $touched = false;
    foreach (['ideologies', 'affiliation'] as $field) {
        $arr = is_array($p->$field) ? $p->$field : [];
        $exists = false;
        foreach ($arr as $v) {
            if (mb_strtolower(trim((string) $v)) === mb_strtolower($canon)) {
                $exists = true;
                break;
            }
        }
        if (! $exists) {
            $arr[] = $canon;
            $p->$field = array_values(array_unique($arr));
            $touched = true;
        }
    }

    if ($touched) {
        $p->save();
        echo "[ensure]  {$p->name}  ideol=[" . implode(', ', $p->ideologies ?? []) . "]  aff=[" . implode(', ', $p->affiliation ?? []) . "]\n";
        $ensured++;
    } else {
        echo "[noop]    {$p->name} already has '{$canon}' on both fields\n";
    }
}

echo "\nDone. renamed={$renamed}, ensured={$ensured}\n";
