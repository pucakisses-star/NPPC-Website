<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Update Alex Stokes / Alexander Contompasis's record with the
 * February 2026 transfer to Sing Sing Correctional Facility and
 * the full NY DOCCS mailing-address details surfaced by the
 * @ADayIn1920 letter-writing call (2026-02-20):
 *
 *   Alex Contompasis #22-B-5028
 *   Sing Sing Correctional Facility
 *   354 Hunter Street
 *   Ossining, NY 10562
 *
 * Idempotent — re-runs report "already current."
 */
final class UpdateAlexStokesSingSing extends Command {
    protected $signature = 'prisoners:update-alex-stokes-sing-sing';
    protected $description = 'Update Alex Stokes/Contompasis record with Sing Sing transfer + mailing details (2026)';

    public function handle(): int {
        $alex = Prisoner::where(function ($q) {
            $q->where('name', 'like', '%Contompasis%')
              ->orWhere('name', 'like', '%Stokes%')
              ->orWhere('aka', 'like', '%Stokes%')
              ->orWhere('aka', 'like', '%Contompasis%')
              ->orWhere('slug', 'like', '%contompasis%')
              ->orWhere('slug', 'like', '%alex-stokes%');
        })->first();

        if (! $alex) {
            $this->error('Alex Stokes / Alexander Contompasis not found in DB.');
            return self::FAILURE;
        }

        DB::transaction(function () use ($alex) {
            // Ensure Sing Sing exists as an Institution with full mailing details.
            $singSing = Institution::firstOrCreate(
                ['name' => 'Sing Sing Correctional Facility'],
                [
                    'city'             => 'Ossining',
                    'state'            => 'New York',
                    'mailing_address'  => "Sing Sing Correctional Facility\n354 Hunter Street\nOssining, NY 10562",
                    'physical_address' => '354 Hunter Street, Ossining, NY 10562',
                ]
            );

            // Back-fill mailing/physical addresses if the institution already
            // existed but didn't have them populated.
            $instDirty = [];
            if (empty($singSing->mailing_address)) {
                $singSing->mailing_address = "Sing Sing Correctional Facility\n354 Hunter Street\nOssining, NY 10562";
                $instDirty[] = 'mailing_address';
            }
            if (empty($singSing->physical_address)) {
                $singSing->physical_address = '354 Hunter Street, Ossining, NY 10562';
                $instDirty[] = 'physical_address';
            }
            if ($instDirty) {
                $singSing->save();
                $this->info('Updated Sing Sing institution: '.implode(', ', $instDirty));
            }

            // Update prisoner record.
            $alexDirty = [];
            if ($alex->inmate_number !== '22-B-5028') {
                $alex->inmate_number = '22-B-5028';
                $alexDirty[] = 'inmate_number';
            }
            if (! $alex->in_custody) {
                $alex->in_custody = true;
                $alexDirty[] = 'in_custody';
            }
            if ($alex->released) {
                $alex->released = false;
                $alexDirty[] = 'released';
            }
            if ($alexDirty) {
                $alex->save();
                $this->info('Updated Alex Stokes: '.implode(', ', $alexDirty));
            } else {
                $this->line('Alex Stokes prisoner record already current.');
            }

            // Point his open case at Sing Sing.
            $openCase = PrisonerCase::where('prisoner_id', $alex->id)
                ->whereNull('release_date')
                ->orderByDesc('arrest_date')
                ->first();
            if ($openCase) {
                if ($openCase->institution_id !== $singSing->id) {
                    $openCase->institution_id = $singSing->id;
                    $openCase->save();
                    $this->info('Pointed open case at Sing Sing.');
                } else {
                    $this->line('Open case already at Sing Sing.');
                }
            } else {
                PrisonerCase::create([
                    'prisoner_id'    => $alex->id,
                    'institution_id' => $singSing->id,
                    'charges'        => 'Charges stemming from January 6, 2021 Albany counter-protest of "Stop the Steal" rally; convicted on multiple felony counts (2022).',
                    'sentence'       => '20 years; parole eligible 2039',
                ]);
                $this->info('Created new case at Sing Sing.');
            }
        });

        $this->line('');
        $this->info('Done. Current mailing address on file:');
        $this->line('  Alex Contompasis #22-B-5028');
        $this->line('  Sing Sing Correctional Facility');
        $this->line('  354 Hunter Street');
        $this->line('  Ossining, NY 10562');

        return self::SUCCESS;
    }
}
