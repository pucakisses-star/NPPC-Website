<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds the incarceration and release dates to James McHugo's case. McHugo, an
 * IWW officer from Oakland, was prosecuted under the Espionage/Sedition Acts
 * and served at San Quentin from December 22, 1919 until his parole on
 * December 25, 1921. The dates are set on his existing case (one is created
 * only if he somehow has none); he is also marked released. Idempotent.
 */
final class SetMcHugoCaseDates extends Command
{
    protected $signature = 'prisoners:set-mchugo-case-dates';

    protected $description = "Add James McHugo's incarceration (1919-12-22) and parole/release (1921-12-25) dates";

    public function handle(): int
    {
        $p = Prisoner::withoutGlobalScopes()->where('slug', 'james-mchugo')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%McHugo%')->first();

        if (! $p) {
            $this->error('No James McHugo record found.');

            return self::FAILURE;
        }

        $case = $p->cases()->first();
        if (! $case) {
            $inst = Institution::firstOrCreate(
                ['name' => 'San Quentin State Prison'],
                ['city' => 'San Quentin', 'state' => 'California'],
            );
            $case = $p->cases()->make([
                'institution_id' => $inst->id,
                'charges' => 'Federal prosecution under the Espionage Act of 1917 and/or the Sedition Act of 1918.',
                'convicted' => 'Yes',
                'sentence' => '1 to 14 years',
            ]);
            $this->line('No existing case — created one (San Quentin).');
        }

        $case->setPartialDate('incarceration_date', 1919, 12, 22);
        $case->setPartialDate('release_date', 1921, 12, 25); // paroled
        $case->save();

        $p->in_custody = false;
        $p->released = true;
        $p->save();

        $this->info("Set McHugo case dates: incarcerated 1919-12-22, paroled 1921-12-25 ({$case->imprisoned_for_days} days).");
        $this->info("View: /prisoner/{$p->slug}");

        return self::SUCCESS;
    }
}
