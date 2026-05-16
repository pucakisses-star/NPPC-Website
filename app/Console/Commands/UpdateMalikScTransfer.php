<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Update Malik Muhammad's record after his 2026 transfer from
 * Oregon to South Carolina. Per @ADayIn1920 (2026-04-07):
 *
 *   Malik F Muhammed #400523
 *   Kirkland Reception and Evaluation Center  A1-50
 *   4344 Broad River Road, Columbia, SC 29210
 *
 * Idempotent — re-runs report "no changes" once applied.
 */
final class UpdateMalikScTransfer extends Command {
    protected $signature = 'prisoners:update-malik-sc-transfer';
    protected $description = 'Update Malik Muhammad with his SC transfer info (#400523, Kirkland R&E Center)';

    public function handle(): int {
        $malik = Prisoner::where('slug', 'malik-muhammad')->first();
        if (! $malik) {
            $this->error('Malik Muhammad not found in DB.');
            return self::FAILURE;
        }

        $kirkland = Institution::firstOrCreate(
            ['name' => 'Kirkland Reception and Evaluation Center'],
            [
                'city'             => 'Columbia',
                'state'            => 'South Carolina',
                'mailing_address'  => "Kirkland Reception and Evaluation Center\nA1-50\n4344 Broad River Road\nColumbia, SC 29210",
                'physical_address' => '4344 Broad River Road, Columbia, SC 29210',
            ]
        );

        DB::transaction(function () use ($malik, $kirkland) {
            $dirty = [];

            if ($malik->inmate_number !== '400523') {
                $malik->inmate_number = '400523';
                $dirty[] = 'inmate_number';
            }
            if ($malik->state !== 'South Carolina') {
                $malik->state = 'South Carolina';
                $dirty[] = 'state';
            }
            if ($dirty) {
                $malik->save();
                $this->info('Updated Malik Muhammad: '.implode(', ', $dirty));
            } else {
                $this->line('Malik record already current.');
            }

            // Update the most-recent active case to point at Kirkland,
            // OR append a new case if no open case exists.
            $openCase = PrisonerCase::where('prisoner_id', $malik->id)
                ->whereNull('release_date')
                ->orderByDesc('arrest_date')
                ->first();

            if ($openCase) {
                if ($openCase->institution_id !== $kirkland->id) {
                    $openCase->institution_id = $kirkland->id;
                    $openCase->save();
                    $this->info('Pointed open case at Kirkland R&E Center.');
                } else {
                    $this->line('Open case already at Kirkland.');
                }
            } else {
                PrisonerCase::create([
                    'prisoner_id'    => $malik->id,
                    'institution_id' => $kirkland->id,
                    'charges'        => 'Continuation of 2020 Portland Uprising arson sentence — transferred to South Carolina custody April 2026.',
                ]);
                $this->info('Created new case at Kirkland R&E Center.');
            }
        });

        $this->line('');
        $this->info('Done. Mailing address now on file:');
        $this->line('  Malik F Muhammed #400523');
        $this->line('  Kirkland Reception and Evaluation Center  A1-50');
        $this->line('  4344 Broad River Road');
        $this->line('  Columbia, SC 29210');

        return self::SUCCESS;
    }
}
