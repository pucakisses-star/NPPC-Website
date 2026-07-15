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
 * Updates and expands the Elaine massacre (Phillips County, Arkansas, 1919)
 * prisoner records with the documented sentences and imprisonment spans.
 *
 * The earlier archive:add-elaine-modoc-harlem command added seven of the
 * "Elaine Twelve" but gave them all the same 1925 release. This command:
 *   - splits the Twelve into the Moore defendants (death → commuted to a
 *     12-year term → indefinite furlough, Oct 1919 – Jan 14, 1925) and the Ware
 *     defendants (death → conviction reversed/retried → discharged June 25,
 *     1923), correcting Ed Ware and adding the five missing Ware defendants
 *     (William Wordlaw, Albert Giles, Joe Fox, John Martin, Alfred Banks Jr.);
 *   - adds the 34 other named penitentiary prisoners listed by Ida B. Wells in
 *     "The Arkansas Race Riot" (1920), whose individual sentences within the
 *     documented 5–21-year range are not separately recorded;
 *   - adds Sam Wilson (21 years, second-degree murder, Luther Earles case).
 *
 * Create-or-update is matched on name AND an Elaine marker, so common names
 * (John Martin, James Moore, Bob Jackson, John Thomas …) never overwrite the
 * unrelated existing prisoners of the same name — a new Elaine record is
 * created with a -2 slug instead. Idempotent.
 */
final class UpdateElainePrisoners extends Command
{
    protected $signature = 'prisoners:update-elaine';

    protected $description = 'Update/expand the Elaine massacre (1919) prisoners with documented sentences';

    private const CONTEXT = 'the Elaine massacre of September–October 1919 in Phillips County, Arkansas, in which white mobs and federal troops killed an estimated 100 to 800 Black residents who had been organizing the Progressive Farmers and Household Union of America to demand fair settlement for their cotton. Arkansas prosecuted 122 Black survivors in trials lasting minutes before all-white juries; not a single white attacker was charged.';

    public function handle(): int
    {
        $inst = Institution::firstOrCreate(
            ['name' => 'Arkansas State Penitentiary'],
            ['state' => 'Arkansas']
        )->id;

        DB::transaction(function () use ($inst) {
            foreach ($this->moore() as $p) {
                $this->write($p, $inst, ['Progressive Farmers and Household Union of America', 'Elaine Twelve']);
            }
            foreach ($this->ware() as $p) {
                $this->write($p, $inst, ['Progressive Farmers and Household Union of America', 'Elaine Twelve']);
            }
            foreach ($this->others() as $p) {
                $this->write($p, $inst, ['Progressive Farmers and Household Union of America', 'Elaine massacre']);
            }
            $this->write($this->wilson(), $inst, ['Elaine massacre']);
        });

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Elaine prisoners updated.');

        return self::SUCCESS;
    }

    /** Moore defendants — death → commuted to 12 years → indefinite furlough Jan 14, 1925. */
    private function moore(): array
    {
        $desc = 'One of the "Elaine Twelve," and one of the six "Moore" defendants — Black sharecroppers sentenced to death by an all-white jury after '.self::CONTEXT.' The Moore defendants\' death sentences were vacated by the U.S. Supreme Court in Moore v. Dempsey (1923), a landmark ruling that mob-dominated trials violate federal due process. Their sentences were commuted to twelve years, and they were freed on indefinite furlough on January 14, 1925.';
        $sentence = 'Death by electrocution, later commuted to a twelve-year term after Moore v. Dempsey (1923); released on indefinite furlough on January 14, 1925, after about five years and three months\' imprisonment.';
        $convicted = 'Yes — death sentence vacated by the U.S. Supreme Court in Moore v. Dempsey (1923); commuted to twelve years.';

        $people = [
            ['name' => 'Frank Moore', 'first' => 'Frank', 'last' => 'Moore'],
            ['name' => 'Frank Hicks', 'first' => 'Frank', 'last' => 'Hicks'],
            ['name' => 'Ed Hicks', 'first' => 'Ed', 'last' => 'Hicks'],
            ['name' => 'J. E. Knox', 'first' => 'J.', 'middle' => 'E.', 'last' => 'Knox', 'aka' => 'Joe Knox'],
            ['name' => 'Paul Hall', 'first' => 'Paul', 'last' => 'Hall'],
            ['name' => 'Ed Coleman', 'first' => 'Ed', 'last' => 'Coleman'],
        ];

        return array_map(fn ($p) => $p + [
            'desc' => $desc, 'convicted' => $convicted, 'sentence' => $sentence,
            'charges' => 'First-degree murder of Clinton Lee, a white man killed during the white-mob assault on Black residents.',
            'incarceration' => [1919, 10], 'sentenced' => [1919, 11, 3], 'release' => [1925, 1, 14],
        ], $people);
    }

