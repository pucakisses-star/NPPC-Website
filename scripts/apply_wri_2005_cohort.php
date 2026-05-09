<?php

declare(strict_types=1);

/**
 * Apply 2002-2005 cases from the WRI Prisoners-for-Peace data the
 * user supplied: SOA Watch Nov 20, 2005 cohort, Sacred Earth and
 * Space Plowshares (Ardeth Platte 2002), Helen Woodson 2004 parole-
 * violation stint, and individual war-tax / Iraq-sanctions / sabotage
 * cases from 2003-2005.
 *
 * For each row, fill blank fields on the existing prisoner and add a
 * new PrisonerCase only if no case row already covers the listed
 * action date. Idempotent.
 *
 * Sections:
 *   A. SOA Watch Nov 20, 2005 cohort at Muscogee County Jail:
 *      - Christine Gaunt (#91356-020) — 6 months from Nov 20, 2005.
 *      - Louis Vitale (#25803-048) — awaiting trial Jan 30, 2006.
 *      - Priscilla Treska — awaiting trial Jan 30, 2006 (NEW).
 *      - Jerome Zawada (#04995-045) — awaiting trial Jan 30, 2006.
 *
 *   B. Ardeth Platte (#10857-039) — Sacred Earth and Space Plowshares
 *      disarmament of a Colorado nuclear missile silo (Oct 6, 2002);
 *      sabotage conviction; 41 months at FPC Danbury, released
 *      Dec 22, 2005.
 *
 *   C. Helen Woodson (#03231-045) — Mar 11, 2004 anti-war protest at
 *      the federal courthouse in Kansas City, MO, in violation of her
 *      parole. Pleaded guilty to four new charges plus the violation
 *      on June 18, 2004. 106 months at FMC Carswell Max Unit.
 *
 *   D. Inge Donato (#40885-050) — 6 months at FDC Philadelphia for
 *      refusal to pay war taxes on religious grounds; convicted
 *      December 2004; released February 6, 2006.
 *
 *   E. Rafil Dhafir (#11921-052) — convicted Feb 2005 for providing
 *      humanitarian and financial aid to Iraqis in violation of U.S.
 *      sanctions; awaiting sentencing Nov 18, 2005 (Jamesville
 *      Correctional Facility).
 *
 *   F. Michael D. Poulin (#14793-097) — 27 months at FPC Sheridan
 *      Unit 5 for damaging electricity transmission towers as a
 *      protest against U.S. empire; released Jan 25, 2006.
 *
 *   G. Laro Nicol (#80430-008) — 2 years at FCI Safford, AZ, framed
 *      on fabricated firearms / explosives charges; pled no contest
 *      to avoid longer term; released June 15, 2006.
 *
 * Heads-up: my earlier scripts/apply_soa_watch_nov2001_cohort.php
 * created "Jerry Zawada" with inmate_number 4995-045 — that's the
 * same person as the existing "Jerome Zawada" (#04995-045 in the DB).
 * The merge_duplicate_prisoners_by_inmate_number.php script needs the
 * register numbers to match exactly to merge them; you may need to
 * normalize the leading zero by hand (or run the merge script after
 * the audit groups them).
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;

$updates = 0; $creates = 0; $unchanged = 0;

// ---------- A. SOA Watch Nov 20, 2005 cohort ----------

$muscogee = Institution::firstOrCreate(
    ['name' => 'Muscogee County Jail'],
    ['city' => 'Columbus', 'state' => 'Georgia', 'mailing_address' => '700 E. 10th St., Columbus GA 31901-2899']
);

$soaContext = "Arrested for trespass during the November 20, 2005 nonviolent civil disobedience action at Fort Benning, Georgia (the U.S. Army Western Hemisphere Institute for Security Cooperation, formerly the School of the Americas) organized by SOA Watch. Held at the Muscogee County Jail in Columbus, GA.";

// Christine Gaunt — 6 months from 2005-11-20.
$gaunt = Prisoner::where('name', 'Christine Gaunt')->first();
if ($gaunt) {
    if (! str_contains((string) $gaunt->description, 'November 20, 2005')) {
        $gaunt->description = trim((string) $gaunt->description) . ' ' . $soaContext;
        $gaunt->save();
        echo "  [update] Christine Gaunt — appended Nov 20, 2005 SOA facts\n"; $updates++;
    }
    if (! $gaunt->cases()->where('arrest_date', '2005-11-20')->exists()) {
        PrisonerCase::create([
            'prisoner_id'        => $gaunt->id,
            'institution_id'     => $muscogee->id,
            'charges'            => '18 U.S.C. § 1382 — entering a military reservation (Fort Benning, GA / WHINSEC), Nov 20, 2005 SOA Watch action.',
            'arrest_date'        => '2005-11-20',
            'incarceration_date' => '2005-11-20',
            'release_date'       => '2006-05-20',
            'sentence'           => '6 months federal misdemeanor at Muscogee County Jail.',
            'convicted'          => 'Yes — § 1382 federal misdemeanor',
        ]);
        echo "  [update] Christine Gaunt — added Nov 20, 2005 SOA case\n"; $updates++;
    }
}

// Louis Vitale, Jerome Zawada, Priscilla Treska — awaiting trial Jan 30, 2006.
$awaitingTrial = [
    ['Louis Vitale',    'Louis',    'Vitale',    null,        false],
    ['Jerome Zawada',   'Jerome',   'Zawada',    null,        false],   // existing record
    ['Priscilla Treska','Priscilla','Treska',    null,        true],    // NEW
];
foreach ($awaitingTrial as [$name, $first, $last, $reg, $isNew]) {
    $p = $isNew ? null : Prisoner::where('name', $name)->first();

    if (! $p && $isNew) {
        $p = Prisoner::create([
            'name'        => $name,
            'first_name'  => $first,
            'last_name'   => $last,
            'description' => $soaContext . ' Stood trial on January 30, 2006 with other activists then on bail.',
            'ideologies'  => ['Pacifism', 'Anti-militarism', 'Anti-imperialism'],
            'affiliation' => ['School of the Americas Watch (SOA Watch)'],
            'era'         => '2000s',
            'in_custody'  => false,
            'released'    => true,
        ]);
        echo "  [add]    {$name} — Nov 20, 2005 SOA arrest\n"; $creates++;
    } elseif ($p) {
        if (! str_contains((string) $p->description, 'November 20, 2005')) {
            $p->description = trim((string) $p->description) . ' ' . $soaContext . ' Stood trial January 30, 2006.';
            $p->save();
            echo "  [update] {$name} — appended Nov 20, 2005 SOA awaiting-trial facts\n"; $updates++;
        }
    } else {
        echo "  [warn]   {$name} not found and not flagged new\n"; continue;
    }

    if (! $p->cases()->where('arrest_date', '2005-11-20')->exists()) {
        PrisonerCase::create([
            'prisoner_id'        => $p->id,
            'institution_id'     => $muscogee->id,
            'charges'            => '18 U.S.C. § 1382 — entering a military reservation (Fort Benning, GA / WHINSEC), Nov 20, 2005 SOA Watch action.',
            'arrest_date'        => '2005-11-20',
            'incarceration_date' => '2005-11-20',
            'release_date'       => null,
            'sentence'           => 'Awaiting trial January 30, 2006 on § 1382 federal misdemeanor; held at Muscogee County Jail pending trial.',
            'convicted'          => 'Pending as of WRI 2005 list',
        ]);
        echo "  [update] {$name} — added Nov 20, 2005 SOA pending case\n"; $updates++;
    }
}

// ---------- B. Ardeth Platte — Sacred Earth and Space Plowshares ----------

$platte = Prisoner::where('name', 'Ardeth Platte')->first();
if ($platte) {
    $note = "On October 6, 2002 Ardeth Platte, Carol Gilbert, and Jackie Hudson — three Dominican Sisters — entered Minuteman III nuclear missile silo N-8 near Greeley, Colorado, painted crosses with their own blood on the silo cover, hammered on it, and prayed. The action was named the \"Sacred Earth and Space Plowshares.\" Platte was convicted of sabotage and obstructing the national defense in U.S. District Court for the District of Colorado and sentenced to 41 months in federal prison; she served the sentence at FPC Danbury, CT and was released December 22, 2005.";
    if (! str_contains((string) $platte->description, 'Sacred Earth and Space')) {
        $platte->description = trim((string) $platte->description) . ' ' . $note;
        $platte->save();
        echo "  [update] Ardeth Platte — appended Sacred Earth and Space Plowshares facts\n"; $updates++;
    }
    if (! $platte->cases()->where('arrest_date', '2002-10-06')->exists()) {
        $danbury = Institution::firstOrCreate(['name' => 'FPC Danbury'], ['city' => 'Danbury', 'state' => 'Connecticut']);
        PrisonerCase::create([
            'prisoner_id'        => $platte->id,
            'institution_id'     => $danbury->id,
            'charges'            => 'Sabotage and obstruction of the national defense (18 U.S.C. § 2155 / § 2154) — Sacred Earth and Space Plowshares disarmament action at Minuteman III silo N-8 near Greeley, CO, October 6, 2002.',
            'arrest_date'        => '2002-10-06',
            'incarceration_date' => '2003-07-22',
            'release_date'       => '2005-12-22',
            'sentence'           => '41 months federal prison at FPC Danbury, CT for sabotage at a nuclear missile silo.',
            'convicted'          => 'Yes — federal jury verdict (D. Colorado)',
        ]);
        echo "  [update] Ardeth Platte — added Sacred Earth and Space Plowshares case\n"; $updates++;
    }
}

// ---------- C. Helen Woodson — Mar 11, 2004 Kansas City courthouse ----------

$woodson = Prisoner::where('name', 'Helen Woodson')->first();
if ($woodson) {
    $note = "On March 11, 2004 — two days after her release from federal prison on March 9, 2004 — Helen Woodson held an anti-war protest at the federal courthouse in Kansas City, Missouri in violation of her parole conditions. She was returned to custody and on June 18, 2004 pleaded guilty to the parole violation and four new charges. She was sentenced to 106 months and held at the Max (Carswell Administrative Maximum) Unit of FMC Carswell, Fort Worth, TX.";
    if (! str_contains((string) $woodson->description, 'March 11, 2004')) {
        $woodson->description = trim((string) $woodson->description) . ' ' . $note;
        $woodson->save();
        echo "  [update] Helen Woodson — appended Mar 11, 2004 Kansas City facts\n"; $updates++;
    }
    if (! $woodson->cases()->where('arrest_date', '2004-03-11')->exists()) {
        $carswell = Institution::firstOrCreate(['name' => 'FMC Carswell (Max Unit)'], ['city' => 'Fort Worth', 'state' => 'Texas']);
        PrisonerCase::create([
            'prisoner_id'        => $woodson->id,
            'institution_id'     => $carswell->id,
            'charges'            => 'Parole violation plus four new charges — anti-war protest at the federal courthouse, Kansas City, MO, March 11, 2004 (two days after release on parole).',
            'arrest_date'        => '2004-03-11',
            'incarceration_date' => '2004-03-11',
            'release_date'       => \Carbon\Carbon::parse('2004-03-11')->addMonths(106)->format('Y-m-d'),
            'sentence'           => '106 months federal prison at the Carswell Max Unit (FMC Carswell, Fort Worth, TX). Pleaded guilty June 18, 2004.',
            'convicted'          => 'Yes — guilty plea',
        ]);
        echo "  [update] Helen Woodson — added Mar 11, 2004 Kansas City case\n"; $updates++;
    }
}

// ---------- D. Inge Donato — war tax refusal ----------

$donato = Prisoner::where('name', 'Inge Donato')->first();
if ($donato) {
    $note = "Convicted in December 2004 for refusal to pay war taxes on religious grounds. Sentenced to 6 months at the Federal Detention Center, Philadelphia (FDC Philadelphia); released February 6, 2006.";
    if (! str_contains((string) $donato->description, 'war taxes')) {
        $donato->description = trim((string) $donato->description) . ' ' . $note;
        $donato->save();
        echo "  [update] Inge Donato — appended war-tax refusal facts\n"; $updates++;
    }
    if (! $donato->cases()->where('charges', 'like', '%war tax%')->exists()) {
        $fdc = Institution::firstOrCreate(['name' => 'FDC Philadelphia'], ['city' => 'Philadelphia', 'state' => 'Pennsylvania']);
        PrisonerCase::create([
            'prisoner_id'        => $donato->id,
            'institution_id'     => $fdc->id,
            'charges'            => 'Refusal to pay federal war taxes on religious grounds (26 U.S.C. § 7203 / related). Convicted December 2004.',
            'arrest_date'        => '2005-08-06',
            'incarceration_date' => '2005-08-06',
            'release_date'       => '2006-02-06',
            'sentence'           => '6 months at FDC Philadelphia.',
            'convicted'          => 'Yes — religious conscientious-objection war-tax refusal',
        ]);
        echo "  [update] Inge Donato — added war-tax-refusal case\n"; $updates++;
    }
}

// ---------- E. Rafil Dhafir — Iraq sanctions case ----------

$dhafir = Prisoner::where('name', 'Dr. Rafil Dhafir')->first()
       ?? Prisoner::where('name', 'Rafil Dhafir')->first();
if ($dhafir) {
    $note = "Convicted in February 2005 in U.S. District Court for the Northern District of New York for providing humanitarian and financial aid to Iraqis in violation of U.S. sanctions. Held at the Jamesville Correctional Facility (Jamesville, NY) awaiting sentencing on November 18, 2005.";
    if (! str_contains((string) $dhafir->description, 'Iraqis in violation of US sanctions') &&
        ! str_contains((string) $dhafir->description, 'humanitarian and financial aid to Iraqis')) {
        $dhafir->description = trim((string) $dhafir->description) . ' ' . $note;
        $dhafir->save();
        echo "  [update] {$dhafir->name} — appended Iraq-sanctions case facts\n"; $updates++;
    }
}

// ---------- F. Michael D. Poulin — transmission towers ----------

$poulin = Prisoner::where('name', 'Michael D. Poulin')->first()
        ?? Prisoner::where('name', 'Michael Poulin')->first();
if ($poulin) {
    $note = "Convicted of damaging electricity transmission towers as a protest against U.S. empire. Sentenced to 27 months at FPC Sheridan, Oregon (Unit 5); released January 25, 2006.";
    if (! str_contains((string) $poulin->description, 'transmission towers') &&
        ! str_contains((string) $poulin->description, 'transmission tower')) {
        $poulin->description = trim((string) $poulin->description) . ' ' . $note;
        $poulin->save();
        echo "  [update] {$poulin->name} — appended transmission-towers facts\n"; $updates++;
    }
    if (! $poulin->cases()->where('charges', 'like', '%transmission tower%')->exists()) {
        $sheridan = Institution::firstOrCreate(['name' => 'Federal Prison Camp Sheridan'], ['city' => 'Sheridan', 'state' => 'Oregon']);
        PrisonerCase::create([
            'prisoner_id'        => $poulin->id,
            'institution_id'     => $sheridan->id,
            'charges'            => 'Damage to electricity transmission towers as protest against U.S. empire / militarism.',
            'arrest_date'        => '2003-10-25',
            'incarceration_date' => '2003-10-25',
            'release_date'       => '2006-01-25',
            'sentence'           => '27 months at FPC Sheridan, OR (Unit 5).',
            'convicted'          => 'Yes',
        ]);
        echo "  [update] {$poulin->name} — added transmission-towers case\n"; $updates++;
    }
}

// ---------- G. Laro Nicol — fabricated firearms / explosives charges ----------

$nicol = Prisoner::where('name', 'Laro Nicol')->first();
if ($nicol) {
    // Inmate number normalization: user has 80430-008; DB has 0430-008 (likely a leading-digit typo).
    if ($nicol->inmate_number === '0430-008') {
        $nicol->inmate_number = '80430-008';
        echo "           inmate_number 0430-008 -> 80430-008 (correcting leading digit per WRI source)\n";
    }
    $note = "Human rights and anti-war activist who pleaded no contest to fabricated firearms and explosives charges in order to avoid a longer term. Held at FCI Safford, Arizona; released June 15, 2006. More info via Phoenix Copwatch (http://www.phoenixcopwatch.org/freelaro.htm).";
    if (! str_contains((string) $nicol->description, 'fabricated firearms')) {
        $nicol->description = trim((string) $nicol->description) . ' ' . $note;
    }
    $nicol->save();
    echo "  [update] Laro Nicol — appended FCI Safford facts and corrected inmate number if needed\n"; $updates++;

    if (! $nicol->cases()->where('release_date', '2006-06-15')->exists()) {
        $safford = Institution::firstOrCreate(['name' => 'FCI Safford'], ['city' => 'Safford', 'state' => 'Arizona']);
        PrisonerCase::create([
            'prisoner_id'        => $nicol->id,
            'institution_id'     => $safford->id,
            'charges'            => 'Federal firearms and explosives charges; defense / supporters say the charges were fabricated against an anti-war and human-rights activist. Pleaded no contest to avoid a longer term.',
            'arrest_date'        => '2004-06-15',
            'incarceration_date' => '2004-06-15',
            'release_date'       => '2006-06-15',
            'sentence'           => '2 years at FCI Safford, AZ (release June 15, 2006).',
            'convicted'          => 'No-contest plea (insufficient defense resources)',
        ]);
        echo "  [update] Laro Nicol — added FCI Safford case\n"; $updates++;
    }
}

echo "\nDone. updates={$updates}, creates={$creates}, unchanged={$unchanged}\n";
