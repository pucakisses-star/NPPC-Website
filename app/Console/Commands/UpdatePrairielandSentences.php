<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;

/**
 * Record the June 23, 2026 federal sentences for the Prairieland Detention
 * Center defendants (U.S. District Court, Northern District of Texas, Fort
 * Worth), convicted March 13, 2026 over the July 4, 2025 attack at the
 * Alvarado, Texas ICE facility. Updates each defendant's case `sentence`
 * and rewrites the forward-looking "faces ... years" line in their bio to
 * the actual term handed down.
 *
 * Idempotent — safe to re-run (the bio rewrite only fires while the old
 * clause is still present; the sentence is simply re-set).
 *
 * NOTE: Ines Soto is intentionally omitted. She was convicted on March 13,
 * 2026, but only eight of the nine defendants were sentenced on June 23 and
 * no sentence for her was reported. Update her separately once confirmed.
 *
 * Sentences per Fort Worth Report, KERA, PBS NewsHour and NBC 5 DFW
 * (all reporting June 23, 2026).
 */
final class UpdatePrairielandSentences extends Command {
    protected $signature = 'prisoners:update-prairieland-sentences';
    protected $description = 'Record the June 23, 2026 sentences for the Prairieland defendants';

    public function handle(): int {
        // exact prisoner name => [case sentence, bio clause to replace, replacement]
        $updates = [
            'Benjamin Song' => [
                'sentence' => 'Sentenced June 23, 2026 to 100 years in federal prison (U.S. District Court, Northern District of Texas, Fort Worth).',
                'descFrom' => 'He faces a minimum of 20 years and up to life in federal prison.',
                'descTo'   => 'On June 23, 2026 he was sentenced to 100 years in federal prison, the longest term of any defendant in the case.',
            ],
            'Maricela Rueda' => [
                'sentence' => 'Sentenced June 23, 2026 to 70 years in federal prison.',
                'descFrom' => 'She faces a minimum of 10 years and a maximum of 60 years in federal prison.',
                'descTo'   => 'On June 23, 2026 she was sentenced to 70 years in federal prison.',
            ],
            'Cameron Arnold' => [
                'sentence' => 'Sentenced June 23, 2026 to 50 years in federal prison, plus 2 years of supervised release.',
                'descFrom' => 'She faces a minimum of 10 years and a maximum of 60 years in federal prison.',
                'descTo'   => 'On June 23, 2026 she was sentenced to 50 years in federal prison (plus 2 years of supervised release).',
            ],
            'Zachary Evetts' => [
                'sentence' => 'Sentenced June 23, 2026 to 50 years in federal prison, plus 2 years of supervised release.',
                'descFrom' => 'He faces a minimum of 10 years and a maximum of 60 years in federal prison.',
                'descTo'   => 'On June 23, 2026 he was sentenced to 50 years in federal prison (plus 2 years of supervised release).',
            ],
            'Savanna Batten' => [
                'sentence' => 'Sentenced June 23, 2026 to 50 years in federal prison, plus 2 years of supervised release.',
                'descFrom' => 'She faces a minimum of 10 years and a maximum of 60 years in federal prison.',
                'descTo'   => 'On June 23, 2026 she was sentenced to 50 years in federal prison (plus 2 years of supervised release).',
            ],
            'Bradford Morris' => [
                'sentence' => 'Sentenced June 23, 2026 to 50 years in federal prison.',
                'descFrom' => 'She faces a minimum of 10 years and a maximum of 60 years in federal prison.',
                'descTo'   => 'On June 23, 2026 she was sentenced to 50 years in federal prison.',
            ],
            'Elizabeth Soto' => [
                'sentence' => 'Sentenced June 23, 2026 to 50 years in federal prison.',
                'descFrom' => 'She faces a minimum of 10 years and a maximum of 60 years in federal prison.',
                'descTo'   => 'On June 23, 2026 she was sentenced to 50 years in federal prison.',
            ],
            'Daniel Sanchez Estrada' => [
                'sentence' => 'Sentenced June 23, 2026 to 30 years in federal prison, plus 1 year of supervised release.',
                'descFrom' => 'He faces up to 40 years in federal prison.',
                'descTo'   => 'On June 23, 2026 he was sentenced to 30 years in federal prison (plus 1 year of supervised release).',
            ],
        ];

        $updated = 0;

        foreach ($updates as $name => $u) {
            $prisoner = Prisoner::where('name', $name)->first();
            if (! $prisoner) {
                $this->warn("Not found: {$name} — skipped.");
                continue;
            }

            // Rewrite the forward-looking bio text to the actual outcome (idempotent):
            // the per-defendant "faces ... years" clause and the shared incident line
            // that still reads "Sentencing is scheduled for June 18, 2026."
            $desc = (string) $prisoner->description;
            $desc = str_replace($u['descFrom'], $u['descTo'], $desc);
            $desc = str_replace(
                'Sentencing is scheduled for June 18, 2026.',
                'Sentencing took place on June 23, 2026.',
                $desc
            );
            if ($desc !== $prisoner->description) {
                $prisoner->description = $desc;
                $prisoner->save();
            }

            $case = PrisonerCase::where('prisoner_id', $prisoner->id)->first();
            if (! $case) {
                $this->warn("No case for {$name} — sentence not recorded.");
                continue;
            }
            $case->sentence = $u['sentence'];
            $case->save();

            $this->info("{$name}: {$u['sentence']}");
            $updated++;
        }

        $this->newLine();
        $this->warn('Ines Soto NOT updated: convicted March 13, 2026 but no sentence reported on June 23 (only 8 of 9 sentenced). Confirm and update separately.');
        $this->info("Done. Updated {$updated} defendant(s).");

        return self::SUCCESS;
    }
}
