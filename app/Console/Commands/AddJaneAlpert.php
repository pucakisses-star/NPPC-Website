<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Jane Alpert — the New York radical (and later radical-feminist writer)
 * who took part in the 1969 NYC bombing campaign with Sam Melville, jumped
 * bail in 1970, lived underground for ~4.5 years, surrendered in 1974, and
 * served a federal sentence. Idempotent (skips if she already exists).
 *
 * Note: she was bailed after the Nov 1969 arrest and only imprisoned after
 * her Nov 1974 surrender, so the incarceration/release dates reflect the
 * mid-1970s term she actually served (approximate), not 1969.
 */
final class AddJaneAlpert extends Command
{
    protected $signature = 'prisoners:add-jane-alpert';

    protected $description = 'Add Jane Alpert (1969 NYC bombings; UFF-era radical) as a prisoner';

    public function handle(): int
    {
        if (Prisoner::withUnderReview()->where('name', 'Jane Alpert')->exists()) {
            $this->warn('Jane Alpert already exists — skipping.');

            return self::SUCCESS;
        }

        DB::transaction(function () {
            $prisoner = Prisoner::create([
                'name' => 'Jane Alpert',
                'first_name' => 'Jane',
                'last_name' => 'Alpert',
                'gender' => 'Female',
                'race' => 'White',
                'birthdate' => '1947-05-20',
                'state' => 'New York',
                'era' => '1960s',
                'ideologies' => ['Anti-war', 'New Left', 'Radical feminism'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Jane Alpert (born 1947) was a New York radical — and later a radical-feminist writer — who took part in a 1969 campaign that bombed eight government and corporate buildings in New York City, helping plan the targets and logistics with her partner Sam Melville. Arrested in November 1969 after other members of the group were caught planting dynamite, she pleaded guilty to conspiracy but jumped bail about a month before her 1970 sentencing and spent roughly four and a half years underground, where she wrote the influential feminist essay "Mother Right." She surrendered in November 1974 and was sentenced to 27 months in prison for the conspiracy; in October 1977 she received an additional four months for contempt of court after refusing to testify at the 1975 trial of a co-defendant. She later recounted her life in the memoir "Growing Up Underground."',
            ]);

            $bop = Institution::firstOrCreate(['name' => 'Federal Bureau of Prisons']);

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'institution_id' => $bop->id,
                'charges' => 'Conspiracy to bomb government and corporate buildings in New York City (1969)',
                'arrest_date' => '1969-11-12',
                'incarceration_date' => '1975-01-01',
                'release_date' => '1977-08-01',
                'convicted' => 'Pleaded guilty to conspiracy (1970); jumped bail before sentencing; surrendered November 1974',
                'sentence' => '27 months for the conspiracy, plus 4 months for contempt of court (1977) for refusing to testify against a co-defendant; imprisoned in the mid-1970s after her 1974 surrender',
            ]);
        });

        $this->info('Added Jane Alpert.');

        return self::SUCCESS;
    }
}
