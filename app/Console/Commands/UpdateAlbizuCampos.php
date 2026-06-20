<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Pedro Albizu Campos's record carried only his 1954 imprisonment as a case,
 * even though his bio describes three separate imprisonments. This adds the two
 * missing ones as their own cases:
 *
 *  - His FIRST arrest: the 1936 seditious-conspiracy prosecution (federal
 *    grand-jury indictment April 3, 1936, after the assassination of Insular
 *    Police Chief Francis Riggs), which sent him to the U.S. Penitentiary in
 *    Atlanta from 1937 until his 1947 return to Puerto Rico. Only the years of
 *    the Atlanta custody are reliably documented, so no day-level
 *    incarceration/release dates are asserted.
 *  - His 1950 arrest (Nov 2, 1950) under the Gag Law (Law 53), the 80-year
 *    sentence, and the conditional pardon of Nov 13, 1953.
 *
 * It also corrects a factual error in the bio: the seditious-conspiracy
 * prosecution followed the 1936 Riggs assassination, NOT the Ponce Massacre,
 * which actually occurred in March 1937 while he was already imprisoned.
 *
 * Idempotent: the bio fix and each case are guarded so re-running is a no-op.
 */
final class UpdateAlbizuCampos extends Command
{
    protected $signature = 'prisoners:update-albizu-campos';

    protected $description = "Add Albizu Campos's missing 1936 and 1950 imprisonment cases and correct the bio";

    private const OLD_BIO = 'In 1937, after the Ponce Massacre in which Insular Police killed 19 unarmed Nationalists and bystanders, federal authorities prosecuted Albizu Campos and other party leaders for seditious conspiracy. He was sentenced to ten years and held at the United States Penitentiary in Atlanta from 1937 until 1947. His health collapsed during this imprisonment, a pattern that would repeat.';

    private const NEW_BIO = 'In 1936, after two Nationalists assassinated Insular Police Chief Francis Riggs, federal authorities indicted Albizu Campos and other party leaders for seditious conspiracy; a first trial ended in a hung jury that voted 7–5 to acquit, but a second jury convicted them. He was held at the United States Penitentiary in Atlanta from 1937, released in 1943 to medical care in New York City, and not allowed to return to Puerto Rico until 1947. (The Ponce Massacre, in which Insular Police killed 19 unarmed Nationalists and bystanders, came during a march in March 1937 while he was imprisoned.) His health collapsed during this imprisonment, a pattern that would repeat.';

    public function handle(): int
    {
        $p = Prisoner::withUnderReview()->where('slug', 'pedro-albizu-campos')->first()
            ?? Prisoner::withUnderReview()->where('name', 'Pedro Albizu Campos')->first();

        if (! $p) {
            $this->warn('Pedro Albizu Campos not found — skipping (no-op).');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($p) {
            if ($p->description && str_contains($p->description, self::OLD_BIO)) {
                $p->description = str_replace(self::OLD_BIO, self::NEW_BIO, $p->description);
                $p->save();
                $this->info('  Corrected bio (1936 Riggs prosecution, not the 1937 Ponce Massacre).');
            }

            if (! $p->cases()->whereDate('arrest_date', '1936-04-03')->exists()) {
                PrisonerCase::create([
                    'prisoner_id' => $p->id,
                    'charges' => 'Seditious conspiracy — conspiring to overthrow the United States government in Puerto Rico; federal prosecution of the Nationalist Party leadership after the February 1936 assassination of Insular Police Chief Francis Riggs (grand-jury indictment April 3, 1936)',
                    'arrest_date' => '1936-04-03',
                    'convicted' => 'Acquitted at the first trial (a jury of seven Puerto Ricans and five Americans voted 7–5 to acquit); convicted at a retrial by a jury of ten Americans and two Puerto Ricans',
                    'sentence' => 'Six to ten years for seditious conspiracy; imprisoned at the United States Penitentiary in Atlanta, Georgia from 1937, released in 1943 to medical care in New York City, and barred from returning to Puerto Rico until 1947',
                ]);
                $this->info('  Added the 1936 first-arrest case (seditious conspiracy).');
            }

            if (! $p->cases()->whereDate('arrest_date', '1950-11-02')->exists()) {
                PrisonerCase::create([
                    'prisoner_id' => $p->id,
                    'charges' => 'Twelve violations of Puerto Rico\'s Law 53 (Ley de la Mordaza, the "Gag Law") — inciting revolt — following the October 30, 1950 Nationalist uprising',
                    'arrest_date' => '1950-11-02',
                    'incarceration_date' => '1950-11-02',
                    'release_date' => '1953-11-13',
                    'convicted' => 'Convicted under the Gag Law (Law 53) and sentenced to 80 years',
                    'sentence' => '80 years; held at La Princesa Prison, San Juan. Granted a conditional pardon by Governor Luis Muñoz Marín on November 13, 1953 in failing health (revoked on March 3, 1954 after the Capitol attack)',
                ]);
                $this->info('  Added the 1950 Gag Law case (80-year sentence).');
            }
        });

        $this->info('Done updating Pedro Albizu Campos.');

        return self::SUCCESS;
    }
}
