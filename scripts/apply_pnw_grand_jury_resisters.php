<?php

declare(strict_types=1);

/**
 * Add the four Pacific Northwest Grand Jury Resisters jailed for
 * civil contempt in 2012-2013 after refusing to testify before a
 * federal grand jury that was investigating PNW anarchist circles
 * in the wake of Occupy and the Seattle May Day 2012 protests:
 *
 *   - Matt Duran               (~5 months at SeaTac FDC)
 *   - Katherine "KteeO" Olejnik (~5 months at SeaTac FDC)
 *   - Maddy Pfeiffer           (~7 months at SeaTac FDC)
 *   - Leah-Lynn Plante         (briefly held in October 2012)
 *
 * None were ever charged with or convicted of any crime — they
 * were jailed solely for refusing to cooperate with the grand
 * jury (a coercive civil-contempt remedy that ends when the
 * grand jury concludes). The grand jury wound down in 2013 with
 * no indictments returned.
 *
 * Idempotent: skips by name match.
 *
 * Sources: NLG-Seattle records, It's Going Down, Sparrows Nest
 * archive, Stranger / Seattle Times contemporaneous coverage,
 * Committee Against Political Repression statements.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\DB;

// SeaTac FDC already exists in the institutions table — look it
// up by name; firstOrCreate as a safety net.
$seaTac = Institution::firstOrCreate(
    ['name' => 'SeaTac Federal Detention Center'],
    ['city' => 'SeaTac', 'state' => 'Washington']
);

$entries = [
    [
        'prisoner' => [
            'name'         => 'Matt Duran',
            'first_name'   => 'Matt',
            'last_name'    => 'Duran',
            'description'  => 'Matt Duran was the first of four Pacific Northwest activists jailed in 2012-2013 for civil contempt after refusing to testify before a federal grand jury convened in the wake of Occupy and the Seattle May Day 2012 protests. Subpoenaed in August 2012, Duran appeared, declined to answer questions, and was sent to the SeaTac Federal Detention Center on September 13, 2012. He was held without charge for approximately five months and released on February 28, 2013 after the grand jury\'s term wound down without returning indictments. Duran was never charged with or convicted of any crime; the entire detention was the coercive civil-contempt remedy used to pressure grand jury testimony.',
            'state'        => 'Washington',
            'race'         => 'White',
            'gender'       => 'Male',
            'ideologies'   => ['Anarchism'],
            'affiliation'  => ['Occupy Movement', 'PNW Grand Jury Resisters'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst' => $seaTac,
            'charges' => "Civil contempt of court (refusal to testify before federal grand jury). Never charged with any underlying offense; held purely to coerce grand jury testimony.",
            'arrest_date' => '2012-09-13',
            'sentenced_date' => '2012-09-13',
            'incarceration_date' => '2012-09-13',
            'release_date' => '2013-02-28',
            'judge' => 'Hon. Richard A. Jones (W.D. Wash.)',
            'plead' => 'N/A — civil contempt, no plea',
            'convicted' => 'No — never charged or convicted; held for civil contempt',
            'sentence' => 'Civil contempt; held until grand jury term expired. ~5 months at SeaTac FDC.',
        ],
    ],
    [
        'prisoner' => [
            'name'         => 'Katherine Olejnik',
            'first_name'   => 'Katherine',
            'last_name'    => 'Olejnik',
            'aka'          => 'KteeO, Kteeo Olejnik',
            'description'  => 'Katherine "KteeO" Olejnik was the second of the PNW Grand Jury Resisters jailed for civil contempt during the 2012-2013 federal grand jury investigation of Pacific Northwest anarchist circles. Subpoenaed and refusing to testify, she was sent to the SeaTac Federal Detention Center on September 27, 2012, where she was held alongside Matt Duran. Olejnik was placed in solitary confinement for stretches of her detention and her treatment generated significant national support-and-press response from prisoner-rights organizations. Released February 28, 2013 when the grand jury wound down without indictments. Never charged with or convicted of any crime.',
            'state'        => 'Washington',
            'race'         => 'White',
            'gender'       => 'Female',
            'ideologies'   => ['Anarchism'],
            'affiliation'  => ['Occupy Movement', 'PNW Grand Jury Resisters'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst' => $seaTac,
            'charges' => "Civil contempt of court (refusal to testify before federal grand jury). Never charged with any underlying offense.",
            'arrest_date' => '2012-09-27',
            'sentenced_date' => '2012-09-27',
            'incarceration_date' => '2012-09-27',
            'release_date' => '2013-02-28',
            'judge' => 'Hon. Richard A. Jones (W.D. Wash.)',
            'plead' => 'N/A — civil contempt, no plea',
            'convicted' => 'No — never charged or convicted; held for civil contempt',
            'sentence' => 'Civil contempt; ~5 months at SeaTac FDC. Held in solitary confinement for portions of her detention.',
        ],
    ],
    [
        'prisoner' => [
            'name'         => 'Maddy Pfeiffer',
            'first_name'   => 'Maddy',
            'last_name'    => 'Pfeiffer',
            'description'  => 'Maddy Pfeiffer was the longest-held of the PNW Grand Jury Resisters: subpoenaed by a successor federal grand jury after Duran and Olejnik were released, Pfeiffer was sent to the SeaTac Federal Detention Center on February 12, 2013 and held for approximately seven months until release on September 27, 2013. As with the other resisters, the entire detention was coercive civil contempt — Pfeiffer was never charged with or convicted of any crime. The grand jury wound down without returning indictments.',
            'state'        => 'Washington',
            'race'         => 'White',
            'gender'       => 'Non-binary',
            'ideologies'   => ['Anarchism'],
            'affiliation'  => ['Occupy Movement', 'PNW Grand Jury Resisters'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst' => $seaTac,
            'charges' => "Civil contempt of court (refusal to testify before federal grand jury). Never charged with any underlying offense.",
            'arrest_date' => '2013-02-12',
            'sentenced_date' => '2013-02-12',
            'incarceration_date' => '2013-02-12',
            'release_date' => '2013-09-27',
            'judge' => 'Hon. Richard A. Jones (W.D. Wash.)',
            'plead' => 'N/A — civil contempt, no plea',
            'convicted' => 'No — never charged or convicted; held for civil contempt',
            'sentence' => 'Civil contempt; ~7 months at SeaTac FDC, the longest of the four PNW resisters.',
        ],
    ],
    [
        'prisoner' => [
            'name'         => 'Leah-Lynn Plante',
            'first_name'   => 'Leah-Lynn',
            'last_name'    => 'Plante',
            'description'  => 'Leah-Lynn Plante was subpoenaed before the same federal grand jury that jailed Duran and Olejnik; she briefly was jailed for civil contempt in October 2012 after refusing to testify, then released within a short period after a court ruling. Plante later released a public statement explaining her refusal that became widely circulated in anarchist and prisoner-rights circles. As with the other PNW Grand Jury Resisters, she was never charged with or convicted of any crime.',
            'state'        => 'Washington',
            'race'         => 'White',
            'gender'       => 'Female',
            'ideologies'   => ['Anarchism'],
            'affiliation'  => ['Occupy Movement', 'PNW Grand Jury Resisters'],
            'era'          => 'Post-9/11',
            'in_custody'   => false,
            'released'     => true,
        ],
        'case' => [
            'inst' => $seaTac,
            'charges' => "Civil contempt of court (refusal to testify before federal grand jury). Never charged with any underlying offense.",
            'arrest_date' => '2012-10-10',
            'sentenced_date' => '2012-10-10',
            'incarceration_date' => '2012-10-10',
            'release_date' => '2012-10-25',
            'judge' => 'Hon. Richard A. Jones (W.D. Wash.)',
            'plead' => 'N/A — civil contempt, no plea',
            'convicted' => 'No — never charged or convicted; held briefly for civil contempt',
            'sentence' => 'Civil contempt; briefly detained in October 2012; released within weeks.',
        ],
    ],
];

$created = 0;
$skipped = 0;
foreach ($entries as $entry) {
    $p = $entry['prisoner'];
    $existing = Prisoner::whereRaw('LOWER(name) = ?', [mb_strtolower($p['name'])])->first();
    if ($existing) {
        echo "[skip]    {$p['name']} already exists (id={$existing->id})\n";
        $skipped++;
        continue;
    }

    DB::transaction(function () use ($p, $entry, &$created) {
        $prisoner = Prisoner::create($p);
        $created++;
        $c = $entry['case'];
        $caseAttrs = array_merge(
            ['prisoner_id' => $prisoner->id, 'institution_id' => $c['inst']->id],
            array_diff_key($c, ['inst' => true])
        );
        PrisonerCase::create($caseAttrs);
        echo "[create]  {$prisoner->name} (id={$prisoner->id})  civil contempt at SeaTac FDC\n";
    });
}

echo "\nDone. created={$created}, skipped={$skipped}\n";
