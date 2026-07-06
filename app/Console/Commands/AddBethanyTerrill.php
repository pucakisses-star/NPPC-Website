<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Adds Bethany Abigail Terrill (b. ~1988), of Malden, Massachusetts, charged
 * October 16, 2025 with threatening a United States official after she
 * physically interjected into an ICE Enforcement and Removal Operations arrest
 * outside the Malden District Court on September 29, 2025, verbally confronting
 * the agents and — per the criminal complaint, captured on body-worn cameras —
 * threatening to kill them, invoking the killing of Charlie Kirk. She was
 * released on bail pending trial and faces up to 10 years. Idempotent —
 * create-or-update by name.
 */
class AddBethanyTerrill extends Command
{
    protected $signature = 'prisoners:add-bethany-terrill';

    protected $description = 'Add Bethany Abigail Terrill (2025 Malden anti-ICE threats case)';

    public function handle(): int
    {
        DB::transaction(function () {
            $p = Prisoner::withUnderReview()->where('name', 'Bethany Terrill')->first()
                ?? new Prisoner(['name' => 'Bethany Terrill']);

            $p->fill([
                'name' => 'Bethany Terrill',
                'first_name' => 'Bethany',
                'middle_name' => 'Abigail',
                'last_name' => 'Terrill',
                'aka' => 'Bethany Abigail Terrill',
                'gender' => 'Female',
                'state' => 'Massachusetts',
                'era' => '2020s',
                'ideologies' => ['Anti-ICE', 'Anti-fascism'],
                'description' => 'Bethany Abigail Terrill, of Malden, Massachusetts, was charged on October 16, 2025 with threatening a United States official. According to the criminal complaint, on September 29, 2025 federal agents supporting Immigration and Customs Enforcement (ICE) Enforcement and Removal Operations were making an administrative immigration arrest in the area of the Malden District Court when Terrill physically interjected herself into the arrest and became verbally abusive, calling the agents "monsters" and "Nazis" and — captured on the agents\' body-worn cameras — threatening to kill them, reportedly yelling "Charlie Kirk died, and we love it… We\'re coming for you, gonna kill you." She was released on bail pending trial; the charge carries a sentence of up to 10 years in prison.',
                'in_custody' => false,
                'released' => false,
                'in_exile' => false,
                'awaiting_trial' => true,
            ]);
            $p->save();

            $case = $p->cases()->first() ?? new PrisonerCase(['prisoner_id' => $p->id]);
            $case->fill([
                'prisoner_id' => $p->id,
                'charges' => 'Threatening a United States official (18 U.S.C. § 115) — for allegedly threatening to kill federal agents who were supporting an ICE immigration arrest outside the Malden District Court on September 29, 2025.',
                'convicted' => 'Charged by criminal complaint on October 16, 2025; awaiting trial. Released on bail (no history of violent crime).',
                'sentence' => 'Not yet tried — the charge provides for up to 10 years in prison, three years of supervised release, and a fine of up to $250,000.',
                'arrest_date' => '2025-10-16',
            ]);
            $case->save();

            $this->info('Added Bethany Terrill (slug: '.$p->slug.'; charged 2025-10-16, awaiting trial).');
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
