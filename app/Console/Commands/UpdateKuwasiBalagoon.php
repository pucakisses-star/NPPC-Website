<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Kuwasi Balagoon's death date. He (born Donald Weems; Black Panther
 * Party / Black Liberation Army; a Brink's/Nyack 1981 defendant) died of
 * AIDS-related illness in custody on December 13, 1986. This sets his
 * death_date and the case's death_in_custody_date (which, via the
 * PrisonerCase saving hook, also sets release_date to the same day), and
 * fills his birthdate so the profile shows his age as deceased. Idempotent.
 */
final class UpdateKuwasiBalagoon extends Command
{
    protected $signature = 'prisoners:update-kuwasi-balagoon';

    protected $description = "Add Kuwasi Balagoon's death date (died in custody, Dec 13, 1986)";

    private const DEATH = '1986-12-13';

    private const BIRTH = '1946-12-22';

    public function handle(): int
    {
        $p = Prisoner::withUnderReview()->where('slug', 'kuwasi-balagoon')->first();

        if (! $p) {
            $this->warn('kuwasi-balagoon not found — skipping (no-op).');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($p) {
            if (empty($p->birthdate)) {
                $p->birthdate = self::BIRTH;
            }
            $p->death_date = self::DEATH;
            $p->in_custody = false;
            $p->released = false; // died in custody — not released
            $p->save();

            // Set the death-in-custody date on his case; the saving hook syncs
            // release_date to match. Create a case only if he has none.
            $case = $p->cases()->first();
            if (! $case) {
                $case = new PrisonerCase([
                    'prisoner_id' => $p->id,
                    'charges' => '1981 Brink\'s armored-car expropriation, Nyack, NY (Black Liberation Army / Revolutionary Armed Task Force)',
                    'convicted' => 'Convicted 1983',
                ]);
            }
            $case->prisoner_id = $p->id;
            $case->death_in_custody_date = self::DEATH;
            $case->save();
        });

        $this->info('Updated Kuwasi Balagoon: death date '.self::DEATH.' (died in custody).');

        return self::SUCCESS;
    }
}
