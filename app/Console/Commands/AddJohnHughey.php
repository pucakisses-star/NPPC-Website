<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds John David Hughey III ("David Hughey"), a member of the 1969 Sam
 * Melville / Jane Alpert New York City bombing collective. Arrested in
 * November 1969 with the group, he pleaded guilty to conspiracy and served
 * about two years; he refused to inform on his co-defendants. The other
 * pictured "suspects" in the 1969 press coverage are already handled:
 * Melville, Alpert, and Swinton are in the database, and George Demmerle was
 * the FBI informant who set the group up (excluded). Idempotent.
 *
 * Birth/death dates are intentionally left blank: the seemingly matching
 * public records (a Southern Baptist missionary lineage) are a different
 * family, and no reliable DOB/DOD for the bomber was found.
 */
final class AddJohnHughey extends Command
{
    protected $signature = 'prisoners:add-john-hughey';

    protected $description = 'Add John (David) Hughey of the 1969 Melville/Alpert bombing group';

    public function handle(): int
    {
        if (Prisoner::withUnderReview()->where('name', 'John Hughey')->exists()) {
            $this->warn('John Hughey already exists — skipping.');

            return self::SUCCESS;
        }

        DB::transaction(function () {
            $prisoner = Prisoner::create([
                'name' => 'John Hughey',
                'first_name' => 'John',
                'middle_name' => 'David',
                'last_name' => 'Hughey',
                'aka' => 'David Hughey; John David Hughey III',
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'New York',
                'era' => '1960s',
                'ideologies' => ['Anti-war', 'New Left', 'Anti-imperialism'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "John David Hughey III (\"David Hughey\") was a member of the Sam Melville–Jane Alpert collective that bombed government and corporate buildings in New York City in 1969. He was arrested in November 1969 at Jane Alpert's Lower East Side apartment, in the same FBI operation that caught Melville, and pleaded guilty to conspiracy to destroy federal property. Sentenced as a young adult offender, he served roughly two years and refused to inform on his co-defendants; his conviction was later set aside under the federal Youth Corrections Act after he completed his term. In 1975 he was subpoenaed at the trial of fellow group member Patricia Swinton, refused to testify even under a grant of immunity, and was jailed for civil contempt until the day she was acquitted. By later accounts he changed his name and became a preacher in the South.",
            ]);

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges' => 'Conspiracy to bomb / destroy federal property (the 1969 New York City bombing campaign)',
                'arrest_date' => '1969-11-12',
                'convicted' => 'Pleaded guilty to conspiracy; conviction later set aside under the federal Youth Corrections Act (18 U.S.C. § 5021) after he completed his sentence',
                'sentence' => 'Sentenced as a young adult offender; served about two years. (Separately jailed for civil contempt in 1975 for refusing to testify at Patricia Swinton\'s trial.)',
            ]);
        });

        $this->info('Added John Hughey.');

        return self::SUCCESS;
    }
}
