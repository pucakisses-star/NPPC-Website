<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Sets the WWI Espionage/Sedition Act case details for three co-defendants —
 * Otto B. Reichelt, Frederick William Bischoff, and William George Heinemeyer —
 * all arrested July 27, 1918 and sentenced October 4, 1918.
 *
 * Reichelt already exists and his case is updated in place (his existing
 * charge text and sourced description are left untouched). Bischoff and
 * Heinemeyer are matched by surname if they already exist, otherwise created
 * with the accurate dates and a factual, non-embellished description.
 *
 * Release dates: Reichelt July 24, 1919; Bischoff May 13, 1924;
 * Heinemeyer May 1924 (month precision). Birth/death: Reichelt b. July 25,
 * 1881; Bischoff b. 1887; Heinemeyer b. Sept 16, 1888, d. March 1964.
 * Idempotent.
 */
final class SetReicheltCodefendantsCases extends Command
{
    protected $signature = 'prisoners:set-reichelt-codefendants-cases';

    protected $description = 'Set cases/dates for Reichelt, Bischoff, and Heinemeyer (July 1918 Espionage/Sedition case)';

    private const ARREST = [1918, 7, 27];

    private const SENTENCED = [1918, 10, 4];

    private const CHARGE = 'Federal prosecution under the Espionage Act of 1917 and/or the Sedition Act of 1918.';

    private const CONVICTED = 'Yes — convicted and sentenced on October 4, 1918, under the wartime Espionage Act of 1917 / Sedition Act of 1918.';

    public function handle(): int
    {
        $people = [
            [
                'slug' => 'otto-b-reichelt',
                'surname' => 'Reichelt',
                'name' => 'Otto B. Reichelt',
                'aka' => 'Otto Bruno Reichelt',
                'first' => 'Otto', 'middle' => 'Bruno', 'last' => 'Reichelt',
                'state' => 'New Jersey',
                'dob' => [1881, 7, 25],
                'dod' => null,
                'release' => [1919, 7, 24],
                'desc' => null, // keep his existing sourced description
            ],
            [
                'slug' => null,
                'surname' => 'Bischoff',
                'name' => 'Frederick William Bischoff',
                'aka' => null,
                'first' => 'Frederick', 'middle' => 'William', 'last' => 'Bischoff',
                'state' => null,
                'dob' => [1887],
                'dod' => null,
                'release' => [1924, 5, 13],
                'desc' => 'Frederick William Bischoff (born 1887) was one of the defendants convicted under the '
                    .'wartime Espionage Act of 1917 and Sedition Act of 1918. He was arrested on July 27, 1918, '
                    .'convicted and sentenced on October 4, 1918, and released on May 13, 1924. The specific conduct '
                    .'underlying his prosecution is not detailed in the available record.',
            ],
            [
                'slug' => null,
                'surname' => 'Heinemeyer',
                'name' => 'William George Heinemeyer',
                'aka' => null,
                'first' => 'William', 'middle' => 'George', 'last' => 'Heinemeyer',
                'state' => null,
                'dob' => [1888, 9, 16],
                'dod' => [1964, 3],
                'release' => [1924, 5], // month precision
                'desc' => 'William George Heinemeyer (September 16, 1888 – March 1964) was one of the defendants '
                    .'convicted under the wartime Espionage Act of 1917 and Sedition Act of 1918. He was arrested on '
                    .'July 27, 1918, convicted and sentenced on October 4, 1918, and released in May 1924. The '
                    .'specific conduct underlying his prosecution is not detailed in the available record.',
            ],
        ];

        DB::transaction(function () use ($people) {
            foreach ($people as $p) {
                $prisoner = $this->resolve($p);
                if ($prisoner === false) {
                    continue; // ambiguous — skipped with a warning
                }

                $created = $prisoner === null;
                if ($created) {
                    $prisoner = Prisoner::create([
                        'name' => $p['name'],
                        'first_name' => $p['first'],
                        'middle_name' => $p['middle'],
                        'last_name' => $p['last'],
                        'gender' => 'Male',
                        'state' => $p['state'],
                        'era' => '1910s',
                        'description' => $p['desc'],
                        'in_custody' => false,
                        'released' => true,
                        'in_exile' => false,
                        'currently_in_exile' => false,
                        'awaiting_trial' => false,
                    ]);
                }

                if ($p['aka'] && empty($prisoner->aka)) {
                    $prisoner->aka = $p['aka'];
                }
                if ($p['state'] && empty($prisoner->state)) {
                    $prisoner->state = $p['state'];
                }
                $this->applyPartial($prisoner, 'birthdate', $p['dob']);
                $this->applyPartial($prisoner, 'death_date', $p['dod']);
                $prisoner->save();

                // Case: update in place if one exists, else create.
                $case = $prisoner->cases()->first();
                if (! $case) {
                    $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
                    $case->charges = self::CHARGE;
                }
                if (empty($case->charges)) {
                    $case->charges = self::CHARGE;
                }
                $case->convicted = self::CONVICTED;
                $this->applyPartial($case, 'arrest_date', self::ARREST);
                $this->applyPartial($case, 'incarceration_date', self::ARREST);
                $this->applyPartial($case, 'sentenced_date', self::SENTENCED);
                $this->applyPartial($case, 'release_date', $p['release']);
                $case->save();

                $verb = $created ? 'Created' : 'Updated';
                $this->info("{$verb}: {$prisoner->name} (slug: {$prisoner->slug}) — case dates set.");
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Done.');

        return self::SUCCESS;
    }

    /**
     * Resolve the target prisoner: existing model, null (create), or false
     * (ambiguous — skip). Matches by slug first, then by surname.
     */
    private function resolve(array $p): Prisoner|null|false
    {
        if ($p['slug']) {
            $bySlug = Prisoner::withUnderReview()->where('slug', $p['slug'])->first();
            if ($bySlug) {
                return $bySlug;
            }
        }

        $matches = Prisoner::withUnderReview()->where('name', 'like', '%'.$p['surname'].'%')->get();
        if ($matches->count() === 1) {
            return $matches->first();
        }
        if ($matches->count() > 1) {
            $this->warn("Ambiguous surname '{$p['surname']}' ({$matches->count()} matches) — skipped; resolve manually.");

            return false;
        }

        return null; // none found → create
    }

    /** Apply a [y], [y,m], or [y,m,d] partial date; null clears nothing. */
    private function applyPartial(object $model, string $field, ?array $date): void
    {
        if ($date === null) {
            return;
        }
        $model->setPartialDate($field, $date[0], $date[1] ?? null, $date[2] ?? null);
    }
}
