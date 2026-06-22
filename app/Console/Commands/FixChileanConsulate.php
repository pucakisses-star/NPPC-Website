<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Repairs the two 1978 Chilean-consulate records on production, where a
 * pre-existing entry for each person meant the earlier `prisoners:add-chilean-
 * consulate` (which skips existing names) never applied their data:
 *
 *   - Pablo Marcano García: the existing record never received his birthdate
 *     (January 15, 1952). It is set here.
 *   - Nydia "Esther" Cuevas: the add created a second "Nydia Cuevas" record
 *     because the pre-existing one is named "Nydia Esther Cuevas". This merges
 *     every "Nydia ... Cuevas" variant into the canonical "Nydia Esther Cuevas"
 *     and applies her August 18, 1985 release date.
 *
 * For each person the accent/middle-name-broad match catches all variants; the
 * canonical record is kept (others merged in — union of tags/affiliations,
 * blank fields filled — then deleted), birth/release/case dates are set, and the
 * record is flagged released. Idempotent.
 */
final class FixChileanConsulate extends Command
{
    protected $signature = 'prisoners:fix-chilean-consulate';

    protected $description = 'Fix Pablo Marcano García birthdate and merge the duplicate Nydia Cuevas records';

    public function handle(): int
    {
        $specs = [
            [
                'first' => 'Pablo', 'second' => 'Marcano',
                'canonical' => 'Pablo Marcano García',
                'birthdate' => '1952-01-15',
                'bio' => null,
                'release' => '1985-06-21',
                'charges' => 'Seized the Chilean consulate in San Juan and held the honorary consul hostage at gunpoint (July 3–4, 1978), to demand freedom for the imprisoned Puerto Rican Nationalists and the cancellation of the island\'s July 4 celebrations.',
                'convicted' => 'Yes — convicted in the U.S. District Court for Puerto Rico (1978); conviction affirmed on appeal (United States v. Marcano-García & Cuevas-Rivera, 622 F.2d 12, 1st Cir. 1980).',
                'sentence' => 'Initially sentenced to 22 years, reduced to seven; held from his July 1978 arrest and released on June 21, 1985.',
            ],
            [
                'first' => 'Nydia', 'second' => 'Cuevas',
                'canonical' => 'Nydia Esther Cuevas',
                'birthdate' => null,
                'bio' => 'Nydia Esther Cuevas (Cuevas Rivera) is a Puerto Rican independence activist who, with Pablo Marcano García, seized the Chilean consulate in San Juan on July 3, 1978 and held the honorary consul at gunpoint for about 22 hours, surrendering on July 4. Their action demanded the release of the imprisoned Puerto Rican Nationalists and the cancellation of the island\'s Fourth of July celebrations. She was convicted in federal court and imprisoned, and was released on August 18, 1985 and welcomed home as a hero.',
                'release' => '1985-08-18',
                'charges' => 'Seized the Chilean consulate in San Juan and held the honorary consul hostage at gunpoint (July 3–4, 1978), with Pablo Marcano García, to demand freedom for the imprisoned Puerto Rican Nationalists.',
                'convicted' => 'Yes — convicted in the U.S. District Court for Puerto Rico (1978); conviction affirmed on appeal (United States v. Marcano-García & Cuevas-Rivera, 622 F.2d 12, 1st Cir. 1980).',
                'sentence' => 'Imprisoned for the consulate takeover from her July 1978 arrest; released on August 18, 1985.',
            ],
        ];

        foreach ($specs as $spec) {
            $matches = Prisoner::withUnderReview()
                ->where('name', 'like', '%'.$spec['first'].'%')
                ->where('name', 'like', '%'.$spec['second'].'%')
                ->get();

            if ($matches->isEmpty()) {
                $this->warn("Not found, skipping: {$spec['canonical']}");

                continue;
            }

            $keeper = $matches->firstWhere('name', $spec['canonical'])
                ?? $matches->first(fn (Prisoner $p) => $p->cases()->count() > 0)
                ?? $matches->sortByDesc(fn (Prisoner $p) => strlen((string) $p->description))->first();

            $dups = $matches->reject(fn (Prisoner $p) => $p->id === $keeper->id)->values();

            DB::transaction(function () use ($keeper, $dups, $spec) {
                $ideologies = collect($keeper->ideologies ?? []);
                $affiliation = collect($keeper->affiliation ?? []);

                foreach ($dups as $dup) {
                    $ideologies = $ideologies->merge($dup->ideologies ?? []);
                    $affiliation = $affiliation->merge($dup->affiliation ?? []);
                    foreach (['photo', 'description', 'aka', 'race', 'gender', 'state', 'era', 'birthdate'] as $field) {
                        if (blank($keeper->getAttribute($field)) && filled($dup->getAttribute($field))) {
                            $keeper->setAttribute($field, $dup->getAttribute($field));
                        }
                    }
                }

                $keeper->name = $spec['canonical'];
                if ($spec['bio'] !== null) {
                    $keeper->description = $spec['bio'];
                }
                if ($spec['birthdate'] !== null) {
                    $keeper->birthdate = $spec['birthdate'];
                }
                $keeper->ideologies = $ideologies->unique()->values()->all();
                $keeper->affiliation = $affiliation->unique()->values()->all();
                $keeper->in_custody = false;
                $keeper->released = true;
                $keeper->save();

                $case = $keeper->cases()->first() ?? new PrisonerCase(['prisoner_id' => $keeper->id]);
                if (blank($case->charges)) {
                    $case->charges = $spec['charges'];
                }
                if (blank($case->convicted)) {
                    $case->convicted = $spec['convicted'];
                }
                $case->arrest_date = '1978-07-04';
                $case->incarceration_date = '1978-07-04';
                $case->release_date = $spec['release'];
                $case->sentence = $spec['sentence'];
                $case->save();

                foreach ($dups as $dup) {
                    $dup->cases()->delete();
                    $dup->delete();
                }
            });

            $this->info("{$spec['canonical']}: kept slug={$keeper->slug}, merged/deleted {$dups->count()} duplicate(s), release {$spec['release']}".
                ($spec['birthdate'] ? ", birthdate {$spec['birthdate']}" : ''));
        }

        return self::SUCCESS;
    }
}
