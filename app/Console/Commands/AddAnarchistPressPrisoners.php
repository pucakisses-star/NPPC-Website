<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Political prisoners surfaced from Ernesto A. Longa's "Anarchist Periodicals in
 * English Published in the United States (1833–1955): An Annotated Guide"
 * (Scarecrow Press, 2010) — editors and writers jailed for their press and
 * speech, mostly under the Comstock obscenity laws, criminal-anarchy statutes,
 * or the draft laws. Only people not already in the database are included
 * (Ricardo Flores Magón, D. M. Bennett, Ezra Heywood, Ammon Hennacy, Oscar
 * Neebe and Librado Rivera were already present and are skipped).
 *
 * Each entry pairs the sentence the Guide documents (cited to court cases where
 * it gives them) with well-established biography. Where an exact date is
 * uncertain it is set to year precision or left out rather than invented.
 * Idempotent — skips any name already present, so it is safe to re-run.
 */
class AddAnarchistPressPrisoners extends Command
{
    protected $signature = 'prisoners:add-anarchist-press-prisoners';

    protected $description = 'Add anarchist editors/writers jailed for their press, from Longa\'s Anarchist Periodicals guide';

    public function handle(): int
    {
        foreach ($this->people() as $p) {
            $this->addOne($p);
        }

        return self::SUCCESS;
    }

