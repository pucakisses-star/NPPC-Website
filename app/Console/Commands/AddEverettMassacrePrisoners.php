<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Adds the 73 identifiable IWW members held for trial after the Everett
 * Massacre (November 5, 1916), from the University of Washington IWW History
 * Project's "Faces of the IWW" roster (database/data/everett-massacre-roster.json).
 *
 * Some 300 Wobblies sailed from Seattle to Everett, Washington to support a
 * shingle-weavers' strike and the IWW free-speech fight; at the dock, Sheriff
 * Donald McRae's posse opened fire on the steamer Verona, killing at least five
 * IWW members. The returning men were arrested and 74 were charged with murder;
 * only Thomas H. Tracy was tried, and after his acquittal (May 5, 1917) the
 * charges against the rest were dropped — but all had been held ~six months in
 * the Snohomish County jail.
 *
 * Birth years are estimated from the ages recorded at arrest (year precision).
 * Create-or-update is matched on name AND an Everett marker, so it updates the
 * existing Thomas H. Tracy record and never overwrites unrelated prisoners who
 * merely share a name (e.g. the different Victor Johnson / John Mitchell /
 * J. H. Beyer already in the database — those get fresh Everett records).
 * The 18 people with mug-shots get their public-domain portrait attached.
 * James McInerney (booked "Jim Mack") is intentionally excluded pending a
 * decision on whether he is the same man as the existing Centralia entry.
 * Idempotent.
 */
final class AddEverettMassacrePrisoners extends Command
{
    protected $signature = 'prisoners:add-everett-massacre';

    protected $description = 'Add the 73 IWW members held after the 1916 Everett Massacre';

    private const CONTEXT = 'the Everett Massacre. On November 5, 1916, some 300 Industrial Workers of the World members sailed from Seattle to Everett, Washington aboard the steamers Verona and Calista to support a shingle-weavers\' strike and the IWW\'s free-speech fight. At the Everett dock, Snohomish County sheriff Donald McRae and a posse of armed vigilantes opened fire on the Verona, killing at least five Wobblies and wounding dozens. The returning IWW members were arrested, and 74 were charged with the murder of two deputies (Jefferson Beard and Charles Curtis) killed in the gunfire. Only Thomas H. Tracy was brought to trial; after his acquittal on May 5, 1917, the charges against the others were dropped.';

    public function handle(): int
    {
        $roster = json_decode((string) file_get_contents(database_path('data/everett-massacre-roster.json')), true);
        if (! is_array($roster)) {
            $this->error('Could not read everett-massacre-roster.json');

            return self::FAILURE;
        }

        $jail = Institution::firstOrCreate(
            ['name' => 'Snohomish County Jail'],
            ['city' => 'Everett', 'state' => 'Washington']
        )->id;

        $added = 0;
        $filled = 0;
        DB::transaction(function () use ($roster, $jail, &$added, &$filled) {
            foreach ($roster as $p) {
                $isTracy = $p['name'] === 'Thomas H. Tracy';

                $prisoner = Prisoner::withUnderReview()->where('name', $p['name'])->get()
                    ->first(fn ($x) => $this->isEverett($x))
                    ?? new Prisoner(['name' => $p['name']]);

                $intro = $p['name'];
                $desc = $intro.' was one of the 74 Industrial Workers of the World members held in the Snohomish County jail and charged with murder after '.self::CONTEXT;
                $who = [];
                if (! empty($p['age'])) {
                    $who[] = 'was '.$p['age'];
                }
                if (! empty($p['occupation'])) {
                    $who[] = 'a '.strtolower($p['occupation']);
                }
                if (! empty($p['birthplace'])) {
                    $who[] = 'born in '.$p['birthplace'];
                }
                if ($who) {
                    $desc .= ' At the time of the massacre he '.implode(', ', $who).'.';
                }
                if (! empty($p['aka'])) {
                    $desc .= ' Booked as '.$p['aka'].'.';
                }

                $prisoner->fill([
                    'name' => $p['name'],
                    'first_name' => $p['first'] ?? null,
                    'middle_name' => $p['middle'] ?? null,
                    'last_name' => $p['last'] ?? null,
                    'aka' => $p['aka'] ?? null,
                    'gender' => 'Male',
                    'race' => 'White',
                    'state' => 'Washington',
                    'era' => '1910s',
                    'ideologies' => ['Labor organizing', 'Free speech'],
                    'affiliation' => ['Industrial Workers of the World (IWW)', 'Everett Massacre defendants'],
                    'description' => $desc,
                    'in_custody' => false,
                    'released' => true,
                    'in_exile' => false,
                    'awaiting_trial' => false,
                ]);
                if (! empty($p['birth_year'])) {
                    $prisoner->setPartialDate('birthdate', (int) $p['birth_year']);
                }
                $prisoner->save();

                $prisoner->cases()->delete();
                $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->fill([
                    'prisoner_id' => $prisoner->id,
                    'institution_id' => $jail,
                    'charges' => 'First-degree murder — for the deaths of deputies Jefferson Beard and Charles Curtis, killed in the gunfire during the November 5, 1916 Everett Massacre. One of 74 IWW members held for trial'
                        .(! empty($p['prisoner_no']) ? ' (IWW prisoner no. '.$p['prisoner_no'].').' : '.'),
                    'convicted' => $isTracy
                        ? 'No — the only one of the 74 defendants brought to trial; acquitted on May 5, 1917 (charged with killing deputy Jefferson Beard).'
                        : 'No — charged and held for trial; after Thomas H. Tracy\'s acquittal on May 5, 1917, the charges against the remaining defendants were dropped.',
                    'sentence' => 'Held in the Snohomish County jail from November 1916 until release in 1917 after the charges were dropped — about six months of pretrial detention.',
                ]);
                $case->setPartialDate('arrest_date', 1916, 11, 5);
                $case->setPartialDate('incarceration_date', 1916, 11, 5);
                if ($isTracy) {
                    $case->setPartialDate('release_date', 1917, 5, 5);
                } else {
                    $case->setPartialDate('release_date', 1917, 5);
                }
                $case->save();

                if (! empty($p['photo'])) {
                    $src = database_path('data/photos/'.$p['photo'].'.jpg');
                    if (is_file($src) && empty($prisoner->photo)) {
                        Storage::disk('public')->makeDirectory('prisoners');
                        Storage::disk('public')->put('prisoners/'.$p['photo'].'.jpg', (string) file_get_contents($src));
                        $prisoner->photo = 'prisoners/'.$p['photo'].'.jpg';
                        $prisoner->save();
                    }
                }

                if ($prisoner->wasRecentlyCreated) {
                    $added++;
                } else {
                    $filled++;
                }
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info("Everett Massacre roster: added {$added}, updated {$filled}.");

        return self::SUCCESS;
    }

    private function isEverett(Prisoner $x): bool
    {
        foreach ((array) $x->affiliation as $a) {
            if (str_contains((string) $a, 'Everett')) {
                return true;
            }
        }

        return str_contains((string) $x->description, 'Everett');
    }
}
