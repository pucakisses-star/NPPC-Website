<?php

declare(strict_types=1);

/**
 * Normalize the ideologies and affiliation arrays across every prisoner:
 *   1. Trim whitespace and collapse internal whitespace
 *   2. Apply explicit canonical mappings (case + variant duplicates)
 *   3. Split the composite "Pacifism / Socialism / Anarchism" ideology
 *      into three separate values
 *   4. Dedupe the resulting array
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

// --- Ideology canonicalization ---
// Map: lowercase trimmed input -> canonical output
$ideologyMap = [
    // Anti-War variants
    'anti-war'                          => 'Anti-War',
    'anti war'                          => 'Anti-War',
    // Pacifism merged to Anti-War per earlier user direction
    'pacifism'                          => 'Anti-War',
    'pacifist'                          => 'Anti-War',

    // Anti-nuclear (lowercase canonical)
    'anti-nuclear'                      => 'Anti-nuclear',
    'antinuclear'                       => 'Anti-nuclear',

    // Black Liberation
    'black liberation'                  => 'Black Liberation',

    // Antifascism
    'antifascism'                       => 'Antifascism',
    'antifa'                            => 'Antifascism',
    'anti-fascism'                      => 'Antifascism',
    'anti-fascist'                      => 'Antifascism',
    'anti-Fascism'                      => 'Antifascism',
    'antifascist'                       => 'Antifascism',

    // Puerto Rican Independence
    'puerto rican independence'         => 'Puerto Rican Independence',
    'puerto rican Independence'         => 'Puerto Rican Independence',

    // Anti-police
    'anti-police'                       => 'Anti-police',

    // Civil Rights
    'civil rights'                      => 'Civil Rights',

    // Anarchism
    'anarchism'                         => 'Anarchism',

    // Anti-Federalist
    'anti-federalist'                   => 'Anti-Federalist',

    // Police Accountability
    'police accountability'             => 'Police Accountability',

    // Suffragism / Women's suffrage
    "women's suffrage"                  => "Women's Suffrage",
    'suffragism'                        => "Women's Suffrage",

    // Reproductive Rights
    'reproductive rights'               => 'Reproductive Rights',

    // Anti-Capitalism
    'anti-capitalism'                   => 'Anti-Capitalism',

    // LGBTQ Rights
    'lgbtq rights'                      => 'LGBTQ Rights',
    'lgbtq+ rights'                     => 'LGBTQ Rights',

    // Pro-Palestine variants
    'pro-palestine'                     => 'Pro-Palestine',
    'palestine solidarity'              => 'Pro-Palestine',
    'palestine activism'                => 'Pro-Palestine',
    'palestine'                         => 'Pro-Palestine',

    // Labor Activism
    'labor activism'                    => 'Labor Activism',
    'labor rights activism'             => 'Labor Activism',
    'labor organizing'                  => 'Labor Activism',

    // Anti-imperialism
    'anti-imperialism'                  => 'Anti-imperialism',
    'anti-imperialist'                  => 'Anti-imperialism',

    // Anti-racism
    'anti-racism'                       => 'Anti-racism',
    'anti-racist'                       => 'Anti-racism',

    // Conscientious objection
    'conscientious objection'           => 'Conscientious Objection',
    'conscientious objector'            => 'Conscientious Objection',

    // Prison Abolition
    'prison abolition'                  => 'Prison Abolition',

    // Anti-apartheid (lowercase canonical for the second word)
    'anti-apartheid'                    => 'Anti-apartheid',

    // Pan-Africanism
    'pan-africanism'                    => 'Pan-Africanism',
    'pan-african'                       => 'Pan-Africanism',
    'pan-africanist'                    => 'Pan-Africanism',

    // Black Lives Matter
    'black lives matter'                => 'Black Lives Matter',

    // Anti-government
    'anti-government'                   => 'Anti-government',

    // Animal Rights
    'animal rights activism'            => 'Animal Rights Activism',
    'animal rights'                     => 'Animal Rights Activism',

    // Environmental Activism
    'environmental activism'            => 'Environmental Activism',

    // Anti-NATO
    'anti-nato'                         => 'Anti-NATO',

    // Catholic Worker
    'catholic worker'                   => 'Catholic Worker',
];

// Composite ideologies that should be SPLIT into their parts
$compositeSplits = [
    'Pacifism / Socialism / Anarchism' => ['Anti-War', 'Socialism', 'Anarchism'],
];

// --- Affiliation canonicalization ---
$affiliationMap = [
    // Plowshares Movement variants (the broader US movement; keep Trident /
    // Kingsbay / Atlantic Life Community separate as they're distinct
    // groups/actions)
    'plowshares movement'               => 'Plowshares Movement',
    'plowshare'                         => 'Plowshares Movement',
    'plowshares'                        => 'Plowshares Movement',

    // Black Panthers
    'black panthers'                    => 'Black Panther Party',
    'black panther party'               => 'Black Panther Party',

    // CPUSA
    'cpusa'                             => 'Communist Party USA',
    'communist party usa'               => 'Communist Party USA',
    'communist party of the united states of america' => 'Communist Party USA',

    // Animal Liberation Front
    'animal liberation front (alf)'     => 'Animal Liberation Front',
    'animal liberation front'           => 'Animal Liberation Front',
    'alf'                               => 'Animal Liberation Front',

    // Earth Liberation Front
    'earth liberation front'            => 'Earth Liberation Front',
    'elf'                               => 'Earth Liberation Front',

    // FALN (canonical with full Spanish name)
    'faln'                              => 'FALN (Fuerzas Armadas de Liberación Nacional)',
    'faln (fuerzas armadas de liberación nacional)' => 'FALN (Fuerzas Armadas de Liberación Nacional)',

    // Macheteros / Ejército Popular Boricua
    'macheteros / ejército popular boricua' => 'Macheteros / Ejército Popular Boricua',
    'macheteros'                        => 'Macheteros / Ejército Popular Boricua',
    'ejercito popular boricua-macheteros' => 'Macheteros / Ejército Popular Boricua',
    'ejército popular boricua'          => 'Macheteros / Ejército Popular Boricua',
    'ejército popular boricua-macheteros' => 'Macheteros / Ejército Popular Boricua',

    // Holy Land Foundation
    'holy land foundation'              => 'Holy Land Foundation',

    // Anonymous
    'anonymous'                         => 'Anonymous',

    // Antifa (affiliation)
    'antifa'                            => 'Antifa',
    'antifascist'                       => 'Antifa',

    // Hamas
    'hamas'                             => 'HAMAS',

    // SDS
    'sds'                               => 'Students for a Democratic Society',
    'students for a democratic society' => 'Students for a Democratic Society',

    // SNCC
    'sncc'                              => 'Student Nonviolent Coordinating Committee',
    'student nonviolent coordinating committee' => 'Student Nonviolent Coordinating Committee',
    'student nonviolent co-ordinating committee' => 'Student Nonviolent Coordinating Committee',

    // IWW
    'iww'                               => 'Industrial Workers of the World',
    'industrial workers of the world'   => 'Industrial Workers of the World',

    // AIM
    'aim'                               => 'American Indian Movement',
    'american indian movement'          => 'American Indian Movement',

    // Black Liberation Army
    'bla'                               => 'Black Liberation Army',
    'black liberation army'             => 'Black Liberation Army',

    // SLA
    'sla'                               => 'Symbionese Liberation Army',
    'symbionese liberation army'        => 'Symbionese Liberation Army',
];

// Helpers
function normalizeKey(string $s): string {
    if (class_exists(\Normalizer::class)) {
        $n = \Normalizer::normalize($s, \Normalizer::FORM_KC);
        if (is_string($n)) $s = $n;
    }
    $s = preg_replace('/\s+/u', ' ', $s);
    return mb_strtolower(trim($s));
}

function canonicalize(string $value, array $map): string {
    $key = normalizeKey($value);
    if (isset($map[$key])) return $map[$key];
    return trim($value);
}

function applyTo(array $arr, array $map, array $compositeSplits = []): ?array {
    $rebuilt = [];
    foreach ($arr as $v) {
        if (! is_string($v)) { $rebuilt[] = $v; continue; }
        $trimmed = trim($v);
        if (isset($compositeSplits[$trimmed])) {
            foreach ($compositeSplits[$trimmed] as $piece) $rebuilt[] = $piece;
        } else {
            $rebuilt[] = canonicalize($trimmed, $map);
        }
    }

    // Dedupe with normalized key
    $seen = [];
    $out = [];
    foreach ($rebuilt as $v) {
        if (! is_string($v)) { $out[] = $v; continue; }
        $key = normalizeKey($v);
        if ($key === '' || isset($seen[$key])) continue;
        $seen[$key] = true;
        $out[] = $v;
    }
    return $out ?: null;
}

$updated = 0;
$untouched = 0;

Prisoner::chunkById(200, function ($chunk) use (&$updated, &$untouched, $ideologyMap, $affiliationMap, $compositeSplits) {
    foreach ($chunk as $p) {
        $changed = false;

        if (is_array($p->ideologies) && ! empty($p->ideologies)) {
            $newIdeo = applyTo($p->ideologies, $ideologyMap, $compositeSplits);
            if ($newIdeo !== $p->ideologies) {
                $p->ideologies = $newIdeo;
                $changed = true;
            }
        }

        if (is_array($p->affiliation) && ! empty($p->affiliation)) {
            $newAff = applyTo($p->affiliation, $affiliationMap);
            if ($newAff !== $p->affiliation) {
                $p->affiliation = $newAff;
                $changed = true;
            }
        }

        if ($changed) {
            $p->save();
            $updated++;
        } else {
            $untouched++;
        }
    }
});

echo "\nNormalization complete.\n";
echo sprintf("  Updated:    %d prisoners\n", $updated);
echo sprintf("  Untouched:  %d prisoners\n", $untouched);
