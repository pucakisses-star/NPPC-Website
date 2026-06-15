<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Patricia "Pat" Swinton, charged in the 1969 Sam Melville–Jane Alpert New
 * York bombings, a fugitive for five years, captured in 1975 and acquitted.
 * Surfaced from the Workers Vanguard "Free Pat Swinton" coverage; sourced to the
 * Dengrove archive (Univ. of Virginia) and the Sam Melville record. Recorded
 * honestly: a real bombing-conspiracy prosecution that ended in acquittal. Idempotent.
 */
class AddPatSwinton extends Command {
    protected $signature = 'prisoners:add-pat-swinton';
    protected $description = 'Add Patricia "Pat" Swinton (1969 NYC bombings case; acquitted 1975)';

    private const BIO = <<<'TXT'
Patricia "Pat" Swinton was a New Left radical charged in connection with a wave of bombings of corporate and government buildings in New York City in 1969. Between July and November 1969, eight buildings were bombed by a small group around Sam Melville and Jane Alpert; Melville, Alpert, and David Hughey were arrested in November 1969, but Swinton — who lived under the name "Shoshana" — went underground and remained a fugitive for more than five years.

The FBI captured her on March 12, 1975 at a commune in Vermont. At trial she was acquitted in September 1975. Afterward she said her case illustrated "one of the ways the Government is willing to use its power... to get political dissenters."
TXT;

    public function handle(): int {
        if (Prisoner::where('name', 'Patricia Swinton')->exists()) {
            $this->error('Patricia Swinton already exists.');
            return self::FAILURE;
        }

        DB::transaction(function () {
            $prisoner = Prisoner::create([
                'name'           => 'Patricia Swinton',
                'first_name'     => 'Patricia',
                'last_name'      => 'Swinton',
                'description'    => self::BIO,
                'gender'         => 'Female',
                'race'           => 'White',
                'state'          => 'New York',
                'era'            => '1970s',
                'ideologies'     => ['Anti-war', 'Anti-imperialism'],
                'affiliation'    => [],
                'in_custody'     => false,
                'released'       => true,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges'     => 'Conspiracy in the 1969 bombings of eight corporate and government buildings in New York City (the Sam Melville–Jane Alpert group). Swinton spent more than five years as a fugitive before her March 12, 1975 capture in Vermont.',
                'arrest_date' => '1975-03-12',
                'convicted'   => 'No — acquitted at trial in September 1975.',
            ]);

            $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
        });

        return self::SUCCESS;
    }
}
