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
 * Fills the cases of William "Lefty" Gilday's accomplices in the September 23,
 * 1970 Brighton bank robbery (State Street Bank and Trust, Boston), in which
 * Officer Walter Schroeder was killed:
 *   - Susan Saxe and Katherine Ann Power are already in the database with empty
 *     cases — this fills them. (It was the federal grand jury hunting Saxe and
 *     Power that produced the Lexington Six.)
 *   - Stanley Bond and Robert Valeri are new.
 *
 * Idempotent — fills the existing single case for Saxe/Power, and skips the
 * new-record creation for Bond/Valeri if they already exist.
 */
class AddBrightonRobberyAccomplices extends Command
{
    protected $signature = 'prisoners:add-brighton-robbery-accomplices';

    protected $description = 'Fill Saxe/Power cases and add Bond & Valeri (the 1970 Brighton bank robbery)';

    public function handle(): int
    {
        // --- Susan Saxe: fill the empty case ---
        DB::transaction(function () {
            $s = Prisoner::withUnderReview()->where('slug', 'susan-saxe')->first();
            if (! $s) {
                $this->warn('Susan Saxe not found.');

                return;
            }
            $case = $s->cases()->first() ?? new PrisonerCase(['prisoner_id' => $s->id]);
            $case->fill([
                'prisoner_id' => $s->id,
                'charges' => 'Armed robbery and murder — for her part in the September 23, 1970 robbery of the State Street Bank and Trust Company in Brighton, Boston, in which Boston Police Officer Walter Schroeder was shot and killed, together with a related National Guard armory raid.',
                'convicted' => 'A fugitive on the FBI Ten Most Wanted list for nearly five years; captured in Philadelphia in March 1975. Pleaded guilty to manslaughter and armed robbery.',
                'sentence' => 'Served roughly seven years in prison; released in 1982.',
            ]);
            $case->save();
            $this->info('Filled Susan Saxe case.');
        });

        // --- Katherine Ann Power: fill the empty case ---
        DB::transaction(function () {
            $p = Prisoner::withUnderReview()->where('slug', 'katherine-ann-power')->first();
            if (! $p) {
                $this->warn('Katherine Ann Power not found.');

                return;
            }
            $case = $p->cases()->first() ?? new PrisonerCase(['prisoner_id' => $p->id]);
            $case->fill([
                'prisoner_id' => $p->id,
                'charges' => 'Armed robbery and murder — for her part in the September 23, 1970 Brighton bank robbery (State Street Bank and Trust, Boston) in which Officer Walter Schroeder was killed.',
                'convicted' => 'A fugitive for 23 years, living under the alias Alice Metzinger in Oregon; she surrendered in September 1993 and pleaded guilty to manslaughter and armed robbery.',
                'sentence' => 'Eight to twelve years; paroled in 1999 after serving about six years.',
            ]);
            $case->save();
            $this->info('Filled Katherine Ann Power case.');
        });

        // --- Stanley Bond & Robert Valeri: add (new) ---
        foreach ($this->newPeople() as $np) {
            DB::transaction(function () use ($np) {
                if (Prisoner::where('name', $np['name'])->exists()) {
                    $this->warn('Skipped (already exists): '.$np['name']);

                    return;
                }
                $prisoner = Prisoner::create(array_merge([
                    'name' => $np['name'],
                    'in_custody' => false, 'released' => true, 'in_exile' => false, 'awaiting_trial' => false,
                ], $np['fields']));
                if (isset($np['death_date'])) {
                    $prisoner->death_date = $np['death_date'];
                    $prisoner->save();
                }

                $case = $np['case'];
                if (isset($case['institution'])) {
                    $inst = Institution::firstOrCreate(['name' => $case['institution']['name']], ['city' => $case['institution']['city'] ?? null, 'state' => $case['institution']['state'] ?? null]);
                    $case['institution_id'] = $inst->id;
                    unset($case['institution']);
                }
                PrisonerCase::create(array_merge(['prisoner_id' => $prisoner->id], $case));
                $this->info('Added: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
            });
        }

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }

    private function newPeople(): array
    {
        // Stanley Bond is handled by prisoners:set-stanley-bond-details
        // (create-or-update + photo), which carries his corrected record.
        return [
            [
                'name' => 'Robert Valeri',
                'fields' => [
                    'first_name' => 'Robert', 'last_name' => 'Valeri',
                    'gender' => 'Male', 'race' => 'White', 'state' => 'Massachusetts', 'era' => '1970s',
                    'ideologies' => ['Anti-War'],
                    'affiliation' => ['Weather Underground'],
                    'description' => 'Robert Valeri was one of the five involved in the September 23, 1970 robbery of the State Street Bank and Trust Company in Brighton, Boston — the bank expropriation, meant to fund the anti-war movement, in which Boston Police Officer Walter Schroeder was killed. He had met William "Lefty" Gilday and Stanley Bond in prison. After the robbery he was captured and turned state\'s evidence, testifying against his co-defendants in exchange for a reduced sentence.',
                ],
                'case' => [
                    'charges' => 'Armed robbery — for his part in the September 23, 1970 Brighton bank robbery in which Officer Walter Schroeder was killed.',
                    'convicted' => 'Yes — pleaded guilty and became a state\'s witness against his co-defendants.',
                    'sentence' => 'A reduced sentence in exchange for his cooperation and testimony.',
                ],
            ],
        ];
    }
}
