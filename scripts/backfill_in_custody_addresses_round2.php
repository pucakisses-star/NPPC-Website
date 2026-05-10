<?php

declare(strict_types=1);

/**
 * Round 2: 5 more in-custody prisoner addresses verified via web
 * search (May 2024 to April 2026 sources). Same idempotent pattern
 * as round 1 — only writes if address is currently empty.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$updates = [
    // Held in VA via interstate compact (sent from IN DOC for political activity)
    'shaka-shakur' => [
        'Beaumont Correctional Center, 3500 Beaumont Road, Beaumont, VA 23014',
        'Free Shaka Shakur campaign Feb 2024 + VADOC facility listing',
    ],
    'damoine-wilcoxson' => [
        'Pendleton Correctional Facility, 4490 W Reformatory Road, Pendleton, IN 46064',
        '2017 conviction returned him to Pendleton; IDOC max-security adult male',
    ],
    'charles-littlejohn' => [
        'FCI Marion, U.S. Federal Correctional Institution, P.O. Box 2000, Marion, IL 62959',
        'BOP inmate locator: FCI Marion, release date 2027-10-22',
    ],
    // UK custody pending US extradition (Westminster Magistrates ruling Feb 2026)
    'daniel-andreas-san-diego' => [
        'HMP Belmarsh, Western Way, London SE28 0EB, United Kingdom',
        'ITV News + Sky News Feb 2026; held since arrest Nov 2024',
    ],
];

$wrote = $skipped = $missing = 0;
foreach ($updates as $slug => [$address, $source]) {
    $p = Prisoner::where('slug', $slug)->first();
    if (! $p) {
        echo "  [missing]  slug={$slug}\n"; $missing++; continue;
    }
    if (! empty(trim((string) $p->address))) {
        echo "  [skip]     {$p->name} already has address\n"; $skipped++; continue;
    }
    $p->address = $address;
    $p->save();
    echo "  [wrote]    {$p->name}  ->  {$address}  (src: {$source})\n";
    $wrote++;
}

echo "\nDone. wrote={$wrote}, skipped={$skipped}, missing={$missing}\n";
echo "Next: php artisan prisoners:backfill-coordinates\n";
