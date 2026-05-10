<?php

declare(strict_types=1);

/**
 * Replace bare "Occupy" with "Occupy Wall Street" in every
 * prisoner's ideologies AND affiliation arrays. Dedupes if both
 * already coexist.
 *
 * Note on canonical spelling: using the standard English form
 * "Occupy Wall Street" (three words). If you actually meant the
 * one-word "Wallstreet" form, edit $canon below and re-run.
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$canon = 'Occupy Wall Street';
$matchExact = ['occupy']; // only the bare word — leave "Occupy ICE", "Occupy Oakland", etc. alone

$touched = 0;
foreach (Prisoner::query()->cursor() as $p) {
    $changed = false;

    foreach (['ideologies', 'affiliation'] as $field) {
        $arr = $p->$field ?? [];
        if (! is_array($arr) || empty($arr)) continue;

        $out = [];
        foreach ($arr as $v) {
            $vlc = mb_strtolower(trim((string) $v));
            if (in_array($vlc, $matchExact, true)) {
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
        echo sprintf("  [updated] %s  ideol=[%s]  aff=[%s]\n",
            $p->name,
            implode(', ', $p->ideologies ?? []),
            implode(', ', $p->affiliation ?? [])
        );
        $touched++;
    }
}

echo "\nDone. Updated {$touched} prisoner(s).\n";
