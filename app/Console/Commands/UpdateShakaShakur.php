<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Sets Shaka Shakur's support website (shakashakur.org) and fills in his case
 * from researched, sourced facts: a 63-year Indiana sentence for the attempted
 * murder of a Gary, Indiana police officer (which supporters describe as a
 * politically motivated charge), in custody since 2002, and — after being held
 * at Indiana State Prison and the Westville Control Unit as one of the
 * "Indiana 6" — transferred to Virginia in 2018 in what supporters call
 * "domestic exile". Updates his existing case in place. Idempotent; matches by
 * slug, then name.
 *
 * Sources: shakashakur.org (About page), the Jericho Movement profile
 * (#1996207), Black Agenda Report, and the Indianapolis Liberation Center.
 */
final class UpdateShakaShakur extends Command
{
    protected $signature = 'prisoners:update-shaka-shakur';

    protected $description = "Set Shaka Shakur's website and document his Indiana case";

    private const WEBSITE = 'https://www.shakashakur.org';

    public function handle(): int
    {
        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'shaka-shakur')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Shaka%Shakur%')->first();

        if (! $prisoner) {
            $this->error('No Shaka Shakur record found.');

            return self::FAILURE;
        }

        $bio = 'Shaka Adiyia Shakur is a New Afrikan political prisoner held under Indiana Department of '
            .'Correction number 1996207. He has been held since 2002 and is serving a 63-year sentence for '
            .'the attempted murder of a Gary, Indiana police officer — a charge his supporters describe as '
            .'politically motivated, arising from harassment by Gary police. He became a New Afrikan '
            .'revolutionary organizer and jailhouse lawyer while incarcerated at Indiana State Prison, where '
            .'he was mentored by fellow prisoners Zolo Azania and James "Yaki" Sayles, and he helped expose '
            .'conditions at Indiana\'s supermax through Human Rights Watch\'s report "Cold Storage." Identified '
            .'as one of the politically active "Indiana 6," he was confined in the Westville Control Unit and, '
            .'in 2018, transferred to Virginia in what supporters call "domestic exile" intended to disrupt '
            .'his organizing and cut off his support networks. He is listed among current U.S. political '
            .'prisoners by the Jericho Movement.';

        $prisoner->website = self::WEBSITE;
        $prisoner->description = $bio;
        $prisoner->in_custody = true;
        $prisoner->released = false;
        if (empty($prisoner->inmate_number)) {
            $prisoner->inmate_number = '1996207';
        }
        $prisoner->save();
        $this->info("Set website + bio on {$prisoner->name}.");

        // He is currently held in Virginia via interstate compact; his support
        // site lists the Lunenburg Correctional Center as his current facility.
        $institution = Institution::firstOrCreate(
            ['name' => 'Lunenburg Correctional Center'],
            ['city' => 'Victoria', 'state' => 'Virginia'],
        );

        $case = $prisoner->cases()->first() ?? $prisoner->cases()->make([]);
        $case->institution_id = $institution->id;
        $case->charges = 'Attempted murder of a Gary, Indiana police officer — a charge supporters describe '
            .'as politically motivated, stemming from harassment by Gary police.';
        $case->convicted = 'Yes — Indiana state conviction';
        $case->sentence = '63 years';
        $case->setPartialDate('incarceration_date', 2002);
        $case->save();
        $this->info('Documented case: 63 years, attempted murder of a Gary, IN officer (held since 2002).');

        $this->info("View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
