<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Batch 5 (final roster batch) from the ILD's Labor Defender (1926-27): the
 * rank-and-file criminal-syndicalism-era class-war prisoners carried on the
 * magazine's rosters — birthday lists, Christmas relief lists, and the
 * "released in recent months" columns, several with inmate numbers and exact
 * release dates. Most of these men appear in no modern secondary source, so
 * each record claims only what the ILD's primary source attests, with the
 * era context (California's and Washington's criminal-syndicalism
 * prosecutions of the IWW) the magazine itself supplies.
 *
 * Skipped: G. J. Terrill (already recorded as "G. Terrill" via the Kohn
 * import), John E. Merrick (recorded via the Haverhill case), and all names
 * shipped in earlier Labor Defender batches.
 *
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddLaborDefenderCsRoster extends Command
{
    protected $signature = 'prisoners:add-labor-defender-cs-roster';

    protected $description = 'Add ~48 rank-and-file criminal-syndicalism-era prisoners from the ILD Labor Defender rosters (1926-27)';

    public function handle(): int
    {
        // [name, first, last, prisonKey, inmate#, [relY,relM,relD]|null, specialNote|null]
        $rows = [
            // San Quentin (California criminal syndicalism era)
            ['Frank Sherman', 'Frank', 'Sherman', 'sq', null, null, null],
            ['C. A. Drew', 'C. A.', 'Drew', 'sq', null, null, null],
            ['J. B. Childs', 'J. B.', 'Childs', 'sq', null, null, null],
            ['Omar J. Eaton', 'Omar', 'Eaton', 'sq', null, [1927, 4, null], null],
            ['D. C. Russell', 'D. C.', 'Russell', 'sq', null, null, null],
            ['Pierre Jans', 'Pierre', 'Jans', 'sq', null, null, null],
            ['Joe Vargo', 'Joe', 'Vargo', 'sq', null, null, null],
            ['A. G. Ross', 'A. G.', 'Ross', 'sq', null, null, null],
            ['James Martin', 'James', 'Martin', 'sq', '37636', null, null],
            ['Fred Bamman', 'Fred', 'Bamman', 'sq', '38531', null, null],
            ['Ivan C. Barnes', 'Ivan', 'Barnes', 'sq', '38530', null, null],
            ['B. Johanson', 'B.', 'Johanson', 'sq', '38364', null, null],
            ['John Bruns', 'John', 'Bruns', 'sq', '40054', null,
                'Convicted in Lassen County for possessing IWW literature; his one-to-fourteen-year sentence was fixed at four years.'],
            ['A. E. Anderson', 'A. E.', 'Anderson', 'sq', null, null, null],
            ['Frank Bailey', 'Frank', 'Bailey', 'sq', '37647', [1926, 7, null], null],
            ['William Minton', 'William', 'Minton', 'sq', '38124', [1927, 1, 25],
                'Serving four years for organizing workers into the IWW, by his own account in the magazine.'],
            ['Joe Varela', 'Joe', 'Varela', 'sq', '38133', null,
                'Serving four years for organizing workers into the IWW, by his own account in the magazine.'],
            ['William Joozdeff', 'William', 'Joozdeff', 'sq', null, [1926, 6, 29],
                'The spelling of his name follows the source and may be an OCR-era rendering of a Slavic surname.'],
            ['Thomas O\'Mara', 'Thomas', 'O\'Mara', 'sq', '38293', null, null],
            ['Roy House', 'Roy', 'House', 'sq', '38535', [1926, 10, 29], null],
            ['A. Bratland', 'A.', 'Bratland', 'sq', '38363', [1927, 2, 17], null],
            ['Jack Beavert', 'Jack', 'Beavert', 'sq', '40628', null, null],
            ['William Bryan', 'William', 'Bryan', 'sq', '39344', null, null],
            ['Roy Carter', 'Roy', 'Carter', 'sq', null, [1927, 1, 29], null],
            ['Tom Connors', 'Tom', 'Connors', 'sq', null, [1927, 3, 8],
                'An IWW member framed, per the ILD, on a Sacramento jury-tampering charge built on the testimony of a perjurer later condemned for murder.'],
            ['H. M. Edwards', 'H. M.', 'Edwards', 'sq', null, null, null],
            ['H. B. Stewart', 'H. B.', 'Stewart', 'sq', null, [1927, 3, 21], null],
            ['R. V. Taylor', 'R. V.', 'Taylor', 'sq', '39350', null, null],
            ['Fred W. Thompson', 'Fred', 'Thompson', 'sq', null, [1927, 2, 7],
                'Almost certainly the future IWW educator and historian Fred W. Thompson, who served a California criminal-syndicalism term at San Quentin from 1923 until early 1927.'],
            ['John J. Cornelison', 'John', 'Cornelison', 'sq', '37287', null,
                'In the fourth year of imprisonment for labor activity when his letters ran in the magazine; he wrote that he was held "for life."'],
            ['Claude Merritt', 'Claude', 'Merritt', 'sq', '37336', null, null],
            ['Alex Nicholson', 'Alex', 'Nicholson', 'sq', '39348', null, null],
            // Folsom
            ['Leo Stark', 'Leo', 'Stark', 'folsom', null, [1927, 2, 12], null],
            ['P. J. Gordon', 'P. J.', 'Gordon', 'folsom', null, null, null],
            ['Earl Firey', 'Earl', 'Firey', 'folsom', null, null, null],
            ['Joe Clohessy', 'Joe', 'Clohessy', 'folsom', null, null, null],
            ['H. C. Duke', 'H. C.', 'Duke', 'folsom', null, [1926, 8, 7], null],
            ['Leo Ellis', 'Leo', 'Ellis', 'folsom', null, [1927, 2, null], null],
            ['C. J. Sullivan', 'C. J.', 'Sullivan', 'folsom', null, [1927, 2, 12], null],
            // Walla Walla (Washington criminal syndicalism, non-Centralia)
            ['Frank Nash', 'Frank', 'Nash', 'ww', null, [1926, 10, null], null],
            ['Fred Suttle', 'Fred', 'Suttle', 'ww', null, null, null],
            ['Ray Baker', 'Ray', 'Baker', 'ww', null, null, null],
            ['Tom Nash', 'Tom', 'Nash', 'ww', null, [1927, 4, null],
                'Refused to apply for parole until the Centralia prisoners were freed, the magazine reported, before the parole board released him.'],
            ['W. F. Moudy', 'W. F.', 'Moudy', 'ww', null, [1927, 4, null], null],
            ['Dan Curtin', 'Dan', 'Curtin', 'ww', null, [1927, 4, null], null],
            // Other states
            ['Joe Neil', 'Joe', 'Neil', 'lansing', null, null,
                'Imprisoned over four years, by his own account, "for persecution" over his working-class activity.'],
            ['Frank Godlasky', 'Frank', 'Godlasky', 'siouxfalls', null, null, null],
            ['Dominick Venturato', 'Dominick', 'Venturato', 'ohio', null, null, null],
        ];

        $prisons = [
            'sq' => ['San Quentin State Prison', 'San Quentin', 'California',
                "during the era of California's criminal-syndicalism prosecutions, which sent more than a hundred IWW members and labor radicals to San Quentin and Folsom on one-to-fourteen-year terms"],
            'folsom' => ['Folsom State Prison', 'Folsom', 'California',
                "during the era of California's criminal-syndicalism prosecutions, which sent more than a hundred IWW members and labor radicals to San Quentin and Folsom on one-to-fourteen-year terms"],
            'ww' => ['Washington State Penitentiary', 'Walla Walla', 'Washington',
                "during Washington's criminal-syndicalism prosecutions of the IWW in the early 1920s"],
            'lansing' => ['Kansas State Penitentiary', 'Lansing', 'Kansas',
                "during the era of Kansas's criminal-syndicalism prosecutions of the IWW"],
            'siouxfalls' => ['South Dakota State Penitentiary', 'Sioux Falls', 'South Dakota',
                "during the era of South Dakota's criminal-syndicalism prosecutions of the IWW"],
            'ohio' => ['Ohio State Prison', 'London', 'Ohio',
                'as a class-war prisoner supported by the International Labor Defense'],
        ];

        $added = 0;
        foreach ($rows as [$name, $first, $last, $pk, $num, $rel, $note]) {
            [$prison, $city, $state, $context] = $prisons[$pk];

            $desc = "{$name} was imprisoned at {$prison} {$context}. He appears on the International Labor Defense's rosters of American class-war prisoners in Labor Defender (1926-27)"
                .($num ? " with inmate number {$num}" : '')
                .'; like most of the rank-and-file labor prisoners of the period, little else about his case entered the historical record.'
                .($note ? ' '.$note : '');

            $payload = [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'description' => $desc,
                'state' => $state,
                'gender' => 'Male',
                'ideologies' => ['Industrial unionism'],
                'affiliation' => ['Industrial Workers of the World'],
                'era' => '1920s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => "Imprisoned {$context}; listed by the International Labor Defense as a class-war prisoner (Labor Defender, 1926-27).",
                    'convicted' => 'Convicted (criminal-syndicalism era)',
                    'sentence' => "Held at {$prison}".($rel ? '; released '.($rel[2] ? sprintf('%d-%02d-%02d', $rel[0], $rel[1], $rel[2]) : ($rel[1] ? sprintf('%d-%02d', $rel[0], $rel[1]) : $rel[0])).' per the ILD.' : ' (term and release date not documented).'),
                    'institution_name' => $prison,
                    'institution_city' => $city,
                    'institution_state' => $state,
                ]],
            ];
            if ($num) {
                $payload['inmate_number'] = $num;
            }

            $existing = Prisoner::withoutGlobalScopes()
                ->where('name', 'like', '%'.$first.'%')
                ->where('name', 'like', '%'.$last.'%')
                ->first();
            if ($existing) {
                $this->line("  already in database as \"{$existing->name}\" — skipping {$name}.");

                continue;
            }

            $this->call('prisoner:add', ['json' => json_encode($payload)]);

            $prisoner = Prisoner::withoutGlobalScopes()->where('name', $name)->first();
            if (! $prisoner) {
                continue;
            }
            $prisoner->in_custody = false;
            $prisoner->released = true;
            $prisoner->save();

            if ($rel && ($case = $prisoner->cases()->first())) {
                $case->setPartialDate('release_date', $rel[0], $rel[1], $rel[2]);
                $case->save();
            }
            $added++;
        }

        $this->info("\nDone. Processed {$added} criminal-syndicalism-era roster prisoner(s).");

        return self::SUCCESS;
    }
}
