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
 * Records jail time for Depression-era and 1890s unemployed-movement prisoners
 * (Coxey's Army, the 6 March 1930 International Unemployment Day arrests, Harlem
 * and Charlotte and Alabama Unemployed Council actions, the 1930 Newark
 * crackdown, Angelo Herndon). Most are already in the database from the Coxey
 * and Labor Defender imports — for those this only sets/refreshes the case's
 * jail-time (sentence, days, incarceration/release dates) and never touches the
 * existing bio/charges. A handful are new (Eleanor Henderson, Mrs. Boozer,
 * Burke, Dozier Graham, Sylvia Ostrow). Idempotent.
 */
final class AddUnemployedMovementDetails extends Command
{
    protected $signature = 'prisoners:add-unemployed-movement-details';

    protected $description = 'Enter jail time for unemployed-movement prisoners (Coxey, 1930 unemployed-day, Herndon, etc.)';

    public function handle(): int
    {
        $set = 0;
        $made = 0;

        // ── Update the case jail-time on records that already exist ──
        // [names to match, description keyword guard, sentence, days|null, incarc|null, release|null]
        $updates = [
            [['Jacob S. Coxey', 'Jacob Coxey'], 'Coxey',
                'Twenty days in the D.C. jail and a $5 fine, for carrying banners and walking on the Capitol grounds during the 1894 Coxey\'s Army march on Washington.', 20, [1894, 5, 21], [1894, 6, 10]],
            [['Carl Browne'], 'Coxey',
                'Twenty days in the D.C. jail (the same case as Jacob Coxey) for the 1894 Coxey\'s Army march on the Capitol.', 20, [1894, 5, 21], [1894, 6, 10]],
            [['Christopher Columbus Jones'], 'Coxey',
                'Twenty days in the D.C. jail, convicted with Coxey and Browne for the 1894 Coxey\'s Army march on the Capitol.', 20, [1894, 5, 21], [1894, 6, 10]],
            [['William Z. Foster'], 'March 1930',
                'Sentenced to a three-year term for the 6 March 1930 unemployment demonstration; the parole board treated it as six months and he was released on 21 October 1930.', null, null, [1930, 10, 21]],
            [['Israel Amter'], 'March 1930',
                'Sentenced to six months to three years for the 6 March 1930 demonstration; released on 21 October 1930.', null, null, [1930, 10, 21]],
            [['Robert Minor'], 'March 1930',
                'Served a six-month term for the 6 March 1930 demonstration; released on 21 October 1930.', null, null, [1930, 10, 21]],
            [['Harry Raymond'], 'March 1930',
                'Served about ten months for the 6 March 1930 demonstration — longer than his co-defendants, reportedly owing to a prior record; held on Hart\'s Island.', null, null, null],
            [['Angelo Herndon'], 'Herndon',
                '18–20 year sentence under Georgia\'s insurrection statute; actual custody intermittent — arrested 11 July 1932, held incommunicado 11 days, sentenced January 1933, released on bail c. late 1934, returned to prison October 1935, and freed after the U.S. Supreme Court\'s 1937 ruling. Exact time served not confirmed.', null, [1932, 7, 11], null],
            [['Sam Brown'], 'relief',
                'Six months for leading an unemployed demonstration at a New York relief station (1932).', null, null, null],
            [['Wirt Taylor'], 'Alabama',
                'Eight weeks in the Birmingham, Alabama jail for unemployed / Communist organizing (1932).', 56, null, null],
        ];

        DB::transaction(function () use ($updates, &$set) {
            foreach ($updates as [$names, $guard, $sentence, $days, $incarc, $release]) {
                $prisoner = Prisoner::withUnderReview()
                    ->whereIn('name', $names)
                    ->get()
                    ->first(fn ($x) => str_contains((string) $x->description, $guard)
                        || str_contains((string) $x->description, 'unemploy')
                        || str_contains((string) $x->description, 'Coxey')
                        || $x->era === '1930s' || $x->era === '1890s');
                if (! $prisoner) {
                    $this->warn('  not found (skipped): '.$names[0]);

                    continue;
                }
                $case = $prisoner->cases()->first() ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->prisoner_id = $prisoner->id;
                $case->sentence = $sentence;
                if ($days !== null) {
                    $case->imprisoned_for_days = $days;
                }
                if ($incarc) {
                    $case->setPartialDate('incarceration_date', ...$incarc);
                }
                if ($release) {
                    $case->setPartialDate('release_date', ...$release);
                }
                $case->save();
                $set++;
                $this->line('  set jail time: '.$prisoner->name);
            }
        });

        // ── New people ──
        $new = [
            [
                'name' => 'Eleanor Henderson', 'first' => 'Eleanor', 'last' => 'Henderson', 'gender' => 'Female', 'race' => 'White',
                'state' => 'New York', 'ideo' => ['Communism', 'Labor organizing'], 'aff' => ['Unemployed Councils'],
                'bio' => 'Eleanor Henderson was a white Unemployed Council activist arrested in the same 1932 New York relief-station unemployed demonstration as Sam Brown, and sentenced to ten days.',
                'charges' => 'Unlawful assembly at a New York relief-station unemployed demonstration (1932).',
                'sentence' => 'Ten days in jail.', 'days' => 10, 'year' => 1932,
                'inst' => ['New York City Penitentiary', 'New York', 'New York'],
            ],
            [
                'name' => 'Mrs. Boozer', 'first' => null, 'last' => 'Boozer', 'gender' => 'Female', 'race' => null,
                'state' => 'North Carolina', 'ideo' => ['Labor organizing'], 'aff' => ['Unemployed Councils'],
                'bio' => 'Mrs. Boozer was a Charlotte, North Carolina woman whose furniture an Unemployed Council action carried back into her home during an eviction; she was sentenced to 25 days in jail or a $17.35 fine. Her first name was not found in the available source.',
                'charges' => 'Anti-eviction Unemployed Council action, Charlotte, North Carolina (early 1930s).',
                'sentence' => '25 days in jail or a $17.35 fine.', 'year' => 1932,
                'inst' => ['Mecklenburg County Jail', 'Charlotte', 'North Carolina'],
            ],
            [
                'name' => 'Burke', 'first' => null, 'last' => 'Burke', 'gender' => 'Male', 'race' => null,
                'state' => 'Alabama', 'ideo' => ['Communism', 'Labor organizing'], 'aff' => ['Unemployed Councils'],
                'bio' => 'Burke was arrested with Wirt Taylor in Birmingham, Alabama, for unemployed / Communist organizing (1932) and served eight weeks in jail. His first name was not verified in the available source.',
                'charges' => 'Unemployed / Communist organizing, Birmingham, Alabama (1932).',
                'sentence' => 'Eight weeks in the Birmingham jail.', 'days' => 56, 'year' => 1932,
                'inst' => ['Birmingham City Jail', 'Birmingham', 'Alabama'],
            ],
            [
                'name' => 'Dozier Graham', 'first' => 'Dozier', 'last' => 'Graham', 'gender' => 'Male', 'race' => null,
                'state' => 'New Jersey', 'ideo' => ['Communism', 'Labor organizing'], 'aff' => ['Unemployed Councils', 'Communist Party USA'],
                'bio' => 'Dozier Graham was among those arrested in the 1930 crackdown on the unemployed movement in Newark, New Jersey; the men were held on very high bail.',
                'charges' => 'Arrested in the 1930 Newark, New Jersey unemployed-movement crackdown.',
                'sentence' => 'Held on very high bail.',
                'inst' => ['Newark City Jail', 'Newark', 'New Jersey'],
            ],
            [
                'name' => 'Sylvia Ostrow', 'first' => 'Sylvia', 'last' => 'Ostrow', 'gender' => 'Female', 'race' => null,
                'state' => 'New Jersey', 'ideo' => ['Communism', 'Labor organizing'], 'aff' => ['Unemployed Councils', 'Communist Party USA'],
                'bio' => 'Sylvia Ostrow was arrested in the 1930 crackdown on the unemployed movement in Newark, New Jersey. Bailed out by her father, she was later released after public pressure.',
                'charges' => 'Arrested in the 1930 Newark, New Jersey unemployed-movement crackdown.',
                'sentence' => 'Held on high bail; bailed out by her father and released after public pressure.',
                'inst' => ['Newark City Jail', 'Newark', 'New Jersey'],
            ],
        ];

        DB::transaction(function () use ($new, &$set, &$made) {
            foreach ($new as $p) {
                $existing = Prisoner::withUnderReview()
                    ->where('name', $p['name'])
                    ->get()
                    ->first(fn ($x) => $x->era === '1930s'
                        || str_contains((string) $x->description, 'Unemployed')
                        || str_contains((string) $x->description, 'Newark'));
                $prisoner = $existing ?? new Prisoner(['name' => $p['name']]);
                $prisoner->fill([
                    'name' => $p['name'], 'first_name' => $p['first'], 'last_name' => $p['last'],
                    'gender' => $p['gender'], 'race' => $p['race'], 'state' => $p['state'],
                    'era' => '1930s', 'ideologies' => $p['ideo'], 'affiliation' => $p['aff'],
                    'description' => $p['bio'], 'in_custody' => false, 'released' => true,
                    'in_exile' => false, 'awaiting_trial' => false,
                ]);
                $prisoner->save();

                $inst = Institution::firstOrCreate(['name' => $p['inst'][0]], ['city' => $p['inst'][1], 'state' => $p['inst'][2]]);
                $case = $prisoner->cases()->where('institution_id', $inst->id)->first()
                    ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->prisoner_id = $prisoner->id;
                $case->institution_id = $inst->id;
                $case->charges = $p['charges'];
                $case->convicted = 'Convicted (Unemployed Council / unemployed-movement case).';
                $case->sentence = $p['sentence'];
                if (! empty($p['days'])) {
                    $case->imprisoned_for_days = $p['days'];
                }
                $case->setPartialDate('incarceration_date', $p['year'] ?? 1930);
                $case->save();

                $existing ? $set++ : $made++;
                $this->info(($existing ? '  updated: ' : '  added: ').$prisoner->name);
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info("\nUnemployed-movement details — updated/new cases: {$set}, new people: {$made}.");

        return self::SUCCESS;
    }
}
