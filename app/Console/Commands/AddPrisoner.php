<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;

class AddPrisoner extends Command {
    protected $signature = 'prisoner:add {json}';
    protected $description = 'Add a prisoner and their cases from a JSON string';

    public function handle(): int {
        $data = json_decode($this->argument('json'), true);

        if (! $data || ! is_array($data)) {
            $this->error('Invalid JSON provided.');

            return self::FAILURE;
        }

        if (empty($data['name'])) {
            $this->error('Prisoner name is required.');

            return self::FAILURE;
        }

        // Check for duplicates
        $existing = Prisoner::where('name', $data['name'])->first();
        if ($existing) {
            $this->error("Prisoner '{$data['name']}' already exists (ID: {$existing->id}).");

            return self::FAILURE;
        }

        // Extract cases before creating prisoner
        $cases = $data['cases'] ?? [];
        unset($data['cases']);

        // Build prisoner fields (only accept known fields)
        $prisonerFields = [
            'name', 'first_name', 'middle_name', 'last_name', 'aka',
            'description', 'state', 'address', 'lat', 'lng',
            'race', 'gender', 'birthdate', 'death_date', 'age',
            'ideologies', 'affiliation', 'era',
            'in_custody', 'released', 'in_exile', 'currently_in_exile',
            'imprisoned_or_exiled', 'awaiting_trial',
            'website', 'twitter', 'facebook', 'instagram', 'inmate_number',
            'years_in_prison', 'sort_order',
        ];

        $prisonerData = array_intersect_key($data, array_flip($prisonerFields));

        // Default booleans to false if not set
        foreach (['in_custody', 'released', 'in_exile', 'currently_in_exile', 'imprisoned_or_exiled', 'awaiting_trial'] as $bool) {
            if (! isset($prisonerData[$bool])) {
                $prisonerData[$bool] = false;
            }
        }

        $prisoner = Prisoner::create($prisonerData);

        $this->info("Created prisoner: {$prisoner->name} (ID: {$prisoner->id}, slug: {$prisoner->slug})");

        // Create cases
        foreach ($cases as $i => $caseData) {
            $institutionId = null;

            // Handle institution
            $instName = $caseData['institution_name'] ?? $caseData['institution'] ?? null;
            if ($instName) {
                $institution = Institution::firstOrCreate(
                    ['name' => $instName],
                    array_filter([
                        'city'  => $caseData['institution_city'] ?? null,
                        'state' => $caseData['institution_state'] ?? null,
                    ])
                );
                $institutionId = $institution->id;
                $waNew = $institution->wasRecentlyCreated ? ' (new)' : ' (existing)';
                $this->info("  Institution: {$instName}{$waNew}");
            }

            // Remove institution fields from case data
            unset($caseData['institution_name'], $caseData['institution'], $caseData['institution_city'], $caseData['institution_state']);

            // Build case fields
            $caseFields = [
                'charges', 'arrest_date', 'indicted', 'convicted', 'plead',
                'sentenced_date', 'incarceration_date', 'release_date',
                'death_in_custody_date', 'in_exile_since', 'end_of_exile',
                'prosecutor', 'judge', 'sentence',
                'imprisoned_for_days', 'in_exile_for_days',
            ];

            $cleanCaseData = array_intersect_key($caseData, array_flip($caseFields));
            $cleanCaseData['prisoner_id'] = $prisoner->id;
            $cleanCaseData['institution_id'] = $institutionId;

            PrisonerCase::create($cleanCaseData);

            $num = $i + 1;
            $charges = $cleanCaseData['charges'] ?? 'no charges listed';
            $this->info("  Case #{$num}: {$charges}");
        }

        $caseCount = count($cases);
        $this->info("\nDone! {$prisoner->name} added with {$caseCount} case(s).");
        $this->info("View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
