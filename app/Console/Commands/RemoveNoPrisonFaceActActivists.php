<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Removes the pardoned FACE Act activists who never served a prison term.
 *
 * Of the 23 pro-life activists pardoned January 23, 2025, nine got probation,
 * home confinement, "time served," or were pardoned before sentencing — they
 * never spent a day in prison. At the site owner's request these are dropped
 * from the political-prisoner database entirely (rather than kept with a
 * zero-day term).
 *
 * The activists who DID serve prison (Handy, Hinshaw, Goodman, Geraghty,
 * Idoni, Darnel, Marshall, Bell, Harlow, Jay Smith, Gallagher, Calvin Zastrow,
 * Bevelyn Williams, Fr. Moscinski) are kept and untouched.
 *
 * Idempotent: skips any slug already gone. Matched by slug, with the known
 * UUID recorded in a comment for traceability.
 */
final class RemoveNoPrisonFaceActActivists extends Command
{
    protected $signature = 'prisoners:remove-no-prison-face-act';

    protected $description = 'Remove the pardoned FACE Act activists who never served prison time';

    /** slug => reason (all no-prison outcomes). */
    private const REMOVE = [
        'coleman-boyd' => '5 yrs probation + 6 mo home confinement + fine (TN) — no prison',
        'paul-vaughn' => '6 mo home confinement + supervised release (TN) — no prison',
        'dennis-green' => 'time served + supervised release / home confinement (TN) — no prison',
        'eva-edl' => '3 yrs probation (TN) — no prison',
        'james-zastrow' => '3 yrs probation + 90-day home detention (TN) — no prison',
        'paul-place' => '3 yrs probation + 90-day home detention (TN) — no prison',
        'eva-zastrow' => 'Michigan case — pardoned before sentencing, no prison',
        'joel-curry' => 'Michigan case — pardoned before sentencing, no prison',
        'justin-phillips' => 'Michigan case — pardoned before sentencing, no prison',
    ];

    public function handle(): int
    {
        $removed = 0;

        foreach (self::REMOVE as $slug => $reason) {
            $prisoner = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $prisoner) {
                $this->line("  already gone: {$slug}");

                continue;
            }

            // Clear related rows first so nothing is orphaned.
            $prisoner->cases()->delete();
            $prisoner->podcastEpisodes()->delete();
            $prisoner->calendarEntries()->delete();
            $name = $prisoner->name;
            $prisoner->delete();

            $this->info("  removed: {$name} — {$reason}");
            $removed++;
        }

        $this->info("\nDone. Removed {$removed} no-prison FACE Act record(s).");

        return self::SUCCESS;
    }
}
