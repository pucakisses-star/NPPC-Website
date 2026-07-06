<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\ArchiveRecord;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Four World War I conscientious objectors documented in the Mennonite Central
 * Committee's Civilian Public Service oral-history archive — a court-martial
 * transcript (Harry L. Charles, Camp Travis, 1918) and three taped interviews
 * recorded in 1968 (Jesse D. Hartzler, George S. Miller, Cornelius Voth). Their
 * primary-source PDFs are registered as ArchiveRecords (they live under
 * public/pdfs/cps-wwi-co/); the prisoner entries themselves are enriched or
 * created from the same accounts.
 *
 * Create-or-update by name for the prisoners (single case rebuilt); create-or-
 * update by slug for the archive records. Idempotent.
 */
final class AddCpsWwiObjectors extends Command
{
    protected $signature = 'prisoners:add-cps-wwi-objectors';

    protected $description = 'Enrich/add the four CPS-archive WWI COs (Charles, Hartzler, Miller, Voth) and register their oral-history PDFs in the archive';

    public function handle(): int
    {
        $leavenworth = Institution::firstOrCreate(
            ['name' => 'United States Disciplinary Barracks, Fort Leavenworth'],
            ['city' => 'Fort Leavenworth', 'state' => 'Kansas']
        )->id;

        $people = [
            [
                'name' => 'Harry L. Charles', 'first' => 'Harry', 'middle' => 'L.', 'last' => 'Charles',
                'state' => 'Oklahoma', 'affiliation' => ['Religious Society of Friends (Quaker)'],
                'birth' => null, 'inst' => null,
                'bio' => 'Harry L. Charles was a Quaker from Woodward County, Oklahoma — a member of the Religious Society of Friends since April 5, 1917 — who was drafted in 1917 and held as a conscientious objector at Camp Travis, Texas, and Camp Riley. At Camp Travis he and his fellow objectors made clear to the officers that any work they did was under protest, and they refused to drill or to work on Sundays; at one point 47 of 61 objectors refused to do any work under military control. On June 7, 1918 a general court-martial convened at Camp Travis to try Charles together with forty-one other conscientious objectors, all charged under the 64th Article of War with disobeying the order to wear the uniform. Charles spoke on behalf of the forty-one defendants. Though absolute in his refusal of military control, he expressed willingness to perform noncombatant relief work such as YMCA or American Friends Service Committee reconstruction. The proceedings of his court-martial are preserved in the Schowalter Oral History Collection (Henry J. Becker file) of the Mennonite Central Committee\'s Civilian Public Service archive.',
                'charges' => 'Violation of the 64th Article of War — refusing the order to wear the uniform, as a Quaker conscientious objector (Camp Travis, Texas).',
                'convicted' => 'Court-martialed at Camp Travis on June 7, 1918, together with 41 conscientious objectors; Charles spoke on behalf of the group.',
                'sentence' => 'Tried by general court-martial at Camp Travis for refusing to wear the uniform; held at Camp Travis and Camp Riley as a conscientious objector.',
                'incarceration' => [1918, 6, 7],
            ],
            [
                'name' => 'Jesse D. Hartzler', 'first' => 'Jesse', 'middle' => 'D.', 'last' => 'Hartzler',
                'state' => 'Missouri', 'affiliation' => ['Mennonite'], 'birth' => [1897, 5, 15],
                'inst' => $leavenworth,
                'bio' => 'Jesse D. Hartzler was a Mennonite conscientious objector from Missouri who was drafted and arrived at Camp MacArthur, Texas, on September 7, 1918. Refusing on religious grounds to wear the uniform or to obey military orders, he was subjected to a mock trial, beaten, and put on a bread-and-water diet, though he also recalled sympathy and kindness from some of the soldiers and officers. Court-martialed in November 1918, he was sentenced to five years of hard labor and sent to the Fort Leavenworth disciplinary barracks; he was released on January 7, 1919, after the Armistice. He recounted the experience in a taped interview with Roger Golden on December 6, 1968, preserved in the Mennonite Central Committee\'s Civilian Public Service oral-history archive.',
                'charges' => 'Refusing to wear the uniform or obey orders as a Mennonite conscientious objector (Camp MacArthur, Texas).',
                'convicted' => 'Yes — court-martialed in November 1918.',
                'sentence' => 'Five years of hard labor at the Fort Leavenworth disciplinary barracks; released January 7, 1919.',
                'incarceration' => [1918, 11, 16], 'release' => [1919, 1, 7],
            ],
            [
                'name' => 'George Samuel Miller', 'first' => 'George', 'middle' => 'Samuel', 'last' => 'Miller',
                'aka' => 'George S. Miller',
                'state' => 'Nebraska', 'affiliation' => ['Mennonite'], 'birth' => null,
                'inst' => $leavenworth,
                'bio' => 'George Samuel Miller was a Mennonite conscientious objector who was inducted at Camp Dodge, Iowa, on July 23, 1918. Non-commissioned officers in his company treated him harshly — in August 1918 a corporal broke his nose for refusing to do kitchen work. He was court-martialed on Armistice Day, November 11, 1918, and sentenced to fifteen years; on Christmas Eve he was sent to the disciplinary barracks at Fort Leavenworth, Kansas, where he spent time in solitary confinement for refusing to work. He was discharged on May 1, 1919. He later lived in Wellman, Iowa, and recounted his experience in a taped interview with Roger Golden on December 18, 1968, preserved in the Mennonite Central Committee\'s Civilian Public Service oral-history archive.',
                'charges' => 'Refusing military service and labor as a Mennonite conscientious objector (Camp Dodge, Iowa).',
                'convicted' => 'Yes — court-martialed on Armistice Day, November 11, 1918.',
                'sentence' => 'Fifteen years; sent to the Fort Leavenworth disciplinary barracks on Christmas Eve 1918, held in solitary confinement for refusing to work; discharged May 1, 1919.',
                'incarceration' => [1918, 12, 24], 'release' => [1919, 5, 1],
            ],
            [
                'name' => 'Cornelius Voth', 'first' => 'Cornelius', 'last' => 'Voth',
                'state' => 'Kansas', 'affiliation' => ['Mennonite'], 'birth' => [1895, 11, 22],
                'inst' => $leavenworth,
                'bio' => 'Cornelius Voth was a Mennonite conscientious objector from Kansas who was drafted on August 5, 1918, and sent to Camp Funston. He refused to sign the army entrance card on the grounds that he was not a soldier, refused to wear the uniform, and declined all work under military control. He firmly believed he was completely within the law in refusing military orders, because he would have been willing to accept a farm furlough. Court-martialed on October 31, 1918, he was sentenced to twenty-five years and imprisoned at Fort Leavenworth, Kansas; he was released on January 27, 1919. He recounted his experience in a taped interview with James C. Juhnke on June 9, 1968, preserved in the Mennonite Central Committee\'s Civilian Public Service oral-history archive.',
                'charges' => 'Refusing to sign the army entrance card, wear the uniform, or perform work under military control, as a Mennonite conscientious objector (Camp Funston, Kansas).',
                'convicted' => 'Yes — court-martialed on October 31, 1918.',
                'sentence' => 'Twenty-five years at the Fort Leavenworth disciplinary barracks; released January 27, 1919.',
                'incarceration' => [1918, 10, 31], 'release' => [1919, 1, 27],
            ],
        ];

        DB::transaction(function () use ($people) {
            foreach ($people as $p) {
                $prisoner = Prisoner::withUnderReview()->where('name', $p['name'])->first()
                    ?? new Prisoner(['name' => $p['name']]);

                $prisoner->fill([
                    'name' => $p['name'],
                    'first_name' => $p['first'],
                    'middle_name' => $p['middle'] ?? null,
                    'last_name' => $p['last'],
                    'aka' => $p['aka'] ?? null,
                    'gender' => 'Male',
                    'race' => 'White',
                    'state' => $p['state'] ?? null,
                    'era' => '1910s',
                    'ideologies' => ['Pacifism', 'Conscientious objection'],
                    'affiliation' => $p['affiliation'] ?? [],
                    'description' => $p['bio'],
                    'in_custody' => false,
                    'released' => true,
                    'in_exile' => false,
                    'awaiting_trial' => false,
                ]);
                if (! empty($p['birth'])) {
                    $prisoner->setPartialDate('birthdate', ...$p['birth']);
                }
                $prisoner->save();

                $prisoner->cases()->delete();
                $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->fill([
                    'prisoner_id' => $prisoner->id,
                    'institution_id' => $p['inst'] ?? null,
                    'charges' => $p['charges'],
                    'convicted' => $p['convicted'],
                    'sentence' => $p['sentence'],
                ]);
                if (! empty($p['incarceration'])) {
                    $case->setPartialDate('incarceration_date', ...$p['incarceration']);
                }
                if (! empty($p['release'])) {
                    $case->setPartialDate('release_date', ...$p['release']);
                }
                $case->save();

                $this->info(($prisoner->wasRecentlyCreated ? 'Added: ' : 'Filled: ').$prisoner->name.' (slug: '.$prisoner->slug.')');
            }
        });

        // Register the primary-source PDFs in the archive (not attached to the
        // prisoner records). Files live under public/pdfs/cps-wwi-co/.
        $records = [
            [
                'slug' => 'cps-wwi-court-martial-harry-l-charles-1918',
                'title' => 'Court-Martial of Harry L. Charles — Camp Travis, Texas (June 1918)',
                'description' => 'Excerpts from the proceedings of a general court-martial convened at Camp Travis, Texas, on June 7, 1918, at which Harry L. Charles and 41 conscientious objectors were tried under the 64th Article of War for refusing the order to wear the uniform. Charles, a Quaker from Woodward County, Oklahoma, spoke on behalf of the forty-one defendants. From the Schowalter Oral History Collection (Henry J. Becker file). Sourcebook, pp. 69–79.',
                'source_format' => 'court-martial transcript',
                'file' => '/pdfs/cps-wwi-co/HarryLCharles.pdf',
                'date' => '1918-06-07',
            ],
            [
                'slug' => 'cps-wwi-oral-history-jesse-d-hartzler',
                'title' => 'Oral History Interview — Jesse D. Hartzler (WWI Conscientious Objector)',
                'description' => 'Transcript of a taped interview with Jesse D. Hartzler, a Mennonite conscientious objector, conducted by Roger Golden on December 6, 1968. Hartzler was drafted, arrived at Camp MacArthur, Texas on September 7, 1918, was given a mock trial, beaten and put on bread and water, court-martialed in November, sent to Fort Leavenworth, and released January 7, 1919. Sourcebook, pp. 151–160.',
                'source_format' => 'oral history',
                'file' => '/pdfs/cps-wwi-co/JesseDHartzler.pdf',
                'date' => '1968-12-06',
            ],
            [
                'slug' => 'cps-wwi-oral-history-george-s-miller',
                'title' => 'Oral History Interview — George S. Miller (WWI Conscientious Objector)',
                'description' => 'Transcript of a taped interview with George S. Miller, a Mennonite conscientious objector, conducted by Roger Golden on December 18, 1968. Miller was inducted at Camp Dodge, Iowa on July 23, 1918, treated harshly by non-commissioned officers, court-martialed on Armistice Day, sent to the disciplinary barracks at Fort Leavenworth on Christmas Eve, held in solitary confinement for refusing to work, and discharged May 1, 1919. Sourcebook, pp. 161–169.',
                'source_format' => 'oral history',
                'file' => '/pdfs/cps-wwi-co/GeorgeSMiller.pdf',
                'date' => '1968-12-18',
            ],
            [
                'slug' => 'cps-wwi-oral-history-cornelius-voth',
                'title' => 'Oral History Interview — Cornelius Voth (WWI Conscientious Objector)',
                'description' => 'Transcript of a taped interview with Cornelius Voth, a Mennonite conscientious objector, conducted by James C. Juhnke on June 9, 1968. Voth was drafted August 5, 1918, refused military orders (believing he was within the law because he would have accepted a farm furlough), was court-martialed October 31, 1918, and sent to Fort Leavenworth. Sourcebook, pp. 5–29.',
                'source_format' => 'oral history',
                'file' => '/pdfs/cps-wwi-co/CorneliusVoth.pdf',
                'date' => '1968-06-09',
            ],
        ];

        foreach ($records as $r) {
            $payload = [
                'title' => $r['title'],
                'description' => $r['description'],
                'record_type' => 'document',
                'source_format' => $r['source_format'],
                'file' => $r['file'],
                'collection' => 'Mennonite Central Committee — Civilian Public Service Archive',
                'publisher' => 'Mennonite Central Committee',
                'year' => 1918,
                'date' => $r['date'],
                'subjects' => ['Conscientious Objectors', 'World War I', 'Pacifism', 'Historic Peace Churches'],
                'is_digitized' => true,
                'published' => true,
            ];

            $existing = ArchiveRecord::where('slug', $r['slug'])->first();
            if ($existing) {
                $existing->update($payload);
                $this->info('Archive updated: '.$r['title']);
            } else {
                ArchiveRecord::create(['slug' => $r['slug']] + $payload);
                $this->info('Archive added: '.$r['title']);
            }
        }

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
