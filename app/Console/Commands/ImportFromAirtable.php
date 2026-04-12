<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ImportFromAirtable extends Command {
    protected $signature = 'airtable:import
                            {--url=https://marisam-airtable.patrickdeamorim.workers.dev/ : The Airtable proxy URL}
                            {--dry-run : Preview without writing to database}';

    protected $description = 'Import prisoner data from the Airtable Cloudflare proxy into local database';

    public function handle(): int {
        $url = $this->option('url');
        $dryRun = $this->option('dry-run');

        $this->info("Fetching data from: {$url}");

        $response = Http::timeout(60)->get($url);

        if (! $response->successful()) {
            $this->error("Failed to fetch data: HTTP {$response->status()}");

            return self::FAILURE;
        }

        $records = $response->json();

        if (! is_array($records) || empty($records)) {
            $this->error('No records returned from Airtable proxy.');

            return self::FAILURE;
        }

        $this->info(count($records).' prisoners found.');

        if ($dryRun) {
            $this->warn('Dry run mode — no data will be written.');
            $this->table(
                ['Name', 'AKA', 'State', 'In Custody', 'Cases'],
                collect($records)->map(fn ($r) => [
                    $this->str($r['name'] ?? null) ?: '-',
                    $this->str($r['AKA'] ?? null) ?: '-',
                    $this->str($r['State'] ?? null) ?: '-',
                    ($r['In Custody'] ?? false) ? 'Yes' : 'No',
                    count($r['cases'] ?? []),
                ])->toArray()
            );

            return self::SUCCESS;
        }

        $this->info('Importing...');
        $bar = $this->output->createProgressBar(count($records));

        $institutionCache = [];
        $prisonersCreated = 0;
        $casesCreated = 0;
        $institutionsCreated = 0;

        DB::beginTransaction();

        try {
        foreach ($records as $record) {
            $prisoner = Prisoner::create([
                'name'                 => $this->str($record['name'] ?? '') ?: 'Unknown',
                'sort_order'           => (int) ($record['SortOrder'] ?? 0),
                'photo'                => null,
                'description'          => $this->str($record['Description'] ?? null),
                'years_in_prison'      => ! empty($record['Years Spent In Prison']) ? (int) (is_array($record['Years Spent In Prison']) ? $record['Years Spent In Prison'][0] : $record['Years Spent In Prison']) : null,
                'state'                => $this->str($record['State'] ?? null),
                'address'              => $this->str($record['Address'] ?? null),
                'lat'                  => is_numeric($record['latitude'] ?? null) ? $record['latitude'] : null,
                'lng'                  => is_numeric($record['longitude'] ?? null) ? $record['longitude'] : null,
                'first_name'           => null,
                'middle_name'          => null,
                'last_name'            => null,
                'aka'                  => $this->str($record['AKA'] ?? null),
                'race'                 => $this->str($record['Race'] ?? null),
                'gender'               => $this->str($record['Gender'] ?? null),
                'birthdate'            => $this->parseDate($record['Birthdate'] ?? null),
                'death_date'           => $this->parseDate($record['Death date'] ?? null),
                'age'                  => is_numeric($record['Age'] ?? null) ? (int) $record['Age'] : null,
                'ideologies'           => $this->toArray($record['Ideologies'] ?? null),
                'era'                  => $this->str($record['Era'] ?? null),
                'affiliation'          => $this->toArray($record['Affiliation'] ?? null),
                'in_custody'           => (bool) ($record['In Custody'] ?? false),
                'released'             => (bool) ($record['Released'] ?? false),
                'in_exile'             => (bool) ($record['In Exile'] ?? false),
                'currently_in_exile'   => (bool) ($record['Currently in Exile'] ?? false),
                'imprisoned_or_exiled' => ($record['Imprisoned or Exiled'] ?? null) === 'T',
                'website'              => $this->str($record['Website'] ?? null),
                'twitter'              => $this->str($record['Twitter'] ?? null),
                'facebook'             => $this->str($record['Facebook'] ?? null),
                'instagram'            => null,
                'inmate_number'        => $this->str($record['inmateNumber'] ?? null),
                'awaiting_trial'       => (bool) ($record['Awaiting Trial'] ?? false),
            ]);

            $prisonersCreated++;

            foreach ($record['cases'] ?? [] as $caseData) {
                $institutionId = null;

                $instName = $this->str($caseData['Institution name'] ?? null);

                if ($instName) {
                    if (isset($institutionCache[$instName])) {
                        $institutionId = $institutionCache[$instName];
                    } else {
                        $institution = Institution::create([
                            'name'             => $instName,
                            'city'             => $this->str($caseData['Institution city'] ?? null),
                            'state'            => $this->str($caseData['Institution state'] ?? null),
                            'security'         => $this->str($caseData['Institution security'] ?? null),
                            'mailing_address'  => $this->str($caseData['Mailing address'] ?? null),
                            'physical_address' => $this->str($caseData['Physical address'] ?? null),
                        ]);
                        $institutionId = $institution->id;
                        $institutionCache[$instName] = $institutionId;
                        $institutionsCreated++;
                    }
                }

                PrisonerCase::create([
                    'prisoner_id'           => $prisoner->id,
                    'institution_id'        => $institutionId,
                    'charges'               => is_array($caseData['Charges'] ?? null) ? implode("\n", $caseData['Charges']) : $this->str($caseData['Charges'] ?? null),
                    'arrest_date'           => $this->parseDate($caseData['Arrest Date'] ?? null),
                    'indicted'              => $this->str($caseData['Indicted'] ?? null),
                    'convicted'             => $this->str($caseData['Convicted'] ?? null),
                    'plead'                 => $this->str($caseData['Plead'] ?? null),
                    'sentenced_date'        => $this->parseDate($caseData['Sentenced Date'] ?? null),
                    'incarceration_date'    => $this->parseDate($caseData['Incarceration Date'] ?? null),
                    'release_date'          => $this->parseDate($caseData['Release Date'] ?? null),
                    'death_in_custody_date' => null,
                    'in_exile_since'        => null,
                    'end_of_exile'          => null,
                    'prosecutor'            => $this->str($caseData['Prosecutor'] ?? null),
                    'judge'                 => $this->str($caseData['Judge'] ?? null),
                    'sentence'              => $this->str($caseData['Sentence'] ?? null),
                    'imprisoned_for_days'   => is_numeric($caseData['imprisonedFor'] ?? null) ? (int) $caseData['imprisonedFor'] : null,
                    'in_exile_for_days'     => is_numeric($caseData['inExileFor'] ?? null) ? (int) $caseData['inExileFor'] : null,
                ]);

                $casesCreated++;
            }

            $bar->advance();
        }

        DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Import failed: {$e->getMessage()}");

            return self::FAILURE;
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Import complete!");
        $this->table(
            ['Type', 'Created'],
            [
                ['Prisoners', $prisonersCreated],
                ['Cases', $casesCreated],
                ['Institutions', $institutionsCreated],
            ]
        );

        return self::SUCCESS;
    }

    /**
     * Safely coerce any value to a string or null.
     * If the value is an array, takes the first element.
     */
    private function str(mixed $value): ?string {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            $first = $value[0] ?? null;

            return $first !== null ? (string) $first : null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }

    /**
     * Ensure a value is a PHP array for Eloquent's array cast.
     * If it's a string, wraps it in an array. Nulls stay null.
     */
    private function toArray(mixed $value): ?array {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return array_values(array_filter($value, fn ($v) => $v !== null && $v !== ''));
        }

        if (is_string($value) && $value !== '') {
            return [$value];
        }

        return null;
    }

    private function parseDate(mixed $value): ?string {
        if (! $value || is_array($value)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }
}
