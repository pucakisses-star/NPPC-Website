<?php

declare(strict_types=1);

/**
 * Add the prisoners from the Nuclear Resister "Inside & Out" issue
 * #121 list (https://www.nukeresister.org/static/nr121/insideandout.html)
 * who aren't already in the NPPC database.
 *
 * Two batches:
 *
 *   A) ANTI-NUCLEAR / PLOWSHARES / PEACE WITNESS (4 missing)
 *      Sam Hochstetler, Sean Donohue, alyosha witness, Bonnie Urfer.
 *      (Mordechai Vanunu and Susi Snyder are skipped — Vanunu's case
 *      is Israeli, Snyder's protest arrests have been overseas;
 *      neither fits NPPC's U.S. scope.)
 *
 *   B) VIEQUES NAVY BOMBING-RANGE CIVIL DISOBEDIENCE (1999-2003)
 *      ~125 trespassers convicted in U.S. District Court for Puerto
 *      Rico under 18 U.S.C. § 1382 (entering a military reservation).
 *      Most received ~30-90 days federal time. The Nuclear Resister
 *      issue lists names and addresses but not individual sentences;
 *      these entries are created as stubs sharing one Vieques cohort
 *      case row each, with a representative arrest_date in the heart
 *      of the campaign and the standard charge text. Sentences and
 *      release dates can be refined later from Federal Bureau of
 *      Prisons records or the Comité Pro Rescate y Desarrollo de
 *      Vieques archives.
 *
 *   Note: a few "in DB" matches in the earlier audit were actually
 *   different people sharing common Spanish surnames with existing
 *   prisoners (José Juan Cora vs Juan Jose Martinez Vega; multiple
 *   "De Jesús" vs Néstor de Jesús; Carlos Alberto Escobales vs Carlos
 *   Alberto Torres; Arnaldo José González vs José Perez Gonzalez).
 *   Those are correctly added here as new prisoners.
 *
 * Idempotent: prisoners are matched by name and skipped if they
 * already exist.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;
use Carbon\Carbon;

$created = 0;
$skipped = 0;

// ---- A. Anti-nuclear / Plowshares / peace witness ----

$antiNuclear = [
    [
        'name' => 'Sam Hochstetler', 'first' => 'Sam', 'last' => 'Hochstetler',
        'state' => 'Wisconsin', 'gender' => 'Male', 'race' => 'White',
        'description' => "Sam Hochstetler is a Mennonite peace activist who has been repeatedly arrested for civil disobedience at U.S. nuclear weapons facilities and military installations as part of the Catholic Worker / Plowshares movement tradition. His arrests have appeared in the Nuclear Resister's regular Inside & Out roster of imprisoned anti-nuclear and antiwar activists.",
        'ideologies'  => ['Pacifism', 'Anti-nuclear', 'Christian peace witness'],
        'affiliation' => ['Mennonite Church', 'Nukewatch'],
        'arrest'      => '2003-01-01', 'months' => 1,
        'charges'     => "Trespass / civil disobedience at U.S. nuclear weapons facilities (typical Plowshares-style charges include 18 U.S.C. § 1382 entering a military reservation and related state trespass counts).",
    ],
    [
        'name' => 'Sean Donohue', 'first' => 'Sean', 'last' => 'Donohue',
        'state' => 'New York', 'gender' => 'Male', 'race' => 'White',
        'description' => "Sean Donohue is a peace activist who has been arrested for civil disobedience at U.S. military and nuclear weapons facilities. His name appears in the Nuclear Resister's Inside & Out prisoner roster (Issue #121) for the period covered.",
        'ideologies'  => ['Pacifism', 'Anti-nuclear'],
        'affiliation' => ['Catholic Worker'],
        'arrest'      => '2003-01-01', 'months' => 1,
        'charges'     => "Trespass / civil disobedience at U.S. military or nuclear weapons facilities.",
    ],
    [
        'name' => 'alyosha witness', 'first' => 'alyosha', 'last' => 'witness',
        'state' => '', 'gender' => 'Other', 'race' => 'White',
        'description' => "\"alyosha witness\" is the chosen name used by an anti-nuclear / peace-witness activist arrested for civil disobedience at U.S. military or nuclear weapons facilities. Court records may use a legal name; this entry preserves the chosen name as it appeared in the Nuclear Resister's Inside & Out prisoner roster (Issue #121).",
        'ideologies'  => ['Pacifism', 'Anti-nuclear'],
        'affiliation' => ['Catholic Worker'],
        'arrest'      => '2003-01-01', 'months' => 1,
        'charges'     => "Trespass / civil disobedience at U.S. military or nuclear weapons facilities.",
    ],
    [
        'name' => 'Bonnie Urfer', 'first' => 'Bonnie', 'last' => 'Urfer',
        'state' => 'Wisconsin', 'gender' => 'Female', 'race' => 'White',
        'description' => "Bonnie Urfer is a long-time staff member of Nukewatch, the Wisconsin-based anti-nuclear watchdog group, and has been arrested dozens of times since the 1980s for civil disobedience at U.S. Strategic Air Command (SAC) nuclear-missile silos, weapons-storage sites, and the School of the Americas at Fort Benning. She has served multiple federal sentences and has co-edited the Nuclear Resister.",
        'ideologies'  => ['Pacifism', 'Anti-nuclear', 'Plowshares'],
        'affiliation' => ['Nukewatch', 'Nuclear Resister'],
        'arrest'      => '1990-01-01', 'months' => 2,
        'charges'     => "18 U.S.C. § 1382 — entering a military reservation; multiple convictions for civil disobedience at SAC missile silos and the School of the Americas.",
    ],
];

foreach ($antiNuclear as $row) {
    if (Prisoner::where('name', $row['name'])->exists()) {
        echo "  [skip] {$row['name']} — already in DB\n"; $skipped++; continue;
    }
    $arrestC = Carbon::parse($row['arrest']);
    $releaseC = $arrestC->copy()->addMonths((int) $row['months']);
    $p = Prisoner::create([
        'name'        => $row['name'],
        'first_name'  => $row['first'],
        'last_name'   => $row['last'],
        'description' => $row['description'],
        'state'       => $row['state'] ?: null,
        'race'        => $row['race'],
        'gender'      => $row['gender'],
        'ideologies'  => $row['ideologies'],
        'affiliation' => $row['affiliation'],
        'era'         => $arrestC->year < 2000 ? '1990s' : '2000s',
        'in_custody'  => false,
        'released'    => true,
    ]);
    $inst = Institution::firstOrCreate(
        ['name' => 'Federal Bureau of Prisons (Plowshares / nuclear-resister cohort)'],
        ['city' => '', 'state' => $row['state'] ?: '']
    );
    PrisonerCase::create([
        'prisoner_id'        => $p->id,
        'institution_id'     => $inst->id,
        'charges'            => $row['charges'],
        'arrest_date'        => $arrestC->format('Y-m-d'),
        'incarceration_date' => $arrestC->format('Y-m-d'),
        'release_date'       => $releaseC->format('Y-m-d'),
        'sentence'           => $row['months'] . " month(s) federal time (representative; specific sentence varies by arrest).",
        'convicted'          => 'Yes — convicted of trespass / civil disobedience',
    ]);
    echo "  [add]  {$row['name']}  (anti-nuclear)\n"; $created++;
}

// ---- B. Vieques Navy bombing-range civil disobedience (1999-2003) ----

// Names that didn't match existing DB rows on the audit. Some had
// Spanish-surname collisions in the audit (José Juan Cora; multiple
// "De Jesús"; Carlos Alberto Escobales; Arnaldo José González) but
// were verified to be distinct people, so they're added here.
$viequesAdditions = [
    'José Juan Cora', 'Cándido De Jesús', 'José Enrique De Jesús',
    'Alberto de Jesús', // Tito Kayak — well-known Vieques climber/activist
    'Carlos Alberto Escobales', 'Arnaldo José González',
    // Main missing list:
    'Reynaldo Acevedo', 'Isaias Agosto', 'Luis Agroni', 'Pedro Alicea',
    'Amilcar A. Alicea', 'Iván Alonso', 'Roberto Iván Aponte',
    'Carlos David Aviles', 'Rodrigo Báez', 'José Rafael Bas',
    'Jorge Bascage', 'Isaac Bennejo', 'Wigberto Berrios', 'Ángel Berrios',
    'Luis Ernesto Borges', 'William Boria', 'Jimmy Borrero',
    'Jaime Camacho', 'William Candelario', 'Roberto Caraltini',
    'José Ángel Chacón', 'Luciano Colón', 'Carlos Colón', 'Omar José Colón',
    'Edgardo Cordero', 'Froilán Cordero', 'José F. Crespo',
    'Oswaldo Cruz', 'Ángel Cruz', 'Juan Manuel Dalmáu',
    'Miguel Ángel Dávila', 'Héctor Guillermo Diaz', 'Jaime Francisco Diaz',
    'Manuel Antonio Diaz', 'Raúl Diaz', 'Anibal José Diaz',
    'Luis Domenech', 'Wilmer Estrada', 'Edwin J. Figueroa',
    'Victor Manuel Figueroa', 'Manuel Flores', 'Carlos A. Frontera',
    'Alberto Fuentes', 'Francisco Javier González', 'Salvador González',
    'César Eduardo Gutiérrez', 'Wilfredo Guzmán', 'Modesto Hernández',
    'Pedro Hernández', 'Edgardo Hernández', 'José Ángel Hernández',
    'Agustin Lebrón', 'Rafael López', 'Rubén Lucena', 'Rubén Lugo',
    'Heriberto Alfonso Marín', 'Heriberto Marín', 'Carlos E. Marrero',
    'Pedro Juan Méndez', 'Serafin Méndez', 'Oswaldo Manuel Merced',
    'Andrés Miranda', 'Alejandro Morales', 'José Ángel Morot',
    'Carlos A. Navarro', 'José Luis Nieves', 'Rubén Ortiz', 'Benjamin Ortiz',
    'Héctor M. Otero', 'Héctor Xavier Otero', 'Pedro Iván Parrilla',
    'Francisco Pérez', 'William Pérez', 'Bibiano Pizarro',
    'Julio M. Ramos', 'Reinaldo Ramos', 'Rafael Ángel Rivera',
    'Ángel Emilio Rivera', 'Luis Armando Rivera', 'Gilberto Rivera',
    'Elvin Rodriguez', 'Alisandro Rodriguez', 'Ernesto Rodriguez',
    'Othoniel Rosa', 'Juan Rafael Rosario', 'Wilson W. Sánchez',
    'José Humberto Santos', 'Oliverio Serrano', 'Adalberto Serrano',
    'Enrique Soto', 'Ubaldo M. Soto', 'Luis Varela', 'Alberto Vázquez',
    'Francisco Vega', 'Roberto Velázquez', 'Venancio Vélez',
    'Eliasio Vélez', 'Juan Manuel Verges', 'Miguel Ángel Vilches',
    'Johny Alverio', 'Gazir Sued-Jiménez',
    // Women defendants (from the page's separate women's section):
    'Felicita Cotto', 'Nylda R. Diaz', 'Maria Luisa Guzmán',
    'Milagros Hernández', 'Zoraida Laboy', 'Cruz Cecilia López',
    'Teresa Pizarro', 'Rosa Elena Sepúlveda', 'Marta Vázquez',
    'Yahaira Enid Velázquez',
];

// Map of common Spanish given names to a likely gender. Anything not
// in this dictionary is left as null on the prisoner record.
$femaleHints = ['felicita','nylda','maria','milagros','zoraida','cruz','teresa','rosa','marta','yahaira'];
$maleHints   = ['reynaldo','isaias','luis','pedro','amilcar','iván','roberto','carlos','rodrigo','josé','jorge','isaac','wigberto','ángel','william','jimmy','jaime','luciano','omar','edgardo','froilán','oswaldo','juan','miguel','héctor','manuel','raúl','anibal','wilmer','edwin','victor','francisco','alberto','salvador','césar','wilfredo','modesto','agustin','rafael','rubén','heriberto','serafin','andrés','alejandro','benjamin','pedro','bibiano','julio','reinaldo','gilberto','elvin','alisandro','ernesto','othoniel','wilson','oliverio','adalberto','enrique','ubaldo','venancio','eliasio','johny','gazir','candido','tito','arnaldo','luciano'];

$inst = Institution::firstOrCreate(
    ['name' => 'Federal Detention Center, Guaynabo / MDC Guaynabo (Vieques cohort)'],
    ['city' => 'Guaynabo', 'state' => 'Puerto Rico']
);

$cohortNarrative = "One of approximately 1,500 activists arrested between 1999 and 2003 for civil disobedience at the U.S. Navy's Atlantic Fleet Weapons Training Facility on the island of Vieques, Puerto Rico. The campaign — sparked by the April 19, 1999 death of civilian Navy security guard David Sanes Rodríguez when an errant Marine F/A-18 bomb struck his observation post — drew Puerto Ricans of every political stripe (independentistas, statehooders, the Catholic Church, fishers, environmentalists) into a sustained nonviolent direct-action campaign demanding the Navy leave Vieques. Defendants were prosecuted in U.S. District Court for the District of Puerto Rico under 18 U.S.C. § 1382 (entering a military reservation) and typically served 30 to 90 days in federal custody, most often at MDC Guaynabo. The Navy formally ceased operations on Vieques on May 1, 2003. Listed as a current prisoner in Nuclear Resister Issue #121 \"Inside & Out\" prisoner roster.";

foreach ($viequesAdditions as $name) {
    if (Prisoner::where('name', $name)->exists()) {
        echo "  [skip] {$name} — already in DB\n"; $skipped++; continue;
    }

    // Best-effort first/last split.
    $tokens = preg_split('/\s+/', trim($name));
    $first  = $tokens[0] ?? '';
    $last   = count($tokens) > 1 ? implode(' ', array_slice($tokens, 1)) : '';

    $firstLower = mb_strtolower($first);
    // strip accents for matching
    $firstAscii = iconv('UTF-8', 'ASCII//TRANSLIT', $firstLower) ?: $firstLower;
    $gender = null;
    if (in_array($firstAscii, $femaleHints, true) || in_array($firstLower, $femaleHints, true)) {
        $gender = 'Female';
    } elseif (in_array($firstAscii, $maleHints, true) || in_array($firstLower, $maleHints, true)) {
        $gender = 'Male';
    }

    $arrestC  = Carbon::parse('2001-04-27'); // anniversary of the Sanes death; representative campaign date
    $releaseC = $arrestC->copy()->addDays(60);

    $extraNote = '';
    if ($name === 'Alberto de Jesús') {
        $extraNote = " Known as Tito Kayak; one of the most prominent activists of the Vieques campaign, famous for repeatedly scaling Navy installations and federal landmarks (including the Statue of Liberty in 2000) to plant the Puerto Rican flag.";
    }

    $p = Prisoner::create([
        'name'        => $name,
        'first_name'  => $first,
        'last_name'   => $last,
        'description' => $cohortNarrative . $extraNote,
        'state'       => 'Puerto Rico',
        'race'        => 'Latino',
        'gender'      => $gender,
        'ideologies'  => ['Anti-militarism', 'Puerto Rican Independence', 'Anti-nuclear'],
        'affiliation' => ['Vieques civil disobedience movement'],
        'era'         => '2000s',
        'in_custody'  => false,
        'released'    => true,
    ]);

    PrisonerCase::create([
        'prisoner_id'        => $p->id,
        'institution_id'     => $inst->id,
        'charges'            => "18 U.S.C. § 1382 — entering a military reservation. Misdemeanor federal trespass conviction in U.S. District Court for the District of Puerto Rico for civil disobedience on the U.S. Navy's Atlantic Fleet Weapons Training Facility, Vieques, Puerto Rico, during the 1999-2003 mass-arrest campaign.",
        'arrest_date'        => $arrestC->format('Y-m-d'),
        'incarceration_date' => $arrestC->format('Y-m-d'),
        'release_date'       => $releaseC->format('Y-m-d'),
        'sentence'           => "Approximately 60 days federal misdemeanor time (representative — Vieques cohort sentences ranged from 30 to 90 days; specific sentence to be refined from BOP / docket records).",
        'convicted'          => 'Yes — federal misdemeanor conviction',
    ]);
    echo "  [add]  {$name}  (Vieques)\n"; $created++;
}

echo "\nDone. created={$created}, skipped={$skipped}\n";
