<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Merges the duplicate Cameron Bishop records, keeping the slug
 * "cameron-bishop" (per request) and folding "cameron-david-bishop" into it.
 * The keeper wins on every field it already has; only its empty fields are
 * filled from the duplicate, cases are moved over (skipping duplicate
 * charges), and the keeper's era is set to "1960s" (his defining act, the
 * 1969 Colorado power-line sabotage). Idempotent: a no-op if the duplicate
 * is already gone.
 */
final class MergeCameronBishop extends Command
{
    protected $signature = 'prisoners:merge-cameron-bishop';

    protected $description = 'Merge the duplicate Cameron Bishop records into cameron-bishop';

    private const KEEPER = 'cameron-bishop';

    private const DUP = 'cameron-david-bishop';

    private const FILL_FIELDS = [
        'description', 'body', 'birthdate', 'death_date', 'race', 'gender', 'state',
        'address', 'lat', 'lng', 'photo', 'inmate_number',
        'first_name', 'middle_name', 'last_name', 'aka',
        'website', 'twitter', 'facebook', 'instagram',
    ];

    public function handle(): int
    {
        $keeper = Prisoner::withUnderReview()->where('slug', self::KEEPER)->first();
        $dup = Prisoner::withUnderReview()->where('slug', self::DUP)->first();

        if (! $keeper) {
            $this->error('Keeper "'.self::KEEPER.'" not found — aborting.');

            return self::FAILURE;
        }

        if (! $dup) {
            $this->info('Duplicate "'.self::DUP.'" already gone — re-asserting era only.');
            if ($keeper->era !== '1960s') {
                $keeper->era = '1960s';
                $keeper->save();
            }

            return self::SUCCESS;
        }

        DB::transaction(function () use ($keeper, $dup) {
            foreach (self::FILL_FIELDS as $f) {
                if (empty($keeper->{$f}) && ! empty($dup->{$f})) {
                    $keeper->{$f} = $dup->{$f};
                }
            }
            if (empty($keeper->ideologies) && ! empty($dup->ideologies)) {
                $keeper->ideologies = $dup->ideologies;
            }
            if (empty($keeper->affiliation) && ! empty($dup->affiliation)) {
                $keeper->affiliation = $dup->affiliation;
            }
            // The 1969 sabotage is his defining act — keep him in the 1960s.
            $keeper->era = '1960s';
            $keeper->save();

            $keeper->load('cases');
            $existing = $keeper->cases->map(fn ($c) => trim((string) $c->charges))->all();
            foreach ($dup->cases as $case) {
                if (in_array(trim((string) $case->charges), $existing, true)) {
                    $case->delete();

                    continue;
                }
                $case->prisoner_id = $keeper->id;
                $case->save();
                $existing[] = trim((string) $case->charges);
            }

            $dup->podcastEpisodes()->update(['prisoner_id' => $keeper->id]);
            $dup->calendarEntries()->update(['prisoner_id' => $keeper->id]);

            $dup->delete();
        });

        $this->info('Merged "'.self::DUP.'" into "'.self::KEEPER.'" (slug kept: '.self::KEEPER.').');

        return self::SUCCESS;
    }
}
