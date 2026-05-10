<?php

declare(strict_types=1);

/**
 * Two fixes in one pass:
 *
 *   1. Merge the Camilo Mejía / Camilo Mejia duplicate prisoner
 *      rows. The accented form is the canonical spelling. All
 *      prisoner_cases, podcast_episodes, and calendar_entries on
 *      the unaccented duplicate are reassigned to the accented
 *      canonical row, then the duplicate is deleted.
 *
 *   2. Patch Jeremy Hinzman's case with documented dates.
 *      Hinzman went AWOL from Fort Bragg, NC on Jan 9, 2004 and
 *      crossed into Canada with his family on Jan 11, 2004 to
 *      seek refugee status as a US Iraq War deserter. He has
 *      remained in Canada in exile ever since (multiple Canadian
 *      refugee-status appeals denied; deportation stayed). So:
 *        case.arrest_date  = 2004-01-09 (AWOL date — start of US
 *                                        military case against him)
 *        case.in_exile_since = 2004-01-11 (fled to Canada)
 *        prisoner.in_exile = true
 *        prisoner.currently_in_exile = true (still in Canada)
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ---- 1. Merge Camilo Mejía / Camilo Mejia ------------------------
$mejiaAccent = Prisoner::where('name', 'Camilo Mejía')->first();
$mejiaPlain  = Prisoner::where('name', 'Camilo Mejia')->first();

if ($mejiaAccent && $mejiaPlain) {
    $canon = $mejiaAccent;
    $dup   = $mejiaPlain;
    echo "[merge] canonical: {$canon->name} (id={$canon->id}, sort={$canon->sort_order})\n";
    echo "         duplicate: {$dup->name} (id={$dup->id}, sort={$dup->sort_order})\n";

    DB::transaction(function () use ($canon, $dup) {
        DB::table('prisoner_cases')
            ->where('prisoner_id', $dup->id)
            ->update(['prisoner_id' => $canon->id]);

        if (Schema::hasTable('podcast_episodes')) {
            DB::table('podcast_episodes')
                ->where('prisoner_id', $dup->id)
                ->update(['prisoner_id' => $canon->id]);
        }
        if (Schema::hasTable('calendar_entries')) {
            DB::table('calendar_entries')
                ->where('prisoner_id', $dup->id)
                ->update(['prisoner_id' => $canon->id]);
        }

        // Fill any blank fields on canon from the dup
        foreach (['description','aka','race','gender','birthdate','death_date','state','address',
                  'inmate_number','website','twitter','facebook','instagram','photo'] as $col) {
            $canonVal = trim((string) $canon->getAttribute($col));
            $dupVal   = trim((string) $dup->getAttribute($col));
            if ($canonVal === '' && $dupVal !== '') {
                $canon->setAttribute($col, $dup->getAttribute($col));
            }
        }
        $canon->save();

        $dup->delete();
    });
    echo "[merge] cases/podcasts/calendar reassigned, duplicate deleted.\n\n";
} elseif ($mejiaAccent || $mejiaPlain) {
    echo "[merge] only one Camilo Mejia variant exists — nothing to merge.\n\n";
} else {
    echo "[merge] no Camilo Mejia at all in DB. Skipping merge.\n\n";
}

// ---- 2. Patch Jeremy Hinzman's case dates + exile flags ---------
$hinz = Prisoner::whereRaw('LOWER(name) = ?', ['jeremy hinzman'])->first();
if (! $hinz) {
    echo "Jeremy Hinzman not found. Aborting.\n";
    exit(0);
}

// Make sure he has a case row to patch
$case = $hinz->cases()->orderBy('created_at')->first();
if (! $case) {
    // Create one if missing
    $case = $hinz->cases()->create([
        'charges' => 'AWOL / desertion (Article 85 UCMJ) — went AWOL from Fort Bragg, NC on Jan 9, 2004 to refuse deployment to the Iraq War; crossed into Canada with his family on Jan 11, 2004 to seek refugee status. Multiple Canadian Federal Court refugee-status appeals denied; remains in Canada under stayed deportation.',
    ]);
    echo "[hinzman] no case row existed — created a new one (id={$case->id}).\n";
}

$case->arrest_date     = '2004-01-09';
$case->in_exile_since  = '2004-01-11';
$case->save();

$hinz->in_exile           = true;
$hinz->currently_in_exile = true;
$hinz->released           = false;
$hinz->in_custody         = false;
$hinz->save();

echo "[hinzman] arrest_date=2004-01-09, in_exile_since=2004-01-11, currently_in_exile=true\n";
echo "Done.\n";
