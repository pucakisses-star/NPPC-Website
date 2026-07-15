<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The two anarchists arrested in Philadelphia on February 20, 1908 and charged
 * with inciting to riot, held under $1,500 bail each, after an unemployed
 * demonstration: the writer Voltairine de Cleyre and the orator Chaim Weinberg.
 * De Cleyre's bail was lowered to $800 (posted by Dr. Leo Gartman on Feb 22) and
 * she was acquitted on June 18, 1908 with no sentenced jail time. Create-or-
 * update matched by name + era 1900s (or an anarchist/1908 marker), so it won't
 * clobber an unrelated same-named person. Idempotent.
 */
final class AddPhiladelphia1908Anarchists extends Command
{
    protected $signature = 'prisoners:add-philadelphia-1908-anarchists';

    protected $description = 'Add the 1908 Philadelphia inciting-to-riot anarchists (Voltairine de Cleyre, Chaim Weinberg)';

    public function handle(): int
    {
        $people = [
            [
                'name' => 'Voltairine de Cleyre', 'first' => 'Voltairine', 'last' => 'de Cleyre',
                'birth' => [1866, 11, 17], 'death' => [1932, 6, 20],
                'ideologies' => ['Anarchism', 'Feminism'],
                'affiliation' => ['Philadelphia anarchist movement'],
                'bio' => 'Voltairine de Cleyre (1866–1932) was an American anarchist writer, essayist, poet, and '
                    .'orator, and a central figure in Philadelphia\'s immigrant anarchist movement. On February 20, '
                    .'1908 she was arrested in Philadelphia and charged with inciting to riot after an unemployed '
                    .'demonstration, and held under $1,500 bail. The bail was lowered to $800 and posted by Dr. Leo '
                    .'Gartman on February 22, 1908; she was acquitted on June 18, 1908 and served no sentenced jail '
                    .'time.',
                'charges' => 'Inciting to riot — arrested in Philadelphia on February 20, 1908 after an unemployed '
                    .'demonstration; held under $1,500 bail (later lowered to $800).',
                'convicted' => 'Acquitted, June 18, 1908.',
                'sentence' => 'No sentenced jail time — jailed briefly pending bail (February 20–22, 1908), then '
                    .'released on $800 bail posted by Dr. Leo Gartman, and acquitted.',
                'arrest' => [1908, 2, 20], 'incarc' => [1908, 2, 20], 'release' => [1908, 2, 22],
            ],
            [
                'name' => 'Chaim Weinberg', 'first' => 'Chaim', 'last' => 'Weinberg', 'aka' => 'Chaim Leib Weinberg',
                'birth' => [1861], 'death' => [1939],
                'ideologies' => ['Anarchism'],
                'affiliation' => ['Cloakmakers Union', 'Philadelphia anarchist movement'],
                'bio' => 'Chaim Leib Weinberg (c. 1861–1939) was a Philadelphia Jewish anarchist, cloakmakers\'-union '
                    .'organizer, and popular Yiddish-language orator in the city\'s immigrant labor movement. He was '
                    .'arrested with Voltairine de Cleyre in Philadelphia on February 20, 1908 and charged with inciting '
                    .'to riot, held under $1,500 bail, in the prosecution arising from an unemployed demonstration.',
                'charges' => 'Inciting to riot — arrested with Voltairine de Cleyre in Philadelphia on February 20, '
                    .'1908; held under $1,500 bail.',
                'convicted' => 'Charged in the February 1908 Philadelphia inciting-to-riot prosecution (de Cleyre, '
                    .'tried on the same charge, was acquitted).',
                'sentence' => 'Held under $1,500 bail on the inciting-to-riot charge.',
                'arrest' => [1908, 2, 20], 'incarc' => [1908, 2, 20],
            ],
        ];

        $added = 0;
        $updated = 0;

        DB::transaction(function () use ($people, &$added, &$updated) {
            foreach ($people as $p) {
                $existing = Prisoner::withUnderReview()
                    ->where('name', $p['name'])
                    ->get()
                    ->first(fn ($x) => $x->era === '1900s'
                        || str_contains((string) $x->description, '1908')
                        || str_contains((string) $x->description, 'anarchist'));
                $prisoner = $existing ?? new Prisoner(['name' => $p['name']]);

                $prisoner->fill([
                    'name' => $p['name'],
                    'first_name' => $p['first'],
                    'last_name' => $p['last'],
                    'aka' => $p['aka'] ?? null,
                    'gender' => $p['name'] === 'Voltairine de Cleyre' ? 'Female' : 'Male',
                    'state' => 'Pennsylvania',
                    'era' => '1900s',
                    'ideologies' => $p['ideologies'],
                    'affiliation' => $p['affiliation'],
                    'description' => $p['bio'],
                    'in_custody' => false,
                    'released' => true,
                    'in_exile' => false,
                    'awaiting_trial' => false,
                ]);
                $prisoner->setPartialDate('birthdate', ...$p['birth']);
                $prisoner->setPartialDate('death_date', ...$p['death']);
                $prisoner->save();

                $case = $prisoner->cases()->first() ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->prisoner_id = $prisoner->id;
                $case->charges = $p['charges'];
                $case->convicted = $p['convicted'];
                $case->sentence = $p['sentence'];
                $case->setPartialDate('arrest_date', ...$p['arrest']);
                $case->setPartialDate('incarceration_date', ...$p['incarc']);
                if (! empty($p['release'])) {
                    $case->setPartialDate('release_date', ...$p['release']);
                }
                $case->save();

                if ($existing) {
                    $updated++;
                    $this->line('  updated: '.$p['name']);
                } else {
                    $added++;
                    $this->info('  added: '.$p['name'].' (slug: '.$prisoner->slug.')');
                }
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info("\n1908 Philadelphia anarchists — added: {$added}, updated: {$updated}.");

        return self::SUCCESS;
    }
}
