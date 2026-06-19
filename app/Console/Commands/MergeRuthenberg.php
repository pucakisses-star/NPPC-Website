<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Merges the two duplicate C.E. Ruthenberg records into one. The keeper is
 * "charles-emil-ruthenberg" (the richer record: DOB, Ohio, fuller bio,
 * recorded prison time). The duplicate "charles-e-ruthenberg" is a thinner
 * stub (mislabeled state Illinois) but carries the New York criminal-anarchy
 * / Sing Sing case, so its case(s) are moved onto the keeper before it is
 * deleted. The keeper wins on every field it already has; only empty fields
 * are filled from the stub. Idempotent: if the stub is already gone it is a
 * no-op.
 */
final class MergeRuthenberg extends Command
{
    protected $signature = 'prisoners:merge-ruthenberg';

    protected $description = 'Merge the duplicate C.E. Ruthenberg records into charles-emil-ruthenberg';

    private const KEEPER = 'charles-emil-ruthenberg';

    private const STUB = 'charles-e-ruthenberg';

    public function handle(): int
    {
        $keeper = Prisoner::withUnderReview()->where('slug', self::KEEPER)->first();
        $stub = Prisoner::withUnderReview()->where('slug', self::STUB)->first();

        if (! $keeper) {
            $this->error('Keeper "'.self::KEEPER.'" not found — aborting.');

            return self::FAILURE;
        }

        if (! $stub) {
            $this->info('Duplicate "'.self::STUB.'" already gone — nothing to merge.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($keeper, $stub) {
            // Fill only the keeper's EMPTY fields from the stub (keeper wins
            // where it already has data — so its correct Ohio state, DOB, and
            // bio are preserved over the stub's thinner/mislabeled values).
            foreach (['description', 'body', 'birthdate', 'death_date', 'race', 'gender', 'state', 'era', 'photo', 'inmate_number', 'address'] as $f) {
                if (empty($keeper->{$f}) && ! empty($stub->{$f})) {
                    $keeper->{$f} = $stub->{$f};
                }
            }
            if (empty($keeper->ideologies) && ! empty($stub->ideologies)) {
                $keeper->ideologies = $stub->ideologies;
            }
            if (empty($keeper->affiliation) && ! empty($stub->affiliation)) {
                $keeper->affiliation = $stub->affiliation;
            }
            $keeper->save();

            // Move the stub's cases onto the keeper, skipping any whose charges
            // duplicate a case the keeper already has.
            $existingCharges = $keeper->cases->map(fn ($c) => trim((string) $c->charges))->all();
            foreach ($stub->cases as $case) {
                if (in_array(trim((string) $case->charges), $existingCharges, true)) {
                    $case->delete();

                    continue;
                }
                $case->prisoner_id = $keeper->id;
                $case->save();
            }

            $stub->delete();
        });

        $this->info('Merged "'.self::STUB.'" into "'.self::KEEPER.'" and deleted the duplicate.');

        return self::SUCCESS;
    }
}
