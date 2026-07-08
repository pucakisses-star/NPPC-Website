<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Adds Reverend Edward Stevenson, a Civil War political prisoner. Federal
 * authorities arrested him at his home in Russellville, Kentucky on June 30,
 * 1862, charging him as a "prominent Secessionist" for having led the local
 * "Home Committee of Safety." He was moved from Russellville through Louisville
 * to Camp Chase in Ohio, where he was held until President Lincoln pardoned him
 * on January 13, 1864. Mirrors the existing Civil War cohort (era "1800s").
 * Idempotent — skips if a record with this name already exists.
 */
final class AddEdwardStevenson extends Command
{
    protected $signature = 'prisoners:add-edward-stevenson';

    protected $description = 'Add Rev. Edward Stevenson, a Civil War political prisoner held at Camp Chase';

    public function handle(): int
    {
        DB::transaction(function () {
            if (Prisoner::where('name', 'Edward Stevenson')->exists()) {
                $this->warn('Skipped (already exists): Edward Stevenson');

                return;
            }

            $prisoner = Prisoner::create([
                'name' => 'Edward Stevenson',
                'first_name' => 'Edward',
                'last_name' => 'Stevenson',
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'Kentucky',
                'era' => '1800s',
                'ideologies' => ['Confederate sympathies'],
                'description' => 'The Reverend Edward Stevenson was a minister in Russellville, Kentucky. On June 30, 1862, federal authorities arrested him at his home, charging him with being a "prominent Secessionist" — evidenced, they said, by his having led the local "Home Committee of Safety." After his arrest he was shuttled from Russellville through Louisville and eventually to Camp Chase in Ohio, where he was held as a political prisoner until President Abraham Lincoln pardoned him on January 13, 1864.',
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'awaiting_trial' => false,
            ]);

            $campChase = Institution::firstOrCreate(
                ['name' => 'Camp Chase'],
                ['city' => 'Columbus', 'state' => 'Ohio']
            )->id;

            $case = new PrisonerCase([
                'prisoner_id' => $prisoner->id,
                'institution_id' => $campChase,
                'charges' => 'Arrested at his home in Russellville, Kentucky as a "prominent Secessionist," on the basis that he had led the local "Home Committee of Safety." Held without trial as a political prisoner.',
                'convicted' => 'No — held as a political prisoner without trial and released by presidential pardon.',
                'sentence' => 'Moved from Russellville through Louisville to Camp Chase, Ohio, where he was held until pardoned by President Lincoln on January 13, 1864.',
            ]);
            $case->setPartialDate('arrest_date', 1862, 6, 30);
            $case->setPartialDate('incarceration_date', 1862, 6, 30);
            $case->setPartialDate('release_date', 1864, 1, 13);
            $case->save();

            $this->info('Added: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
