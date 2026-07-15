<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Fills the empty "Bill Morgan" stub — a farmer from Greenville, Alabama
 * convicted under the federal Espionage and Sedition Acts for opposing U.S.
 * involvement in World War I. Sentenced to two years, he served at the United
 * States Penitentiary in Atlanta from December 1919 to November 23, 1920, and
 * famously refused parole, choosing to serve his full term.
 *
 * Create-or-update by slug; rebuilds his single case. Idempotent.
 */
class FillBillMorgan extends Command
{
    protected $signature = 'prisoners:fill-bill-morgan';

    protected $description = 'Fill Bill Morgan (WWI Espionage/Sedition Act prisoner who refused parole)';

    public function handle(): int
    {
        DB::transaction(function () {
            $atlanta = Institution::firstOrCreate(
                ['name' => 'United States Penitentiary, Atlanta'],
                ['city' => 'Atlanta', 'state' => 'Georgia']
            );

            $m = Prisoner::withUnderReview()->where('slug', 'bill-morgan')->first()
                ?? new Prisoner(['name' => 'Bill Morgan']);

            $m->fill([
                'name' => 'Bill Morgan',
                'first_name' => 'Bill',
                'last_name' => 'Morgan',
                'gender' => 'Male',
                'state' => 'Alabama',
                'era' => '1910s',
                'ideologies' => ['Anti-War', 'Free speech'],
                'affiliation' => [],
                'description' => 'Bill Morgan was a farmer from Greenville, Alabama, one of many people convicted and imprisoned under the federal Espionage and Sedition Acts for opposing American involvement in World War I. Sentenced to two years, he served his term at the United States Penitentiary in Atlanta from December 1919 to November 23, 1920 — and famously refused an offer of parole, choosing to serve out his full sentence rather than accept early release on the government\'s terms.',
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'awaiting_trial' => false,
            ]);
            $m->save();

            $m->cases()->delete();
            $case = new PrisonerCase(['prisoner_id' => $m->id]);
            $case->fill([
                'prisoner_id' => $m->id,
                'institution_id' => $atlanta->id,
                'charges' => 'Violating the federal Espionage and Sedition Acts — for speech opposing U.S. participation in World War I.',
                'convicted' => 'Yes — convicted and sentenced to two years; he refused an offer of parole and served the full term.',
                'sentence' => 'Two years, served in full at the United States Penitentiary in Atlanta (December 1919 – November 23, 1920), having refused parole.',
                'release_date' => '1920-11-23',
            ]);
            $case->setPartialDate('incarceration_date', 1919, 12);
            $case->save();

            $this->info('Filled Bill Morgan (slug: '.$m->slug.').');
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
