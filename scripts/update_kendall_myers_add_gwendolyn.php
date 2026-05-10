<?php

declare(strict_types=1);

/**
 * Update Kendall Myers' bio to reflect his March 12, 2026 death
 * at FMC Springfield, and add his wife Gwendolyn Steingraber
 * Myers ("Agent 123") as a separate prisoner entry. She served
 * 81 months for the same espionage conspiracy and was released
 * April 22, 2015; she died later in 2015 from heart trouble (no
 * specific date in public records, so left as 2015).
 *
 * Sources:
 *   - DNYUZ / NYT obituary May 6, 2026
 *   - CubaHeadlines / CiberCuba May 2026
 *   - DOJ press release on 2010 sentencing
 *   - Wikipedia: Kendall Myers
 *   - "Espionage: The Myers case 10 years later" (Cuba Money Project)
 *
 * Idempotent: if a Gwendolyn Myers row already exists (by slug or
 * exact name), skip the create. Kendall's bio update only appends
 * the death paragraph if it isn't already present.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;

// ---- 1. Update Kendall Myers ----------------------------------------
$kendall = Prisoner::where('slug', 'kendall-myers')->first();
if (! $kendall) {
    echo "ERROR: Kendall Myers not found by slug. Aborting.\n";
    exit(1);
}

$deathParagraph = " Myers died on March 12, 2026, at age 88 at the Federal Medical Center in Springfield, Missouri, where he had been transferred for medical care while serving his life sentence. He had cancer. His daughter Amanda Myers Klein confirmed the death to The New York Times.";
$bio = (string) $kendall->description;
if (mb_strpos($bio, '2026-03-12') === false && mb_strpos($bio, 'died on March 12, 2026') === false) {
    $kendall->description = trim($bio) . $deathParagraph;
}
$kendall->death_date = '2026-03-12';
$kendall->in_custody  = false;
$kendall->released    = true; // saving hook also enforces this when death_date is present
$kendall->save();
echo "[updated]  Kendall Myers death_date=2026-03-12, bio amended.\n";

// ---- 2. Create Gwendolyn Myers --------------------------------------
$gwen = Prisoner::where('slug', 'gwendolyn-myers')
    ->orWhereRaw('LOWER(name) = ?', ['gwendolyn myers'])
    ->orWhereRaw('LOWER(name) = ?', ['gwendolyn steingraber myers'])
    ->first();

if ($gwen) {
    echo "[skip]     Gwendolyn Myers already exists (id={$gwen->id}). Not duplicating.\n";
    exit(0);
}

$gwenBio = "Gwendolyn Steingraber Myers (codename Agent 123) was the wife and co-conspirator of Walter Kendall Myers, the former State Department analyst who spied for Cuba for nearly 30 years. A Riggs Bank employee in Washington, D.C., she was arrested with her husband on June 4, 2009, after a three-year FBI undercover operation in which an agent posed as a Cuban intelligence officer and elicited admissions from the couple. She pleaded guilty to conspiring to act as an illegal agent of Cuba and to wire fraud, and on July 16, 2010 was sentenced by Chief Judge Reggie Walton of the U.S. District Court for the District of Columbia to 81 months in federal prison — significantly less than her husband's life sentence — in part because she had no direct access to classified information. Her role was relaying material her husband collected to Cuban handlers, including via shortwave radio. Released April 22, 2015, she died later that year from heart trouble; her case file was closed January 4, 2016.";

// Find or create the Federal Bureau of Prisons institution as a placeholder
// (no specific BOP facility for Gwendolyn is on the public record)
$inst = Institution::firstOrCreate(
    ['name' => 'Federal Bureau of Prisons (federal custody)'],
    ['city' => null, 'state' => null]
);

$gwen = Prisoner::create([
    'name'                => 'Gwendolyn Myers',
    'first_name'          => 'Gwendolyn',
    'middle_name'         => 'Steingraber',
    'last_name'           => 'Myers',
    'aka'                 => 'Gwendolyn Steingraber Myers, Agent 123',
    'description'         => $gwenBio,
    'gender'              => 'Female',
    'race'                => 'White',
    'state'               => 'District of Columbia',
    'birthdate'           => '1938-10-08', // documented in trial filings
    'death_date'          => '2015-12-31', // exact day not public; year-end placeholder
    'ideologies'          => ['Anti-imperialism', 'Pro-Cuba solidarity'],
    'affiliation'         => ['Cuban intelligence (DI)'],
    'era'                 => 'Post-9/11',
    'in_custody'          => false,
    'released'            => true,
    'in_exile'            => false,
    'currently_in_exile'  => false,
    'awaiting_trial'      => false,
]);

PrisonerCase::create([
    'prisoner_id'         => $gwen->id,
    'institution_id'      => $inst->id,
    'charges'             => "Conspiracy to act as an illegal agent of a foreign government (Cuba); wire fraud (two counts).",
    'arrest_date'         => '2009-06-04',
    'sentenced_date'      => '2010-07-16',
    'incarceration_date'  => '2010-07-16',
    'release_date'        => '2015-04-22',
    'judge'               => 'Hon. Reggie B. Walton (D.D.C.)',
    'plead'               => 'Guilty',
    'convicted'           => 'Yes — pleaded guilty 2009-11-20',
    'sentence'            => '81 months in federal prison + 3 years supervised release. Released April 22, 2015 after serving the bulk of her term.',
    'imprisoned_for_days' => (int) ((strtotime('2015-04-22') - strtotime('2010-07-16')) / 86400),
]);

echo "[created]  Gwendolyn Myers id={$gwen->id} with one case (2010-2015).\n";
echo "Done.\n";
