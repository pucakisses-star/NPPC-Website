<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Adds Bettina Aptheker (b. 1944) — Free Speech Movement Steering Committee
 * member, Communist Party member, and later a feminist-studies professor at UC
 * Santa Cruz. Arrested in the December 4, 1964 Sproul Hall mass arrest at UC
 * Berkeley, she was convicted in the 1965 FSM mass trial of trespassing and
 * resisting arrest and sentenced to 45 days, which she served at the Santa Rita
 * Rehabilitation Center in 1967 (while seven months pregnant) after her appeals
 * failed.
 *
 * Dates: the arrest (Dec 4, 1964) is well documented. The exact day she entered
 * and left Santa Rita is not documented in accessible sources (it would be in
 * her memoir "Intimate Politics" or her papers); sources place her surrender in
 * late June 1967, with a photograph dated July 2, 1967 showing her "preparing to
 * serve." incarceration_date is therefore stored as 1967-07-02 and release_date
 * as 1967-08-16 (that start plus the 45-day term) — approximate, as noted in the
 * sentence text.
 *
 * Idempotent.
 */
final class AddBettinaAptheker extends Command
{
    protected $signature = 'prisoners:add-bettina-aptheker';

    protected $description = 'Add Bettina Aptheker (Free Speech Movement; 45 days at Santa Rita, 1967)';

    public function handle(): int
    {
        $santaRita = Institution::firstOrCreate(
            ['name' => 'Santa Rita Rehabilitation Center'],
            ['city' => 'Pleasanton', 'state' => 'California'],
        );

        $fields = [
            'name' => 'Bettina Aptheker',
            'first_name' => 'Bettina',
            'last_name' => 'Aptheker',
            'gender' => 'Female',
            'race' => 'White',
            'state' => 'California',
            'era' => '1960s',
            'birthdate' => '1944-09-13',
            'ideologies' => ['Communism', 'Feminism'],
            'affiliation' => ['Free Speech Movement', 'Communist Party USA'],
            'in_custody' => false,
            'released' => true,
            'awaiting_trial' => false,
            'description' => 'Bettina Aptheker (b. 1944) is an American political activist, feminist scholar, and longtime professor of feminist studies at UC Santa Cruz. The daughter of Marxist historian Herbert Aptheker and a member of the Communist Party, she was a leader of the 1964 Berkeley Free Speech Movement and sat on its Steering Committee. She was arrested in the December 1964 Sproul Hall sit-in — the largest mass arrest in California history to that point — convicted of trespassing and resisting arrest, and sentenced to 45 days, which she served at the Santa Rita Rehabilitation Center in 1967 while seven months pregnant. She went on to help lead the campaign to free Angela Davis.',
        ];

        $case = [
            'charges' => 'Trespassing and resisting arrest — December 1964 Sproul Hall sit-in (Free Speech Movement), UC Berkeley',
            'arrest_date' => '1964-12-04',
            'incarceration_date' => '1967-07-02',
            'release_date' => '1967-08-16',
            'convicted' => 'Yes — convicted 1965 in the Free Speech Movement mass trial',
            'sentence' => '45 days at the Santa Rita Rehabilitation Center; surrendered to serve in late June/early July 1967 after appeals failed, while seven months pregnant. Exact entry and release days are not documented — the dates shown reflect the ~July 2, 1967 start (a photograph that day shows her preparing to serve) plus the 45-day term.',
            'judge' => 'Rupert Crittenden',
            'institution_id' => $santaRita->id,
        ];

        $existing = Prisoner::withUnderReview()->where('name', 'Bettina Aptheker')->first();

        if (! $existing) {
            DB::transaction(function () use ($fields, $case) {
                $prisoner = Prisoner::create($fields);
                $case['prisoner_id'] = $prisoner->id;
                PrisonerCase::create($case);
            });
            $this->info('Added Bettina Aptheker.');
        } else {
            DB::transaction(function () use ($existing, $fields, $case) {
                $existing->fill($fields)->save();
                $row = $existing->cases()->first();
                if ($row) {
                    $row->fill($case)->save();
                } else {
                    $case['prisoner_id'] = $existing->id;
                    PrisonerCase::create($case);
                }
            });
            $this->info('Updated Bettina Aptheker.');
        }

        $prisoner = Prisoner::withUnderReview()->where('name', 'Bettina Aptheker')->first();
        $this->attachLocalPhoto($prisoner, 'photos/bettina-aptheker.jpg');

        return self::SUCCESS;
    }

    /**
     * Copy the committed public-domain photo (her 1975 portrait from Wikimedia
     * Commons) onto the public disk and set it as her photo. Re-synced each run.
     */
    private function attachLocalPhoto(Prisoner $prisoner, string $relative): void
    {
        $src = database_path('data/'.$relative);
        if (! is_file($src)) {
            $this->warn("  Local photo not found: {$relative}");

            return;
        }

        $ext = strtolower(pathinfo($src, PATHINFO_EXTENSION) ?: 'jpg');
        $path = 'prisoners/'.Str::slug($prisoner->name).'.'.$ext;
        Storage::disk('public')->put($path, (string) file_get_contents($src));
        $prisoner->photo = $path;
        $prisoner->save();
        $this->info("  Photo set: {$path}");
    }
}
