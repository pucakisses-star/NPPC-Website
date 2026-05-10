<?php

declare(strict_types=1);

/**
 * Remove a batch of values from every prisoner's ideologies array.
 * User explicitly scoped this to "as an ideology" so we leave the
 * affiliation column alone — even though some of these (Sanctuary,
 * Industrial Unionism) might also live there as movement names.
 *
 * Duplicates with different casing (Tax Resistance / Tax resistance,
 * Press freedom / Press Freedom, etc.) are caught case-insensitively.
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$kill = array_map('mb_strtolower', [
    'Bolivarianism',
    'Anti-Capitalism',
    'Arms Trafficking',
    'Sunni Islam',
    'Religious community organizing',
    'German nationalism',
    'Anti-NATO',
    'Black community self-defense',
    'Anti-Republican',
    'Free Exercise',
    'Anti-pipeline',
    'Industrial Unionism',
    'Trans Liberation',
    'Anti-conservative',
    'Pro-immigrant',
    'Naturalist / back-to-nature',
    'Sanctuary',
    'Counterculture',
    'Chicano nationalism',
    'Voting rights',
    'Liberalism',
    'Cuban revolutionary',
    'Suffrage',
    "Women's rights",
    'Tax Resistance',
    'Press Freedom',
    'Press freedom',
    'Immigrant rights',
    'Pro-Cuba solidarity',
    'Direct action',
]);

$touched = 0;
foreach (Prisoner::query()->whereNotNull('ideologies')->cursor() as $p) {
    $arr = $p->ideologies ?? [];
    if (! is_array($arr) || empty($arr)) continue;

    $out = [];
    $changed = false;
    foreach ($arr as $v) {
        if (in_array(mb_strtolower(trim((string) $v)), $kill, true)) {
            $changed = true;
            continue;
        }
        $out[] = $v;
    }
    if (! $changed) continue;

    $p->ideologies = array_values(array_unique($out));
    $p->save();
    echo sprintf("[updated] %s -> [%s]\n", $p->name, implode(', ', $p->ideologies));
    $touched++;
}

echo "\nDone. Updated {$touched} prisoner(s).\n";
