<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Adds Union-held political prisoners of the Civil War not already in the
 * database: the Baltimore police commissioners and Maryland legislators/civilians
 * seized in the 1861 arrests, other civilian detainees held at Forts McHenry,
 * Lafayette, Warren, Delaware and Fortress Monroe, and the Copperhead
 * military-commission defendants (Bowles, Horsey, Humphreys, Dodd, Harris).
 * Roster in database/data/union-political-prisoners.json. Excludes people
 * already present (Merryman, Vallandigham, Milligan, Brown, Kane, May, Francis
 * Key Howard, Winans, Mason, Slidell, Stephens). Create-or-update matched by
 * name + era 1800s, so it won't clobber an unrelated modern person of the same
 * name. Idempotent.
 */
final class AddUnionPoliticalPrisoners extends Command
{
    protected $signature = 'prisoners:add-union-political-prisoners';

    protected $description = 'Add Union-held Civil War political prisoners (Maryland arrests, forts, Copperhead cases)';

    public function handle(): int
    {
        $path = database_path('data/union-political-prisoners.json');
        if (! is_file($path)) {
            $this->error('Missing data file: '.$path);

            return self::FAILURE;
        }
        $people = json_decode((string) file_get_contents($path), true);
        if (! is_array($people)) {
            $this->error('Could not parse '.$path);

            return self::FAILURE;
        }

        $added = 0;
        $updated = 0;

        foreach ($people as $p) {
            DB::transaction(function () use ($p, &$added, &$updated) {
                $existing = Prisoner::withUnderReview()
                    ->where('name', $p['name'])
                    ->get()
                    ->first(fn ($x) => $x->era === '1800s'
                        || str_contains((string) $x->description, 'Fort ')
                        || str_contains((string) $x->description, 'Civil War'));
                $prisoner = $existing ?? new Prisoner(['name' => $p['name']]);

                $prisoner->fill([
                    'name' => $p['name'],
                    'first_name' => $p['first'] ?: null,
                    'middle_name' => $p['middle'] ?: null,
                    'last_name' => $p['last'] ?: null,
                    'aka' => $p['aka'] ?: null,
                    'gender' => 'Male',
                    'race' => 'White',
                    'state' => $p['state'] ?: null,
                    'era' => '1800s',
                    'ideologies' => [$p['ideology']],
                    'description' => $p['bio'],
                    'in_custody' => false,
                    'released' => true,
                    'in_exile' => false,
                    'awaiting_trial' => false,
                ]);
                $this->setDate($prisoner, 'birthdate', $p['birth'] ?? null);
                $this->setDate($prisoner, 'death_date', $p['death'] ?? null);
                $prisoner->save();

                $case = $prisoner->cases()->first() ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->prisoner_id = $prisoner->id;
                if (! empty($p['prison'])) {
                    $case->institution_id = Institution::firstOrCreate(
                        ['name' => $p['prison']],
                        ['city' => $p['prison_city'], 'state' => $p['prison_state']]
                    )->id;
                }
                $case->charges = $p['charges'];
                $case->convicted = $p['convicted'];
                $case->sentence = $p['sentence'];
                $this->setDate($case, 'arrest_date', $p['arrest'] ?? null);
                $this->setDate($case, 'incarceration_date', $p['arrest'] ?? null);
                $this->setDate($case, 'release_date', $p['release'] ?? null);
                $case->save();

                if ($existing) {
                    $updated++;
                    $this->line('  updated: '.$p['name']);
                } else {
                    $added++;
                    $this->info('  added: '.$p['name']);
                }
            });
        }

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info("\nUnion-held political prisoners — added: {$added}, updated: {$updated}.");

        return self::SUCCESS;
    }

    /** Set a partial date from a "YYYY", "YYYY-MM", or "YYYY-MM-DD" string. */
    private function setDate($model, string $field, ?string $value): void
    {
        if (! $value) {
            return;
        }
        $parts = array_map('intval', explode('-', $value));
        $model->setPartialDate($field, $parts[0], $parts[1] ?? null, $parts[2] ?? null);
    }
}
