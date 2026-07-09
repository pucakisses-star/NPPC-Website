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
 * Adds the Molly Maguires — Irish and Irish-American anthracite coal miners in
 * eastern Pennsylvania who were convicted and hanged between 1877 and 1879 in
 * trials driven by the Reading Railroad's Franklin B. Gowen and built on the
 * testimony of the Pinkerton infiltrator James McParland. Loads the roster from
 * database/data/molly-maguires.json (the men whose executions are well
 * documented; twenty were hanged in all). Each gets a case at the county prison
 * where he was executed, with the hanging recorded as a death in custody.
 * Create-or-update by name + Molly Maguire context, so it is idempotent and
 * won't clobber an unrelated person who shares a name.
 */
final class AddMollyMaguires extends Command
{
    protected $signature = 'prisoners:add-molly-maguires';

    protected $description = 'Add the Molly Maguires (Pennsylvania coal miners hanged 1877–1879)';

    public function handle(): int
    {
        $path = database_path('data/molly-maguires.json');
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
                // Match an existing Molly Maguire by name + context so we never
                // clobber an unrelated person who happens to share the name.
                $existing = Prisoner::withUnderReview()
                    ->where('name', $p['name'])
                    ->get()
                    ->first(fn ($x) => str_contains((string) $x->description, 'Molly Maguire')
                        || in_array('Molly Maguires', (array) $x->affiliation, true));
                $prisoner = $existing ?? new Prisoner(['name' => $p['name']]);

                $prisoner->fill([
                    'name' => $p['name'],
                    'first_name' => $p['first'] ?: null,
                    'middle_name' => $p['middle'] ?: null,
                    'last_name' => $p['last'] ?: null,
                    'aka' => $p['aka'] ?: null,
                    'gender' => 'Male',
                    'race' => 'White',
                    'state' => 'Pennsylvania',
                    'era' => '1800s',
                    'ideologies' => ['Labor Activism'],
                    'affiliation' => ['Molly Maguires'],
                    'description' => $p['bio'],
                    'in_custody' => false,
                    'released' => false,
                    'in_exile' => false,
                    'awaiting_trial' => false,
                ]);
                [$hy, $hm, $hd] = array_map('intval', explode('-', $p['hanged']));
                $prisoner->setPartialDate('death_date', $hy, $hm, $hd);
                $prisoner->save();

                $inst = Institution::firstOrCreate(
                    ['name' => $p['prison']],
                    ['city' => $p['prison_city'], 'state' => 'Pennsylvania']
                )->id;

                // One case: convicted and executed at the county prison.
                $case = $prisoner->cases()->first() ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->fill([
                    'prisoner_id' => $prisoner->id,
                    'institution_id' => $inst,
                    'charges' => 'Tried for murder as an alleged member of the Molly Maguires, in prosecutions led by Reading Railroad president Franklin B. Gowen and built largely on the testimony of the Pinkerton infiltrator James McParland.',
                    'convicted' => 'Yes — convicted of murder and executed by hanging.',
                    'sentence' => 'Death by hanging at the '.$p['prison'].' in '.$p['prison_city'].', Pennsylvania, on '.date('F j, Y', mktime(0, 0, 0, $hm, $hd, $hy)).'.',
                ]);
                $case->setPartialDate('death_in_custody_date', $hy, $hm, $hd);
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
        $this->info("\nMolly Maguires — added: {$added}, updated: {$updated}.");

        return self::SUCCESS;
    }
}
