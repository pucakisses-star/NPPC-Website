<?php

declare(strict_types=1);

/**
 * Apply additional 1995-1999 anti-nuclear / SOA Watch / Raytheon
 * defendants from the WRI Prisoners-for-Peace data the user supplied.
 *
 * For each row, if a matching prisoner already exists in the DB the
 * script fills missing fields (inmate_number, era, description) and
 * adds a new PrisonerCase if no case row already covers the listed
 * action date. Missing prisoners are created with full Prisoner +
 * PrisonerCase. Idempotent.
 *
 * Sections:
 *   A. Tom Lewis (-Borbely): #03609-036 — 3 months for refusing to
 *      pay restitution from the 1997 Prince of Peace Plowshares case.
 *   B. Martha Scarborough & Joyce Parkhurst — "De-fence" action at
 *      the Nevada nuclear weapons test site.
 *   C. Michele Naar-Obed — 12 months for "Jubilee Plowshares/East"
 *      direct disarmament of a fast-attack submarine, August 1995.
 *      Probation revoked, returned to prison, July 1999.
 *   D. Daniel Sicken (#28360-013) and Oliver Sachio Coe (#28361-013)
 *      — Minuteman III Plowshares direct disarmament, August 6, 1998.
 *      Sicken 41 months FPC Lewisburg; Coe 30 months FPC Allenwood.
 *   E. John Patrick Liteky (#83725-020) — three blood-pouring actions
 *      against the SOA: Pentagon Sept 29 and Oct 20, 1997, plus Fort
 *      Benning Feb 25, 1998. Held at FPC Sheridan, OR.
 *   F. Bill Bichsel S.J. (#86275-020) — SOA Watch action; held at
 *      FPC Sheridan Unit 5, OR.
 *   G. Raytheon blockade defendants (May 28, 1999 corporate-offices
 *      blockade; sentenced October 14, 1999 to 30 days each):
 *      Lauren Cannon, Kateri McCarthy, Eddy Dyer, Sean Donahue,
 *      Jonathan Leavitt, Scott Kenji Warren.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;
use Carbon\Carbon;

$updates = 0; $creates = 0; $unchanged = 0;

$findOne = function (array $candidates): ?Prisoner {
    foreach ($candidates as $name) {
        $hit = Prisoner::where('name', $name)->first();
        if ($hit) return $hit;
    }
    return null;
};

// ---- A. Tom Lewis: 3-month restitution-refusal stint ----

$tomLewis = $findOne(['Tom Lewis', 'Tom Lewis-Borbely']);
if ($tomLewis) {
    if (empty($tomLewis->inmate_number)) {
        $tomLewis->inmate_number = '03609-036';
        $tomLewis->save();
        echo "  [update] Tom Lewis — inmate_number 03609-036 set\n";
        $updates++;
    }
    if (! $tomLewis->cases()->where('charges', 'like', '%restitution%')->exists()) {
        PrisonerCase::create([
            'prisoner_id'        => $tomLewis->id,
            'charges'            => "Refusal to pay restitution from the 1997 Prince of Peace Plowshares conviction (court-ordered \$4,000 restitution).",
            'arrest_date'        => '1999-01-01',
            'incarceration_date' => '1999-01-01',
            'release_date'       => '1999-04-01',
            'sentence'           => '3 months federal custody for civil contempt / refusal to pay PoP-Plowshares restitution.',
            'convicted'          => 'Yes — civil contempt',
        ]);
        echo "  [update] Tom Lewis — added restitution-refusal case (3 mo)\n";
        $updates++;
    }
} else {
    echo "  [warn]   Tom Lewis not found; skipping A\n";
}

// ---- B. Martha Scarborough & Joyce Parkhurst — Nevada Test Site ----

$nyeJail = Institution::firstOrCreate(
    ['name' => 'Nye County Detention Center'],
    ['city' => 'Tonopah', 'state' => 'Nevada', 'mailing_address' => 'PO Box 831, Tonopah, NV 89049']
);

foreach (['Martha Scarborough', 'Joyce Parkhurst'] as $name) {
    $p = Prisoner::where('name', $name)->first();
    if (! $p) {
        echo "  [warn]   {$name} not in DB; skipping\n";
        continue;
    }
    $note = "Arrested for the \"De-fence\" action at the Nevada Nuclear Weapons Test Site (Nevada National Security Site / Mercury, NV) — anti-nuclear nonviolent civil disobedience involving cutting or pulling down the perimeter fence to enter the Test Site. Held at the Nye County Detention Center in Tonopah, NV.";
    if (! str_contains((string) $p->description, 'De-fence')) {
        $p->description = trim((string) $p->description) . ' ' . $note;
        $p->save();
        echo "  [update] {$name} — appended Nevada Test Site De-fence facts\n";
        $updates++;
    }
    if (! $p->cases()->where('institution_id', $nyeJail->id)->exists()) {
        PrisonerCase::create([
            'prisoner_id'        => $p->id,
            'institution_id'     => $nyeJail->id,
            'charges'            => 'Trespass / nonviolent civil disobedience at the Nevada Nuclear Weapons Test Site ("De-fence" action — cutting / pulling down the perimeter fence).',
            'arrest_date'        => '1999-04-01',
            'incarceration_date' => '1999-04-01',
            'release_date'       => '1999-04-08',
            'sentence'           => 'Brief detention at the Nye County Detention Center, Tonopah, NV (representative — exact dates not in source).',
            'convicted'          => 'Yes — misdemeanor trespass',
        ]);
        echo "  [update] {$name} — added Nevada Test Site case\n";
        $updates++;
    }
}

// ---- C. Michele Naar-Obed — Jubilee Plowshares/East ----

$mno = Prisoner::where('name', 'Michele Naar-Obed')->first();
if ($mno) {
    $note = "On August 6, 1995 (Hiroshima Day) Michele Naar-Obed and Brennan Plowshares partners boarded a U.S. Navy fast-attack submarine in port and used hammers and blood to symbolically disarm it as part of the \"Jubilee Plowshares/East\" action. She received 12 months in federal prison. Her probation was later revoked and she was returned to federal custody in July 1999.";
    if (! str_contains((string) $mno->description, 'Jubilee Plowshares')) {
        $mno->description = trim((string) $mno->description) . ' ' . $note;
        $mno->save();
        echo "  [update] Michele Naar-Obed — appended Jubilee Plowshares/East facts\n";
        $updates++;
    }
    if (! $mno->cases()->where('arrest_date', '1995-08-06')->exists()) {
        PrisonerCase::create([
            'prisoner_id'        => $mno->id,
            'charges'            => 'Sabotage / damage to government property — "Jubilee Plowshares/East" boarding and symbolic disarmament of a U.S. Navy fast-attack submarine, August 6, 1995 (Hiroshima Day).',
            'arrest_date'        => '1995-08-06',
            'incarceration_date' => '1995-08-06',
            'release_date'       => '1996-08-06',
            'sentence'           => '12 months federal prison for the Jubilee Plowshares/East action.',
            'convicted'          => 'Yes',
        ]);
        echo "  [update] Michele Naar-Obed — added Jubilee Plowshares/East case\n";
        $updates++;
    }
    if (! $mno->cases()->where('arrest_date', '1999-07-01')->exists()) {
        PrisonerCase::create([
            'prisoner_id'        => $mno->id,
            'charges'            => 'Probation violation — returned to federal custody July 1999 in connection with the 1995 Jubilee Plowshares/East conviction.',
            'arrest_date'        => '1999-07-01',
            'incarceration_date' => '1999-07-01',
            'release_date'       => '2000-01-01',
            'sentence'           => 'Probation-violation incarceration (estimated; exact term not in source).',
            'convicted'          => 'Yes — probation revocation',
        ]);
        echo "  [update] Michele Naar-Obed — added July 1999 probation-revocation case\n";
        $updates++;
    }
} else {
    echo "  [warn]   Michele Naar-Obed not found; skipping C\n";
}

// ---- D. Daniel Sicken & Oliver Sachio Coe — Minuteman III Plowshares ----

$mm3Action = "On August 6, 1998 (Hiroshima Day) Daniel Sicken and Oliver Sachio Coe entered Minuteman III nuclear missile silo \"N-9\" near Concrete, North Dakota, and hammered on its silo lid as the \"Minuteman III Plowshares\" disarmament action. They were prosecuted in federal court (D. North Dakota).";

$sicken = Prisoner::where('name', 'Daniel Sicken')->first();
if ($sicken) {
    if (! str_contains((string) $sicken->description, 'Minuteman III Plowshares')) {
        $sicken->description = trim((string) $sicken->description) . ' ' . $mm3Action;
        $sicken->save();
        echo "  [update] Daniel Sicken — appended Minuteman III Plowshares facts\n";
        $updates++;
    }
    if (! $sicken->cases()->where('arrest_date', '1998-08-06')->exists()) {
        $inst = Institution::firstOrCreate(['name' => 'Federal Prison Camp Lewisburg'], ['city' => 'Lewisburg', 'state' => 'Pennsylvania']);
        PrisonerCase::create([
            'prisoner_id'        => $sicken->id,
            'institution_id'     => $inst->id,
            'charges'            => 'Damage to government property — "Minuteman III Plowshares" disarmament action at silo N-9 near Concrete, ND, August 6, 1998 (Hiroshima Day).',
            'arrest_date'        => '1998-08-06',
            'incarceration_date' => '1998-08-06',
            'release_date'       => Carbon::parse('1998-08-06')->addMonths(41)->format('Y-m-d'),
            'sentence'           => '41 months federal prison at FPC Lewisburg.',
            'convicted'          => 'Yes — federal jury verdict (D. North Dakota)',
        ]);
        echo "  [update] Daniel Sicken — added Minuteman III Plowshares case (41 mo)\n";
        $updates++;
    }
}

// Use the canonical Coe record (Sachio Oliver Coe) per the merge-script ordering.
$coe = Prisoner::where('name', 'Sachio Oliver Coe')->first()
     ?? Prisoner::where('name', 'Oliver Sachio Coe')->first();
if ($coe) {
    if (empty($coe->aka) || ! str_contains((string) $coe->aka, 'Oliver Sachio Coe')) {
        $existing = (string) ($coe->aka ?? '');
        $coe->aka = trim($existing . ($existing ? ', ' : '') . 'Oliver Sachio Coe');
    }
    if (! str_contains((string) $coe->description, 'Minuteman III Plowshares')) {
        $coe->description = trim((string) $coe->description) . ' ' . $mm3Action;
    }
    $coe->save();
    if (! $coe->cases()->where('arrest_date', '1998-08-06')->exists()) {
        $inst = Institution::firstOrCreate(['name' => 'Federal Prison Camp Allenwood'], ['city' => 'Montgomery', 'state' => 'Pennsylvania']);
        PrisonerCase::create([
            'prisoner_id'        => $coe->id,
            'institution_id'     => $inst->id,
            'charges'            => 'Damage to government property — "Minuteman III Plowshares" disarmament action at silo N-9 near Concrete, ND, August 6, 1998 (Hiroshima Day).',
            'arrest_date'        => '1998-08-06',
            'incarceration_date' => '1998-08-06',
            'release_date'       => Carbon::parse('1998-08-06')->addMonths(30)->format('Y-m-d'),
            'sentence'           => '30 months federal prison at FPC Allenwood (Unit AD).',
            'convicted'          => 'Yes — federal jury verdict (D. North Dakota)',
        ]);
        echo "  [update] {$coe->name} — added Minuteman III Plowshares case (30 mo)\n";
        $updates++;
    }
}

// ---- E. John Patrick Liteky — SOA blood pourings ----

$jpl = Prisoner::where('name', 'John Patrick Liteky')->first();
if ($jpl) {
    $note = "Convicted for three blood-pouring direct actions against the U.S. Army School of the Americas (SOA / WHINSEC): the Pentagon on September 29, 1997 and again on October 20, 1997, and Fort Benning, Georgia on February 25, 1998. Held at Federal Prison Camp Sheridan, Oregon (BOP register 83725-020).";
    if (! str_contains((string) $jpl->description, 'blood-pouring')) {
        $jpl->description = trim((string) $jpl->description) . ' ' . $note;
        $jpl->save();
        echo "  [update] John Patrick Liteky — appended SOA blood-pouring facts\n";
        $updates++;
    }
    if (! $jpl->cases()->where('arrest_date', '1998-02-25')->exists()) {
        $inst = Institution::firstOrCreate(['name' => 'Federal Prison Camp Sheridan'], ['city' => 'Sheridan', 'state' => 'Oregon']);
        PrisonerCase::create([
            'prisoner_id'        => $jpl->id,
            'institution_id'     => $inst->id,
            'charges'            => 'Federal trespass / damage to government property for three blood-pouring direct actions against the SOA: Pentagon Sept 29 1997 and Oct 20 1997, plus Fort Benning Feb 25 1998.',
            'arrest_date'        => '1998-02-25',
            'incarceration_date' => '1998-02-25',
            'release_date'       => '1999-02-25',
            'sentence'           => 'Federal prison at FPC Sheridan, OR (representative dates; consolidated sentence for all three actions).',
            'convicted'          => 'Yes',
        ]);
        echo "  [update] John Patrick Liteky — added SOA blood-pouring case\n";
        $updates++;
    }
}

// ---- F. Bill Bichsel S.J. — SOA Watch ----

$bichsel = Prisoner::where('name', 'Bill Bichsel')->first()
        ?? Prisoner::where('name', 'William Bichsel')->first();
if ($bichsel) {
    $note = "Held at Federal Prison Camp Sheridan, Oregon (Unit 5, BOP register 86275-020) for participating in the School of the Americas Watch annual line-crossing protest at Fort Benning, GA.";
    if (! str_contains((string) $bichsel->description, 'School of the Americas Watch')) {
        $bichsel->description = trim((string) $bichsel->description) . ' ' . $note;
        $bichsel->save();
        echo "  [update] {$bichsel->name} — appended SOA Watch / FPC Sheridan facts\n";
        $updates++;
    }
}

// ---- G. Raytheon corporate-offices blockade (May 28, 1999) ----

$raytheonNarrative = "Sentenced October 14, 1999 to 30 days for blockading Raytheon corporate offices on May 28, 1999 — a nonviolent direct action against the world's largest missile manufacturer (Patriot, Tomahawk, AIM-120 air-to-air, AGM-86 cruise missile and others). All defendants were released after serving the 30-day sentences.";

$raytheonRows = [
    ['Lauren Cannon',     'Lauren',   'Cannon'],
    ['Kateri McCarthy',   'Kateri',   'McCarthy'],
    ['Eddy Dyer',         'Eddy',     'Dyer'],
    ['Sean Donahue',      'Sean',     'Donahue'],   // user spelling — note "Sean Donohue" was added separately via the Nuclear Resister script
    ['Jonathan Leavitt',  'Jonathan', 'Leavitt'],
];

foreach ($raytheonRows as [$name, $first, $last]) {
    $p = Prisoner::where('name', $name)->first();
    // For Sean Donahue, also try the alternate spelling that may have been
    // added by the Nuclear Resister script.
    if (! $p && $name === 'Sean Donahue') {
        $p = Prisoner::where('name', 'Sean Donohue')->first();
        if ($p) {
            // Ensure the alternate spelling is preserved as an AKA.
            $existing = (string) ($p->aka ?? '');
            if (! str_contains(mb_strtolower($existing), 'sean donahue')) {
                $p->aka = trim($existing . ($existing ? ', ' : '') . 'Sean Donahue');
                $p->save();
            }
        }
    }

    if ($p) {
        if (! str_contains((string) $p->description, 'Raytheon')) {
            $p->description = trim((string) $p->description) . ' ' . $raytheonNarrative;
            $p->save();
            echo "  [update] {$p->name} — appended Raytheon-blockade facts\n";
            $updates++;
        }
        if (! $p->cases()->where('arrest_date', '1999-05-28')->exists()) {
            PrisonerCase::create([
                'prisoner_id'        => $p->id,
                'charges'            => 'Misdemeanor trespass / disorderly conduct — blockade at Raytheon corporate offices, May 28, 1999.',
                'arrest_date'        => '1999-05-28',
                'incarceration_date' => '1999-10-14',
                'release_date'       => '1999-11-13',
                'sentence'           => '30 days for blockading Raytheon corporate offices, sentenced October 14, 1999.',
                'convicted'          => 'Yes — misdemeanor',
            ]);
            echo "  [update] {$p->name} — added Raytheon-blockade case\n";
            $updates++;
        }
        continue;
    }

    // Net new.
    $p = Prisoner::create([
        'name'        => $name,
        'first_name'  => $first,
        'last_name'   => $last,
        'description' => $raytheonNarrative,
        'ideologies'  => ['Pacifism', 'Anti-militarism', 'Anti-nuclear'],
        'affiliation' => null,
        'era'         => '1990s',
        'in_custody'  => false,
        'released'    => true,
    ]);
    PrisonerCase::create([
        'prisoner_id'        => $p->id,
        'charges'            => 'Misdemeanor trespass / disorderly conduct — blockade at Raytheon corporate offices, May 28, 1999.',
        'arrest_date'        => '1999-05-28',
        'incarceration_date' => '1999-10-14',
        'release_date'       => '1999-11-13',
        'sentence'           => '30 days for blockading Raytheon corporate offices, sentenced October 14, 1999.',
        'convicted'          => 'Yes — misdemeanor',
    ]);
    echo "  [add]    {$name} — Raytheon blockade May 28, 1999\n";
    $creates++;
}

// Scott Kenji Warren also at the Raytheon blockade — add the case to
// the existing record from the prior script (if it was run) or noop.
$skw = Prisoner::where('name', 'Scott Kenji Warren')->first();
if ($skw) {
    if (! $skw->cases()->where('arrest_date', '1999-05-28')->exists()) {
        PrisonerCase::create([
            'prisoner_id'        => $skw->id,
            'charges'            => 'Misdemeanor trespass / disorderly conduct — blockade at Raytheon corporate offices, May 28, 1999.',
            'arrest_date'        => '1999-05-28',
            'incarceration_date' => '1999-10-14',
            'release_date'       => '1999-11-13',
            'sentence'           => '30 days for blockading Raytheon corporate offices, sentenced October 14, 1999.',
            'convicted'          => 'Yes — misdemeanor',
        ]);
        echo "  [update] Scott Kenji Warren — added Raytheon-blockade case\n";
        $updates++;
    }
} else {
    echo "  [warn]   Scott Kenji Warren not yet in DB. Run scripts/apply_soa_watch_nov2000_and_plowshares_cohort.php first.\n";
}

echo "\nDone. updates={$updates}, creates={$creates}, unchanged={$unchanged}\n";
