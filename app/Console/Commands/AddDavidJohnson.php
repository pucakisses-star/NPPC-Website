<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds David Johnson, a Black Chicago Transit Authority bus driver and Amalgamated
 * Transit Union member charged in 1985 in what fellow workers called a racist
 * police frame-up — and freed when a mass transit-worker mobilization forced the
 * courts to drop the charges. Documented in Workers Vanguard (No. 386, 6 Sept
 * 1985); the underlying incident's details are not specified in that source, and
 * the case is otherwise thinly recorded. Idempotent.
 */
class AddDavidJohnson extends Command {
    protected $signature = 'prisoners:add-david-johnson';
    protected $description = 'Add David Johnson (Chicago ATU bus driver; 1985 frame-up beaten by a labor mobilization)';

    private const BIO = <<<'TXT'
David Johnson was a Black Chicago Transit Authority bus driver and member of the Amalgamated Transit Union (ATU) who, in the summer of 1985, became the focus of a labor and civil-rights mobilization after his arrest on charges his fellow workers denounced as a racist police frame-up. Rather than wait on the courts, Chicago's transit workers organized: on August 14, 1985, more than 700 rank-and-file drivers and supporters marched from Grant Park to police headquarters on State Street chanting "Free David Johnson — Drop the Charges!", and the union threatened to bring all 12,000 of its Chicago members to his August 29 court hearing — with drivers proposing to surround the courthouse with buses.

Under the pressure of the threatened citywide action, the courts "reconsidered the evidence," and the campaign — covered by Workers Vanguard as a victory for "labor/black power" — won the dropping of the charges. The detailed facts of the underlying incident are not specified in the available source, and the case is otherwise thinly documented.
TXT;

    public function handle(): int {
        if (Prisoner::where('name', 'David Johnson')->exists()) {
            $this->error('David Johnson already exists.');
            return self::FAILURE;
        }

        DB::transaction(function () {
            $prisoner = Prisoner::create([
                'name'           => 'David Johnson',
                'first_name'     => 'David',
                'last_name'      => 'Johnson',
                'description'    => self::BIO,
                'gender'         => 'Male',
                'race'           => 'Black',
                'state'          => 'Illinois',
                'era'            => '1980s',
                'ideologies'     => ['Labor', 'Civil rights'],
                'affiliation'    => ['Amalgamated Transit Union (Chicago)'],
                'in_custody'     => false,
                'released'       => true,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges'     => 'Criminal charges that his fellow transit workers called a racist police frame-up — a Black Chicago Transit Authority bus driver and Amalgamated Transit Union member charged in 1985 (the specific charge and underlying incident are not detailed in the available source).',
                'convicted'   => 'No — after a mass mobilization of Chicago transit workers (a 700-strong march on police headquarters on August 14, 1985), the courts "reconsidered the evidence" and the charges were dropped.',
            ]);

            $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
        });

        return self::SUCCESS;
    }
}
