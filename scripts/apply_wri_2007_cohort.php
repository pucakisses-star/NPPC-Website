<?php

declare(strict_types=1);

/**
 * Apply 2004-2008 updates from the user-supplied WRI data:
 *
 *   A. Helen Woodson (#03231-045) — refines release date for the
 *      March 11, 2004 Kansas City courthouse parole-violation case
 *      to September 9, 2011 (the WRI 2007 source's projected release;
 *      she actually served the full 106 months and was released Sept
 *      9, 2011 from FMC Carswell Max Unit).
 *
 *   B. Joseph Donato (#40884-050) — NEW. 27 months for refusal to
 *      pay federal war taxes on religious grounds; convicted Dec 2004;
 *      released Jan 31, 2008 from CCM Philadelphia.
 *
 *   C. Rafil Dhafir (#11921-052) — refines release date and institution
 *      for the Feb 2005 conviction case (humanitarian/financial aid to
 *      Iraqis in violation of U.S. sanctions): 22-year sentence served
 *      at FCI Terre Haute, released April 26, 2022.
 *
 *   D. Louis Vitale (#25803-048) and Stephen Kelly (#00816-111) —
 *      both received 5-month federal sentences for a "prayerful
 *      trespass" at U.S. Army Intelligence Headquarters, Fort
 *      Huachuca, Sierra Vista, Arizona on November 6, 2006, where
 *      they attempted to deliver a letter protesting official U.S.
 *      torture policy (Guantánamo, Abu Ghraib, the SERE-derived
 *      "enhanced interrogation" program). Both released March 14,
 *      2008.
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;

$updates = 0; $creates = 0; $unchanged = 0;

// ---- A. Helen Woodson — set release_date 2011-09-09 ----

$woodson = Prisoner::where('name', 'Helen Woodson')->first();
if ($woodson) {
    $case = $woodson->cases()->where('arrest_date', '2004-03-11')->first();
    if ($case) {
        if ((string) $case->release_date !== '2011-09-09') {
            $case->release_date = '2011-09-09';
            $case->sentence    = '106 months federal prison at the Carswell Max Unit (FMC Carswell, Fort Worth, TX). Pleaded guilty June 18, 2004; released September 9, 2011.';
            $case->save();
            echo "  [update] Helen Woodson — release_date set to 2011-09-09\n"; $updates++;
        } else {
            echo "  [ok]     Helen Woodson — release date already 2011-09-09\n"; $unchanged++;
        }
    } else {
        echo "  [warn]   Helen Woodson Mar 11 2004 case not found; run scripts/apply_wri_2005_cohort.php first.\n";
    }
}

// ---- B. Joseph Donato — NEW (war-tax refusal, 27 months) ----

if (! Prisoner::where('name', 'Joseph Donato')->exists()) {
    $ccm = Institution::firstOrCreate(
        ['name' => 'CCM Philadelphia'],
        ['city' => 'Philadelphia', 'state' => 'Pennsylvania', 'mailing_address' => '2nd Chestnut St., Philadelphia, PA 19106']
    );
    $p = Prisoner::create([
        'name'          => 'Joseph Donato',
        'first_name'    => 'Joseph',
        'last_name'     => 'Donato',
        'inmate_number' => '40884-050',
        'description'   => "Joseph Donato was convicted in December 2004 for refusal to pay federal war taxes on religious grounds. Sentenced to 27 months federal prison; released January 31, 2008 from CCM Philadelphia (Community Corrections Management). Joseph and Inge Donato are connected — both are Catholic war-tax resisters from the same family.",
        'ideologies'    => ['Pacifism', 'Christian peace witness', 'Anti-militarism', 'War tax resistance'],
        'affiliation'   => null,
        'era'           => '2000s',
        'in_custody'    => false,
        'released'      => true,
    ]);
    PrisonerCase::create([
        'prisoner_id'        => $p->id,
        'institution_id'     => $ccm->id,
        'charges'            => 'Refusal to pay federal war taxes on religious grounds (26 U.S.C. § 7203 / related). Convicted December 2004.',
        'arrest_date'        => '2005-10-31',
        'incarceration_date' => '2005-10-31',
        'release_date'       => '2008-01-31',
        'sentence'           => '27 months at CCM Philadelphia for religious war-tax refusal.',
        'convicted'          => 'Yes — religious conscientious-objection war-tax refusal',
    ]);
    echo "  [add]    Joseph Donato  reg=40884-050  (war-tax refusal, 27 mo)\n"; $creates++;
} else {
    echo "  [ok]     Joseph Donato — already in DB\n"; $unchanged++;
}

// ---- C. Rafil Dhafir — refine release date / institution ----

$dhafir = Prisoner::where('name', 'Dr. Rafil Dhafir')->first()
       ?? Prisoner::where('name', 'Rafil Dhafir')->first();
if ($dhafir) {
    $note = "Sentenced to 22 years federal prison; served at FCI Terre Haute, Indiana; released April 26, 2022.";
    if (! str_contains((string) $dhafir->description, 'FCI Terre Haute')) {
        $dhafir->description = trim((string) $dhafir->description) . ' ' . $note;
        $dhafir->save();
        echo "  [update] {$dhafir->name} — appended FCI Terre Haute / 22-year / Apr 2022 release facts\n"; $updates++;
    }
    $terreHaute = Institution::firstOrCreate(['name' => 'FCI Terre Haute'], ['city' => 'Terre Haute', 'state' => 'Indiana']);
    $case = $dhafir->cases()->orderBy('arrest_date')->first() ?? new PrisonerCase(['prisoner_id' => $dhafir->id]);
    $caseChanged = false;
    if ($case->institution_id !== $terreHaute->id)        { $case->institution_id = $terreHaute->id; $caseChanged = true; }
    if ((string) $case->release_date !== '2022-04-26')    { $case->release_date   = '2022-04-26';    $caseChanged = true; }
    if (! str_contains((string) $case->sentence, '22 years')) {
        $case->sentence = '22 years federal prison at FCI Terre Haute for providing humanitarian and financial aid to Iraqis in violation of U.S. sanctions; released April 26, 2022.';
        $caseChanged = true;
    }
    if (empty($case->charges)) {
        $case->charges = 'International Emergency Economic Powers Act (IEEPA) violations / providing humanitarian and financial aid to Iraqis in violation of U.S. sanctions. Convicted Feb 2005.';
        $caseChanged = true;
    }
    if (empty($case->convicted)) { $case->convicted = 'Yes — federal jury verdict (N.D.N.Y., Feb 2005)'; $caseChanged = true; }
    if ($caseChanged) {
        if (! $case->prisoner_id) $case->prisoner_id = $dhafir->id;
        $case->save();
        echo "  [update] {$dhafir->name} — case updated with FCI Terre Haute / Apr 2022 release\n"; $updates++;
    }
}

// ---- D. Louis Vitale & Stephen Kelly — Ft. Huachuca prayerful trespass ----

$huachuca = "On November 6, 2006 Louis Vitale and Stephen Kelly conducted a 'prayerful trespass' at U.S. Army Intelligence Headquarters, Fort Huachuca, Sierra Vista, Arizona, attempting to deliver a letter protesting official U.S. torture policy at Guantánamo, Abu Ghraib, and through the SERE-derived 'enhanced interrogation' program. Both were convicted of federal trespass (18 U.S.C. § 1382) and sentenced to 5 months in federal custody; both were released March 14, 2008.";

$pair = [
    ['Louis Vitale',           '25803-048'],
    ['Stephen Michael Kelly',  '00816-111'],
];
foreach ($pair as [$name, $reg]) {
    $p = Prisoner::where('name', $name)->first()
       ?? Prisoner::where('name', explode(' ', $name)[0] . ' ' . array_slice(explode(' ', $name), -1)[0])->first()
       ?? ($name === 'Stephen Michael Kelly' ? Prisoner::where('name', 'Steve Kelly')->first() : null);

    if (! $p) {
        echo "  [warn]   {$name} not found; skipping Ft. Huachuca add\n";
        continue;
    }

    if (empty($p->inmate_number)) {
        $p->inmate_number = $reg;
        $p->save();
    }

    if (! str_contains((string) $p->description, 'Fort Huachuca')) {
        $p->description = trim((string) $p->description) . ' ' . $huachuca;
        $p->save();
        echo "  [update] {$p->name} — appended Ft. Huachuca prayerful-trespass facts\n"; $updates++;
    }

    if (! $p->cases()->where('arrest_date', '2006-11-06')->exists()) {
        PrisonerCase::create([
            'prisoner_id'        => $p->id,
            'charges'            => '18 U.S.C. § 1382 — entering a military reservation. November 6, 2006 prayerful trespass at U.S. Army Intelligence Headquarters, Fort Huachuca, Sierra Vista, AZ to protest official U.S. torture policy.',
            'arrest_date'        => '2006-11-06',
            'incarceration_date' => '2007-10-14',
            'release_date'       => '2008-03-14',
            'sentence'           => '5 months federal custody for the Ft. Huachuca prayerful trespass; released March 14, 2008.',
            'convicted'          => 'Yes — § 1382 federal misdemeanor',
        ]);
        echo "  [update] {$p->name} — added Ft. Huachuca case\n"; $updates++;
    }
}

echo "\nDone. updates={$updates}, creates={$creates}, unchanged={$unchanged}\n";
