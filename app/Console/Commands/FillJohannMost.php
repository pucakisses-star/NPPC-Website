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
 * The original entry carried only a single thin 1902 case. This command:
 *   - rebuilds his case list as his three documented U.S. imprisonments —
 *     the 1886 Blackwell's Island term, the 1887 Kramer's Hall speech case
 *     (served on appeal in 1891), and the 1901–1903 McKinley/Freiheit
 *     "Murder vs. Murder" case;
 *   - attaches his portrait from database/data/photos/johann-most.jpg (public
 *     domain 19th-century studio portrait, cropped to a profile head-and-shoulders).
 *
 * The biography is left untouched. Where an exact day is uncertain the date is
 * stored at month precision. Idempotent — rebuilds the cases each run.
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

        DB::transaction(function () use ($prisoner, $blackwells) {
            // Biography is left untouched — only dates, flags and cases are set.
            $prisoner->fill([
                'in_custody' => false,
                'released' => true,
            ]);
            $prisoner->setPartialDate('birthdate', 1846, 2, 5);
            $prisoner->setPartialDate('death_date', 1906, 3, 17);
            $prisoner->save();

            $prisoner->cases()->delete();

            $cases = [
                [
                    'institution_id' => $blackwells,
                    'charges' => 'Incendiary speech / unlawful assembly — for a New York address advocating armed action.',
                    'convicted' => 'Yes — convicted in New York; sentenced June 2, 1886.',
                    'sentence' => 'One year\'s imprisonment and a $500 fine. Committed to Blackwell\'s Island on June 2, 1886 and released on April 1, 1887.',
                    'incarceration' => [1886, 6, 2],
                    'release' => [1887, 4, 1],
                ],
                [
                    'institution_id' => $blackwells,
                    'charges' => 'Incendiary speech at Kramer\'s Hall, New York.',
                    'convicted' => 'Yes — sentenced December 8, 1887 to twelve months, then released on $5,000 bail pending appeal; the conviction was affirmed June 16, 1891.',
                    'sentence' => 'Twelve months. Having exhausted his appeal (affirmed June 16, 1891), he served the term on Blackwell\'s Island beginning in late June 1891.',
                    'incarceration' => [1891, 6],
                    'release' => null,
                ],
                [
                    'institution_id' => $blackwells,
                    'charges' => 'Endangering the public peace — for republishing in Freiheit Karl Heinzen\'s article "Murder against Murder" advocating the assassination of political rulers, days after the assassination of President McKinley (People v. Most, 75 N.Y.S. 591).',
                    'convicted' => 'Yes — sentenced in October 1901; the conviction was affirmed on final appeal June 10, 1902.',
                    'sentence' => 'One year on Blackwell\'s Island. The term began shortly after the appeal was affirmed on June 10, 1902; he was released by April 9, 1903, having served a year less about two months\' commutation for good behavior.',
                    'incarceration' => [1902, 6],
                    'release' => [1903, 4],
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

        $this->info('Rebuilt Johann Most with 3 U.S. imprisonment cases.');

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