    /** Ware defendants — death → conviction reversed/retried → discharged June 25, 1923. */
    private function ware(): array
    {
        $desc = 'One of the "Elaine Twelve," and one of the six "Ware" defendants — Black sharecroppers sentenced to death by an all-white jury after '.self::CONTEXT.' The Ware defendants\' convictions were reversed by the Arkansas Supreme Court (Ware v. State) for procedural defects; when the state failed to retry them within the time the law allowed, they were discharged on June 25, 1923.';
        $convicted = 'Yes — sentenced to death; conviction reversed by the Arkansas Supreme Court and, the state having failed to retry within the statutory term, discharged.';
        $sentence = 'Death sentence; conviction reversed and set for retrial. Discharged on June 25, 1923, after about three years and eight months\' imprisonment.';
        // Ed Ware entered custody about November 1919 (~3 years 7 months); the others from October 1919.
        $edWareSentence = 'Death sentence; conviction reversed and set for retrial. Discharged on June 25, 1923, after about three years and seven months\' imprisonment.';

        $charges = 'First-degree murder of Clinton Lee, a white man killed during the white-mob assault on Black residents.';

        return [
            ['name' => 'Ed Ware', 'first' => 'Ed', 'last' => 'Ware', 'desc' => $desc, 'convicted' => $convicted, 'sentence' => $edWareSentence, 'charges' => $charges, 'incarceration' => [1919, 11], 'sentenced' => [1919, 11, 3], 'release' => [1923, 6, 25]],
            ['name' => 'William Wordlaw', 'first' => 'William', 'last' => 'Wordlaw', 'aka' => 'Will Wordlaw', 'desc' => $desc, 'convicted' => $convicted, 'sentence' => $sentence, 'charges' => $charges, 'incarceration' => [1919, 10], 'sentenced' => [1919, 11, 3], 'release' => [1923, 6, 25]],
            ['name' => 'Albert Giles', 'first' => 'Albert', 'last' => 'Giles', 'desc' => $desc, 'convicted' => $convicted, 'sentence' => $sentence, 'charges' => $charges, 'incarceration' => [1919, 10], 'sentenced' => [1919, 11, 3], 'release' => [1923, 6, 25]],
            ['name' => 'Joe Fox', 'first' => 'Joe', 'last' => 'Fox', 'aka' => 'Joseph Fox', 'desc' => $desc, 'convicted' => $convicted, 'sentence' => $sentence, 'charges' => $charges, 'incarceration' => [1919, 10], 'sentenced' => [1919, 11, 3], 'release' => [1923, 6, 25]],
            ['name' => 'John Martin', 'first' => 'John', 'last' => 'Martin', 'desc' => $desc, 'convicted' => $convicted, 'sentence' => $sentence, 'charges' => $charges, 'incarceration' => [1919, 10], 'sentenced' => [1919, 11, 3], 'release' => [1923, 6, 25]],
            ['name' => 'Alfred Banks Jr.', 'first' => 'Alfred', 'last' => 'Banks', 'aka' => 'Alf Banks', 'desc' => $desc, 'convicted' => $convicted, 'sentence' => $sentence, 'charges' => $charges, 'incarceration' => [1919, 10], 'sentenced' => [1919, 11, 3], 'release' => [1923, 6, 25]],
        ];
    }

