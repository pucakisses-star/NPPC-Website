<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Records when Pete O'Neal fled the United States into exile. The Kansas City
 * Black Panther leader was sentenced in October 1970 (four years, for
 * transporting a gun across state lines), jumped bail, and fled to Algeria,
 * later settling in Tanzania — where he has remained ever since. Sources fix
 * the flight to 1970, so the exile date is recorded at year precision.
 * Idempotent; leaves the rest of his record untouched.
 */
final class SetPeteONealExileDate extends Command
{
    protected $signature = 'prisoners:set-oneal-exile-date';

    protected $description = "Set Pete O'Neal's in-exile-since date (fled the US in 1970)";

    public function handle(): int
    {
        $p = Prisoner::withoutGlobalScopes()->where('slug', 'pete-oneal')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Pete%O%Neal%')->first();

        if (! $p) {
            $this->error("No Pete O'Neal record found.");

            return self::FAILURE;
        }

        $p->in_exile = true;
        $p->save();

        // Attach the exile-start date to his case (create one if none exists).
        $case = $p->cases()->first() ?? $p->cases()->make([
            'charges' => 'Transporting a firearm across state lines (1969).',
            'convicted' => 'Yes (1970)',
            'sentence' => '4 years',
        ]);
        $case->setPartialDate('in_exile_since', 1970); // fled after his October 1970 sentencing
        $case->save();

        $this->info("Set in_exile_since = 1970 on {$p->name} (case {$case->id}).");
        $this->info("View: /prisoner/{$p->slug}");

        return self::SUCCESS;
    }
}
