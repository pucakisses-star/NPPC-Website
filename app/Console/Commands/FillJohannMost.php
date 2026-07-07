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
 * Fills out the Johann Most entry created by prisoners:add-anarchist-press-prisoners.
 * Most was imprisoned repeatedly on both sides of the Atlantic; the original entry
 * carried only a single thin 1902 case. This command:
 *   - rebuilds his case list as one PrisonerCase per documented imprisonment
 *     (Austria 1870, Germany 1870s, London 1881–82, New York 1887, New York 1902);
 *   - broadens the biography to note the count of imprisonments;
 *   - attaches his portrait from database/data/photos/johann-most.jpg (public
 *     domain 19th-century studio portrait, cropped to a profile head-and-shoulders).
 *
 * Where an exact month/day is uncertain the date is stored at year precision.
 * Idempotent — rebuilds the cases each run.
 */
final class FillJohannMost extends Command
{
    protected $signature = 'prisoners:fill-johann-most';

    protected $description = 'Rebuild Johann Most\'s multiple imprisonment cases and attach his portrait';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'Johann Most')->first();
        if (! $prisoner) {
            $this->error('Johann Most not found — run prisoners:add-anarchist-press-prisoners first.');

            return self::FAILURE;
        }

        $blackwells = Institution::firstOrCreate(
            ['name' => "Blackwell's Island Penitentiary"],
            ['city' => 'New York', 'state' => 'New York']
        )->id;
        $clerkenwell = Institution::firstOrCreate(
            ['name' => 'Clerkenwell Prison'],
            ['city' => 'London', 'state' => 'England']
        )->id;

        $description = 'Johann Most (1846–1906) was a German-American anarchist and the editor of '
            .'the newspaper Freiheit, an advocate of "propaganda of the deed" whose writings influenced a '
            .'generation of radicals. He was imprisoned repeatedly on both sides of the Atlantic — at least '
            .'five times over some three decades. He was first jailed as a young bookbinder in Austria, '
            .'convicted of high treason in 1870 for his part in a Vienna workers\' movement; as a Social '
            .'Democratic deputy in the German Reichstag he was jailed again and again in the 1870s for his '
            .'speeches and his paper. Expelled to London, he was sentenced in 1881 to sixteen months of hard '
            .'labour for an article in Freiheit celebrating the assassination of Tsar Alexander II. After '
            .'emigrating to the United States he served about a year on Blackwell\'s Island in 1887 for an '
            .'incendiary speech, and after the assassination of President McKinley he was prosecuted once '
            .'more: in People v. Most (1902) he was convicted of endangering the public peace and again '
            .'sentenced to a year on Blackwell\'s Island for republishing a fifty-year-old article by Karl '
            .'Heinzen advocating the assassination of political rulers.';

        DB::transaction(function () use ($prisoner, $description, $blackwells, $clerkenwell) {
            $prisoner->fill([
                'description' => $description,
                'ideologies' => ['Anarchism'],
                'affiliation' => ['Freiheit'],
                'in_custody' => false,
                'released' => true,
            ]);
            $prisoner->setPartialDate('birthdate', 1846, 2, 5);
            $prisoner->setPartialDate('death_date', 1906, 3, 17);
            $prisoner->save();

            $prisoner->cases()->delete();

            $cases = [
                [
                    'institution_id' => null,
                    'charges' => 'High treason — for his part as a young bookbinder in the Vienna workers\' movement and its December 1869 demonstration.',
                    'convicted' => 'Yes — convicted of high treason by an Austrian court in 1870.',
                    'sentence' => 'Five years\' imprisonment; amnestied after about a year and expelled from Austria in 1871.',
                    'incarceration' => [1870],
                    'release' => [1871],
                ],
                [
                    'institution_id' => null,
                    'charges' => 'Repeated press, speech and lèse-majesté offences in the German Empire — as editor and as a Social Democratic member of the Reichstag.',
                    'convicted' => 'Yes — convicted and jailed several times during the 1870s.',
                    'sentence' => 'A series of prison terms through the 1870s before he was driven out of Germany.',
                    'incarceration' => [1874],
                    'release' => null,
                ],
                [
                    'institution_id' => $clerkenwell,
                    'charges' => 'Incitement to murder — for an article in Freiheit hailing the March 1881 assassination of Tsar Alexander II of Russia (R v. Most).',
                    'convicted' => 'Yes — convicted at the Old Bailey in 1881.',
                    'sentence' => 'Sixteen months\' imprisonment with hard labour.',
                    'incarceration' => [1881],
                    'release' => [1882],
                ],
                [
                    'institution_id' => $blackwells,
                    'charges' => 'Unlawful assembly / incendiary speech — for an address in New York shortly after the Haymarket affair.',
                    'convicted' => 'Yes — convicted; conviction upheld by the New York courts.',
                    'sentence' => 'About one year on Blackwell\'s Island.',
                    'incarceration' => [1887],
                    'release' => [1888],
                ],
                [
                    'institution_id' => $blackwells,
                    'charges' => 'Endangering the public peace — for republishing in Freiheit a fifty-year-old article by Karl Heinzen advocating the assassination of political rulers, days after the assassination of President McKinley (People v. Most, 75 N.Y.S. 591, 1902).',
                    'convicted' => 'Yes — convicted; conviction upheld by the New York courts.',
                    'sentence' => 'One year on Blackwell\'s Island.',
                    'incarceration' => [1902],
                    'release' => [1903],
                ],
            ];

            foreach ($cases as $c) {
                $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->fill([
                    'prisoner_id' => $prisoner->id,
                    'institution_id' => $c['institution_id'],
                    'charges' => $c['charges'],
                    'convicted' => $c['convicted'],
                    'sentence' => $c['sentence'],
                ]);
                if (! empty($c['incarceration'])) {
                    $case->setPartialDate('incarceration_date', ...$c['incarceration']);
                }
                if (! empty($c['release'])) {
                    $case->setPartialDate('release_date', ...$c['release']);
                }
                $case->save();
            }
        });

        $this->info('Rebuilt Johann Most with 5 imprisonment cases.');

        $src = database_path('data/photos/johann-most.jpg');
        if (is_file($src)) {
            if (empty($prisoner->photo)) {
                Storage::disk('public')->makeDirectory('prisoners');
                Storage::disk('public')->put('prisoners/johann-most.jpg', file_get_contents($src));
                $prisoner->photo = 'prisoners/johann-most.jpg';
                $prisoner->save();
                $this->info('Attached portrait: prisoners/johann-most.jpg');
            } else {
                $this->info('Portrait already set; left as-is.');
            }
        } else {
            $this->warn('Portrait file not found at database/data/photos/johann-most.jpg — cases set, photo skipped.');
        }

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