    /** 34 other named penitentiary prisoners from Ida B. Wells's list — 5–21-year terms, individual length unknown. */
    private function others(): array
    {
        $desc = 'One of the Black sharecroppers imprisoned after '.self::CONTEXT.' He was among the men named by the anti-lynching journalist Ida B. Wells-Barnett in her pamphlet "The Arkansas Race Riot" (1920) as then serving penitentiary terms of five to twenty-one years; the length of his individual sentence is not separately documented.';
        $convicted = 'Yes — sentenced to a penitentiary term (the group\'s terms ranged from five to twenty-one years; his individual sentence is not documented).';
        $sentence = 'A penitentiary term within the documented five-to-twenty-one-year range; exact length and release date not established in the sources.';

        $names = [
            ['Walter Guley', 'Walter', 'Guley', 'Walter Gulley'],
            ['B. Earl', 'B.', 'Earl', null],
            ['John Foster', 'John', 'Foster', null],
            ['E. F. Foster', 'E.', 'Foster', null, 'F.'],
            ['Will Hampton', 'Will', 'Hampton', null],
            ['I. W. Swats', 'I.', 'Swats', null, 'W.'],
            ['Andrew Goff', 'Andrew', 'Goff', null],
            ['Gilmore Jenkins', 'Gilmore', 'Jenkins', null],
            ['Ed Mitchell', 'Ed', 'Mitchell', null],
            ['Dave Haas', 'Dave', 'Haas', null],
            ['Sykes Fox', 'Sykes', 'Fox', null],
            ['Will Curry', 'Will', 'Curry', null],
            ['Ed Baker', 'Ed', 'Baker', null],
            ['Joe Leggens', 'Joe', 'Leggens', null],
            ['Joe Meshane', 'Joe', 'Meshane', null],
            ['S. J. Jackson', 'S.', 'Jackson', null, 'J.'],
            ['Dan Rollins', 'Dan', 'Rollins', null],
            ['D. Paine', 'D.', 'Paine', null],
            ['Charley Jones', 'Charley', 'Jones', null],
            ['C. C. Hubert', 'C.', 'Hubert', null, 'C.'],
            ['T. Dixon', 'T.', 'Dixon', null],
            ['James Moore', 'James', 'Moore', null],
            ['Will Mack', 'Will', 'Mack', null],
            ['Sam Barber', 'Sam', 'Barber', null],
            ['Abe Brown', 'Abe', 'Brown', null],
            ['Dave Reed', 'Dave', 'Reed', null],
            ['Henry Avant', 'Henry', 'Avant', null],
            ['Charley Hubbard', 'Charley', 'Hubbard', null],
            ['John Thomas', 'John', 'Thomas', null],
            ['John Jefferson', 'John', 'Jefferson', null],
            ['Bob Jackson', 'Bob', 'Jackson', null],
            ['Walter Ward', 'Walter', 'Ward', null],
            ['Will Steward', 'Will', 'Steward', null],
            ['Jim Smith', 'Jim', 'Smith', null],
        ];

        return array_map(fn ($n) => [
            'name' => $n[0], 'first' => $n[1], 'last' => $n[2], 'aka' => $n[3], 'middle' => $n[4] ?? null,
            'desc' => $desc, 'convicted' => $convicted, 'sentence' => $sentence,
            'charges' => 'Charges arising from the Elaine massacre; one of the Elaine defendants sent to the state penitentiary.',
            'incarceration' => [1919, 11], 'sentenced' => [1919, 11], 'release' => null,
        ], $names);
    }

    private function wilson(): array
    {
        return [
            'name' => 'Sam Wilson', 'first' => 'Sam', 'last' => 'Wilson',
            'desc' => 'One of the Black defendants imprisoned after '.self::CONTEXT.' Rather than face a death sentence, he pleaded guilty to second-degree murder in the Luther Earles case and was sentenced to twenty-one years in the state penitentiary. His exact release date is not established in the sources.',
            'convicted' => 'Yes — pleaded guilty to second-degree murder (Luther Earles case).',
            'sentence' => 'Twenty-one years for second-degree murder after a guilty plea; exact release date not established.',
            'charges' => 'Second-degree murder (Luther Earles case) — entered on a guilty plea.',
            'incarceration' => [1919, 11], 'sentenced' => [1919, 11], 'release' => null,
        ];
    }

    /** Create-or-update, matched on name AND an Elaine marker so unrelated same-name records are never touched. */
    private function write(array $p, string $inst, array $affiliation): void
    {
        $existing = Prisoner::withUnderReview()->where('name', $p['name'])->get()
            ->first(fn ($x) => $this->isElaine($x));
        $prisoner = $existing ?? new Prisoner(['name' => $p['name']]);

        $prisoner->fill([
            'name' => $p['name'],
            'first_name' => $p['first'],
            'middle_name' => $p['middle'] ?? null,
            'last_name' => $p['last'],
            'aka' => $p['aka'] ?? null,
            'gender' => 'Male',
            'race' => 'Black',
            'state' => 'Arkansas',
            'era' => '1910s',
            'ideologies' => ['Black Southern labor organizing'],
            'affiliation' => $affiliation,
            'description' => $p['desc'],
            'in_custody' => false,
            'released' => true,
            'in_exile' => false,
            'awaiting_trial' => false,
        ]);
        $prisoner->save();

        $prisoner->cases()->delete();
        $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
        $case->fill([
            'prisoner_id' => $prisoner->id,
            'institution_id' => $inst,
            'charges' => $p['charges'],
            'convicted' => $p['convicted'],
            'sentence' => $p['sentence'],
        ]);
        $case->setPartialDate('arrest_date', 1919, 10);
        if (! empty($p['sentenced'])) {
            $case->setPartialDate('sentenced_date', ...$p['sentenced']);
        }
        if (! empty($p['incarceration'])) {
            $case->setPartialDate('incarceration_date', ...$p['incarceration']);
        }
        if (! empty($p['release'])) {
            $case->setPartialDate('release_date', ...$p['release']);
        }
        $case->save();

        $this->info(($prisoner->wasRecentlyCreated ? 'Added:  ' : 'Filled: ').$p['name']);
    }

    private function isElaine(Prisoner $x): bool
    {
        $aff = (array) $x->affiliation;
        foreach ($aff as $a) {
            if (str_contains((string) $a, 'Elaine')) {
                return true;
            }
        }

        return str_contains((string) $x->description, 'Elaine');
    }
}
