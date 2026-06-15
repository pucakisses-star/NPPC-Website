<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Gary Tyler, convicted at 17 in a case widely condemned as a racial
 * frame-up after the October 7, 1974 school-desegregation violence in Destrehan,
 * Louisiana — once the youngest person on U.S. death row, declared a political
 * prisoner by Amnesty International (1994), and released in 2016. Surfaced in the
 * Out Front / Workers Vanguard / Militant readings; sourced to Wikipedia. Idempotent.
 */
class AddGaryTyler extends Command {
    protected $signature = 'prisoners:add-gary-tyler';
    protected $description = 'Add Gary Tyler (Destrehan, LA, 1974) — frame-up; youngest on U.S. death row';

    private const BIO = <<<'TXT'
Gary Tyler (born July 19, 1958, in St. Rose, Louisiana) became, at 17, the youngest person on death row in the United States — convicted in a case widely condemned as a racial frame-up. On October 7, 1974, amid violent white resistance to school desegregation in Destrehan, Louisiana, a bus carrying Black students from Destrehan High School was attacked by a crowd of 100–200 white protesters, and a 13-year-old white student, Timothy Weber, was fatally shot. The bus driver initially believed the shot had come from outside, and no weapon was found in early searches.

Tyler, then 16, was arrested for "disturbing the peace" after talking back to police and was then charged with Weber's murder. He said he was severely beaten in an effort to force a confession, which he refused to give. The state produced a .45-caliber pistol said to have been stolen from a sheriff's firing range; a witness who claimed Tyler had hidden it on the bus later recanted, and the gun itself disappeared from the evidence room. Represented by a court-appointed lawyer who had never tried a murder case, Tyler was convicted of first-degree murder by an all-white jury in 1975 and received Louisiana's mandatory death sentence.

After the U.S. Supreme Court struck down mandatory death sentences (Roberts v. Louisiana, 1976), Tyler's sentence was commuted to life imprisonment, and he spent roughly four decades at the Louisiana State Penitentiary at Angola. Amnesty International described him as a political prisoner in 1994. Following Miller v. Alabama (2012), which barred mandatory life-without-parole for juveniles, Tyler was released on April 29, 2016 after pleading guilty to manslaughter.
TXT;

    public function handle(): int {
        if (Prisoner::where('name', 'Gary Tyler')->exists()) {
            $this->error('Gary Tyler already exists.');
            return self::FAILURE;
        }

        DB::transaction(function () {
            $angola = Institution::firstOrCreate(
                ['name' => 'Louisiana State Penitentiary (Angola)'],
                ['city' => 'Angola', 'state' => 'Louisiana']
            );

            $prisoner = Prisoner::create([
                'name'           => 'Gary Tyler',
                'first_name'     => 'Gary',
                'last_name'      => 'Tyler',
                'description'    => self::BIO,
                'gender'         => 'Male',
                'race'           => 'Black',
                'birthdate'      => '1958-07-19',
                'state'          => 'Louisiana',
                'era'            => '1970s',
                'ideologies'     => ['Civil rights'],
                'affiliation'    => [],
                'in_custody'     => false,
                'released'       => true,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id'    => $prisoner->id,
                'institution_id' => $angola->id,
                'charges'        => 'First-degree murder in the October 7, 1974 fatal shooting of 13-year-old white student Timothy Weber during school-desegregation violence at Destrehan High School in Destrehan, Louisiana — a case widely condemned as a frame-up (the key witness recanted and the alleged weapon vanished from the sheriff\'s evidence room).',
                'arrest_date'    => '1974-10-07',
                'convicted'      => 'Yes — convicted of first-degree murder by an all-white jury in 1975 and given a mandatory death sentence (at 17, the youngest person then on U.S. death row); commuted to life after Roberts v. Louisiana (1976). Amnesty International declared him a political prisoner in 1994.',
                'release_date'   => '2016-04-29',
                'sentence'       => 'Death (1975), commuted to life imprisonment; about 41 years served at the Louisiana State Penitentiary (Angola); released April 29, 2016 after a manslaughter plea.',
            ]);

            $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
        });

        return self::SUCCESS;
    }
}
