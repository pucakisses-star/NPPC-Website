<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * World War I conscientious objectors court-martialed and imprisoned in the
 * U.S. military-prison system — Fort Leavenworth (Kansas), Alcatraz, and the
 * Fort Douglas War Prison Barracks (Utah). Drawn from Anne Yoder's Swarthmore
 * College Peace Collection WWI CO database and the "Conscientious Objection &
 * the Great War" digital project, plus the standard histories (Stoltzfus,
 * "Pacifists in Chains"; Kohn, "Jailed for Peace").
 *
 * Includes the four Hutterite absolutists (Joseph and Michael Hofer died at
 * Leavenworth), seven named Molokan objectors, and the Jewish-socialist,
 * secular, and Christian absolutists behind the 1919 Leavenworth prison
 * strikes. Create-or-update by name (fills several existing stubs); rebuilds
 * each single case. Idempotent.
 */
class AddWwiLeavenworthObjectors extends Command
{
    protected $signature = 'prisoners:add-wwi-leavenworth-objectors';

    protected $description = 'Add WWI conscientious objectors imprisoned at Fort Leavenworth / Alcatraz / Fort Douglas';

    public function handle(): int
    {
        $leavenworth = Institution::firstOrCreate(['name' => 'United States Disciplinary Barracks, Fort Leavenworth'], ['city' => 'Fort Leavenworth', 'state' => 'Kansas']);

        $CO = 'Refusing military service as a conscientious objector during World War I — court-martialed for disobeying orders / refusing to wear the uniform.';
        $GUILTY = 'Yes — court-martialed and convicted.';

        $people = [
            // ---- The four Hutterite absolutists (Rockport Colony, South Dakota) ----
            [
                'name' => 'Joseph Hofer', 'first' => 'Joseph', 'last' => 'Hofer',
                'race' => 'White', 'state' => 'South Dakota', 'era' => '1910s',
                'death' => [1918, 11, 29],
                'ideologies' => ['Pacifism', 'Conscientious objection'], 'affiliation' => ['Hutterite'],
                'bio' => 'Joseph Hofer was one of four Hutterite absolutists from the Rockport Colony in South Dakota who, drafted in 1918, refused as a matter of faith to wear the uniform or perform any military service. Court-martialed and sentenced to twenty years hard labor, he was held at Alcatraz — where the men were tortured in "the hole" and hung by their wrists ("high-cuffing") — and transferred to the Fort Leavenworth Disciplinary Barracks on November 19, 1918. He died there on November 29, 1918 after brutal treatment (the Army recorded pneumonia); the Hutterites buried him as a martyr, and his death, with his brother Michael\'s, became the National Civil Liberties Bureau\'s "Exhibit A" of the mistreatment of objectors.',
                'charges' => 'Refusing all military service as a Hutterite conscientious objector during World War I.',
                'convicted' => 'Yes — court-martialed and sentenced to twenty years hard labor.',
                'sentence' => 'Twenty years hard labor; he died in custody at Fort Leavenworth on November 29, 1918, days after transfer from Alcatraz.',
                'died' => [1918, 11, 29], 'institution' => $leavenworth->id,
            ],
            [
                'name' => 'Michael Hofer', 'first' => 'Michael', 'last' => 'Hofer',
                'race' => 'White', 'state' => 'South Dakota', 'era' => '1910s',
                'death' => [1918, 12, 2],
                'ideologies' => ['Pacifism', 'Conscientious objection'], 'affiliation' => ['Hutterite'],
                'bio' => 'Michael Hofer, a Hutterite from the Rockport Colony in South Dakota and brother of Joseph Hofer, was one of the four Hutterite absolutists court-martialed in 1918 for refusing military service. Sentenced to twenty years hard labor, tortured at Alcatraz and transferred to Fort Leavenworth on November 19, 1918, he died there on December 2, 1918 — three days after his brother Joseph. Their bodies were returned to South Dakota dressed in the uniforms they had refused to wear.',
                'charges' => 'Refusing all military service as a Hutterite conscientious objector during World War I.',
                'convicted' => 'Yes — court-martialed and sentenced to twenty years hard labor.',
                'sentence' => 'Twenty years hard labor; he died in custody at Fort Leavenworth on December 2, 1918.',
                'died' => [1918, 12, 2], 'institution' => $leavenworth->id,
            ],
            [
                'name' => 'David Hofer', 'first' => 'David', 'last' => 'Hofer',
                'race' => 'White', 'state' => 'South Dakota', 'era' => '1910s',
                'ideologies' => ['Pacifism', 'Conscientious objection'], 'affiliation' => ['Hutterite'],
                'bio' => 'David Hofer, a Hutterite from the Rockport Colony in South Dakota, was one of four brothers-in-faith court-martialed in 1918 and sentenced to twenty years hard labor for refusing military service. Held at Alcatraz and Fort Leavenworth, he survived the ordeal that killed his brothers Joseph and Michael and was released soon after their deaths, giving the surviving testimony of their treatment.',
                'charges' => 'Refusing all military service as a Hutterite conscientious objector during World War I.',
                'convicted' => 'Yes — court-martialed and sentenced to twenty years hard labor.',
                'sentence' => 'Twenty years hard labor; released from Fort Leavenworth in December 1918, soon after his brothers\' deaths.',
                'incarceration' => [1918], 'release' => [1918, 12], 'institution' => $leavenworth->id,
            ],
            [
                'name' => 'Jacob Wipf', 'first' => 'Jacob', 'last' => 'Wipf',
                'race' => 'White', 'state' => 'South Dakota', 'era' => '1910s',
                'birth' => [1888, 9, 28],
                'ideologies' => ['Pacifism', 'Conscientious objection'], 'affiliation' => ['Hutterite'],
                'bio' => 'Jacob Wipf, a Hutterite from the Rockport Colony in South Dakota and brother-in-law of the Hofers, was the fourth of the Hutterite absolutists court-martialed in 1918 and sentenced to twenty years hard labor. Tortured at Alcatraz and imprisoned at Fort Leavenworth, he outlived the two Hofer brothers who died there and was released in the spring of 1919. The four men\'s ordeal spurred a wave of Hutterite emigration to Canada.',
                'charges' => 'Refusing all military service as a Hutterite conscientious objector during World War I.',
                'convicted' => 'Yes — court-martialed and sentenced to twenty years hard labor.',
                'sentence' => 'Twenty years hard labor; released from Fort Leavenworth on April 13, 1919.',
                'incarceration' => [1918, 5, 28], 'release' => [1919, 4, 13], 'institution' => $leavenworth->id,
            ],

            // ---- Molokan absolutists (Glendale, Arizona community) ----
            [
                'name' => 'J. D. Conovaloff', 'first' => 'J. D.', 'last' => 'Conovaloff',
                'race' => 'White', 'state' => 'Arizona', 'era' => '1910s',
                'ideologies' => ['Pacifism', 'Conscientious objection'], 'affiliation' => ['Molokan'],
                'bio' => 'J. D. Conovaloff was one of six Molokan absolutists from the Glendale, Arizona community — Russian sectarian pacifists — who refused to register or serve under the 1917 draft. Routed through Fort Huachuca and the Fort Riley Disciplinary Barracks, he was court-martialed and sent to Fort Leavenworth under a twenty-five-year sentence, and was amnestied by President Wilson in April 1919.',
                'charges' => 'Refusing to register or serve as a Molokan conscientious objector during World War I.',
                'convicted' => 'Yes — court-martialed; sentenced to twenty-five years.',
                'sentence' => 'Twenty-five years at Fort Leavenworth; amnestied by President Wilson in April 1919.',
                'incarceration' => [1918], 'release' => [1919, 4], 'institution' => $leavenworth->id,
            ],
            [
                'name' => 'F. F. Wren', 'first' => 'F. F.', 'last' => 'Wren',
                'race' => 'White', 'state' => 'Arizona', 'era' => '1910s',
                'ideologies' => ['Pacifism', 'Conscientious objection'], 'affiliation' => ['Molokan'],
                'bio' => 'F. F. Wren was one of the six Molokan absolutists from Glendale, Arizona who refused the WWI draft. Court-martialed after passing through Fort Huachuca and Fort Riley, he was imprisoned at Fort Leavenworth on a twenty-five-year sentence and amnestied by President Wilson in April 1919.',
                'charges' => 'Refusing to register or serve as a Molokan conscientious objector during World War I.',
                'convicted' => 'Yes — court-martialed; sentenced to twenty-five years.',
                'sentence' => 'Twenty-five years at Fort Leavenworth; amnestied in April 1919.',
                'incarceration' => [1918], 'release' => [1919, 4], 'institution' => $leavenworth->id,
            ],
            [
                'name' => 'A. F. Shubin', 'first' => 'A. F.', 'last' => 'Shubin',
                'race' => 'White', 'state' => 'Arizona', 'era' => '1910s',
                'ideologies' => ['Pacifism', 'Conscientious objection'], 'affiliation' => ['Molokan'],
                'bio' => 'A. F. Shubin was one of the six Molokan absolutists from Glendale, Arizona who refused the WWI draft. Court-martialed via Fort Huachuca and Fort Riley, he was imprisoned at Fort Leavenworth on a fifteen-year sentence and amnestied by President Wilson in April 1919.',
                'charges' => 'Refusing to register or serve as a Molokan conscientious objector during World War I.',
                'convicted' => 'Yes — court-martialed; sentenced to fifteen years.',
                'sentence' => 'Fifteen years at Fort Leavenworth; amnestied in April 1919.',
                'incarceration' => [1918], 'release' => [1919, 4], 'institution' => $leavenworth->id,
            ],
            [
                'name' => 'Ivan Kulikoff', 'first' => 'Ivan', 'middle' => 'W.', 'last' => 'Kulikoff',
                'race' => 'White', 'state' => 'Arizona', 'era' => '1910s',
                'ideologies' => ['Pacifism', 'Conscientious objection'], 'affiliation' => ['Molokan'],
                'bio' => 'Ivan W. Kulikoff was one of the six Molokan absolutists from Glendale, Arizona who refused the WWI draft. Court-martialed after Fort Huachuca and Fort Riley, he served a fifteen-year sentence at Fort Leavenworth and was amnestied by President Wilson in April 1919.',
                'charges' => 'Refusing to register or serve as a Molokan conscientious objector during World War I.',
                'convicted' => 'Yes — court-martialed; sentenced to fifteen years.',
                'sentence' => 'Fifteen years at Fort Leavenworth; amnestied in April 1919.',
                'incarceration' => [1918], 'release' => [1919, 4], 'institution' => $leavenworth->id,
            ],
            [
                'name' => 'Ivan Sussoyeff', 'first' => 'Ivan', 'middle' => 'W.', 'last' => 'Sussoyeff', 'aka' => 'Ivan Sussoff',
                'race' => 'White', 'state' => 'Arizona', 'era' => '1910s',
                'ideologies' => ['Pacifism', 'Conscientious objection'], 'affiliation' => ['Molokan'],
                'bio' => 'Ivan W. Sussoyeff (also rendered Sussoff) was one of the six Molokan absolutists from Glendale, Arizona who refused the WWI draft. He left a documented account of being tortured at Fort Riley — "they dragged me like an animal with a rope around my neck." Court-martialed and imprisoned at Fort Leavenworth on a fifteen-year sentence, he was amnestied by President Wilson in April 1919.',
                'charges' => 'Refusing to register or serve as a Molokan conscientious objector during World War I.',
                'convicted' => 'Yes — court-martialed; sentenced to fifteen years.',
                'sentence' => 'Fifteen years; held at Fort Riley and Fort Leavenworth; amnestied in April 1919.',
                'incarceration' => [1918], 'release' => [1919, 4], 'institution' => $leavenworth->id,
            ],
            [
                'name' => 'Morris E. Shubin', 'first' => 'Morris', 'middle' => 'E.', 'last' => 'Shubin',
                'race' => 'White', 'state' => 'Arizona', 'era' => '1910s',
                'ideologies' => ['Pacifism', 'Conscientious objection'], 'affiliation' => ['Molokan'],
                'bio' => 'Morris E. Shubin was one of the six Molokan absolutists from Glendale, Arizona who refused the WWI draft. Court-martialed via Fort Huachuca and Fort Riley, he served a fifteen-year sentence at Fort Leavenworth and was amnestied by President Wilson in April 1919.',
                'charges' => 'Refusing to register or serve as a Molokan conscientious objector during World War I.',
                'convicted' => 'Yes — court-martialed; sentenced to fifteen years.',
                'sentence' => 'Fifteen years at Fort Leavenworth; amnestied in April 1919.',
                'incarceration' => [1918], 'release' => [1919, 4], 'institution' => $leavenworth->id,
            ],
            [
                'name' => 'M. J. Bolotin', 'first' => 'M. J.', 'last' => 'Bolotin',
                'race' => 'White', 'state' => 'California', 'era' => '1910s',
                'ideologies' => ['Pacifism', 'Conscientious objection'], 'affiliation' => ['Molokan'],
                'bio' => 'M. J. Bolotin was a naturalized Molokan conscientious objector whose separate WWI case ended in a twelve-year sentence, served at Fort MacArthur in San Pedro, California. He was among the Molokan absolutists amnestied after the war.',
                'charges' => 'Refusing military service as a Molokan conscientious objector during World War I.',
                'convicted' => 'Yes — court-martialed; sentenced to twelve years.',
                'sentence' => 'Twelve years at Fort MacArthur, San Pedro, California; amnestied after the war.',
                'incarceration' => [1918], 'release' => [1919],
            ],

            // ---- Jewish-socialist, secular, and Christian absolutists ----
            [
                'name' => 'Howard Moore', 'first' => 'Howard', 'middle' => 'W.', 'last' => 'Moore', 'aka' => 'Howard Wilbur Moore',
                'race' => 'White', 'state' => 'New York', 'era' => '1910s',
                'ideologies' => ['Pacifism', 'Anti-War', 'Conscientious objection', 'Socialism'],
                'bio' => 'Howard Wilbur Moore of Cherry Valley, New York, was an atheist and socialist absolutist who grounded his refusal in liberty of conscience. One of the "Fort Riley four," he refused the uniform, was court-martialed, and was sent to Fort Leavenworth around Armistice Day 1918 and later the Fort Douglas War Prison Barracks. Sentenced to five years (reduced to three), he refused prison work and was punished with extended solitary confinement and shackling. Among the last COs released (December 1919), he told his story in the memoir "Plowing My Own Furrow."',
                'charges' => $CO, 'convicted' => $GUILTY,
                'sentence' => 'Five years, reduced to three; held at Fort Leavenworth and Fort Douglas, Utah, and released in December 1919.',
                'incarceration' => [1918, 11], 'release' => [1919, 12],
            ],
            [
                'name' => 'Harold Studley Gray', 'first' => 'Harold', 'middle' => 'Studley', 'last' => 'Gray',
                'race' => 'White', 'state' => 'Michigan', 'era' => '1910s',
                'birth' => [1894, 2, 23], 'death' => [1972],
                'ideologies' => ['Pacifism', 'Conscientious objection'],
                'bio' => 'Harold Studley Gray (1894–1972) of Detroit was a Christian absolutist whose pacifism crystallized while doing YMCA work in a German prisoner-of-war camp. Charged with disobeying orders at Camp Custer and Leavenworth, he was sentenced to twenty-five years (reduced to three), arriving at Fort Leavenworth on July 18, 1918 and later held at Alcatraz. His sentence was remitted on August 19, 1919. His letters were published as the memoir "Character Bad: The Story of a Conscientious Objector."',
                'charges' => $CO, 'convicted' => $GUILTY,
                'sentence' => 'Twenty-five years, reduced to three; held at Fort Leavenworth and Alcatraz; sentence remitted August 19, 1919.',
                'incarceration' => [1918, 7, 18], 'release' => [1919, 8, 19],
            ],
            [
                'name' => 'David Eichel', 'first' => 'David', 'last' => 'Eichel',
                'race' => 'White', 'state' => 'New York', 'era' => '1910s',
                'birth' => [1894], 'death' => [1956],
                'ideologies' => ['Pacifism', 'Socialism', 'Conscientious objection'],
                'bio' => 'David Eichel (1894–1956) was a Jewish socialist absolutist from New York\'s Lower East Side, one of three Eichel brothers who resisted the WWI draft on socialist and humanitarian grounds. Arraigned as a deserter, he was sentenced to thirty years hard labor (reduced to two and a half). He was held in the Tombs, Camp Upton, Fort Riley, Fort Leavenworth, and the Fort Douglas War Prison Barracks, where he was released on May 1, 1920.',
                'charges' => $CO, 'convicted' => $GUILTY,
                'sentence' => 'Thirty years hard labor, reduced to two and a half; held at Fort Leavenworth and Fort Douglas, Utah, and released May 1, 1920.',
                'incarceration' => [1918], 'release' => [1920, 5, 1],
            ],
            [
                'name' => 'Julius Eichel', 'first' => 'Julius', 'last' => 'Eichel',
                'race' => 'White', 'state' => 'New York', 'era' => '1910s',
                'birth' => [1896], 'death' => [1989],
                'ideologies' => ['Pacifism', 'Socialism', 'Conscientious objection'],
                'bio' => 'Julius Eichel (1896–1989), David\'s brother, was a Jewish socialist absolutist imprisoned during World War I after refusing the draft. Held in the Tombs, at Fort Jay on Governor\'s Island, and at Fort Leavenworth, he joined the December 1918 delegation that pressed Secretary of War Baker on behalf of imprisoned objectors and was released in 1920. He spent the rest of his life as a pacifist organizer, editing "The Absolutist" and helping found Friends and Families of Imprisoned Conscientious Objectors.',
                'charges' => $CO, 'convicted' => $GUILTY,
                'sentence' => 'Imprisoned at Fort Jay and Fort Leavenworth; released in 1920.',
                'incarceration' => [1918], 'release' => [1920],
            ],
            [
                'name' => 'Roderick Seidenberg', 'first' => 'Roderick', 'last' => 'Seidenberg',
                'race' => 'White', 'state' => 'New York', 'era' => '1910s',
                'birth' => [1889], 'death' => [1973],
                'ideologies' => ['Pacifism', 'Anti-War', 'Conscientious objection'],
                'bio' => 'Roderick Seidenberg (1889–1973), a German-born New York architect and writer, was a secular absolutist who objected on grounds of liberty of conscience. Charged with disobeying orders, he was sentenced to twenty-five years (reduced to a year and a half) and held at Camp Upton, Fort Riley, and Fort Leavenworth from 1918 to 1920. He later recounted his stand in the essay "I Refuse to Serve."',
                'charges' => $CO, 'convicted' => $GUILTY,
                'sentence' => 'Twenty-five years, reduced to eighteen months; held at Fort Leavenworth, 1918–1920.',
                'incarceration' => [1918], 'release' => [1920],
            ],
            [
                'name' => 'Carl Haessler', 'first' => 'Carl', 'last' => 'Haessler',
                'race' => 'White', 'state' => 'Illinois', 'era' => '1910s',
                'birth' => [1888], 'death' => [1972, 12, 2],
                'ideologies' => ['Pacifism', 'Anti-War', 'Socialism', 'Conscientious objection'],
                'bio' => 'Carl Haessler (1888–1972) was a Rhodes Scholar and University of Illinois philosophy professor who joined the Socialist Party and refused to fight "for a capitalist country." Accepting induction but refusing the uniform, he was court-martialed and imprisoned at Fort Leavenworth and Alcatraz from June 1918 until a presidential pardon in August 1920. He was a leader of the January 1919 Fort Leavenworth prisoners\' work strike and later a prominent labor journalist with the Federated Press.',
                'charges' => $CO, 'convicted' => $GUILTY,
                'sentence' => 'Imprisoned at Fort Leavenworth and Alcatraz, June 1918 – August 1920 (released by presidential pardon).',
                'incarceration' => [1918, 6], 'release' => [1920, 8],
            ],
            [
                'name' => 'Erling Lunde', 'first' => 'Erling', 'middle' => 'H.', 'last' => 'Lunde',
                'race' => 'White', 'state' => 'Illinois', 'era' => '1910s',
                'ideologies' => ['Pacifism', 'Anti-War', 'Conscientious objection'],
                'bio' => 'Erling H. Lunde of Chicago was a conscientious objector court-martialed at Camp Funston, Kansas on October 15, 1918 and imprisoned at Fort Leavenworth (and its military hospital) and the Fort Douglas War Prison Barracks. His prison writings, "Letters from a Political Prisoner in a Military Hospital U.S.A.," documented the July 1919 Fort Leavenworth uprising and the brutal treatment of the Hutterite objectors.',
                'charges' => $CO, 'convicted' => $GUILTY,
                'sentence' => 'Court-martialed October 15, 1918; imprisoned at Fort Leavenworth and Fort Douglas, Utah.',
                'incarceration' => [1918, 10, 15], 'release' => [1919],
            ],
            [
                'name' => 'Brent Dow Allinson', 'first' => 'Brent', 'middle' => 'Dow', 'last' => 'Allinson',
                'race' => 'White', 'state' => 'Illinois', 'era' => '1910s',
                'ideologies' => ['Pacifism', 'Anti-War', 'Conscientious objection'],
                'bio' => 'Brent Dow Allinson of Chicago refused to register for the draft and was charged with desertion. Sentenced to fifteen-to-twenty-five years (reduced to four), he was imprisoned at Fort Leavenworth from July 30, 1918 into 1921 — among the last WWI objectors released.',
                'charges' => 'Failing to register for the draft (charged with desertion) as a conscientious objector during World War I.',
                'convicted' => $GUILTY,
                'sentence' => 'Fifteen to twenty-five years, reduced to four; imprisoned at Fort Leavenworth, July 30, 1918 – 1921.',
                'incarceration' => [1918, 7, 30], 'release' => [1921],
            ],
            [
                'name' => 'H. Austin Simons', 'first' => 'H. Austin', 'last' => 'Simons',
                'race' => 'White', 'state' => 'Illinois', 'era' => '1910s',
                'ideologies' => ['Pacifism', 'Anti-War', 'Conscientious objection'],
                'bio' => 'H. Austin Simons was a writer and conscientious objector imprisoned at Fort Leavenworth, where he was a leader of the January 1919 prisoners\' work strike — urging nonviolence as the protest, which began with the objectors, grew to some 2,300 inmates demanding sentence reviews and better conditions.',
                'charges' => $CO, 'convicted' => $GUILTY,
                'sentence' => 'Imprisoned at Fort Leavenworth; a leader of the January 1919 prisoners\' strike.',
                'incarceration' => [1918], 'release' => [1919],
            ],
            [
                'name' => 'Max Sandin', 'first' => 'Max', 'last' => 'Sandin',
                'race' => 'White', 'state' => 'Ohio', 'era' => '1910s',
                'birth' => [1889],
                'ideologies' => ['Pacifism', 'Anti-War', 'Socialism', 'Conscientious objection'],
                'bio' => 'Max Sandin, a Russian-Jewish immigrant living in Cleveland, was a WWI absolutist who refused all military service. Court-martialed and originally sentenced to death, his sentence was commuted, and he was imprisoned at Fort Leavenworth before his release in 1920. A lifelong pacifist, he again refused to cooperate with conscription during World War II.',
                'charges' => $CO, 'convicted' => $GUILTY,
                'sentence' => 'Originally sentenced to death; commuted, and imprisoned at Fort Leavenworth until 1920.',
                'incarceration' => [1918], 'release' => [1920], 'institution' => $leavenworth->id,
            ],

            // ---- Peace-church and other objectors (Yoder database) ----
            [
                'name' => 'Edward Waltner', 'first' => 'Edward', 'middle' => 'J. B.', 'last' => 'Waltner',
                'race' => 'White', 'state' => 'South Dakota', 'era' => '1910s',
                'birth' => [1890],
                'ideologies' => ['Pacifism', 'Conscientious objection'], 'affiliation' => ['Mennonite'],
                'bio' => 'Edward J. B. Waltner, a General Conference Mennonite from Marion Junction, South Dakota, was drafted in 1917 and imprisoned at Fort Leavenworth as a conscientious objector before his release on January 27, 1919.',
                'charges' => $CO, 'convicted' => $GUILTY,
                'sentence' => 'Imprisoned at Fort Leavenworth; released January 27, 1919.',
                'incarceration' => [1917], 'release' => [1919, 1, 27], 'institution' => $leavenworth->id,
            ],
            [
                'name' => 'Monroe Wulff', 'first' => 'Monroe', 'last' => 'Wulff',
                'race' => 'White', 'state' => 'Michigan', 'era' => '1910s',
                'birth' => [1895], 'death' => [1936],
                'ideologies' => ['Pacifism', 'Conscientious objection'], 'affiliation' => ['Israelite House of David'],
                'bio' => 'Monroe Wulff (1895–1936) of Benton Harbor, Michigan, a member of the Israelite House of David religious community, was a conscientious objector imprisoned at Fort Leavenworth and released on January 27, 1919.',
                'charges' => $CO, 'convicted' => $GUILTY,
                'sentence' => 'Imprisoned at Fort Leavenworth; released January 27, 1919.',
                'incarceration' => [1918], 'release' => [1919, 1, 27], 'institution' => $leavenworth->id,
            ],
            [
                'name' => 'William Kantor', 'first' => 'William', 'middle' => 'M.', 'last' => 'Kantor',
                'race' => 'White', 'state' => 'Pennsylvania', 'era' => '1910s',
                'birth' => [1893, 6, 20],
                'ideologies' => ['Pacifism', 'Conscientious objection'],
                'bio' => 'William M. Kantor of Philadelphia was drafted in 1917 and imprisoned as a conscientious objector at Fort Leavenworth and Alcatraz before his release in November 1919.',
                'charges' => $CO, 'convicted' => $GUILTY,
                'sentence' => 'Imprisoned at Fort Leavenworth and Alcatraz; released November 1919.',
                'incarceration' => [1917], 'release' => [1919, 11], 'institution' => $leavenworth->id,
            ],
            [
                'name' => 'Frederick Briehl', 'first' => 'Frederick', 'last' => 'Briehl', 'aka' => 'Fred Briehl',
                'race' => 'White', 'state' => 'New York', 'era' => '1910s',
                'birth' => [1892, 12, 15],
                'ideologies' => ['Pacifism', 'Anti-War', 'Conscientious objection'],
                'bio' => 'Frederick "Fred" Briehl, of Brooklyn, New York, was a conscientious objector imprisoned during World War I at Fort Leavenworth and the Fort Douglas War Prison Barracks in Utah.',
                'charges' => $CO, 'convicted' => $GUILTY,
                'sentence' => 'Imprisoned at Fort Leavenworth and Fort Douglas, Utah.',
                'incarceration' => [1918], 'release' => [1919], 'institution' => $leavenworth->id,
            ],
            [
                'name' => 'Philip Caplovitz', 'first' => 'Philip', 'last' => 'Caplovitz',
                'race' => 'White', 'state' => 'Connecticut', 'era' => '1910s',
                'ideologies' => ['Pacifism', 'Conscientious objection'],
                'bio' => 'Philip Caplovitz of New Haven, Connecticut was a conscientious objector imprisoned during World War I at Fort Leavenworth and the Fort Douglas War Prison Barracks; his discharge papers are dated April 17, 1920.',
                'charges' => $CO, 'convicted' => $GUILTY,
                'sentence' => 'Imprisoned at Fort Leavenworth and Fort Douglas, Utah; discharged April 17, 1920.',
                'incarceration' => [1918], 'release' => [1920, 4, 17], 'institution' => $leavenworth->id,
            ],
            [
                'name' => 'William Nye Doty', 'first' => 'William', 'middle' => 'Nye', 'last' => 'Doty',
                'race' => 'White', 'state' => 'Iowa', 'era' => '1910s',
                'ideologies' => ['Pacifism', 'Conscientious objection'],
                'bio' => 'William Nye Doty of Cedar Rapids, Iowa was a conscientious objector imprisoned during World War I at Fort Leavenworth and the Fort Douglas War Prison Barracks in Utah.',
                'charges' => $CO, 'convicted' => $GUILTY,
                'sentence' => 'Imprisoned at Fort Leavenworth and Fort Douglas, Utah.',
                'incarceration' => [1918], 'release' => [1919], 'institution' => $leavenworth->id,
            ],
            [
                'name' => 'Lazarus Marcowitz', 'first' => 'Lazarus', 'middle' => 'Baer', 'last' => 'Marcowitz',
                'race' => 'White', 'state' => 'New York', 'era' => '1910s',
                'ideologies' => ['Pacifism', 'Conscientious objection'],
                'bio' => 'Lazarus Baer Marcowitz of Brooklyn, New York was drafted in 1918 and imprisoned as a conscientious objector at Fort Leavenworth before his release in 1919.',
                'charges' => $CO, 'convicted' => $GUILTY,
                'sentence' => 'Imprisoned at Fort Leavenworth; released in 1919.',
                'incarceration' => [1918], 'release' => [1919], 'institution' => $leavenworth->id,
            ],
            [
                'name' => 'Earl Ross Whitaker', 'first' => 'Earl', 'middle' => 'Ross', 'last' => 'Whitaker',
                'race' => 'White', 'state' => 'Pennsylvania', 'era' => '1910s',
                'ideologies' => ['Pacifism', 'Conscientious objection'],
                'bio' => 'Earl Ross Whitaker of Eldorado, Pennsylvania was a Christian conscientious objector imprisoned at Fort Leavenworth during World War I.',
                'charges' => $CO, 'convicted' => $GUILTY,
                'sentence' => 'Imprisoned at Fort Leavenworth.',
                'incarceration' => [1918], 'release' => [1919], 'institution' => $leavenworth->id,
            ],
        ];

        DB::transaction(function () use ($people) {
            foreach ($people as $p) {
                $prisoner = Prisoner::withUnderReview()->where('name', $p['name'])->first()
                    ?? new Prisoner(['name' => $p['name']]);
                $died = ! empty($p['died']);

                $prisoner->fill([
                    'name' => $p['name'],
                    'first_name' => $p['first'] ?? null,
                    'middle_name' => $p['middle'] ?? null,
                    'last_name' => $p['last'] ?? null,
                    'aka' => $p['aka'] ?? null,
                    'gender' => 'Male',
                    'race' => $p['race'] ?? null,
                    'state' => $p['state'] ?? null,
                    'era' => $p['era'],
                    'ideologies' => $p['ideologies'],
                    'affiliation' => $p['affiliation'] ?? [],
                    'description' => $p['bio'],
                    'in_custody' => false,
                    'released' => ! $died,
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
                    'institution_id' => $p['institution'] ?? null,
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
                if ($died) {
                    $case->setPartialDate('death_in_custody_date', ...$p['died']);
                }
                $case->save();

                $this->info(($prisoner->wasRecentlyCreated ? 'Added: ' : 'Filled: ').$prisoner->name.' (slug: '.$prisoner->slug.')');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
