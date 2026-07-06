<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Joseph P. Kamp (1900–1993), a right-wing pamphleteer who ran the
 * Constitutional Educational League, imprisoned for contempt of Congress for
 * refusing to hand the group's donor list to the House Campaign Expenditures
 * Committee — a direct parallel to the JAFRC board members the site already
 * carries, jailed for the same act of defiance from the opposite end of the
 * political spectrum. Added for factual completeness of contempt-of-Congress
 * jailings.
 *
 * Create-or-update by name; rebuilds the single case. Idempotent.
 */
class AddContemptOfCongressDefiers extends Command
{
    protected $signature = 'prisoners:add-contempt-of-congress-defiers';

    protected $description = 'Add Joseph P. Kamp (jailed for contempt of Congress over his group\'s donor list)';

    public function handle(): int
    {
        DB::transaction(function () {
            $kamp = Prisoner::withUnderReview()->where('name', 'Joseph P. Kamp')->first()
                ?? new Prisoner(['name' => 'Joseph P. Kamp']);

            $kamp->fill([
                'name' => 'Joseph P. Kamp',
                'first_name' => 'Joseph',
                'middle_name' => 'P.',
                'last_name' => 'Kamp',
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'New York',
                'era' => '1940s',
                'ideologies' => ['Anti-communism', 'Right-wing populism'],
                'affiliation' => ['Constitutional Educational League'],
                'description' => 'Joseph P. Kamp (1900–1993) was a right-wing pamphleteer who ran the Constitutional Educational League, a militantly anti-communist propaganda group. When a House committee investigating 1944 campaign expenditures demanded to know who had financed a League brochure it deemed political, Kamp refused to disclose the organization\'s contributors. Convicted of contempt of Congress, he was sentenced to four months and began serving on June 16, 1950 — his refusal to surrender a donor list mirroring, from the opposite end of the political spectrum, the Joint Anti-Fascist Refugee Committee board members jailed for the same act of defiance.',
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'awaiting_trial' => false,
            ]);
            $kamp->setPartialDate('birthdate', 1900, 6, 24);
            $kamp->setPartialDate('death_date', 1993, 6);
            $kamp->save();

            $kamp->cases()->delete();
            $case = new PrisonerCase(['prisoner_id' => $kamp->id]);
            $case->fill([
                'prisoner_id' => $kamp->id,
                'charges' => 'Contempt of Congress — for refusing to tell the House Campaign Expenditures Committee who had financed the Constitutional Educational League\'s 1944 political literature.',
                'convicted' => 'Yes — convicted of contempt of Congress; conviction upheld on appeal.',
                'sentence' => 'Four months in federal prison, beginning June 16, 1950.',
            ]);
            $case->incarceration_date = '1950-06-16';
            $case->save();

            $this->info('Set Joseph P. Kamp (slug: '.$kamp->slug.').');
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
