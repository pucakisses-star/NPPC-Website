<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Updates Alex Saab to reflect that he is back in U.S. custody. After his
 * December 2023 prisoner-swap release he returned to Venezuela; following
 * Maduro's January 2026 capture he was removed from his post, arrested in
 * Venezuela (Feb 2026), and deported to the United States (May 2026), where he
 * was ordered held in federal custody in Miami. Flips his status flags, appends
 * a current-status paragraph to his bio (without clobbering existing text), and
 * adds a new case for the renewed Miami prosecution. Idempotent; matches the
 * live record by name/slug.
 */
final class UpdateAlexSaabCustody extends Command
{
    protected $signature = 'prisoners:update-alex-saab-custody';

    protected $description = 'Update Alex Saab: back in US custody (Miami, May 2026) + add the new case';

    private const STATUS_PARAGRAPH = 'After Nicolás Maduro was taken into U.S. custody in January 2026, Saab — who had returned to Venezuela following his December 2023 prisoner-swap release — was removed from his ministerial post, arrested in Venezuela in February 2026, and deported to the United States in May 2026, where he was ordered held in federal custody in Miami to face money-laundering charges.';

    private const NEW_CASE_INSTITUTION = 'Federal Detention Center, Miami';

    public function handle(): int
    {
        $prisoner = Prisoner::withoutGlobalScopes()
            ->where('slug', 'alex-saab')
            ->orWhere('slug', 'alex-nain-saab-moran')
            ->orWhere('name', 'like', '%Saab%')
            ->first();

        if (! $prisoner) {
            $this->warn('No Alex Saab record found — nothing to update.');

            return self::SUCCESS;
        }

        // Status: back in U.S. custody, awaiting trial, no longer released.
        $prisoner->in_custody = true;
        $prisoner->released = false;
        $prisoner->awaiting_trial = true;

        // Append the current-status paragraph once (preserve existing bio).
        $desc = (string) $prisoner->description;
        if (mb_stripos($desc, 'deported to the United States in May 2026') === false) {
            $prisoner->description = trim($desc) === ''
                ? self::STATUS_PARAGRAPH
                : trim($desc)."\n\n".self::STATUS_PARAGRAPH;
            $this->info('Appended current-status paragraph to bio.');
        } else {
            $this->line('Bio already mentions the May 2026 deportation — left unchanged.');
        }

        $prisoner->save();
        $this->info("Updated status on {$prisoner->name}: in custody, awaiting trial.");

        // Add the new Miami case, unless it's already present.
        $alreadyHasNewCase = $prisoner->cases()
            ->whereHas('institution', fn ($q) => $q->where('name', self::NEW_CASE_INSTITUTION))
            ->exists();

        if ($alreadyHasNewCase) {
            $this->line('New Miami case already present — left unchanged.');
        } else {
            $institution = Institution::firstOrCreate(
                ['name' => self::NEW_CASE_INSTITUTION],
                ['city' => 'Miami', 'state' => 'Florida'],
            );

            $case = $prisoner->cases()->make([
                'institution_id' => $institution->id,
                'charges' => 'Federal money laundering and conspiracy (S.D. Fla.) — alleged bribery of Venezuelan officials tied to government food-distribution (CLAP) contracts. Re-detained after his December 2023 prisoner-swap release.',
                'convicted' => 'No — awaiting trial',
            ]);
            $case->setPartialDate('arrest_date', 2026, 2, 4);        // arrested in Venezuela
            $case->setPartialDate('incarceration_date', 2026, 5, 16); // deported to U.S. / Miami custody
            $case->save();
            $this->info("Added new case at {$institution->name} (arrested Feb 2026; deported/held May 2026).");
        }

        $this->info("View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
