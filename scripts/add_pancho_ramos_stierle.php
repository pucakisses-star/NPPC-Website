<?php

declare(strict_types=1);

/**
 * Add Pancho Ramos Stierle, Occupy Oakland organizer detained by
 * ICE on November 14, 2011 during a peaceful candlelight vigil at
 * the Occupy Oakland encampment in Frank Ogawa Plaza. Held in
 * immigration detention for several weeks (initially in Calif.
 * jails, transferred to ICE facility in Texas). Released January
 * 2012 after a sustained campaign by Oakland faith leaders and
 * activists; granted relief from removal in 2013 after multi-year
 * advocacy.
 *
 * Sources: SF Bay Guardian, Mother Jones, El Tecolote,
 * Indybay, Casa Latina coverage 2011-2013.
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\DB;

if (Prisoner::whereRaw('LOWER(name) IN (?, ?, ?)', [
    'pancho ramos stierle', 'francisco ramos stierle', 'pancho ramos-stierle',
])->exists()) {
    echo "Pancho Ramos Stierle already in DB. Nothing to do.\n";
    return;
}

DB::transaction(function () {
    $alameda = Institution::firstOrCreate(
        ['name' => 'Alameda County Jail'],
        ['city' => 'Oakland', 'state' => 'California']
    );

    $prisoner = Prisoner::create([
        'name'        => 'Pancho Ramos Stierle',
        'first_name'  => 'Francisco',
        'aka'         => 'Pancho, Francisco Ramos Stierle',
        'last_name'   => 'Ramos Stierle',
        'description' => 'Pancho Ramos Stierle, a Mexican-born nonviolence organizer affiliated with Casa de Paz / the Canticle Farm community in Oakland, was detained by ICE on November 14, 2011 while peacefully meditating at a candlelight vigil at the Occupy Oakland encampment in Frank Ogawa Plaza. He was the only protester among hundreds at the eviction-eve vigil that night singled out for ICE pickup, on the basis of an immigration violation tied to a long-expired student visa from his physics doctorate at UC Berkeley. He was held first at the Alameda County Jail, then transferred to ICE detention in Texas (LaSalle / Pearsall area). Following a sustained Oakland-wide campaign — open letters from faith leaders, the SEIU, the United Farm Workers, and prominent figures including Coleman Barks and Father Greg Boyle — Stierle was released on bond in January 2012. After years of advocacy he was granted relief from removal in 2013. Stierle\'s case became a defining moment for the intersection of the immigrant-rights movement and Occupy Oakland.',
        'state'       => 'California',
        'race'        => 'Latino',
        'gender'      => 'Male',
        'ideologies'  => ['Nonviolence', 'Liberation theology'],
        'affiliation' => ['Occupy Movement', 'Occupy Oakland', 'Casa de Paz / Canticle Farm'],
        'era'         => 'Post-9/11',
        'in_custody'  => false,
        'released'    => true,
    ]);

    PrisonerCase::create([
        'prisoner_id'        => $prisoner->id,
        'institution_id'     => $alameda->id,
        'charges'            => 'ICE administrative detention on basis of an expired F-1 student visa (his UC Berkeley astrophysics doctoral program had ended). No criminal charges. Notable: the only person among hundreds at the candlelight vigil singled out for ICE pickup, an action critics argued was retaliation for Stierle\'s organizing visibility.',
        'arrest_date'        => '2011-11-14',
        'incarceration_date' => '2011-11-14',
        'release_date'       => '2012-01-13',
        'plead'              => 'N/A — administrative immigration detention',
        'convicted'          => 'No — civil immigration detention; no criminal conviction',
        'sentence'           => 'Held in ICE detention for approximately two months (Alameda County Jail, then transferred to ICE facility in Texas). Released on bond January 2012 after sustained Oakland faith-leader and labor campaign. Granted relief from removal 2013.',
    ]);

    echo "[create] Pancho Ramos Stierle (id={$prisoner->id})\n";
});

echo "Done.\n";
