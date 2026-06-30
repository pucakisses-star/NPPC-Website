<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Removes 9 confirmed-duplicate records from the currently-imprisoned list,
 * keeping the most complete record (photo / most cases) for each of the 8
 * people. Deletes strictly by UUID so no other record can be affected, and
 * clears related cases/podcast/calendar rows first. Idempotent — any id that
 * no longer exists is skipped.
 *
 * Kept (NOT touched): jamil-abdullah-al-amin, mujera-benjamin-lungaho,
 * alexander-stokes-contompasis, byron-chubbuck, john-johnson,
 * malik-fard-muhammad, margaret-channon, matthew-rupert.
 */
final class RemoveDuplicatePrisoners extends Command
{
    protected $signature = 'prisoners:remove-duplicates';

    protected $description = 'Remove 9 confirmed duplicate currently-imprisoned prisoner records (by id)';

    /** id => label (the redundant copy to delete) */
    private const REMOVE = [
        '00e2089e-652c-495f-8013-1e601f5553b7' => 'Jamil Abdullah Al-Amin (jamil-abdullah-al-amin-2)',
        '1af6fc28-d2c3-4b96-b98f-78dadcde16a6' => 'Jamil Al-Amin (jamil-al-amin)',
        'f80c6d42-9416-46ae-9e9c-7286851942bc' => "Mujera Benjamin Lunga'ho (mujera-benjamin-lungaho-2)",
        'f3042f4a-f7f9-4750-a09a-9da1037dac1d' => 'Alexander Contompasis (alexander-contompasis)',
        'bb3cb677-6c28-41ea-bb0b-f7044ed4ea96' => 'Byron Shane Chubbuck (byron-shane-chubbuck)',
        'b6dc56fa-cee7-4a36-a1fe-78fe070e1967' => 'John Fitzgerald Johnson (john-fitzgerald-johnson)',
        '57d0b492-ec67-4e1d-9054-cb023350673d' => 'Malik Muhammad (malik-muhammad)',
        '3630435a-a2af-4cc9-804d-cbb319948a43' => 'Margaret Aislinn Channon (margaret-aislinn-channon)',
        '38076d9d-4a6a-4f7f-b2d4-ed0ee76bab67' => 'Matthew Lee Rupert (matthew-lee-rupert)',
    ];

    public function handle(): int
    {
        $removed = 0;

        foreach (self::REMOVE as $id => $label) {
            $prisoner = Prisoner::withoutGlobalScopes()->find($id);
            if (! $prisoner) {
                $this->line("Already gone, skipping: {$label}");

                continue;
            }

            $prisoner->cases()->delete();
            $prisoner->podcastEpisodes()->delete();
            $prisoner->calendarEntries()->delete();
            $prisoner->delete();

            $this->info("Removed duplicate: {$label}");
            $removed++;
        }

        $this->info("\nDone. Removed {$removed} duplicate record(s).");

        return self::SUCCESS;
    }
}
