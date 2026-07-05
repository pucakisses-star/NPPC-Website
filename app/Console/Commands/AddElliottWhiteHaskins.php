<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Elliott White Haskins — one of the four men arrested alongside BLA fugitive
 * Henry "Sha Sha" Brown in the October 3, 1973 police raid on 79 Menahan Street,
 * Bushwick, Brooklyn. Per The New York Times (Oct. 4, 1973), Haskins, 25, was
 * wanted in Freeport, Long Island, on a murder charge dating to February 4,
 * 1972; the men taken with Brown were to be charged with harboring him and
 * hindering prosecution.
 *
 * NOTE: the database also contains a "Tariq James Haskins" (New Afrikan
 * political prisoner, BOP #40075-133) who appears to be James "Gunnie" Haskins.
 * Elliott White Haskins has a different given/middle name and is treated here as
 * a distinct person; if they prove to be the same individual, merge the two.
 * Idempotent (skips by name).
 */
class AddElliottWhiteHaskins extends Command
{
    protected $signature = 'prisoners:add-elliott-white-haskins';

    protected $description = 'Add Elliott White Haskins (arrested with Henry "Sha Sha" Brown, Oct 1973)';

    public function handle(): int
    {
        DB::transaction(function () {
            $name = 'Elliott White Haskins';

            if (Prisoner::where('name', $name)->exists()) {
                $this->warn('Skipped (already exists): '.$name);

                return;
            }

            $prisoner = Prisoner::create([
                'name' => $name,
                'first_name' => 'Elliott',
                'middle_name' => 'White',
                'last_name' => 'Haskins',
                'description' => 'Elliott White Haskins was a 25-year-old man alleged to be a member of the Black Liberation Army. He was wanted in Freeport, Long Island, on a murder charge dating to February 4, 1972 when, on October 3, 1973, he was among four men arrested with the BLA fugitive Henry "Sha Sha" Brown in a police raid on a tenement at 79 Menahan Street in the Bushwick section of Brooklyn. The Brooklyn District Attorney said the men taken with Brown would be charged with harboring him and hindering prosecution. The outcome of the charges against Haskins is not documented in the available sources.',
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'New York',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => ['Black Liberation Army'],
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges' => 'Murder (Freeport, Long Island, dating to February 4, 1972); and harboring a fugitive and hindering prosecution — for sheltering Henry "Sha Sha" Brown, with whom he was arrested in the October 3, 1973 Bushwick raid.',
                'convicted' => 'Wanted and arrested; the outcome of the charges is not documented in the available sources.',
                'arrest_date' => '1973-10-03',
            ]);

            $this->info('Added: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
        });

        return self::SUCCESS;
    }
}
