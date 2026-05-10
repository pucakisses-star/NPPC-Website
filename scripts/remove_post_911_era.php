<?php

declare(strict_types=1);

/**
 * Null out era="Post-9/11" on every prisoner. Case-insensitive
 * matching so spacing/punctuation variants (Post 9/11, Post-9-11,
 * post-9/11, etc.) all get caught.
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$variants = ['post-9/11', 'post 9/11', 'post-9-11', 'post 9-11', 'post-911', 'post911'];

$touched = 0;
foreach (Prisoner::query()->whereNotNull('era')->cursor() as $p) {
    $eraLc = mb_strtolower(trim((string) $p->era));
    if (! in_array($eraLc, $variants, true)) continue;

    $p->era = null;
    $p->save();
    echo "[cleared] {$p->name}\n";
    $touched++;
}

echo "\nDone. Cleared era on {$touched} prisoner(s).\n";
