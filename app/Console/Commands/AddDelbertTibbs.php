<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Delbert Tibbs, the former divinity student sentenced to death in a 1974
 * Florida rape-murder case widely condemned as a racist frame-up of a Black
 * hitchhiker; conviction reversed by the Florida Supreme Court (1976), charges
 * dismissed, and Tibbs became a leading anti-death-penalty activist. Surfaced
 * from the Workers Vanguard "Free Delbert Tibbs" coverage; sourced to Wikipedia.
 */
class AddDelbertTibbs extends Command {
    protected $signature = 'prisoners:add-delbert-tibbs';
    protected $description = 'Add Delbert Tibbs (1974 Florida death-row frame-up; later exonerated)';

    private const BIO = <<<'TXT'
Delbert Tibbs (June 19, 1939 – November 23, 2013) was a former divinity student whose 1974 conviction and death sentence in Florida became a nationally prominent example of racist wrongful conviction. In 1974, near Fort Myers, Florida, Terry Milroy was murdered and Cynthia Nadeau was raped; Nadeau described a Black assailant who had picked her up while she was hitchhiking. Tibbs — himself hitchhiking some 220 miles north of the crime scene — was detained, and Nadeau identified him from police photographs. An all-white jury convicted him of murder and rape in 1974, and he was sentenced to death.

The case rested on Nadeau's disputed identification and a jailhouse informant who later recanted. The Florida Supreme Court reversed the conviction in 1976, citing "considerable doubt that Delbert Tibbs is the man who committed the crimes," and the charges were dismissed in 1982 after the state declined to retry him. Tibbs went on to become a leading anti-death-penalty activist and appeared in the 2002 documentary play "The Exonerated."
TXT;

    public function handle(): int {
        if (Prisoner::where('name', 'Delbert Tibbs')->exists()) {
            $this->error('Delbert Tibbs already exists.');
            return self::FAILURE;
        }

        DB::transaction(function () {
            $fsp = Institution::firstOrCreate(
                ['name' => 'Florida State Prison'],
                ['city' => 'Raiford', 'state' => 'Florida']
            );

            $prisoner = Prisoner::create([
                'name'           => 'Delbert Tibbs',
                'first_name'     => 'Delbert',
                'last_name'      => 'Tibbs',
                'description'    => self::BIO,
                'gender'         => 'Male',
                'race'           => 'Black',
                'birthdate'      => '1939-06-19',
                'death_date'     => '2013-11-23',
                'state'          => 'Florida',
                'era'            => '1970s',
                'ideologies'     => ['Civil rights'],
                'affiliation'    => [],
                'in_custody'     => false,
                'released'       => true,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id'    => $prisoner->id,
                'institution_id' => $fsp->id,
                'charges'        => 'Murder and rape in a 1974 attack near Fort Myers, Florida (the killing of Terry Milroy and rape of Cynthia Nadeau) — a conviction widely condemned as a racist frame-up of a Black hitchhiker; the case rested on a since-recanted jailhouse informant and a disputed eyewitness identification made from police photographs.',
                'convicted'      => 'Convicted of murder and rape by an all-white jury in 1974 and sentenced to death. The Florida Supreme Court reversed the conviction in 1976, citing "considerable doubt" of his guilt, and the charges were dismissed in 1982.',
                'sentence'       => 'Death (1974); conviction reversed by the Florida Supreme Court in 1976; freed and charges dropped. Tibbs became a prominent anti-death-penalty activist.',
            ]);

            $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
        });

        return self::SUCCESS;
    }
}
