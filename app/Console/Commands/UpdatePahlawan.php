<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Sets Muhammad Pahlawan's federal inmate number (00633-511) and his facility
 * (USP Florence High, Colorado) on his case. Matches the live record by slug,
 * then name. Idempotent.
 */
final class UpdatePahlawan extends Command
{
    protected $signature = 'prisoners:update-pahlawan';

    protected $description = "Set Muhammad Pahlawan's inmate number and facility (USP Florence High)";

    public function handle(): int
    {
        $p = Prisoner::withoutGlobalScopes()->where('slug', 'muhammad-pahlawan')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Pahlawan%')->first();

        if (! $p) {
            $this->error('No Muhammad Pahlawan record found.');

            return self::FAILURE;
        }

        $p->inmate_number = '00633-511';
        $p->save();
        $this->info("Set inmate number 00633-511 on {$p->name}.");

        $institution = Institution::firstOrCreate(
            ['name' => 'USP Florence High'],
            ['city' => 'Florence', 'state' => 'Colorado'],
        );
        $case = $p->cases()->first() ?? $p->cases()->make([]);
        $case->institution_id = $institution->id;
        $case->save();
        $this->info("Set facility to {$institution->name}.");

        $this->info("View: /prisoner/{$p->slug}");

        return self::SUCCESS;
    }
}
