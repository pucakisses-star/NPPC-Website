<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Tommy Lee Hines, the Black man with a severe intellectual disability whose
 * 1978 rape conviction in Decatur, Alabama (all-white jury; written "confession"
 * despite illiteracy) became a major civil-rights flashpoint — SCLC marches met
 * by KKK violence — and was reversed by the Alabama Court of Criminal Appeals in
 * 1980. Sourced to UPI, the Washington Post, the Harvard Crimson, and Hines v.
 * State (1980). Idempotent.
 */
class AddTommyLeeHines extends Command {
    protected $signature = 'prisoners:add-tommy-lee-hines';
    protected $description = 'Add Tommy Lee Hines (Decatur, AL 1978 frame-up; conviction reversed 1980)';

    private const BIO = <<<'TXT'
Tommy Lee Hines was a 25-year-old Black man with a severe intellectual disability — an IQ of about 39, described by doctors as having the mind of a young child — living in Decatur, Alabama, when in 1978 he was charged with the rape and robbery of three white women. Picked up for loitering on May 23, 1978, he was within hours accused of the three assaults; though illiterate, he was confronted with a written "confession" that was later used against him.

His prosecution became one of the most explosive civil-rights confrontations of the late 1970s. The Southern Christian Leadership Conference led large marches in Decatur and — after the trial was moved — in Cullman, met by Ku Klux Klan counter-demonstrations; during a 1979 Decatur march, gunfire wounded two Black marchers and two Klansmen. An all-white jury convicted Hines in October 1978 and he was sentenced to 30 years, and he was held at the Kilby Correctional Facility. In 1980 the Alabama Court of Criminal Appeals reversed the conviction (Hines v. State); a court ordered a competency hearing, and Hines was committed to a state mental hospital rather than retried.
TXT;

    public function handle(): int {
        if (Prisoner::where('name', 'Tommy Lee Hines')->exists()) {
            $this->error('Tommy Lee Hines already exists.');
            return self::FAILURE;
        }

        DB::transaction(function () {
            $kilby = Institution::firstOrCreate(
                ['name' => 'Kilby Correctional Facility'],
                ['city' => 'Montgomery', 'state' => 'Alabama']
            );

            $prisoner = Prisoner::create([
                'name'           => 'Tommy Lee Hines',
                'first_name'     => 'Tommy',
                'last_name'      => 'Hines',
                'description'    => self::BIO,
                'gender'         => 'Male',
                'race'           => 'Black',
                'state'          => 'Alabama',
                'era'            => '1970s',
                'ideologies'     => [],
                'affiliation'    => [],
                'in_custody'     => false,
                'released'       => true,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id'    => $prisoner->id,
                'institution_id' => $kilby->id,
                'charges'        => 'Rape and robbery of three white women in Decatur, Alabama in 1978 — a prosecution widely condemned as a racist frame-up of a Black man with a severe intellectual disability (an IQ of about 39), whose written "confession" was used against him despite his being illiterate.',
                'arrest_date'    => '1978-05-23',
                'convicted'      => 'Convicted by an all-white jury in October 1978 and sentenced to 30 years. The Alabama Court of Criminal Appeals reversed the conviction in 1980 (Hines v. State); a competency hearing was ordered and Hines was committed to a state mental hospital rather than retried.',
                'sentence'       => '30 years (1978); conviction reversed in 1980.',
            ]);

            $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
        });

        return self::SUCCESS;
    }
}
