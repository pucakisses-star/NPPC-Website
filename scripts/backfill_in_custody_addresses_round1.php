<?php

declare(strict_types=1);

/**
 * Backfill prisoner.address for in-custody prisoners whose current
 * facility I was able to confirm from public records / news /
 * support-organization mailing lists. Each entry has a `source`
 * note for verification later.
 *
 * Run on the production server:
 *   cd /var/www/NPPC-Website && php scripts/backfill_in_custody_addresses_round1.php
 *
 * Idempotent: only writes prisoners whose address is currently
 * empty, so re-running is a no-op. The Prisoner saving hook will
 * geocode each address on save.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

// slug => [address, source-note]
$updates = [
    // ---- Tarrant County Jail (9 prisoners; Defend the Atlanta Forest) ----
    'benjamin-song'           => ['Tarrant County Jail, 100 N Lamar St, Fort Worth, TX 76196', 'Tarrant County Sheriff inmate-mail page'],
    'bradford-morris'         => ['Tarrant County Jail, 100 N Lamar St, Fort Worth, TX 76196', 'Tarrant County Sheriff inmate-mail page'],
    'cameron-arnold'          => ['Tarrant County Jail, 100 N Lamar St, Fort Worth, TX 76196', 'Tarrant County Sheriff inmate-mail page'],
    'daniel-sanchez-estrada'  => ['Tarrant County Jail, 100 N Lamar St, Fort Worth, TX 76196', 'Tarrant County Sheriff inmate-mail page'],
    'elizabeth-soto'          => ['Tarrant County Jail, 100 N Lamar St, Fort Worth, TX 76196', 'Tarrant County Sheriff inmate-mail page'],
    'ines-soto'               => ['Tarrant County Jail, 100 N Lamar St, Fort Worth, TX 76196', 'Tarrant County Sheriff inmate-mail page'],
    'maricela-rueda'          => ['Tarrant County Jail, 100 N Lamar St, Fort Worth, TX 76196', 'Tarrant County Sheriff inmate-mail page'],
    'savanna-batten'          => ['Tarrant County Jail, 100 N Lamar St, Fort Worth, TX 76196', 'Tarrant County Sheriff inmate-mail page'],
    'zachary-evetts'          => ['Tarrant County Jail, 100 N Lamar St, Fort Worth, TX 76196', 'Tarrant County Sheriff inmate-mail page'],

    // ---- Federal facilities ----
    'abdullah-malik-kabah'         => ['ADX Florence, U.S. Penitentiary, Administrative Maximum, P.O. Box 8500, Florence, CO 81226', 'BOP listed; ADX Florence'],
    'christopher-tindal'           => ['USP Big Sandy, U.S. Penitentiary, P.O. Box 2068, Inez, KY 41224', 'BOP register #04392-509'],
    'jared-lee-loughner'           => ['Federal Medical Center Rochester, PMB 4000, Rochester, MN 55903', 'BOP register #15213-196'],
    'cilia-adela-flores-de-maduro' => ['MDC Brooklyn, Metropolitan Detention Center, P.O. Box 329002, Brooklyn, NY 11232', 'BOP register #00735-506'],
    'nicolas-maduro-moros'         => ['MDC Brooklyn, Metropolitan Detention Center, P.O. Box 329002, Brooklyn, NY 11232', 'BOP custody Jan 2026'],
    'monzer-al-kassar'             => ['USP Marion, U.S. Penitentiary, 4500 Prison Road, Marion, IL 62959', 'BOP register #61111-054'],
    'mujera-benjamin-lungaho'      => ['FCI Beaumont Medium, Federal Correctional Institution, P.O. Box 26040, Beaumont, TX 77720', 'BOP register #08572-509'],

    // ---- State facilities ----
    'andrew-mickel'           => ['San Quentin Rehabilitation Center, San Quentin, CA 94964', 'CDCR death-row #V77400'],
    'joseph-shine-white-stewart' => ['Granville Correctional Institution, 500 Camp Road, Butner, NC 27509', 'NC DOC #0802041'],
    'kwame-shakur'            => ['Miami Correctional Facility, 3038 W 850 S, Bunker Hill, IN 46914', 'IDOC #149677'],
    'salih-ali-abdullah'      => ['Auburn Correctional Facility, 135 State Street, Auburn, NY 13024', 'NY DOCCS #74-A-2614'],
    'siddique-abdullah-hasan' => ['Ohio State Penitentiary, 878 Coitsville-Hubbard Road, Youngstown, OH 44505', 'ODRC; long-term OSP placement'],
    'wade-greely-lay'         => ['Oklahoma State Penitentiary, 1300 N West Street, McAlester, OK 74501', 'BOP register #11559-062 — listed at OSP'],
    'yaakub-ira-vijandre'     => ['Folkston ICE Processing Center, 3000 Trade Center Boulevard, Folkston, GA 31537', 'ICE detention; CCA-operated'],

    // ---- International ----
    'daithi-ocorrain'         => ['Limerick Prison, Mulgrave Street, Limerick, V94 E20D, Ireland', 'Irish Prison Service'],

    // ---- From WebSearch research (2025-2026) ----
    'abdul-olugbala-shakur'   => ['Kern Valley State Prison, P.O. Box 5102, Delano, CA 93216', 'Jericho Movement contact info; CDCR #C48884'],
    'kevin-rashid-johnson'    => ['Perry Correctional Institution, 430 Oaklawn Road, Pelzer, SC 29669', 'sfbayview.com May 2025; SC DOC #397279'],
    'colin-ferguson'          => ['Mid-State Correctional Facility, P.O. Box 2500, Marcy, NY 13403', 'Wikipedia LIRR shooting; NY DOCCS'],
    'david-annarelli'         => ['Lawrenceville Correctional Center, 1607 Planters Road, Lawrenceville, VA 23868', 'Prison Journalism Project Feb 2025'],
    'salah-sarsour'           => ['Clay County Justice Center, 609 E National Avenue, Brazil, IN 47834', 'CNN April 2026; ICE detention'],
];

// Also: mark Kendall Myers deceased — he died at FMC Springfield
// March 12, 2026 (NYT/CibrCuba/CubaHeadlines obituaries).
$kendall = Prisoner::where('slug', 'kendall-myers')->first();
if ($kendall && empty($kendall->death_date)) {
    $kendall->death_date = '2026-03-12';
    $kendall->in_custody = false;
    $kendall->released   = true; // saving hook also sets released=1 when death_date is present
    $kendall->save();
    echo "  [death-date] Kendall Myers -> 2026-03-12 (FMC Springfield)\n";
}

$wrote = $skipped = $missing = 0;
foreach ($updates as $slug => [$address, $source]) {
    $p = Prisoner::where('slug', $slug)->first();
    if (! $p) {
        echo "  [missing]  slug={$slug} not found\n";
        $missing++;
        continue;
    }
    if (! empty(trim((string) $p->address))) {
        echo "  [skip]     {$p->name} already has address\n";
        $skipped++;
        continue;
    }
    $p->address = $address;
    $p->save();
    echo "  [wrote]    {$p->name}  ->  {$address}  (src: {$source})\n";
    $wrote++;
}

echo "\nDone. wrote={$wrote}, skipped={$skipped}, missing={$missing}\n";
echo "Next: php artisan prisoners:backfill-coordinates\n";
