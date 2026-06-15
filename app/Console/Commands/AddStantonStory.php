<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Stanton Story, a Black Pittsburgh man held nearly five decades after a
 * 1974 all-white-jury conviction for killing a police officer, who maintained
 * his innocence to the end and was recognized by prison-rights advocates as a
 * political prisoner. Framed honestly: the prosecution's account (including a
 * prior escape) is stated alongside his frame-up claim and the repeated
 * racial-bias reversals of his death sentence. Sourced to Commonwealth v.
 * Story (476 Pa. 391) and the Pittsburgh death-penalty history record.
 * Idempotent.
 */
class AddStantonStory extends Command {
    protected $signature = 'prisoners:add-stanton-story';
    protected $description = 'Add Stanton Story (Pittsburgh; 1974 all-white-jury conviction; racial-bias death-sentence reversals)';

    private const BIO = <<<'TXT'
Stanton Story was a Black man from Pittsburgh who spent nearly five decades in Pennsylvania prisons after being convicted of the July 3, 1974 killing of Pittsburgh police officer Patrick J. Wallace Jr., and who maintained until his death that he was innocent and had been framed. According to the prosecution, Story — who had escaped from Western Penitentiary about a month earlier — shot Wallace during a street encounter; Story insisted he was not the gunman. Tried before an all-white jury, he was convicted and sentenced to death.

The Pennsylvania Supreme Court twice set aside his death sentence amid intense scrutiny of racial bias in capital sentencing (Commonwealth v. Story, 476 Pa. 391 (1978)), and on December 28, 1981 it upheld the conviction but reduced his sentence to life imprisonment. Over more than forty years inside, Story came to be recognized by prison-rights advocates as a political prisoner and a symbol of racially biased capital prosecution, and he never stopped asserting his innocence. Terminally ill with cancer, he was granted compassionate release from the State Correctional Institution at Frackville in April 2023 and died on June 9, 2023, at the age of 70.
TXT;

    public function handle(): int {
        if (Prisoner::where('name', 'Stanton Story')->exists()) {
            $this->error('Stanton Story already exists.');
            return self::FAILURE;
        }

        DB::transaction(function () {
            $prisoner = Prisoner::create([
                'name'           => 'Stanton Story',
                'first_name'     => 'Stanton',
                'last_name'      => 'Story',
                'description'    => self::BIO,
                'gender'         => 'Male',
                'race'           => 'Black',
                'death_date'     => '2023-06-09',
                'state'          => 'Pennsylvania',
                'era'            => '1970s',
                'ideologies'     => [],
                'affiliation'    => [],
                'in_custody'     => false,
                'released'       => true,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges'     => 'First-degree murder in the July 3, 1974 shooting of Pittsburgh police officer Patrick J. Wallace Jr. — a conviction Story, tried before an all-white jury, maintained throughout his life was a racially motivated frame-up.',
                'convicted'   => 'Convicted and sentenced to death; the Pennsylvania Supreme Court twice set aside the death sentence over concerns about racial bias in capital sentencing (Commonwealth v. Story, 476 Pa. 391), and in 1981 his sentence was reduced to life imprisonment.',
                'sentence'    => 'Death, reversed; resentenced to life imprisonment in 1981. He served roughly 49 years before being granted compassionate release in April 2023, dying that June at age 70.',
            ]);

            $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
        });

        return self::SUCCESS;
    }
}
