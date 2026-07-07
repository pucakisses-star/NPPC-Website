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
 * Fills out Moses Harman, added by prisoners:add-anarchist-press-prisoners with
 * a single 1905 case. Sets his exact birth/death dates (Oct 12, 1830 –
 * Jan 30, 1910), attaches his Wikimedia Commons portrait if placed at
 * database/data/photos/moses-harman.jpg, and rebuilds his case list as the four
 * documented imprisonments for his Lucifer, the Light-Bearer obscenity
 * prosecutions:
 *   1. May 4, 1890 – Aug 30, 1890 (Kansas State Penitentiary, Lansing)
 *   2. late June 1892 – Feb 1893 (Kansas State Penitentiary, Lansing)
 *   3. June 2, 1895 – Apr 4, 1896 (Lansing, transferred to Leavenworth)
 *   4. Feb 26, 1906 – Dec 26, 1906 (Cook County Jail → Joliet → Leavenworth)
 *
 * The biography is left untouched. Uncertain days are stored at month precision.
 * Idempotent — rebuilds the cases each run.
 */
final class FillMosesHarman extends Command
{
    protected $signature = 'prisoners:fill-moses-harman';

    protected $description = 'Set Moses Harman\'s dates and portrait and rebuild his four Comstock imprisonment cases';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()->where('name', 'Moses Harman')->first();
        if (! $prisoner) {
            $this->error('Moses Harman not found — run prisoners:add-anarchist-press-prisoners first.');

            return self::FAILURE;
        }

        $lansing = Institution::firstOrCreate(
            ['name' => 'Kansas State Penitentiary'],
            ['city' => 'Lansing', 'state' => 'Kansas']
        )->id;
        $leavenworth = Institution::firstOrCreate(
            ['name' => 'United States Penitentiary, Leavenworth'],
            ['city' => 'Leavenworth', 'state' => 'Kansas']
        )->id;

        $comstock = 'Sending "obscene" material through the mails (Comstock Act) — for articles in Lucifer, the Light-Bearer on marriage, the body, and sexual freedom.';

        DB::transaction(function () use ($prisoner, $lansing, $leavenworth, $comstock) {
            $prisoner->setPartialDate('birthdate', 1830, 10, 12);
            $prisoner->setPartialDate('death_date', 1910, 1, 30);
            $prisoner->save();

            $prisoner->cases()->delete();

            $cases = [
                [
                    'institution_id' => $lansing,
                    'charges' => $comstock,
                    'convicted' => 'Yes — sentenced April 30, 1890 to five years and a $300 fine.',
                    'sentence' => 'Five years and a $300 fine. Committed to the Kansas State Penitentiary at Lansing on May 4, 1890 and released on a writ of error on August 30, 1890.',
                    'incarceration' => [1890, 5, 4],
                    'release' => [1890, 8, 30],
                ],
                [
                    'institution_id' => $lansing,
                    'charges' => $comstock,
                    'convicted' => 'Yes — a further term in the continuing Lucifer prosecutions.',
                    'sentence' => 'Re-imprisoned at the Kansas State Penitentiary (Lansing) in late June 1892; released in February 1893.',
                    'incarceration' => [1892, 6],
                    'release' => [1893, 2],
                ],
                [
                    'institution_id' => $leavenworth,
                    'charges' => $comstock,
                    'convicted' => 'Yes.',
                    'sentence' => 'Committed at the Kansas State Penitentiary (Lansing) on June 2, 1895 and later transferred to the U.S. penitentiary at Leavenworth; released April 4, 1896.',
                    'incarceration' => [1895, 6, 2],
                    'release' => [1896, 4, 4],
                ],
                [
                    'institution_id' => $leavenworth,
                    'charges' => $comstock,
                    'convicted' => 'Yes — convicted in Chicago at the age of seventy-five and sentenced to a year of hard labor.',
                    'sentence' => 'Taken to the Cook County Jail on February 26, 1906, transferred to the Joliet penitentiary, and then to the U.S. penitentiary at Leavenworth on June 28, 1906; released December 26, 1906.',
                    'incarceration' => [1906, 2, 26],
                    'release' => [1906, 12, 26],
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
                $case->setPartialDate('incarceration_date', ...$c['incarceration']);
                $case->setPartialDate('release_date', ...$c['release']);
                $case->save();
            }
        });

        $this->info('Set Moses Harman\'s dates and rebuilt his 4 imprisonment cases.');

        $src = database_path('data/photos/moses-harman.jpg');
        if (is_file($src)) {
            if (empty($prisoner->photo)) {
                Storage::disk('public')->makeDirectory('prisoners');
                Storage::disk('public')->put('prisoners/moses-harman.jpg', file_get_contents($src));
                $prisoner->photo = 'prisoners/moses-harman.jpg';
                $prisoner->save();
                $this->info('Attached portrait: prisoners/moses-harman.jpg');
            } else {
                $this->info('Portrait already set; left as-is.');
            }
        } else {
            $this->warn('Portrait file not found at database/data/photos/moses-harman.jpg — dates/cases set, photo skipped.');
        }

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
