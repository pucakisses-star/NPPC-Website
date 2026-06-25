<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds date of birth and federal Bureau of Prisons locator details for Clíver
 * Alcalá Cordones (BOP register #87971-054, FCI Cumberland, projected release
 * 2038-09-14). Updates the existing record if present (prod may hold him even
 * when the local snapshot does not) and otherwise creates a minimal record so
 * the data isn't lost. The facility + release date are recorded on his case.
 * Idempotent.
 */
final class UpdateCliverAlcalaCordones extends Command
{
    protected $signature = 'prisoners:update-cliver-alcala-cordones';

    protected $description = 'Add Clíver Alcalá Cordones DOB and BOP locator details (register #, facility, release date)';

    public function handle(): int
    {
        $prisoner = Prisoner::withoutGlobalScopes()
            ->where('slug', 'cliver-alcala-cordones')
            ->orWhere('name', 'like', '%Alcal%Cordones%')
            ->orWhere('name', 'like', '%Cl_ver%')
            ->first();

        $fields = [
            'birthdate' => '1961-11-21',
            'gender' => 'Male',
            'inmate_number' => '87971-054',
            'in_custody' => true,
            'released' => false,
        ];

        if ($prisoner) {
            $prisoner->fill($fields)->save();
            $this->info("Updated existing prisoner: {$prisoner->name} (ID: {$prisoner->id})");
        } else {
            $prisoner = Prisoner::create(array_merge($fields, [
                'name' => 'Clíver Alcalá Cordones',
                'first_name' => 'Clíver',
                'middle_name' => 'Antonio',
                'last_name' => 'Alcalá Cordones',
                'state' => 'Venezuela',
                'under_review' => false,
                'description' => 'Clíver Antonio Alcalá Cordones is a retired Venezuelan Army major general. He surrendered to U.S. authorities in 2020 and was prosecuted in the Southern District of New York on narco-terrorism conspiracy charges; he is held in federal custody.',
            ]));
            $this->warn("No existing record found — created a new one: {$prisoner->name} (ID: {$prisoner->id})");
        }

        // Record the current federal facility + projected release on a case.
        $institution = Institution::firstOrCreate(
            ['name' => 'FCI Cumberland'],
            ['city' => 'Cumberland', 'state' => 'Maryland'],
        );

        $case = $prisoner->cases()->orderByDesc('incarceration_date')->first();
        if (! $case) {
            $case = $prisoner->cases()->make([]);
            $this->line('No existing case — adding one for the federal incarceration.');
        }

        $case->institution_id = $institution->id;
        $case->setPartialDate('release_date', 2038, 9, 14);
        $case->save();

        $this->info("Set facility to {$institution->name} and release date to 2038-09-14 on his case.");
        $this->info("View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
