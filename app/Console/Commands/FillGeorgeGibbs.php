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
 * Fills out George Gibbs's 1940 Illinois criminal-syndicalism ("treason") case
 * with the documented details:
 *   - Arrested July 22, 1940 (age 39) and held in the Fulton County Jail,
 *     Lewistown, Illinois, for soliciting Communist Party ballot-petition
 *     signatures and possessing Earl Browder literature.
 *   - Co-defendants: Ira I. Silbar, Mary Wilson, and Jane Curtiss.
 *   - Held under a combined $80,000 bond for the four; released August 29, 1940
 *     at 4:30 p.m. after $14,400 total bail was posted — about 38 days' jail.
 *   - Later indicted for criminal syndicalism and arraigned about October 1,
 *     1940; no record found that he served a prison sentence.
 *
 * His exact birth date is unknown; age 39 in August 1940 implies a birth about
 * 1901, stored at year precision. Idempotent — rebuilds the single case.
 */
final class FillGeorgeGibbs extends Command
{
    protected $signature = 'prisoners:fill-george-gibbs';

    protected $description = 'Fill George Gibbs\'s 1940 Illinois criminal-syndicalism case (Fulton County Jail)';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()
            ->where('name', 'George Gibbs')
            ->get()
            ->first(fn ($p) => in_array('Communist Party USA', (array) $p->affiliation, true) || ($p->state === 'Illinois'));

        if (! $prisoner) {
            $this->error('George Gibbs (Illinois Communist petition case) not found.');

            return self::FAILURE;
        }

        $jail = Institution::firstOrCreate(
            ['name' => 'Fulton County Jail'],
            ['city' => 'Lewistown', 'state' => 'Illinois']
        )->id;

        DB::transaction(function () use ($prisoner, $jail) {
            $prisoner->setPartialDate('birthdate', 1901);
            $prisoner->save();

            $prisoner->cases()->delete();
            $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $case->fill([
                'prisoner_id' => $prisoner->id,
                'institution_id' => $jail,
                'charges' => '"Treason" / criminal syndicalism (Illinois) — for soliciting Communist Party ballot-petition signatures and possessing Earl Browder literature. Co-defendants: Ira I. Silbar, Mary Wilson, and Jane Curtiss.',
                'convicted' => 'Charged and jailed, then released on bail. Later indicted for criminal syndicalism and arraigned about October 1, 1940; no record found that he served a prison sentence.',
                'sentence' => 'Held about 38 days. Arrested July 22, 1940 under a combined $80,000 bond for the four defendants; released on August 29, 1940 at 4:30 p.m. after $14,400 in total bail was posted.',
            ]);
            $case->setPartialDate('arrest_date', 1940, 7, 22);
            $case->setPartialDate('incarceration_date', 1940, 7, 22);
            $case->setPartialDate('release_date', 1940, 8, 29);
            $case->save();
        });

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Filled George Gibbs\'s Fulton County Jail case (Jul 22 – Aug 29, 1940).');

        return self::SUCCESS;
    }
}
