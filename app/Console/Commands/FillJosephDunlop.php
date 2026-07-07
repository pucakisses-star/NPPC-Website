<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Fills out Joseph R. Dunlop's case, added by prisoners:add-anarchist-press-prisoners
 * with no institution or dates. Sets the Joliet penitentiary and his exact
 * imprisonment dates (committed May 4, 1897; released February 10, 1899) and
 * attaches his portrait if the file is placed at
 * database/data/photos/joseph-r-dunlop.jpg. Idempotent.
 */
final class FillJosephDunlop extends Command
{
    protected $signature = 'prisoners:fill-joseph-dunlop';

    protected $description = 'Set Joseph R. Dunlop\'s Joliet imprisonment dates and attach his portrait';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'Joseph R. Dunlop')->first();
        if (! $prisoner) {
            $this->error('Joseph R. Dunlop not found — run prisoners:add-anarchist-press-prisoners first.');

            return self::FAILURE;
        }

        $joliet = Institution::firstOrCreate(
            ['name' => 'Joliet Penitentiary'],
            ['city' => 'Joliet', 'state' => 'Illinois']
        )->id;

        DB::transaction(function () use ($prisoner, $joliet) {
            $prisoner->cases()->delete();

            $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $case->fill([
                'prisoner_id' => $prisoner->id,
                'institution_id' => $joliet,
                'charges' => 'Circulating "obscene" literature through the mails (Comstock Act), as editor of the Chicago Dispatch.',
                'convicted' => 'Yes.',
                'sentence' => 'Two years in prison. Committed to the Joliet penitentiary on May 4, 1897 and released on February 10, 1899.',
            ]);
            $case->setPartialDate('incarceration_date', 1897, 5, 4);
            $case->setPartialDate('release_date', 1899, 2, 10);
            $case->save();
        });

        $this->info('Set Joseph R. Dunlop\'s Joliet case (May 4, 1897 – Feb 10, 1899).');

        $src = database_path('data/photos/joseph-r-dunlop.jpg');
        if (is_file($src)) {
            if (empty($prisoner->photo)) {
                Storage::disk('public')->makeDirectory('prisoners');
                Storage::disk('public')->put('prisoners/joseph-r-dunlop.jpg', file_get_contents($src));
                $prisoner->photo = 'prisoners/joseph-r-dunlop.jpg';
                $prisoner->save();
                $this->info('Attached portrait: prisoners/joseph-r-dunlop.jpg');
            } else {
                $this->info('Portrait already set; left as-is.');
            }
        } else {
            $this->warn('Portrait file not found at database/data/photos/joseph-r-dunlop.jpg — dates set, photo skipped.');
        }

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
