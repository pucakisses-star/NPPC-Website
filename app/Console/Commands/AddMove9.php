<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Adds the missing MOVE 9 members — the Philadelphia MOVE members convicted of
 * third-degree murder in the death of Officer James Ramp during the August 8,
 * 1978 police confrontation at their Powelton Village home (each sentenced to
 * 30–100 years). Six of these seven were paroled 2018–2020; William "Phil"
 * Africa died in prison in 2015. (Michael Davis Africa is already in the DB.)
 * Idempotent.
 */
final class AddMove9 extends Command
{
    protected $signature = 'prisoners:add-move9';

    protected $description = 'Add the missing MOVE 9 members (Africa family)';

    public function handle(): int
    {
        $people = [
            ['name' => 'Charles Sims Africa', 'first' => 'Charles', 'middle' => 'Sims', 'aka' => 'Chuck Africa', 'gender' => 'Male',
                'bday' => [1956, 4, 7], 'inmate' => 'AM4975', 'prison' => ['SCI Retreat', 'Hunlock Creek', 'Pennsylvania'], 'release' => [2020, 2, null]],
            ['name' => 'Debbie Sims Africa', 'first' => 'Debbie', 'middle' => 'Sims', 'gender' => 'Female',
                'bday' => [1956, 8, 4], 'inmate' => '006307', 'prison' => ['SCI Cambridge Springs', 'Cambridge Springs', 'Pennsylvania'], 'release' => [2018, 6, null]],
            ['name' => 'Delbert Orr Africa', 'first' => 'Delbert', 'middle' => 'Orr', 'gender' => 'Male',
                'bday' => null, 'inmate' => 'AM4985', 'prison' => ['SCI Dallas', 'Dallas', 'Pennsylvania'], 'release' => [2020, 1, null]],
            ['name' => 'Edward Goodman Africa', 'first' => 'Edward', 'middle' => 'Goodman', 'aka' => 'Eddie Goodman Africa', 'gender' => 'Male',
                'bday' => [1949, 10, 21], 'inmate' => 'AM4974', 'prison' => ['SCI Mahanoy', 'Frackville', 'Pennsylvania'], 'release' => [2019, null, null]],
            ['name' => 'Janet Holloway Africa', 'first' => 'Janet', 'middle' => 'Holloway', 'gender' => 'Female',
                'bday' => [1951, 4, 13], 'inmate' => '006308', 'prison' => ['SCI Cambridge Springs', 'Cambridge Springs', 'Pennsylvania'], 'release' => [2019, 5, null]],
            ['name' => 'Janine Phillips Africa', 'first' => 'Janine', 'middle' => 'Phillips', 'gender' => 'Female',
                'bday' => [1956, 4, 25], 'inmate' => '006309', 'prison' => ['SCI Cambridge Springs', 'Cambridge Springs', 'Pennsylvania'], 'release' => [2019, null, null]],
            ['name' => 'William Phillips Africa', 'first' => 'William', 'middle' => 'Phillips', 'aka' => 'Phil Africa', 'gender' => 'Male',
                'bday' => [1956, 5, 12], 'inmate' => 'AM4984', 'prison' => ['SCI Dallas', 'Dallas', 'Pennsylvania'], 'death' => [2015, 1, 10]],
        ];

        foreach ($people as $p) {
            $prisoner = Prisoner::withoutGlobalScopes()
                ->where('slug', Str::slug($p['name']))
                ->orWhere('name', $p['name'])
                ->first();

            $diedInCustody = ! empty($p['death']);
            $attrs = [
                'name' => $p['name'], 'first_name' => $p['first'], 'middle_name' => $p['middle'] ?? null,
                'last_name' => 'Africa', 'aka' => $p['aka'] ?? null, 'gender' => $p['gender'],
                'state' => 'Pennsylvania', 'inmate_number' => $p['inmate'], 'era' => '1970s',
                'ideologies' => ['Black liberation'], 'affiliation' => ['MOVE'],
                'in_custody' => false, 'released' => ! $diedInCustody, 'under_review' => false,
                'description' => "{$p['name']} is a member of MOVE, the Philadelphia-based Black liberation and back-to-nature group founded by John Africa, and one of the \"MOVE 9.\" Following the August 8, 1978 armed police confrontation at MOVE's Powelton Village house — in which Officer James Ramp was killed — the nine surviving adult members were convicted of third-degree murder in 1980 and each sentenced to 30 to 100 years."
                    .($diedInCustody
                        ? ' He died in prison in 2015, still incarcerated after more than 36 years.'
                        : ' After about four decades inside, consistently maintaining innocence, the MOVE 9 members were granted parole between 2018 and 2020.'),
            ];

            $prisoner ? $prisoner->fill($attrs) : $prisoner = new Prisoner($attrs);
            if (! empty($p['bday'])) {
                $prisoner->setPartialDate('birthdate', ...$p['bday']);
            }
            if ($diedInCustody) {
                $prisoner->setPartialDate('death_date', ...$p['death']);
            }
            $prisoner->save();
            $this->info(($prisoner->wasRecentlyCreated ? 'Created: ' : 'Updated: ').$prisoner->name);

            if ($prisoner->cases()->count() === 0) {
                $inst = Institution::firstOrCreate(
                    ['name' => $p['prison'][0]],
                    ['city' => $p['prison'][1], 'state' => $p['prison'][2]],
                );
                $case = $prisoner->cases()->make([
                    'institution_id' => $inst->id,
                    'charges' => 'Third-degree murder of Philadelphia police officer James Ramp in the August 8, 1978 confrontation at MOVE\'s Powelton Village house.',
                    'convicted' => 'Third-degree murder (1980)',
                    'sentence' => '30 to 100 years',
                ]);
                $case->setPartialDate('arrest_date', 1978, 8, 8);
                $case->setPartialDate('incarceration_date', 1978, 8, 8);
                if ($diedInCustody) {
                    $case->setPartialDate('death_in_custody_date', ...$p['death']);
                } elseif (! empty($p['release'])) {
                    $case->setPartialDate('release_date', ...$p['release']);
                }
                $case->save();
            }
        }

        return self::SUCCESS;
    }
}
