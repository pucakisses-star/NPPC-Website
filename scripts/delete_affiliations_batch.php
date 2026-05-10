<?php

declare(strict_types=1);

/**
 * Bulk-remove a long list of affiliation values that the user has
 * decided don't belong in the affiliation taxonomy. Mirrors the
 * earlier ideologies cleanup; same idempotent walk over every
 * prisoner.
 *
 * Operates on BOTH `ideologies` and `affiliation` arrays so a
 * value misfiled to either column gets cleaned regardless. Case-
 * insensitive matching catches variants like "occupy chicago" vs
 * "Occupy Chicago".
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$kill = array_map('mb_strtolower', [
    // 5-count tier
    'Teamsters Local 544',
    'Southern Christian Leadership Conference',
    'Ground Zero Center for Nonviolent Action',
    'Anti-NATO summit organizing (Chicago 2012)',
    'Screen Writers Guild',
    'Catholic Left',
    'May Day Movement',
    'Palestinian Islamic Jihad (PIJ)',

    // 4-count tier
    'Tax Resister',
    'United Mine Workers Union',
    'Armed Resistance Unit',
    "National Women's Party (NWP)",
    'Nashville Student Movement',
    'Fellowship of Reconciliation',
    'Nevada Desert Experience',
    'Jericho Movement',
    'U.S. Army (former)',
    'Western Federation of Miners',
    'ELN',
    'Infocom',
    'Hutterite',
    'Rockport Colony',
    'U.S. Army 24th Infantry Regiment',
    'PNW Grand Jury Resisters',

    // 3-count tier
    'Religious Society of Friends',
    'Leonard Peltier Defense Committee',
    'Missionary Oblates of Mary Immaculate',
    'Silo Pruning Hooks',
    'Wilmington student-protester',
    'Voices for Creative Nonviolence',
    'Dominican Sisters of Grand Rapids',
    'Society of Jesus (Jesuits)',
    'Catholic Peace Fellowship',
    'American Friends Service Committee',
    'Network for Strong Communities',
    'Anti-drone',
    'Anti-Iraq-war UK',
    'Project ELF resistance',
    'New Hampshire Peace Action',
    'Soledad Brothers',
    'International Labor Defense',
    'EMETIC',
    'Declare Emergency',
    'Long Island UFO Network',
    'Pledge of Resistance',
    'Honeywell Project',
    'IWW (former)',
    'German Imperial Foreign Service',
    'Church of Jesus Christ of Latter-day Saints (LDS Church)',
    'Occupy Oakland',

    // 2-count tier
    'War Resister',
    'Committee for Non-Violent Action',
    'No-Conscription League',
    'Mother Earth (publication)',
    'Iraq Veterans Against the War',
    'Direct Action Everywhere (DxE)',
    'Order of Friars Minor (Franciscans)',
    'Pax Christi USA',
    'Upstate Drone Action',
    'Episcopal Church',
    'Italian Plowshares',
    'Vieques Committee for the Rescue and Development',
    'Sisters of the Presentation',
    'Roman Catholic Womenpriests',
    'Citizens Inspection',
    'Baltimore Emergency Response Network',
    'The Masses',
    'The Liberator',
    'Lucasville Uprising defendants',
    'Resistance',
    'National Lawyers Guild',
    'Masjid As-Salam (Albany)',
    'Roadblock Earth First!',
    'Appalachians Against Pipelines',
    'Austin Affinity Group',
    'United Socialist Party of Venezuela (PSUV)',
    'Cartel de los Soles',
    'Los Narcosobrinos',
    'XU Free Palestine',
    'At The Well Ministries',
    'Al-Qassam Brigades',
    'West German peace movement',
    'Roman Catholic Church',
    'Religious Society of Friends (Quaker)',
    'RAICES (later)',
    'Sam Melville/Jonathan Jackson Unit',
    'Free South Africa Movement (founder)',
    'Pace e Bene',
    'Nuremberg Action',
    'The Nuclear Resister (co-founder)',
    'Veterans Fast for Life (founder)',
    'Abalone Alliance',
    'Rocky Flats Truth Force',
    'Naropa Institute',
    'Sisters of Loretto',
    'Peacemakers',
    'NWTRCC',
    'Traprock Peace Center',
    'Frayhayt anarchist group',
    'Socialist Party',
    'Archdiocese of Baltimore',
    'Workers Party (post-1946)',
    'Mississippi Freedom Democratic Party',
    'League of American Writers',
    'Cuban Intelligence Directorate',
    'Wasp Network (La Red Avispa)',
    'Congress of Racial Equality (CORE)',
    'Fellowship of Reconciliation (FOR)',
    'Occupy Wall Street',
    'Occupy Seattle',

    // 1-count tier
    'Cuban intelligence (DI)',
    'Occupy Chicago',
    'Casa de Paz / Canticle Farm',
    'Occupy Denver',
    'Occupy San Diego',
]);

$touched = 0;
foreach (Prisoner::query()->cursor() as $p) {
    $changed = false;

    foreach (['ideologies', 'affiliation'] as $field) {
        $arr = $p->$field ?? [];
        if (! is_array($arr) || empty($arr)) continue;

        $out = [];
        foreach ($arr as $v) {
            if (in_array(mb_strtolower(trim((string) $v)), $kill, true)) {
                $changed = true;
                continue;
            }
            $out[] = $v;
        }

        if ($changed) {
            $p->$field = array_values(array_unique($out));
        }
    }

    if ($changed) {
        $p->save();
        echo sprintf("[updated] %s :: ideol=[%s]  aff=[%s]\n",
            $p->name,
            implode(', ', $p->ideologies ?? []),
            implode(', ', $p->affiliation ?? [])
        );
        $touched++;
    }
}

echo "\nDone. Updated {$touched} prisoner(s).\n";