    private function addOne(array $p): void
    {
        DB::transaction(function () use ($p) {
            if (Prisoner::where('name', $p['name'])->exists()) {
                $this->warn('Skipped (already exists): '.$p['name']);

                return;
            }

            $prisoner = Prisoner::create(array_merge([
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'awaiting_trial' => false,
            ], $p['fields']));

            if (isset($p['birthYear'])) {
                $prisoner->setPartialDate('birthdate', $p['birthYear']);
            }
            if (isset($p['deathYear'])) {
                $prisoner->setPartialDate('death_date', $p['deathYear']);
            }
            if (isset($p['birthdate'])) {
                $prisoner->birthdate = $p['birthdate'];
            }
            if (isset($p['death_date'])) {
                $prisoner->death_date = $p['death_date'];
            }
            $prisoner->save();

            $case = $p['case'];
            if (isset($case['institution'])) {
                $inst = Institution::firstOrCreate(
                    ['name' => $case['institution']['name']],
                    ['city' => $case['institution']['city'] ?? null, 'state' => $case['institution']['state'] ?? null]
                );
                $case['institution_id'] = $inst->id;
            }
            unset($case['institution']);
            $case['prisoner_id'] = $prisoner->id;
            PrisonerCase::create($case);

            $this->info('Added: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
        });
    }

    private function people(): array
    {
        return [
            [
                'name' => 'Johann Most',
                'birthdate' => '1846-02-05',
                'death_date' => '1906-03-17',
                'fields' => [
                    'first_name' => 'Johann', 'last_name' => 'Most',
                    'gender' => 'Male', 'race' => 'White', 'state' => 'New York', 'era' => '1900s',
                    'ideologies' => ['Anarchism'], 'affiliation' => ['Freiheit'],
                    'description' => 'Johann Most (1846–1906) was a German-American anarchist and the editor of the newspaper Freiheit, an advocate of "propaganda of the deed" whose writings influenced a generation of radicals. He was imprisoned repeatedly on both sides of the Atlantic. In the United States he served a year on Blackwell\'s Island in 1887 for an incendiary speech, and after the assassination of President McKinley he was again prosecuted: in People v. Most (1902) he was convicted of endangering the public peace and sentenced to one year in prison for republishing a fifty-year-old article by Karl Heinzen advocating the assassination of political rulers.',
                ],
                'case' => [
                    'institution' => ['name' => "Blackwell's Island Penitentiary", 'city' => 'New York', 'state' => 'New York'],
                    'charges' => 'Endangering the public peace — for republishing in Freiheit a fifty-year-old article by Karl Heinzen advocating the assassination of political rulers, days after the assassination of President McKinley (People v. Most, 75 N.Y.S. 591, 1902).',
                    'convicted' => 'Yes — convicted; conviction upheld by the New York courts.',
                    'sentence' => 'One year in prison. (He had also served a year on Blackwell\'s Island in 1887 for an earlier speech.)',
                ],
            ],
            [
                'name' => 'Moses Harman',
                'birthYear' => 1830,
                'deathYear' => 1910,
                'fields' => [
                    'first_name' => 'Moses', 'last_name' => 'Harman',
                    'gender' => 'Male', 'race' => 'White', 'state' => 'Illinois', 'era' => '1900s',
                    'ideologies' => ['Anarchism', 'Free speech'], 'affiliation' => ['Lucifer, the Light-Bearer'],
                    'description' => 'Moses Harman (1830–1910) was a free-thought and individualist-anarchist editor who published Lucifer, the Light-Bearer, a paper devoted to free speech, women\'s emancipation, and sexual freedom. He was prosecuted repeatedly under the Comstock obscenity laws for what he printed about marriage and the body. In 1905, at the age of seventy-five, he was sentenced to a year of hard labor breaking rocks at the Joliet penitentiary in Illinois.',
                ],
                'case' => [
                    'institution' => ['name' => 'Joliet Penitentiary', 'city' => 'Joliet', 'state' => 'Illinois'],
                    'charges' => 'Sending "obscene" material through the mails (Comstock Act) — for articles in Lucifer, the Light-Bearer on marriage, the body, and sexual freedom.',
                    'convicted' => 'Yes — one of several Comstock convictions across his career as editor of Lucifer.',
                    'sentence' => 'One year of hard labor at the Joliet penitentiary, imposed in 1905 when he was seventy-five years old.',
                ],
            ],
            [
                'name' => 'Ida Craddock',
                'birthdate' => '1857-08-01',
                'death_date' => '1902-10-16',
                'fields' => [
                    'first_name' => 'Ida', 'middle_name' => 'C.', 'last_name' => 'Craddock',
                    'gender' => 'Female', 'race' => 'White', 'state' => 'New York', 'era' => '1900s',
                    'ideologies' => ['Free speech'], 'affiliation' => [],
                    'description' => 'Ida C. Craddock (1857–1902) was an American sex educator, freethinker, and advocate of marriage reform who wrote frank instructional pamphlets on sexuality. She was repeatedly prosecuted for "obscenity." In 1902 she was arrested by Anthony Comstock himself for distributing her pamphlet "The Wedding Night" through the mail and sentenced to five years in prison. Rather than return to prison she took her own life on October 16, 1902, leaving a public letter denouncing Comstockery that became a landmark document of the free-speech movement.',
                ],
                'case' => [
                    'charges' => 'Distributing "obscene" literature through the mail (Comstock Act) — her sex-education pamphlet "The Wedding Night." Arrested by Anthony Comstock in person.',
                    'convicted' => 'Yes — convicted in federal court in 1902.',
                    'sentence' => 'Five years in prison. She committed suicide on October 16, 1902 rather than serve the sentence.',
                    'death_in_custody_date' => null,
                ],
            ],
            [
                'name' => 'William Buwalda',
                'fields' => [
                    'first_name' => 'William', 'last_name' => 'Buwalda',
                    'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'era' => '1900s',
                    'ideologies' => ['Free speech'], 'affiliation' => [],
                    'description' => 'William Buwalda was a United States Army soldier with some fifteen years of service who, in 1908, attended a lecture by the anarchist Emma Goldman in San Francisco and shook her hand afterward. For that he was court-martialed and sentenced to five years in a military prison, and sent to Alcatraz. The sentence provoked a national free-speech outcry; it was commuted after he had served about ten months. Embittered by his treatment, Buwalda returned to the War Department the medal he had been awarded for his service in the Philippines.',
                ],
                'case' => [
                    'institution' => ['name' => 'Alcatraz Military Prison', 'city' => 'San Francisco', 'state' => 'California'],
                    'charges' => 'Military charges (conduct/association) — for attending an Emma Goldman lecture in San Francisco and shaking her hand while in uniform.',
                    'convicted' => 'Yes — court-martialed in 1908.',
                    'sentence' => 'Five years in military prison (Alcatraz); commuted after about ten months.',
                    'sentenced_date' => '1908-01-01',
                ],
            ],
            [
                'name' => 'Jay Fox',
                'birthYear' => 1870,
                'deathYear' => 1961,
                'fields' => [
                    'first_name' => 'Jay', 'last_name' => 'Fox',
                    'gender' => 'Male', 'race' => 'White', 'state' => 'Washington', 'era' => '1910s',
                    'ideologies' => ['Anarchism', 'Free speech'], 'affiliation' => ['The Agitator', 'Home Colony'],
                    'description' => 'Jay Fox (1870–1961) was an anarchist and labor organizer who edited The Agitator at the Home anarchist colony in Washington State. In 1911 he published an editorial, "The Nude and the Prudes," defending nude bathing at Home; he was arrested and convicted under a Washington statute for "encouraging or advocating disrespect for law." His appeal reached the U.S. Supreme Court, which upheld the conviction in Fox v. Washington (1915); he served a short jail term.',
                ],
                'case' => [
                    'charges' => 'Publishing matter tending to "encourage or advocate disrespect for law" — for the editorial "The Nude and the Prudes" in The Agitator, defending nude bathing at the Home colony.',
                    'convicted' => 'Yes — conviction upheld by the U.S. Supreme Court in Fox v. Washington, 236 U.S. 273 (1915).',
                    'sentence' => 'A short jail term (about two months).',
                    'arrest_date' => '1911-07-01',
                ],
            ],
            [
                'name' => 'Marcus Graham',
                'fields' => [
                    'first_name' => 'Marcus', 'last_name' => 'Graham',
                    'gender' => 'Male', 'race' => 'White', 'state' => 'New York', 'era' => '1910s',
                    'ideologies' => ['Anarchism'], 'affiliation' => ['Anarchist Soviet Bulletin', 'MAN!'],
                    'description' => 'Marcus Graham was an anarchist editor — of the Anarchist Soviet Bulletin (later Free Society) and, in the 1930s, the paper MAN! A Journal of the Anarchist Ideal and Movement. A Romanian-Jewish immigrant who refused on principle to disclose his birthplace or cooperate with immigration authorities, he was imprisoned on Ellis Island around 1919 in connection with issuing the Bulletin, and later faced deportation proceedings; he was ordered jailed for six months, or until he agreed to answer immigration inspectors\' questions about his age and birthplace.',
                ],
                'case' => [
                    'institution' => ['name' => 'Ellis Island Immigration Station', 'city' => 'New York', 'state' => 'New York'],
                    'charges' => 'Immigration/anarchist-exclusion detention — held for issuing the Anarchist Soviet Bulletin, and later jailed for refusing to answer immigration inspectors\' questions about his age and birthplace.',
                    'convicted' => 'Detained under the anarchist-exclusion and immigration laws; ordered jailed for contempt of the immigration inquiry.',
                    'sentence' => 'Imprisoned on Ellis Island (c. 1919); later ordered jailed six months, or until he agreed to answer the inspectors\' questions.',
                ],
            ],
            [
                'name' => 'Joseph R. Dunlop',
                'fields' => [
                    'first_name' => 'Joseph', 'middle_name' => 'R.', 'last_name' => 'Dunlop',
                    'gender' => 'Male', 'race' => 'White', 'state' => 'Illinois', 'era' => '1890s',
                    'ideologies' => ['Free speech'], 'affiliation' => ['Chicago Dispatch'],
                    'description' => 'Joseph R. Dunlop was the editor of the Chicago Dispatch. In the 1890s he was prosecuted under the Comstock postal-obscenity law and sentenced to two years in prison for circulating "obscene" literature through the mails — one of the era\'s many press prosecutions defended by the free-thought and anarchist papers.',
                ],
                'case' => [
                    'charges' => 'Circulating "obscene" literature through the mails (Comstock Act), as editor of the Chicago Dispatch.',
                    'convicted' => 'Yes.',
                    'sentence' => 'Two years in prison.',
                ],
            ],
            [
                'name' => 'Elmina Slenker',
                'birthYear' => 1827,
                'deathYear' => 1908,
                'fields' => [
                    'first_name' => 'Elmina', 'middle_name' => 'Drake', 'last_name' => 'Slenker',
                    'gender' => 'Female', 'race' => 'White', 'state' => 'Virginia', 'era' => '1880s',
                    'ideologies' => ['Free speech'], 'affiliation' => [],
                    'description' => 'Elmina Drake Slenker (1827–1908) was a freethought writer and correspondent in Snowville, Virginia, known for her frank views on sex and marriage. In 1887 she was ensnared by Anthony Comstock\'s decoy letters and arrested and tried for mailing "obscene" private letters — one of the most notorious Comstock cases against a woman of the free-thought press.',
                ],
                'case' => [
                    'charges' => 'Mailing "obscene" private letters (Comstock Act) — written in reply to the decoy letters of Comstock\'s agents.',
                    'convicted' => 'Arrested and tried in 1887 under the Comstock law.',
                    'sentence' => 'Prosecuted federally for obscenity; the case became a cause célèbre of the free-thought press.',
                    'arrest_date' => '1887-01-01',
                ],
            ],
            [
                'name' => 'T. R. Kinget',
                'fields' => [
                    'first_name' => 'T.', 'middle_name' => 'R.', 'last_name' => 'Kinget',
                    'gender' => 'Male', 'race' => 'White', 'state' => 'New York', 'era' => '1880s',
                    'ideologies' => ['Free speech'], 'affiliation' => [],
                    'description' => 'Dr. T. R. Kinget was a physician prosecuted under the Comstock laws in the late 1880s for selling his book "Medical Good Sense" and for recommending "prudential checks" (contraception) to families in his practice. He was sentenced to three months at the Blackwell\'s Island penitentiary — one of the many medical and free-thought figures jailed for circulating birth-control information.',
                ],
                'case' => [
                    'institution' => ['name' => "Blackwell's Island Penitentiary", 'city' => 'New York', 'state' => 'New York'],
                    'charges' => 'Comstock obscenity charge — for selling the book "Medical Good Sense" and recommending contraception ("prudential checks") in his medical practice.',
                    'convicted' => 'Yes.',
                    'sentence' => 'Three months at the Blackwell\'s Island penitentiary.',
                    'imprisoned_for_days' => null,
                ],
            ],
            [
                'name' => 'Mike Lindway',
                'fields' => [
                    'first_name' => 'Mike', 'last_name' => 'Lindway',
                    'gender' => 'Male', 'race' => 'White', 'state' => 'Ohio', 'era' => '1930s',
                    'ideologies' => ['Anarchism'], 'affiliation' => ['Industrial Workers of the World'],
                    'description' => 'Mike Lindway was an Industrial Workers of the World militant in Ohio whose 1936 prosecution was denounced by the anarchist press as a frame-up. He was convicted of possessing bombs and ammunition with intent to use them for unlawful purposes; his appeal was reported in State v. Lindway, 2 N.E. 2d 490 (Ohio 1936), and taken up to the U.S. Supreme Court.',
                ],
                'case' => [
                    'charges' => 'Possessing bombs and ammunition with intent to use them for unlawful purposes (State v. Lindway, 2 N.E. 2d 490, Ohio 1936).',
                    'convicted' => 'Yes — convicted in Ohio; the anarchist press called it a frame-up.',
                    'sentence' => 'Convicted 1936; conviction sustained on appeal.',
                ],
            ],
            [
                'name' => 'Sander Katz',
                'fields' => [
                    'first_name' => 'Sander', 'last_name' => 'Katz',
                    'gender' => 'Male', 'race' => 'White', 'state' => 'New York', 'era' => '1940s',
                    'ideologies' => ['Pacifism'], 'affiliation' => ['Alternative'],
                    'description' => 'Sander Katz was a pacifist and anarchist associated with the postwar libertarian magazine Alternative (published in New York from 1948). A draft resister, he refused to register under the Cold War conscription law and was sentenced to one year and one day in the federal penitentiary — one of the Peacemakers-movement draft refusers of the late 1940s.',
                ],
                'case' => [
                    'charges' => 'Refusing to register for the draft under the peacetime (Cold War) Selective Service law.',
                    'convicted' => 'Yes — convicted of draft refusal, 1948.',
                    'sentence' => 'One year and one day in the federal penitentiary.',
                    'imprisoned_for_days' => 366,
                ],
            ],
        ];
    }
}
