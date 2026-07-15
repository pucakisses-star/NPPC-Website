<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The New Orleans martial-law prisoners of March 1815. After the Battle of New
 * Orleans, and even after word arrived that the Treaty of Ghent had ended the
 * war, General Andrew Jackson refused to lift the martial law he had imposed on
 * the city. When the legislator Louis Louaillier published a newspaper article
 * demanding its end, Jackson had him arrested; when U.S. District Judge Dominick
 * A. Hall issued a writ of habeas corpus for Louaillier, Jackson arrested and
 * banished the judge as well, and detained the district attorney John Dick, who
 * had helped bring the petition. All were freed once martial law was lifted;
 * Hall afterward fined Jackson $1,000 for contempt of court.
 *
 * Create-or-update matched by name + era 1810s (or a martial-law / New Orleans
 * marker in the description), so it won't clobber an unrelated person of the
 * same name (e.g. another "John Dick"). Rebuilds the single case. Idempotent.
 */
final class AddJacksonMartialLawPrisoners extends Command
{
    protected $signature = 'prisoners:add-jackson-martial-law-prisoners';

    protected $description = 'Add the 1815 New Orleans martial-law prisoners (Louaillier, Judge Hall, John Dick)';

    public function handle(): int
    {
        $people = [
            [
                'name' => 'Louis Louaillier', 'first' => 'Louis', 'last' => 'Louaillier',
                'bio' => 'Louis Louaillier was a member of the Louisiana state legislature who, in March 1815 — after the Battle of New Orleans and the arrival of news that peace had been signed at Ghent — published a signed article in the Louisiana Courier attacking General Andrew Jackson\'s refusal to lift the martial law he had imposed on the city. Jackson had him arrested on March 5, 1815, and brought before a court-martial. When the military court declined to convict him, Jackson disregarded the verdict and kept him confined until martial law was finally lifted.',
                'charges' => 'Court-martialed for a newspaper article demanding an end to Gen. Andrew Jackson\'s continued martial law in New Orleans (March 1815).',
                'convicted' => 'No — the court-martial declined to convict; Jackson held him anyway.',
                'sentence' => 'Imprisoned under martial law until it was lifted in mid-March 1815.',
            ],
            [
                'name' => 'Dominick A. Hall', 'first' => 'Dominick', 'middle' => 'Augustin', 'last' => 'Hall',
                'aka' => 'Dominic A. Hall', 'birth' => [1765], 'death' => [1820],
                'bio' => 'Dominick Augustin Hall (1765–1820) was the United States district judge for Louisiana. When Louis Louaillier\'s friends applied for a writ of habeas corpus, Judge Hall granted it, ordering the prisoner brought before his court. General Andrew Jackson responded by having Hall himself arrested and marched out of New Orleans beyond the American lines. Released only after martial law ended, Hall later summoned Jackson before his court and fined him $1,000 for contempt — a penalty Congress refunded with interest nearly thirty years afterward.',
                'charges' => 'Arrested and expelled from New Orleans by Gen. Jackson for issuing a writ of habeas corpus for Louis Louaillier (March 1815).',
                'convicted' => 'No — never charged; seized and banished by military order.',
                'sentence' => 'Detained and put outside the lines until martial law ended (March 1815).',
            ],
            [
                'name' => 'John Dick', 'first' => 'John', 'last' => 'Dick',
                'bio' => 'John Dick was the United States district attorney at New Orleans who helped bring Louis Louaillier\'s petition for a writ of habeas corpus before Judge Dominick A. Hall. For his part in the challenge to General Andrew Jackson\'s martial law, Jackson ordered him detained as well, in the same March 1815 confrontation over the rule of law in the occupied city.',
                'charges' => 'Detained by Gen. Jackson\'s order for his role in seeking habeas corpus against the New Orleans martial law (March 1815).',
                'convicted' => 'No — never charged; detained by military order.',
                'sentence' => 'Briefly detained under martial law (March 1815).',
            ],
        ];

        $added = 0;
        $updated = 0;

        DB::transaction(function () use ($people, &$added, &$updated) {
            foreach ($people as $p) {
                $existing = Prisoner::withUnderReview()
                    ->where('name', $p['name'])
                    ->get()
                    ->first(fn ($x) => $x->era === '1810s'
                        || str_contains((string) $x->description, 'martial law')
                        || str_contains((string) $x->description, 'New Orleans'));
                $prisoner = $existing ?? new Prisoner(['name' => $p['name']]);

                $prisoner->fill([
                    'name' => $p['name'],
                    'first_name' => $p['first'],
                    'middle_name' => $p['middle'] ?? null,
                    'last_name' => $p['last'],
                    'aka' => $p['aka'] ?? null,
                    'gender' => 'Male',
                    'race' => 'White',
                    'state' => 'Louisiana',
                    'era' => '1810s',
                    'ideologies' => ['Civil liberties', 'Rule of law'],
                    'affiliation' => ['Opponents of martial law (New Orleans, 1815)'],
                    'description' => $p['bio'],
                    'in_custody' => false,
                    'released' => true,
                    'in_exile' => false,
                    'awaiting_trial' => false,
                ]);
                if (! empty($p['birth'])) {
                    $prisoner->setPartialDate('birthdate', ...$p['birth']);
                }
                if (! empty($p['death'])) {
                    $prisoner->setPartialDate('death_date', ...$p['death']);
                }
                $prisoner->save();

                $prisoner->cases()->delete();
                $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->fill([
                    'prisoner_id' => $prisoner->id,
                    'charges' => $p['charges'],
                    'convicted' => $p['convicted'],
                    'sentence' => $p['sentence'],
                ]);
                $case->setPartialDate('arrest_date', 1815, 3);
                $case->setPartialDate('incarceration_date', 1815, 3);
                $case->setPartialDate('release_date', 1815, 3);
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
        $this->info("\nNew Orleans martial-law prisoners — added: {$added}, updated: {$updated}.");

        return self::SUCCESS;
    }
}
