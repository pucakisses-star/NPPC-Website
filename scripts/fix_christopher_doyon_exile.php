<?php

declare(strict_types=1);

/**
 * Patch Christopher Doyon's record to record his ~9-year exile in
 * Mexico (jumped bail 2012-08, recaptured by Mexican authorities
 * and extradited 2021-06-11). His prisoner row currently has no
 * exile flags and the case has no in_exile_since / end_of_exile,
 * so the front-end shows zero exile time.
 *
 * Prisoner row:
 *   in_exile = true (he WAS in exile)
 *   currently_in_exile = false (no longer; in US federal custody)
 *
 * Case row:
 *   in_exile_since   = 2012-08-01 (bail-jump approximate)
 *   end_of_exile     = 2021-06-11 (extradition date)
 *   in_exile_for_days = computed
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use Carbon\Carbon;

$p = Prisoner::whereRaw('LOWER(name) = ?', ['christopher doyon'])->first();
if (! $p) {
    echo "Christopher Doyon not found. Aborting.\n";
    exit(1);
}

$exileStart = '2012-02-15';   // missed federal court hearing in San Jose; exact day not in public sources, mid-Feb 2012
$exileEnd   = '2021-06-12';   // deported from Mexico to FBI custody (captured by Mexican immigration June 11)
$exileDays  = (int) Carbon::parse($exileStart)->diffInDays(Carbon::parse($exileEnd));

// --- prisoner-row flags ---
$dirty = false;
if (! $p->in_exile)          { $p->in_exile = true;         $dirty = true; }
if ($p->currently_in_exile)  { $p->currently_in_exile = false; $dirty = true; }
if ($dirty) $p->save();

// --- case-row exile fields ---
$case = $p->cases()->orderBy('created_at')->first();
if (! $case) {
    echo "Christopher Doyon has no PrisonerCase row to patch. Aborting.\n";
    exit(1);
}

$case->in_exile_since   = $exileStart;
$case->end_of_exile     = $exileEnd;
$case->in_exile_for_days = $exileDays;
$case->save();

echo sprintf("[fix] Christopher Doyon: in_exile=true, %s -> %s (%d days)\n",
    $exileStart, $exileEnd, $exileDays);
echo "Done.\n";
