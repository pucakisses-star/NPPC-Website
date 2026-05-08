<?php

declare(strict_types=1);

/**
 * Remove a user-supplied denylist of ideology values from every
 * prisoner's `ideologies` array.
 *
 * This is a destructive bulk update. It does NOT delete prisoners; it
 * filters elements out of the JSON array column. Any ideology not on
 * the denylist is preserved.
 *
 * Idempotent: re-running is a no-op once the denylist values are gone.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$deny = [
    'Anti-government', 'Nationalism', 'Catholic', 'Prison abolition',
    'Conscientious objector', 'Bolivarianism', 'Indigenous sovereignty',
    'Christian Right', 'Christian peace activism', 'Anti-fascist',
    'Anti-Israel', 'Indigenous Sovereignty', 'Anti-fascism',
    'Catholic peace tradition', 'New Left', 'Indigenous-territory defense',
    'Reproductive Rights', 'Marxism', 'Palestine solidarity',
    'Anti-racist', 'Feminism', 'Anti-racism', 'Conscientious objection',
    'Housing Rights', 'Refugee rights', 'Reparations', "Women's suffrage",
    'Civil Rights', 'Irish Republicanism', 'Black liberation',
    'Anti-Capitalism', 'Salafi/Jihadist/Islamist', 'Anti-relocation',
    'Environmental justice', 'Antifascist', 'Plowshares', 'Communist',
    'Arms Trafficking', 'Anti-Fascism', 'Labor Activism', 'Anti-Trump',
    'Antifascism', 'Indigenous Activism', 'Abolitionist', 'Whistleblower',
    'Religious community organizing', 'Anti-Police', 'Marxism-Leninism',
    'Revolutionary anti-imperialism', 'Revolutionary socialism',
    'Palestine Activism', 'Liberation theology', 'Sunni Islam', 'MOVE',
    'Anti-white', 'Street Vendor Rights', 'Tamil Eelam', 'White Supremacy',
    'Revolutionary anti-capitalism', 'Student activism',
    'Catholic peace witness', 'Prison Abolition', 'Suffragism', 'Anti-NATO',
    'Symbionese Liberation Army', 'Accelerationism', 'Anti-DeSantis',
    'Anti-DEI-Repeal', 'Pro-Israel', 'War tax resistance', 'Nonviolence',
    'Communism (former)', 'Black community self-defense', 'Anti-ICE',
    'Occupy', 'Anti-Republican', 'Anti-Nuclear', 'Anti-Jewish',
    'Anti-Native', 'Veterans peace movement', 'Black Liberation Theology',
    'Virgin Islands Independence', 'Socialist', 'Pan-Africanist',
    'LGBTQ rights', 'Immigrant Rights', 'Anti-technology', 'Antifa',
    'Anti-Black', 'Anti-pipeline', 'Treaty rights', 'Chicano Movement',
    'Anti-Apartheid', 'Labor organizing', 'Democratic socialism',
    'Reproductive rights', 'Anti-HUAC', 'American Indian Movement',
    'Industrial Unionism', 'Trans Liberation', 'Anti-conservative',
    'Pro-immigrant', 'NVE', 'Animal rights', 'Naturalist / back-to-nature',
    'Catholic social teaching', 'Sanctuary', 'Counterculture',
    'Chicano nationalism', 'Free speech', 'Voting rights',
    'Police Accountability', 'Tax Resistance', 'LGBTQ Rights', 'Libertarian',
    'Abolitionism', 'Feminist', 'Civil libertarian', 'Educational justice',
    'Anti-authoritarian', 'Humanitarian', 'Migrant rights', 'Straight edge',
    'Tax resistance', 'Quaker', 'Anti-police violence',
    'Mental health justice', 'Pentagon resistance', 'Free press',
    'Revolutionary', 'Targeted by national security prosecution',
    'Cultural criticism', 'Civil liberties', 'Black community organizing',
    'Lebanese resistance', 'Christian peace', 'SDS', 'Pan-African',
    'Symbionese Liberation Army (under captivity)', 'Lesbian liberation',
    'Indigenous', 'Lesbian feminism', 'Attorney-client privilege',
    'Civil rights law', 'Medical justice', 'Western Federation of Miners',
    'Cuba solidarity', 'Anti-Imperialism', 'Right-Wing', 'Boogaloo',
    'Election2020', 'Anti-Tesla', 'Eco-extremism', 'Anti-immigrant',
    'Anti-Confederate', 'Defend the Atlanta Forest', 'Separatism',
    'Black Indigenous Sovereignty', 'Religious-separatism', 'Armenian',
    'Jewish Defense', 'Sikh Khalistan', 'Anti-Democratic-Party',
    'Anti-Russia-Ukraine-War', 'Anti-Palestinian', 'Anti-Muslim',
    'Anti-homeless', 'Efilism', 'Anti-natalism', 'Press freedom',
    'Whistleblowing', 'Anti-Vietnam War', 'Third Worldism',
    'Nonviolent direct action', 'Catholic Left', 'Land grant restoration',
    'Indigenous-Hispano sovereignty', 'Aztlán self-determination',
    'Student power', 'Catholic pacifism',
    'Anti-war (selective conscientious objection)', 'Medical ethics',
    'Selective conscientious objection', 'Black Power', 'Black self-defense',
    'School desegregation', 'Voter education', 'Native self-determination',
    'Environmental stewardship', 'Indigenous feminism', 'Pan-Africanism',
];

$denyMap = array_flip($deny);

$updated = 0;
$cleared = 0;
$untouched = 0;

Prisoner::whereNotNull('ideologies')->chunkById(200, function ($chunk) use (
    &$updated, &$cleared, &$untouched, $denyMap
) {
    foreach ($chunk as $prisoner) {
        $current = $prisoner->ideologies;
        if (! is_array($current) || empty($current)) { $untouched++; continue; }

        $filtered = [];
        foreach ($current as $value) {
            if (! is_string($value)) { $filtered[] = $value; continue; }
            if (! isset($denyMap[$value])) $filtered[] = $value;
        }

        if (count($filtered) === count($current)) { $untouched++; continue; }

        if (empty($filtered)) {
            $prisoner->ideologies = null;
            $cleared++;
        } else {
            $prisoner->ideologies = array_values($filtered);
        }
        $prisoner->save();
        $updated++;
    }
});

echo "\nIdeology denylist filter complete.\n";
echo sprintf("  Updated:    %d prisoners\n", $updated);
echo sprintf("    of which cleared to NULL: %d\n", $cleared);
echo sprintf("  Untouched:  %d (no denylist values present)\n", $untouched);
echo sprintf("  Denylist:   %d ideology values\n", count($deny));
echo "\n";
