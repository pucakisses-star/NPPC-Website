<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Enriches the two ILWU Local 37 (Seattle Filipino cannery workers' union)
 * leaders already in the database from the National Guardian imports — Chris
 * Mensalvas (imported as "Chris Mensalvos") and Ernesto Mangaoang (imported as
 * "Ernest Mangaoang") — with fuller biographies and their canonical name
 * spellings (keeping the imported spellings as aka). Both were McCarthy-era
 * deportation targets under the McCarran / McCarran–Walter Act; Mangaoang's
 * fight produced Mangaoang v. Boyd and the 1954 Supreme Court decision ILWU v.
 * Boyd. Updates existing records in place (matched by name variants) so no
 * duplicates are created. Idempotent.
 */
final class EnrichLocal37Leaders extends Command
{
    protected $signature = 'prisoners:enrich-local37-leaders';

    protected $description = 'Enrich the ILWU Local 37 leaders Chris Mensalvas and Ernesto Mangaoang';

    public function handle(): int
    {
        $people = [
            [
                'match' => ['Chris Mensalvas', 'Chris Mensalvos'],
                'name' => 'Chris Mensalvas', 'first' => 'Chris', 'last' => 'Mensalvas', 'aka' => 'Chris Mensalvos',
                'bio' => 'Chris Mensalvas (also spelled Mensalvos) was a Filipino American labor leader and poet who in 1949 was elected president of Local 7 of the cannery workers\' union — which soon became Local 37 of the ILWU, the Seattle-based Filipino cannery workers\' union. Shortly after taking office he was arrested on anti-Communist and deportation charges, and he spent the McCarthy years fighting repeated attempts to deport him under the McCarran–Walter Act. He defeated the deportation drive and remained president of Local 37 throughout the 1950s. His case was part of the government\'s campaign to break militant, foreign-born trade unionists by deporting them for their labor and political activity.',
                'charges' => 'Deportation proceedings under the McCarran–Walter Act, brought soon after his 1949 election as president of the ILWU Local 37 cannery workers\' union, for his union leadership and alleged Communist affiliation.',
                'convicted' => 'No criminal conviction; the deportation drive was defeated.',
                'sentence' => 'Repeated deportation proceedings through the 1950s; never deported.',
                'incarceration' => [1950],
            ],
            [
                'match' => ['Ernesto Mangaoang', 'Ernest Mangaoang'],
                'name' => 'Ernesto Mangaoang', 'first' => 'Ernesto', 'last' => 'Mangaoang', 'aka' => 'Ernest Mangaoang',
                'bio' => 'Ernesto Mangaoang was a business agent and leader of ILWU Local 37, the Seattle-based Filipino cannery workers\' union. Arrested by federal immigration authorities in the late 1940s and held under the McCarran Act, he faced years of legal battles over the government\'s effort to deport him as an alien. His fight produced the landmark case Mangaoang v. Boyd and the 1954 U.S. Supreme Court decision International Longshoremen\'s & Warehousemen\'s Union v. Boyd, which held that Filipinos who had come to the United States as U.S. nationals before Philippine independence could not be deported as aliens — clarifying the immigration status of some 70,000 Filipino Americans.',
                'charges' => 'Deportation under the McCarran Act as an alleged Communist and militant union leader (ILWU Local 37); arrested by immigration authorities in the late 1940s and held in the King County Jail.',
                'convicted' => 'No criminal conviction; deportation defeated in Mangaoang v. Boyd / ILWU v. Boyd (1954).',
                'sentence' => 'Detention pending deportation; not deported.',
                'incarceration' => [1949],
            ],
        ];

        DB::transaction(function () use ($people) {
            foreach ($people as $p) {
                $prisoner = Prisoner::withUnderReview()->whereIn('name', $p['match'])->first();
                if (! $prisoner) {
                    $this->warn('Not found (skipping): '.$p['name'].' — run the National Guardian imports first.');

                    continue;
                }

                // Only touch identity + narrative fields; leave state/race/
                // ideologies/affiliation/released as imported.
                $prisoner->name = $p['name'];
                $prisoner->first_name = $p['first'];
                $prisoner->last_name = $p['last'];
                $prisoner->aka = $p['aka'];
                $prisoner->description = $p['bio'];
                $prisoner->save();

                $case = $prisoner->cases()->first() ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->prisoner_id = $prisoner->id;
                $case->charges = $p['charges'];
                $case->convicted = $p['convicted'];
                $case->sentence = $p['sentence'];
                if (! empty($p['incarceration'])) {
                    $case->setPartialDate('incarceration_date', ...$p['incarceration']);
                }
                $case->save();

                $this->info('Enriched: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
