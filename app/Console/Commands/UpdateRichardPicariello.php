<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Fills in Richard Picariello's details (Fred Hampton Unit of the People's
 * Forces): middle name Joseph, DOB Oct 12 1948, and the Oct 21 1976 arrest /
 * incarceration with a July 27 1995 release. The PrisonerCase saving hook
 * recomputes imprisoned_for_days from the incarceration and release dates.
 * Idempotent.
 */
final class UpdateRichardPicariello extends Command
{
    protected $signature = 'prisoners:update-richard-picariello';

    protected $description = "Add Richard Picariello's middle name, DOB, and arrest/incarceration/release dates";

    public function handle(): int
    {
        $p = Prisoner::withUnderReview()->where('slug', 'richard-picariello')->first();

        if (! $p) {
            $this->warn('richard-picariello not found — skipping (no-op).');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($p) {
            $p->first_name = $p->first_name ?: 'Richard';
            $p->middle_name = 'Joseph';
            $p->last_name = $p->last_name ?: 'Picariello';
            $p->birthdate = '1948-10-12';
            $p->in_custody = false;
            $p->released = true;
            $p->save();

            $case = $p->cases()->first() ?? new PrisonerCase([
                'prisoner_id' => $p->id,
                'charges' => "Interstate transportation of explosives — the Fred Hampton Unit's 1976 bombing campaign in Massachusetts and New Hampshire",
            ]);
            $case->prisoner_id = $p->id;
            $case->arrest_date = '1976-10-21';
            $case->incarceration_date = '1976-10-21';
            $case->release_date = '1995-07-27';
            $case->save();
        });

        $this->info('Updated Richard Picariello: middle name Joseph, DOB 1948-10-12, arrest/incarceration 1976-10-21, release 1995-07-27.');

        return self::SUCCESS;
    }
}
