<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Enriches the existing Jasmine Richards ("Abdullah") record — founder of Black
 * Lives Matter Pasadena — with the case it was missing, drawn from the LA Times
 * report on her June 2016 sentencing. She is already in the database (with a
 * photo) but had zero cases, so this adds one rather than creating a duplicate.
 * Idempotent: skips if she already has a case.
 */
final class AddJasmineRichardsCase extends Command
{
    protected $signature = 'prisoners:add-jasmine-richards-case';

    protected $description = 'Add the missing case to the existing Jasmine Richards record';

    public function handle(): int
    {
        $prisoner = Prisoner::withoutGlobalScopes()->where('slug', 'jasmine-richards')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'Jasmine Richards')->first();

        if (! $prisoner) {
            $this->warn('No Jasmine Richards record found — nothing to enrich.');

            return self::SUCCESS;
        }

        if ($prisoner->cases()->count() > 0) {
            $this->info("{$prisoner->name} already has a case — nothing to add.");

            return self::SUCCESS;
        }

        $prisoner->in_custody = false;
        $prisoner->released = true;
        $prisoner->save();

        $prisoner->cases()->create([
            'charges' => "Convicted under California Penal Code § 405a — attempting to take a person from the lawful custody of a peace officer, the statute historically labeled \"felony lynching\" — for intervening as Pasadena police arrested a woman during an August 2015 Black Lives Matter demonstration near La Pintoresca Park. Her conviction, an early one under the statute after its \"lynching\" wording drew national criticism, became a cause célèbre.",
            'arrest_date' => '2015-09-01',
            'convicted' => 'Convicted June 1, 2016 (California Penal Code § 405a)',
            'sentenced_date' => '2016-06-07',
            'incarceration_date' => '2016-06-07',
            'sentence' => "90 days in county jail and three years' probation.",
        ]);

        $this->info("Added case to {$prisoner->name}. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
