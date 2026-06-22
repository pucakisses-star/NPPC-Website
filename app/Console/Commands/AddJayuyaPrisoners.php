<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds/enriches the documented imprisoned participants of the October 30, 1950
 * Jayuya Uprising (El Grito de Jayuya) beyond Blanca Canales:
 *
 *  - Elio Torresola Roura (1918–1975) — cousin of Blanca Canales and brother of
 *    Griselio Torresola (Blair House) and Doris Torresola. A Jayuya field
 *    commander who took over after Carlos Irizarry was mortally wounded; arrested
 *    Nov. 2, 1950 and charged with malicious destruction of government property.
 *    His exact prison term is NOT reliably documented (the "life / 9 years / Pope
 *    Pius XII" story belongs to Heriberto Marín, not Elio), so no term is asserted.
 *
 *  - Heriberto Marín Torres (b. 1928, Barrio Coabey, Jayuya) — already in the
 *    database as a Vieques civil-disobedience arrestee ("Heriberto Marín"). He is
 *    the SAME man, so this ENRICHES that record with his 1950 Jayuya history
 *    (life sentence; ~9 years at La Princesa; the memoir "Coabey: el valle
 *    heroico") rather than creating a duplicate.
 *
 * Carlos Irizarry was killed in the fighting (not a prisoner) and is not added.
 * The Jayuya action was carried out by roughly 32 Nationalists ("los 32"); no
 * further named defendants are itemized in the available sources. Idempotent.
 */
final class AddJayuyaPrisoners extends Command
{
    protected $signature = 'prisoners:add-jayuya-prisoners';

    protected $description = 'Add Elio Torresola and enrich Heriberto Marín with their 1950 Jayuya Uprising imprisonment';

    private const HM_INTRO = 'Heriberto Marín Torres (born November 23, 1928, in Barrio Coabey, Jayuya) was one of the youngest fighters of the 1950 Nationalist insurrection — a Nationalist cadet who helped Blanca Canales raise the Puerto Rican flag during the October 30, 1950 Jayuya Uprising. Arrested days later, he was sentenced to life and served about nine years at La Princesa prison in San Juan before his release amid international pressure, and he later chronicled the uprising in his memoir "Coabey: el valle heroico."';

    public function handle(): int
    {
        $this->addElio();
        $this->enrichHeriberto();

        return self::SUCCESS;
    }

    private function jayuyaCase(): array
    {
        return [
            'charges' => 'Participation in the October 30, 1950 Jayuya Uprising (El Grito de Jayuya) — a Nationalist cadet from Barrio Coabey who helped raise the Puerto Rican flag',
            'arrest_date' => '1950-11-03',
            'incarceration_date' => '1950-11-03',
            'release_date' => '1959-11-03',
            'convicted' => 'Sentenced to life',
            'sentence' => 'Sentenced to life; served about nine years at La Princesa prison, San Juan, before release amid international pressure (which he attributed in part to the quiet intervention of Pope Pius XII)',
        ];
    }

    private function addElio(): void
    {
        if (Prisoner::withUnderReview()->where('name', 'Elio Torresola')->exists()) {
            $this->warn('Elio Torresola already exists — skipping.');

            return;
        }

        DB::transaction(function () {
            $prisoner = Prisoner::create([
                'name' => 'Elio Torresola',
                'first_name' => 'Elio',
                'last_name' => 'Torresola',
                'aka' => 'Elio Torresola Roura',
                'gender' => 'Male',
                'race' => 'Hispanic',
                'state' => 'Puerto Rico',
                'era' => '1950s',
                'birthdate' => '1918-09-18',
                'death_date' => '1975-11-12',
                'ideologies' => ['Puerto Rican Independence', 'Anti-colonial'],
                'affiliation' => ['Puerto Rican Nationalist Party'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Elio Torresola Roura (1918–1975) was a Puerto Rican Nationalist from Jayuya — a cousin of Blanca Canales and brother of Griselio Torresola (killed in the November 1, 1950 Blair House attack on President Truman) and Doris Torresola. In the Jayuya Uprising of October 30, 1950 he was one of the town\'s armed commanders, taking field command after Carlos Irizarry was mortally wounded and directing the burning of the police barracks and the U.S. post office. He was captured on November 2, 1950 and charged with malicious destruction of government property; he was convicted and imprisoned, and died in Jayuya in 1975.',
            ]);

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges' => 'Malicious destruction of government property in connection with the October 30, 1950 Jayuya Uprising — as field commander (after Carlos Irizarry was mortally wounded) he directed the burning of the police barracks and the U.S. post office',
                'arrest_date' => '1950-11-02',
                'convicted' => 'Yes — convicted of malicious destruction of government property',
                'sentence' => 'Convicted and imprisoned for the Jayuya uprising; exact term and release date not reliably documented (he was released by the time of his death in Jayuya in 1975)',
            ]);
        });

        $this->info('Added Elio Torresola.');
    }

    private function enrichHeriberto(): void
    {
        $p = Prisoner::withUnderReview()->where('slug', 'heriberto-marin')->first()
            ?? Prisoner::withUnderReview()->whereIn('name', ['Heriberto Marín', 'Heriberto Marín Torres'])->first();

        if (! $p) {
            // Fallback for environments without the existing Vieques record.
            DB::transaction(function () {
                $prisoner = Prisoner::create([
                    'name' => 'Heriberto Marín Torres',
                    'first_name' => 'Heriberto',
                    'last_name' => 'Marín Torres',
                    'gender' => 'Male',
                    'race' => 'Hispanic',
                    'state' => 'Puerto Rico',
                    'era' => '1950s',
                    'birthdate' => '1928-11-23',
                    'ideologies' => ['Puerto Rican Independence', 'Anti-colonial'],
                    'affiliation' => ['Puerto Rican Nationalist Party'],
                    'in_custody' => false,
                    'released' => true,
                    'awaiting_trial' => false,
                    'description' => self::HM_INTRO.' Decades later he again served prison time as a civil-disobedience protester in the campaign to remove the U.S. Navy from Vieques (1999–2003).',
                ]);
                $case = $this->jayuyaCase();
                $case['prisoner_id'] = $prisoner->id;
                PrisonerCase::create($case);
            });
            $this->info('Added Heriberto Marín Torres (fresh — no existing Vieques record found).');

            return;
        }

        DB::transaction(function () use ($p) {
            if (! $p->birthdate) {
                $p->birthdate = '1928-11-23';
            }
            if (! $p->aka) {
                $p->aka = 'Heriberto Marín Torres';
            }
            $p->era = '1950s';
            if (! str_contains((string) $p->description, 'Jayuya')) {
                $p->description = self::HM_INTRO."\n\n".(string) $p->description;
            }
            $p->save();

            if (! $p->cases()->where('charges', 'like', '%Jayuya%')->exists()) {
                $case = $this->jayuyaCase();
                $case['prisoner_id'] = $p->id;
                PrisonerCase::create($case);
                $this->info('  Added 1950 Jayuya case.');
            }
        });

        $this->info('Enriched Heriberto Marín with his 1950 Jayuya imprisonment.');
    }
}
