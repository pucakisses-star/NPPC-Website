<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Adds World War I–era political prisoners drawn from the National Civil
 * Liberties Bureau's March 1919 compilation "War-Time Prosecutions and Mob
 * Violence" (the NCLB was the direct predecessor of the ACLU). This batch
 * covers people who were actually jailed — i.e. convicted under the Espionage
 * Act, the Sedition Act, draft-obstruction charges, or wartime state/local
 * laws and given a custodial (jail or prison) sentence — and who were not
 * already present in the database.
 *
 * Source data lives in database/data/wartime_1919_prisoners.json, one object
 * per person with name parts, gender, city/state, the conviction date, a
 * verbatim sentence/charge note, and inferred ideology/affiliation tags.
 *
 * Idempotent: skips anyone whose name already exists (the JSON was pre-filtered
 * against the live roster, but the runtime check is the real safeguard).
 * Query-builder-free creates so the model's slug/age hooks run. Busts the
 * prisoner API cache. Re-run prisoners:normalize-sort-order afterward.
 */
final class AddWartime1919Prisoners extends Command
{
    protected $signature = 'prisoners:add-wartime-1919 {--dry : Preview without writing}';

    protected $description = 'Add jailed WWI free-speech prisoners from the NCLB 1919 "War-Time Prosecutions" compilation';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry');
        $path = database_path('data/wartime_1919_prisoners.json');
        if (! is_file($path)) {
            $this->error("Data file not found: {$path}");

            return self::FAILURE;
        }

        $rows = json_decode((string) file_get_contents($path), true);
        if (! is_array($rows)) {
            $this->error('Could not parse the data file.');

            return self::FAILURE;
        }

        $added = 0;
        $skipped = 0;

        foreach ($rows as $r) {
            $name = trim($r['name'] ?? '');
            if ($name === '') {
                continue;
            }

            if (Prisoner::withUnderReview()->where('name', $name)->exists()) {
                $skipped++;
                $this->line("  skip (exists): {$name}");

                continue;
            }

            if ($dry) {
                $added++;
                $this->line("  would add: {$name}".($r['state'] ? " ({$r['state']})" : ''));

                continue;
            }

            DB::transaction(function () use ($r, $name) {
                $isCo = ($r['kind'] ?? 'conviction') === 'co';
                $he = ($r['gender'] ?? 'Male') === 'Female' ? 'she' : 'he';
                $where = trim(($r['city'] ?? '').(($r['city'] ?? '') && ($r['state'] ?? '') ? ', ' : '').($r['state'] ?? ''));
                $when = $r['date'] ?? null;

                if ($isCo) {
                    $desc = "{$name} was a conscientious objector imprisoned during World War I. He was among the 179 "
                        .'conscientious objectors recorded by the National Civil Liberties Bureau as still confined in '
                        .'federal military or civil prisons — almost all in the Disciplinary Barracks at Fort Leavenworth, '
                        .'Kansas — as of March 1, 1919, in its compilation "War-Time Prosecutions and Mob Violence." The '
                        .'NCLB, the direct predecessor of the American Civil Liberties Union, noted the list likely '
                        .'covered fewer than half the objectors still confined at that date.';
                } else {
                    $desc = "{$name} appears in the National Civil Liberties Bureau's March 1919 compilation "
                        .'"War-Time Prosecutions and Mob Violence" among people convicted and jailed under World War I–era '
                        .'free-speech statutes — chiefly the Espionage Act, the Sedition Act, draft-obstruction charges, or '
                        .'wartime state and local laws. Per that record, '
                        .($when ? "on {$when}" : 'during 1917–1919')
                        .($where ? " in {$where}" : '')
                        .", {$he} was sentenced: {$r['sentence_note']}. The NCLB — the direct predecessor of the American "
                        .'Civil Liberties Union — compiled these cases from April 1, 1917 to March 1, 1919 as a public record '
                        .'of the wartime suppression of free speech, a free press, and peaceful assembly.';
                }

                $prisoner = Prisoner::create([
                    'name' => $name,
                    'first_name' => $r['first_name'] ?? null,
                    'middle_name' => $r['middle_name'] ?? null,
                    'last_name' => $r['last_name'] ?? null,
                    'gender' => $r['gender'] ?? null,
                    'state' => $r['state'] ?: null,
                    'era' => '1910s',
                    'ideologies' => $r['ideologies'] ?? [],
                    'affiliation' => $r['affiliation'] ?? [],
                    'description' => $desc,
                    'in_custody' => false,
                    'released' => true,
                    'in_exile' => false,
                    'currently_in_exile' => false,
                    'awaiting_trial' => false,
                ]);

                $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
                if ($isCo) {
                    $case->fill([
                        'prisoner_id' => $prisoner->id,
                        'charges' => 'Refusal of military service as a conscientious objector (World War I); court-martialed '
                            .'and imprisoned, chiefly at the Fort Leavenworth Disciplinary Barracks.',
                        'convicted' => 'Yes — court-martialed as a conscientious objector.',
                        'sentence' => 'Still confined as a conscientious objector, per the NCLB, as of March 1, 1919.',
                    ]);
                } else {
                    $case->fill([
                        'prisoner_id' => $prisoner->id,
                        'charges' => 'Prosecuted under World War I–era wartime speech statutes (Espionage Act, Sedition Act, '
                            .'draft obstruction, or related state/local law). Recorded offense/sentence: '.$r['sentence_note'].'.',
                        'convicted' => 'Yes — listed among the convictions in the NCLB 1919 report "War-Time Prosecutions and Mob Violence."',
                        'sentence' => $r['sentence_note'].'.',
                    ]);
                    if (! empty($r['year'])) {
                        $case->setPartialDate('sentenced_date', (int) $r['year'], $r['month'] ?: null, $r['day'] ?: null);
                    }
                }
                $case->save();
            });

            $added++;
            $this->info('  added: '.$name);
        }

        $verb = $dry ? 'Would add' : 'Added';
        $this->info("{$verb} {$added}, skipped {$skipped} (already present).");

        if (! $dry && $added > 0) {
            Cache::forget(PrisonerApiController::cacheKey());
            $this->comment('Re-run prisoners:normalize-sort-order to settle ordering.');
        }

        return self::SUCCESS;
    }
}
