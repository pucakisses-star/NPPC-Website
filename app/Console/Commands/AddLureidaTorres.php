<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Lureida Torres, the Puerto Rican Socialist Party member and Claridad
 * staffer jailed in New York in 1976 for grand-jury contempt — refusing to
 * cooperate with a federal "fishing expedition" against the Puerto Rican
 * independence movement. Documented across multiple 1976 issues of The Militant
 * (the case is otherwise thinly covered on the general web). Idempotent.
 */
class AddLureidaTorres extends Command {
    protected $signature = 'prisoners:add-lureida-torres';
    protected $description = 'Add Lureida Torres (PSP; 1976 NY grand-jury-resistance contempt jailing)';

    private const BIO = <<<'TXT'
Lureida Torres was a twenty-six-year-old New York City schoolteacher and a member of the Puerto Rican Socialist Party (PSP), on the staff of the party's newspaper Claridad, when she was jailed in 1976 for refusing to cooperate with a federal grand jury. Imprisoned in New York on June 26, 1976 for civil contempt, Torres had declined to testify before a grand jury that was ostensibly investigating bombings by the Armed Forces of National Liberation (FALN) — an inquiry the PSP and independence leader Juan Mari Bras denounced as a "fishing expedition" and "witch-hunt" against the Puerto Rican independence movement, noting that neither Torres nor the party had any connection to the FALN.

A federal judge rejected the argument that her continued jailing was purely punitive, and a national "Campaign to Free Lureida Torres" organized demonstrations and a petition drive directed at U.S. Attorney General Edward Levi. Torres, who said she would never testify, was released on October 28, 1976 after completing a four-month sentence.
TXT;

    public function handle(): int {
        if (Prisoner::where('name', 'Lureida Torres')->exists()) {
            $this->error('Lureida Torres already exists.');
            return self::FAILURE;
        }

        DB::transaction(function () {
            $prisoner = Prisoner::create([
                'name'           => 'Lureida Torres',
                'first_name'     => 'Lureida',
                'last_name'      => 'Torres',
                'description'    => self::BIO,
                'gender'         => 'Female',
                'race'           => 'Hispanic/Latina',
                'state'          => 'New York',
                'era'            => '1970s',
                'ideologies'     => ['Puerto Rican independence', 'Socialism'],
                'affiliation'    => ['Puerto Rican Socialist Party (PSP)', 'Claridad'],
                'in_custody'     => false,
                'released'       => true,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id'        => $prisoner->id,
                'charges'            => 'Civil contempt of court for refusing to testify before a federal grand jury in New York that was investigating FALN bombings — a proceeding the PSP condemned as a "fishing expedition" against the Puerto Rican independence movement (the party said Torres had no connection to the FALN).',
                'incarceration_date' => '1976-06-26',
                'release_date'       => '1976-10-28',
                'convicted'          => 'Jailed for civil contempt (not a criminal conviction).',
                'sentence'           => 'About four months in jail for civil contempt (June 26 – October 28, 1976).',
            ]);

            $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
        });

        return self::SUCCESS;
    }
}
