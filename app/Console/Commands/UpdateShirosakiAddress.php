<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Updates Tsutomu Shirosaki's record with his current mailing address
 * — transferred from FCI Terre Haute CMU to FCI Yazoo City Low in
 * Mississippi. Federal register number: 20924-016.
 */
final class UpdateShirosakiAddress extends Command {
    protected $signature = 'archive:update-shirosaki-address';
    protected $description = 'Update Tsutomu Shirosaki to FCI Yazoo City Low (MS), inmate #20924-016';

    public function handle(): int {
        $prisoner = Prisoner::withUnderReview()->with('cases')->where('slug', 'tsutomu-shirosaki')->first();
        if (! $prisoner) {
            $this->error('Prisoner not found: tsutomu-shirosaki');

            return self::FAILURE;
        }

        if (empty($prisoner->inmate_number)) {
            $prisoner->inmate_number = '20924-016';
            $this->info('Set inmate_number = 20924-016');
        }
        if ($prisoner->state !== 'Mississippi') {
            $prisoner->state = 'Mississippi';
            $this->info('Set state = Mississippi');
        }
        if (empty($prisoner->address) || ! str_contains($prisoner->address, 'Yazoo City')) {
            $prisoner->address = "T. Shirosaki, #20924-016\nFCI Low\nPO Box 5000\nYazoo City, MS 39194";
            $this->info('Updated mailing address to FCI Yazoo City Low');
        }
        if ($prisoner->isDirty()) {
            $prisoner->save();
        }

        $institution = Institution::firstOrCreate(
            ['name' => 'FCI Yazoo City Low'],
            [
                'city' => 'Yazoo City',
                'state' => 'Mississippi',
            ]
        );

        $case = $prisoner->cases->sortByDesc('arrest_date')->first() ?? $prisoner->cases->first();
        if ($case) {
            if ($case->institution_id !== $institution->id) {
                $case->institution_id = $institution->id;
                $case->institution_name = 'FCI Yazoo City Low';
                $case->institution_city = 'Yazoo City';
                $case->institution_state = 'Mississippi';
                $case->save();
                $this->info('Updated case institution → FCI Yazoo City Low');
            } else {
                $this->info('Case already linked to FCI Yazoo City Low.');
            }
        } else {
            $this->warn('No PrisonerCase row to update.');
        }

        $this->info("\nDone.");

        return self::SUCCESS;
    }
}
