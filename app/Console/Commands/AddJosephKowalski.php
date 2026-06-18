<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Joseph Kowalski — secretary of the Polish Language Federation of the
 * Communist Party of America at its 1919 founding, swept up in the post-WWI
 * Red Scare, accused by the government of Cheka/Comintern ties, sentenced in
 * a federal proceeding before Judge Julian W. Mack, and deported to Soviet
 * Russia. Idempotent (skips if he already exists).
 *
 * Era/flags follow the convention already used for the other first-Red-Scare
 * deportees in the database (Emma Goldman, Alexander Berkman, Bill Haywood):
 * era "1910s", released = true, exile flags off.
 */
final class AddJosephKowalski extends Command
{
    protected $signature = 'prisoners:add-joseph-kowalski';

    protected $description = 'Add Joseph Kowalski (CPA Polish Federation secretary; Red Scare deportee)';

    public function handle(): int
    {
        if (Prisoner::withUnderReview()->where('name', 'Joseph Kowalski')->exists()) {
            $this->warn('Joseph Kowalski already exists — skipping.');

            return self::SUCCESS;
        }

        DB::transaction(function () {
            $prisoner = Prisoner::create([
                'name' => 'Joseph Kowalski',
                'first_name' => 'Joseph',
                'last_name' => 'Kowalski',
                'gender' => 'Male',
                'race' => 'White',
                'era' => '1910s',
                'ideologies' => ['Communism'],
                'affiliation' => ['Communist Party of America'],
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'currently_in_exile' => false,
                'awaiting_trial' => false,
                'description' => "Joseph Kowalski was a Polish-born communist organizer who served as secretary of the Polish Language Federation of the Communist Party of America (CPA) at the party's founding in 1919. During the post–World War I Red Scare he was among the CPA leaders swept up in the federal anti-radical crackdown of early 1920. The government alleged that he was an agent of the Soviet Cheka (the Bolshevik secret police) and an emissary of the Communist International (Comintern). He was sentenced in a federal proceeding before Judge Julian W. Mack and deported to Soviet Russia — one of hundreds of foreign-born radicals expelled from the United States during the deportation drive of 1919–1921.",
            ]);

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges' => 'Leadership in the Communist Party of America; targeted in the post-WWI Red Scare anti-radical prosecutions. The government alleged membership in the Soviet Cheka and the Communist International (Comintern).',
                'indicted' => 'January 23, 1920 (Communist Party of America leaders)',
                'judge' => 'Julian W. Mack',
                'convicted' => 'Ordered deported as an alien member of the Communist Party',
                'sentence' => 'Sentenced before Judge Julian W. Mack and deported (expelled) to Soviet Russia',
            ]);
        });

        $this->info('Added Joseph Kowalski.');

        return self::SUCCESS;
    }
}
