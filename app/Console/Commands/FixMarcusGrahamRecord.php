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
 * Corrects the Marcus Graham record. The original entry (from
 * prisoners:add-anarchist-press-prisoners) conflated two separate episodes into
 * one case. The accurate timeline, per the biographical record:
 *
 *   1. 1919 — held about six months on Ellis Island over The Anarchist Soviet
 *      Bulletin. A deportation order was issued (the "seventeen-year-old edict"
 *      later dismissed in 1938 dates the mandate to 1919), but he was released
 *      rather than deported because Russia, Mexico, and Canada all refused to
 *      accept him. Exact arrest/release days do not survive.
 *   2. 1936–1938 — the dormant 1919 order was revived (notice ~June 11, 1936);
 *      immigration officers raided the MAN! office on October 6, 1937; Judge
 *      Leon R. Yankovich dismissed the edict on January 14, 1938.
 *   3. June 1940 — he lost a contempt appeal and was sentenced to serve six
 *      months (or until he agreed to answer immigration inspectors' questions
 *      about his age and birthplace).
 *
 * Also sets his real name and dates: Shmuel Marcus, b. 1893 (Dorohoi, Romania),
 * d. December 1985 (Manchester, New Hampshire). Birth is year-precision, death
 * month-precision — no exact days are documented. Idempotent.
 */
class FixMarcusGrahamRecord extends Command
{
    protected $signature = 'prisoners:fix-marcus-graham-record';

    protected $description = 'Correct Marcus Graham: split the 1919 Ellis Island detention from the 1936-40 deportation/contempt case, add DOB/DOD';

    public function handle(): int
    {
        DB::transaction(function () {
            $g = Prisoner::withUnderReview()->where('name', 'Marcus Graham')->first();
            if (! $g) {
                $this->warn('Marcus Graham not found — run prisoners:add-anarchist-press-prisoners first.');

                return;
            }

            $g->aka = 'Shmuel Marcus';
            $g->description = 'Marcus Graham (the pen name of Shmuel Marcus) was an anarchist editor — of the Anarchist Soviet Bulletin (1919), later Free Society and The Road to Freedom, and, in the 1930s, the Los Angeles paper MAN! A Journal of the Anarchist Ideal and Movement. A Romanian-Jewish immigrant born in Dorohoi in 1893, he refused on principle to disclose his birthplace or cooperate with immigration authorities, and the government spent two decades trying to deport him. Arrested during the 1919 Red Scare for distributing the Anarchist Soviet Bulletin, he was held about six months on Ellis Island while officials tried to establish his country of origin; a deportation order was issued, but he was released rather than deported when Russia, Mexico, and Canada all refused to accept him. That dormant 1919 edict was revived in 1936; immigration officers raided the MAN! office on October 6, 1937, and Judge Leon R. Yankovich finally dismissed the seventeen-year-old order on January 14, 1938. In June 1940 Graham lost a contempt appeal and was sentenced to six months (or until he agreed to answer immigration inspectors\' questions about his age and birthplace). He was never deported. He died in Manchester, New Hampshire in December 1985.';
            $g->state = 'New York';
            $g->era = '1910s';
            $g->setPartialDate('birthdate', 1893);          // year only
            $g->setPartialDate('death_date', 1985, 12);     // December 1985
            $g->save();

            $ellis = Institution::firstOrCreate(
                ['name' => 'Ellis Island Immigration Station'],
                ['city' => 'New York', 'state' => 'New York']
            );

            // --- Case 1: 1919 Ellis Island detention ---
            // Reuse the existing (single) case as the Ellis Island one.
            $ellisCase = $g->cases()->where('charges', 'like', '%Ellis Island%')->first()
                ?? $g->cases()->first()
                ?? new PrisonerCase(['prisoner_id' => $g->id]);
            $ellisCase->fill([
                'prisoner_id' => $g->id,
                'institution_id' => $ellis->id,
                'charges' => 'Held for deportation under the anarchist-exclusion / immigration laws for distributing the Anarchist Soviet Bulletin during the 1919 Red Scare.',
                'convicted' => 'Ordered deported (the order was issued in 1919), but released rather than deported when Russia, Mexico, and Canada all refused to accept him.',
                'sentence' => 'Held about six months on Ellis Island in 1919 while the government tried to determine his country of origin; then released. Exact arrest and release dates are not documented.',
            ]);
            $ellisCase->save();

            // --- Case 2: 1936-1940 revival of the 1919 order + 1940 contempt ---
            $deportCase = $g->cases()->where('charges', 'like', '%1938%')->first()
                ?? new PrisonerCase(['prisoner_id' => $g->id]);
            $deportCase->fill([
                'prisoner_id' => $g->id,
                'charges' => 'Revival of the 1919 deportation order (notice received about June 11, 1936); immigration officers raided the MAN! magazine office in Los Angeles on October 6, 1937. The seventeen-year-old edict was dismissed by Judge Leon R. Yankovich on January 14, 1938.',
                'convicted' => 'In June 1940 he lost a contempt appeal arising from the case and was sentenced to serve his time. He was never deported.',
                'sentence' => 'Sentenced (June 1940) to six months, or until he agreed to answer immigration inspectors\' questions about his age and birthplace.',
            ]);
            $deportCase->save();

            $this->info('Corrected Marcus Graham (Shmuel Marcus): 1919 Ellis Island case + 1936-40 deportation/contempt case; DOB 1893, DOD Dec 1985.');
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
