<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Timothy Adams — one of the four men arrested alongside BLA fugitive Henry
 * "Sha Sha" Brown when police raided a tenement at 79 Menahan Street, Bushwick,
 * Brooklyn, on October 3, 1973. Per The New York Times (Oct. 4, 1973), Adams,
 * 25, of Bennettsville, S.C., was already under indictment for the 1971
 * attempted murder of two Brooklyn police officers; the men taken with Brown
 * were to be charged with harboring him and hindering prosecution. Idempotent.
 */
class AddTimothyAdams extends Command
{
    protected $signature = 'prisoners:add-timothy-adams';

    protected $description = 'Add Timothy Adams (arrested with Henry "Sha Sha" Brown, Oct 1973)';

    public function handle(): int
    {
        DB::transaction(function () {
            $name = 'Timothy Adams';

            if (Prisoner::where('name', $name)->exists()) {
                $this->warn('Skipped (already exists): '.$name);

                return;
            }

            $prisoner = Prisoner::create([
                'name' => $name,
                'first_name' => 'Timothy',
                'last_name' => 'Adams',
                'description' => 'Timothy Adams was a 25-year-old man from Bennettsville, South Carolina, alleged to be a member of the Black Liberation Army. He was already under indictment for the 1971 attempted murder of two Brooklyn police officers when, on October 3, 1973, he was one of four men arrested alongside the BLA fugitive Henry "Sha Sha" Brown in a police raid on a tenement at 79 Menahan Street in the Bushwick section of Brooklyn. The Brooklyn District Attorney announced that the men arrested with Brown would be charged with harboring him and hindering prosecution. The outcome of the charges against Adams is not documented in the available sources.',
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
                'charges' => 'Attempted murder of two Brooklyn police officers (1971); and harboring a fugitive and hindering prosecution — for sheltering Henry "Sha Sha" Brown, with whom he was arrested in the October 3, 1973 Bushwick raid.',
                'convicted' => 'Under indictment and arrested; the outcome of the charges is not documented in the available sources.',
                'arrest_date' => '1973-10-03',
            ]);

            $this->info('Added: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
        });

        return self::SUCCESS;
    }
}
