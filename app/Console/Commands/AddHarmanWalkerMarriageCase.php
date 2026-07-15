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
 * Adds Lillian Harman and her husband Edwin C. Walker for the 1886
 * "autonomistic marriage" case (State v. Walker) — the free-love union they
 * entered without the sanction of church or state, for which they were arrested
 * the next morning under Kansas marriage law. Both were held in the Oskaloosa
 * (Jefferson County) jail from September 20, 1886 until April 4, 1887, long past
 * their short formal sentences, because release was withheld until the court
 * costs were paid; Moses Harman finally paid the $113.80.
 *
 * Create-or-update by name; rebuilds each single case and attaches the portraits
 * from database/data/photos/. Idempotent.
 */
final class AddHarmanWalkerMarriageCase extends Command
{
    protected $signature = 'prisoners:add-harman-walker-marriage-case';

    protected $description = 'Add Lillian Harman and Edwin C. Walker (1886 autonomistic-marriage case)';

    public function handle(): int
    {
        $jail = Institution::firstOrCreate(
            ['name' => 'Jefferson County Jail'],
            ['city' => 'Oskaloosa', 'state' => 'Kansas']
        )->id;

        $people = [
            [
                'name' => 'Lillian Harman',
                'first' => 'Lillian', 'last' => 'Harman',
                'gender' => 'Female',
                'birth' => [1869, 12, 23],
                'death' => [1929, 3, 5],
                'ideologies' => ['Free love', 'Feminism', 'Free speech'],
                'affiliation' => ['Lucifer, the Light-Bearer'],
                'photo' => 'lillian-harman.jpg',
                'bio' => 'Lillian Harman (1869–1929) was an American free-thought and free-love activist, the daughter of Lucifer, the Light-Bearer editor Moses Harman. In September 1886, at the age of sixteen, she entered into an "autonomistic marriage" with the anarchist writer Edwin C. Walker — a union deliberately made without the sanction of church or state to protest the legal subordination of wives. The morning after the ceremony the couple were arrested under Kansas marriage law. Convicted in State v. Walker, Lillian was formally sentenced to forty-five days in the county jail but, like Walker, was held far longer because neither would pay the court costs; they were freed only after her father paid them in April 1887. She went on to edit radical journals and to serve as president of the British Legitimation League.',
                'sentence' => 'Forty-five days (one and a half months) in the county jail, with release withheld until the court costs were paid. Held from September 20, 1886 until April 4, 1887 — about six and a half months — when Moses Harman paid the $113.80 in costs.',
            ],
            [
                'name' => 'Edwin C. Walker',
                'first' => 'Edwin', 'middle' => 'C.', 'last' => 'Walker',
                'gender' => 'Male',
                'birth' => [1849],
                'death' => [1931],
                'ideologies' => ['Anarchism', 'Free love', 'Free speech'],
                'affiliation' => ['Lucifer, the Light-Bearer'],
                'photo' => 'edwin-c-walker.jpg',
                'bio' => 'Edwin C. Walker (1849–1931) was an American individualist-anarchist and free-thought writer, an associate editor of Lucifer, the Light-Bearer. In September 1886 he and Lillian Harman, the sixteen-year-old daughter of editor Moses Harman, celebrated an "autonomistic marriage" — a free union entered without the authority of church or state, as a protest against the marriage laws. The couple were arrested the next morning and prosecuted under Kansas law in State v. Walker. Walker was sentenced to two and a half months in the county jail and, because neither he nor Harman would pay the court costs, was not released until Moses Harman paid them the following April.',
                'sentence' => 'Two and a half months in the county jail, with release withheld until the court costs were paid. Held from September 20, 1886 until April 4, 1887, when Moses Harman paid the $113.80 in costs.',
            ],
        ];

        DB::transaction(function () use ($people, $jail) {
            foreach ($people as $p) {
                $prisoner = Prisoner::withUnderReview()->where('name', $p['name'])->first()
                    ?? new Prisoner(['name' => $p['name']]);

                $prisoner->fill([
                    'name' => $p['name'],
                    'first_name' => $p['first'],
                    'middle_name' => $p['middle'] ?? null,
                    'last_name' => $p['last'],
                    'gender' => $p['gender'],
                    'race' => 'White',
                    'state' => 'Kansas',
                    'era' => '1880s',
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

                $prisoner->cases()->delete();
                $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->fill([
                    'prisoner_id' => $prisoner->id,
                    'institution_id' => $jail,
                    'charges' => 'Violating the Kansas marriage law — for their September 1886 "autonomistic" (free-love) marriage, entered without a license or clergy (State v. Walker, 36 Kan. 297).',
                    'convicted' => 'Yes — convicted under the Kansas marriage act; the convictions were upheld by the Kansas Supreme Court in State v. Walker (1887). Arrested September 20, 1886; sentenced October 19, 1886.',
                    'sentence' => $p['sentence'],
                ]);
                $case->setPartialDate('arrest_date', 1886, 9, 20);
                $case->setPartialDate('sentenced_date', 1886, 10, 19);
                $case->setPartialDate('incarceration_date', 1886, 9, 20);
                $case->setPartialDate('release_date', 1887, 4, 4);
                $case->save();

                // Attach portrait if present and not already set.
                $src = database_path('data/photos/'.$p['photo']);
                if (is_file($src) && empty($prisoner->photo)) {
                    Storage::disk('public')->makeDirectory('prisoners');
                    Storage::disk('public')->put('prisoners/'.$p['photo'], file_get_contents($src));
                    $prisoner->photo = 'prisoners/'.$p['photo'];
                    $prisoner->save();
                }

                $this->info(($prisoner->wasRecentlyCreated ? 'Added: ' : 'Filled: ').$prisoner->name.' (slug: '.$prisoner->slug.')');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
