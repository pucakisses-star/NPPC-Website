<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Abolitionists and Underground Railroad conductors arrested, tried, convicted,
 * or imprisoned for helping enslaved people escape — the anti-slavery political
 * prisoners drawn from the National Park Service "Network to Freedom" site
 * listings (and, for the famous cases, the standard histories). Distinct from
 * the freedom-seekers who were jailed as accused "fugitives," these are people
 * punished for the political act of aiding escape.
 *
 * Create-or-update by name; rebuilds each single case. Idempotent.
 */
class AddUndergroundRailroadPrisoners extends Command
{
    protected $signature = 'prisoners:add-underground-railroad-prisoners';

    protected $description = 'Add abolitionists/UGRR conductors jailed or convicted for aiding escapes from slavery';

    public function handle(): int
    {
        $people = $this->people();

        DB::transaction(function () use ($people) {
            foreach ($people as $p) {
                $prisoner = Prisoner::withUnderReview()->where('name', $p['name'])->first()
                    ?? new Prisoner(['name' => $p['name']]);

                $prisoner->fill([
                    'name' => $p['name'],
                    'first_name' => $p['first'] ?? null,
                    'last_name' => $p['last'] ?? null,
                    'aka' => $p['aka'] ?? null,
                    'gender' => $p['gender'] ?? 'Male',
                    'race' => $p['race'] ?? null,
                    'state' => $p['state'] ?? null,
                    'era' => $p['era'],
                    'ideologies' => $p['ideologies'] ?? ['Abolitionism'],
                    'affiliation' => $p['affiliation'] ?? ['Underground Railroad'],
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
                if (! empty($p['incarceration'])) {
                    $case->setPartialDate('incarceration_date', ...$p['incarceration']);
                }
                if (! empty($p['release'])) {
                    $case->setPartialDate('release_date', ...$p['release']);
                }
                $case->save();

                $this->info(($prisoner->wasRecentlyCreated ? 'Added: ' : 'Set: ').$prisoner->name.' (slug: '.$prisoner->slug.')');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }

    private function people(): array
    {
        return [
            [
                'name' => 'Richard Eells', 'first' => 'Richard', 'last' => 'Eells', 'aka' => 'Dr. Richard Eells',
                'race' => 'White', 'state' => 'Illinois', 'era' => '1840s',
                'bio' => 'Dr. Richard Eells was a Quincy, Illinois physician and abolitionist. In 1842 he was arrested and charged with harboring a fugitive enslaved man named Charley. Convicted and fined $400 by Judge Stephen A. Douglas, he lost his appeals to the Illinois and United States Supreme Courts — a landmark test of the fugitive-slave laws.',
                'charges' => 'Harboring a fugitive from slavery — for aiding a freedom seeker named Charley in 1842.',
                'convicted' => 'Yes — convicted; the conviction was upheld on appeal to the Illinois and U.S. Supreme Courts.',
                'sentence' => 'Fined $400 (by Judge Stephen A. Douglas).',
            ],
            [
                'name' => 'Samuel D. Burris', 'first' => 'Samuel', 'last' => 'Burris',
                'race' => 'Black', 'state' => 'Delaware', 'era' => '1840s', 'death' => [1863],
                'bio' => 'Samuel D. Burris was a free Black Underground Railroad conductor from western Kent County, Delaware, documented in William Still\'s "The Underground Railroad." In July 1847 he was imprisoned for assisting the freedom seeker Maria Matthews; convicted in two of the cases against him, he was sentenced to be sold into slavery by the state. At his auction the Pennsylvania Abolition Society secretly raised $500 and bought his freedom. He later moved to Philadelphia and then San Francisco.',
                'charges' => 'Assisting enslaved people to escape (Kent County, Delaware) — conducting freedom seekers such as Maria Matthews and the Hawkins family toward Wilmington.',
                'convicted' => 'Yes — convicted in two cases and sentenced to be sold into slavery.',
                'sentence' => 'Sold at auction; the Pennsylvania Abolition Society covertly bought him for $500 and freed him.',
                'incarceration' => [1847, 7],
            ],
            [
                'name' => 'William Baylis', 'first' => 'William', 'last' => 'Baylis',
                'race' => 'White', 'state' => 'Virginia', 'era' => '1850s',
                'bio' => 'William Baylis was the captain of the schooner Keziah and an Underground Railroad operative known to leaders such as William Still and Thomas Garrett. On May 31, 1858, on the James River, Petersburg officials overtook the Keziah and arrested Baylis and his mate, seizing five freedom seekers hidden below. His trial so crowded the Petersburg Court House that it had to be postponed twice. Convicted on five counts of slave abduction and sentenced to a total of forty years, he served about six years before being pardoned in March 1865 — his wife Martha having moved to Richmond and campaigned tirelessly for his release — and returned home to Wilmington, Delaware.',
                'charges' => 'Aiding the escape of enslaved people — carrying five freedom seekers aboard the schooner Keziah, seized May 31, 1858.',
                'convicted' => 'Yes — convicted in Petersburg Circuit Court on five counts of slave abduction.',
                'sentence' => 'Sentenced to a total of forty years in the Virginia penitentiary; he served about six years before being pardoned in March 1865 and returning home to Wilmington, Delaware.',
                'incarceration' => [1858], 'release' => [1865, 3],
            ],
            // Thomas Garrett is deliberately absent. The 1848 New Castle
            // prosecution he shared with John Hunn ended in ruinous fines, not
            // imprisonment — the entry that used to sit here carried an 1848
            // incarceration date its own sentence text contradicted. Removed
            // at the site owner's direction; do not re-add him without a
            // source for actual custody.
            [
                'name' => 'John Hunn', 'first' => 'John', 'last' => 'Hunn',
                'race' => 'White', 'state' => 'Delaware', 'era' => '1840s',
                'ideologies' => ['Abolitionism', 'Quaker'],
                'bio' => 'John Hunn was a Quaker Underground Railroad stationmaster in Delaware, often called the "superintendent" of the railroad there. In 1848 he and Thomas Garrett were tried and convicted in federal court at New Castle for violating the fugitive-slave laws by aiding the escape of an enslaved family, and were fined heavily.',
                'charges' => 'Violating the fugitive-slave laws — for aiding the escape of an enslaved family.',
                'convicted' => 'Yes — tried and convicted with Thomas Garrett in federal court (1848).',
                'sentence' => 'Heavy fines.',
                'incarceration' => [1848],
            ],
            [
                'name' => 'Jonathan Walker', 'first' => 'Jonathan', 'last' => 'Walker', 'aka' => 'The Man with the Branded Hand',
                'race' => 'White', 'state' => 'Florida', 'era' => '1840s',
                'birth' => [1799, 3, 22], 'death' => [1878, 4, 30],
                'bio' => 'Captain Jonathan Walker (1799–1878) was a Massachusetts sea captain and abolitionist. In 1844, while sailing seven freedom seekers from Florida toward the Bahamas, his boat was captured. Charged with slave stealing and jailed for about a year, he was, after a lengthy trial, sentenced to stand in the pillory and to be branded "SS" (for "Slave Stealer") on his right hand by order of the federal court — becoming the celebrated "Man with the Branded Hand," memorialized in Whittier\'s poem. He spent the rest of his life lecturing against slavery.',
                'charges' => 'Slave stealing — for attempting to sail seven freedom seekers from Florida to the Bahamas in 1844.',
                'convicted' => 'Yes — convicted after a lengthy trial.',
                'sentence' => 'About a year in jail, placed in the pillory, and branded "SS" ("Slave Stealer") on his right hand.',
                'incarceration' => [1844],
            ],
            [
                'name' => 'William Chaplin', 'first' => 'William', 'last' => 'Chaplin',
                'race' => 'White', 'state' => 'Maryland', 'era' => '1850s',
                'affiliation' => ['Underground Railroad', 'Liberty Party', 'Albany Vigilance Committee'],
                'bio' => 'William L. Chaplin was an Underground Railroad operative associated with the Liberty Party and the Albany Vigilance Committee. On August 8, 1850 his carriage — carrying two men escaping from enslavement by two Southern congressmen — was stopped in a gunfight by a posse at the DC–Maryland line. Arrested, he was imprisoned six weeks in Washington (released on $6,000 bond) and then thirteen weeks in Rockville, Maryland (released on $19,000 bond). He left the area, forfeiting the bonds, and never came to trial.',
                'charges' => 'Aiding the escape of two enslaved men (owned by Southern congressmen) — arrested after a gunfight at the DC–Maryland border, August 8, 1850.',
                'convicted' => 'Never tried — he forfeited bail and fled after months in jail.',
                'sentence' => 'Imprisoned about six weeks in Washington and thirteen weeks in Rockville, Maryland, before jumping bail.',
                'incarceration' => [1850, 8],
            ],
            [
                'name' => 'John Gill Craven', 'first' => 'John', 'last' => 'Craven', 'aka' => 'John Gill Craven',
                'race' => 'White', 'state' => 'Indiana', 'era' => '1850s',
                'bio' => 'John Gill Craven was principal of the Eleutherian Institute (later Eleutherian College) in Lancaster, Indiana — an abolition school open to students of all colors, a few miles from slave territory. He and his wife Martha opened their home to Black students and aided freedom seekers. Craven, along with James and Lucy Nelson, was arrested by the sheriff of Jefferson County under the 1850 Fugitive Slave Law.',
                'charges' => 'Violating the 1850 Fugitive Slave Law — for aiding freedom seekers at and around the abolitionist Eleutherian Institute.',
                'convicted' => 'Arrested and prosecuted under the Fugitive Slave Law.',
                'sentence' => 'Arrested by the sheriff of Jefferson County, Indiana.',
            ],
            [
                'name' => 'Mark Caesar', 'first' => 'Mark', 'last' => 'Caesar',
                'race' => 'Black', 'state' => 'Maryland', 'era' => '1840s',
                'bio' => 'Mark Caesar, a free Black man of Charles County, Maryland, was tried in 1845 as an "accomplice of slave flight" for his part in the Port Tobacco mass escape, in which a large armed group of freedom seekers set out for the north before being surrounded near Rockville. Maryland State Archives researchers found him documented in the Maryland Penitentiary prisoners\' record of 1850.',
                'charges' => 'Aiding a mass escape from slavery (the 1845 Port Tobacco escape) — charged as an "accomplice of slave flight."',
                'convicted' => 'Yes — tried in 1845; imprisoned in the Maryland Penitentiary.',
                'sentence' => 'Imprisonment in the Maryland Penitentiary (documented in the 1850 prisoners\' record).',
                'incarceration' => [1845],
            ],
            [
                'name' => 'William Wheeler', 'first' => 'William', 'last' => 'Wheeler', 'aka' => 'Bill Wheeler',
                'race' => 'Black', 'state' => 'Maryland', 'era' => '1840s',
                'bio' => 'William "Bill" Wheeler was an architect of the July 8, 1845 Port Tobacco Mass Escape, in which some 40 to 70 enslaved people of Charles County, Maryland staged a coordinated daylight escape and reached Rockville before armed white mobs surrounded them. Tried alongside Mark Caesar in 1845, Wheeler was imprisoned at the Port Tobacco jail; the Maryland legislature passed a special law to ensure his life imprisonment should he escape execution. He ultimately escaped from the jail and was never recaptured.',
                'charges' => 'Organizing and leading the 1845 Port Tobacco mass escape from slavery.',
                'convicted' => 'Yes — tried in 1845; a special Maryland law was passed to ensure his life imprisonment.',
                'sentence' => 'Imprisoned at the Port Tobacco jail; he escaped and was never recaptured.',
                'incarceration' => [1845],
            ],
            [
                'name' => 'Samuel Green', 'first' => 'Samuel', 'last' => 'Green', 'aka' => 'Rev. Samuel Green',
                'race' => 'Black', 'state' => 'Maryland', 'era' => '1850s',
                'ideologies' => ['Abolitionism'],
                'affiliation' => ['Underground Railroad', 'Methodist Episcopal Church'],
                'bio' => 'The Reverend Samuel Green was a free Black Methodist preacher and Underground Railroad agent in Dorchester County, Maryland who had purchased his own freedom in 1833; Harriet Tubman sometimes sheltered with him. In spring 1857, after aiding the escape of the "Dover Eight," he was arrested and tried — and acquitted. Slaveholders then charged him again, this time for possessing a copy of Harriet Beecher Stowe\'s "Uncle Tom\'s Cabin" in violation of a Maryland law against abolitionist literature. He was convicted in May 1857 and sentenced to ten years in the state penitentiary.',
                'charges' => 'Possessing abolitionist literature ("Uncle Tom\'s Cabin") in violation of Maryland law, after an earlier acquittal for aiding the "Dover Eight" escape.',
                'convicted' => 'Yes — acquitted on the first charge, then convicted in May 1857 on the book-possession charge.',
                'sentence' => 'Ten years in the Maryland state penitentiary.',
                'incarceration' => [1857, 5],
            ],
            [
                'name' => 'Hugh Hazlett', 'first' => 'Hugh', 'last' => 'Hazlett',
                'race' => 'White', 'state' => 'Maryland', 'era' => '1850s',
                'bio' => 'Hugh Hazlett was an Irish immigrant wood sawyer and Underground Railroad conductor in Maryland. On the night of July 24, 1858 he led seven freedom seekers out of the Cambridge area of Dorchester County; betrayed by an informer, the group was ambushed and captured in Caroline County. Hazlett was jailed in Denton, nearly lynched by a mob at Cambridge, tried and convicted, and sentenced to forty-four years in prison. His case drew national attention, reported in the New York Tribune.',
                'charges' => 'Assisting enslaved people to escape (Dorchester County, July 1858) in violation of the Fugitive Slave Act.',
                'convicted' => 'Yes — tried and convicted in Dorchester County (1858).',
                'sentence' => 'Forty-four years in prison.',
                'incarceration' => [1858],
            ],
            [
                'name' => 'Isaac Gibson', 'first' => 'Isaac', 'last' => 'Gibson',
                'race' => 'Black', 'state' => 'Maryland', 'era' => '1850s',
                'bio' => 'Isaac Gibson, a free African American of Caroline County, Maryland, helped an enslaved man named John Stokes attempt to flee his enslaver in Hillsborough in 1849. Captured and jailed in Denton, Gibson was tried and convicted at the Caroline County Courthouse in March 1851 and sentenced to a little more than three years in prison.',
                'charges' => 'Aiding the attempted escape of an enslaved man, John Stokes (1849), from Hillsborough, Caroline County.',
                'convicted' => 'Yes — tried and convicted at the Caroline County Courthouse, March 1851.',
                'sentence' => 'A little more than three years in prison.',
                'incarceration' => [1851, 3],
            ],
            [
                'name' => 'Luther Donnell', 'first' => 'Luther', 'last' => 'Donnell',
                'race' => 'White', 'state' => 'Indiana', 'era' => '1840s',
                'bio' => 'Luther Donnell was a Decatur County, Indiana abolitionist who in 1847 helped a woman named Caroline and her children — recaptured after escaping from Kentucky — obtain a writ of habeas corpus and reach freedom in Canada via the Underground Railroad. Arrested and indicted under Indiana law for "aiding Negroes to escape," he was found guilty, and a $3,000 civil judgment was entered against him. In 1852 the Indiana Supreme Court overturned the criminal verdict, holding the law unconstitutional.',
                'charges' => 'Aiding freedom seekers to escape (Indiana, 1847) — helping Caroline and her children reach Canada.',
                'convicted' => 'Yes — found guilty; the criminal verdict was overturned by the Indiana Supreme Court in 1852.',
                'sentence' => 'A $3,000 civil judgment; the criminal conviction was later voided.',
                'incarceration' => [1847],
            ],
            [
                'name' => 'Leonard Grimes', 'first' => 'Leonard', 'last' => 'Grimes', 'aka' => 'Leonard Andrew Grimes',
                'race' => 'Black', 'state' => 'Virginia', 'era' => '1840s',
                'birth' => [1815], 'death' => [1873],
                'affiliation' => ['Underground Railroad', 'Boston Vigilance Committee'],
                'bio' => 'Leonard Andrew Grimes (1815–1873) was a free-born African American Underground Railroad activist in Washington, DC. He served two years in the Virginia penitentiary on circumstantial evidence that he had helped an enslaved family escape. After his release he moved to Massachusetts, became pastor of the Twelfth Baptist Church in Boston, and worked with the Boston Vigilance Committee in the efforts to prevent the renditions of Anthony Burns, Shadrach Minkins, and Thomas Sims.',
                'charges' => 'Aiding an enslaved family to escape — prosecuted in Leesburg, Virginia.',
                'convicted' => 'Yes — convicted on circumstantial evidence.',
                'sentence' => 'Two years in the Virginia penitentiary.',
            ],
            [
                'name' => 'Oswell Wright', 'first' => 'Oswell', 'last' => 'Wright',
                'race' => 'Black', 'state' => 'Indiana', 'era' => '1850s', 'death' => [1875],
                'bio' => 'Oswell Wright was a free African American of Corydon, Indiana. In the 1857 "Bell–Wright" or "Brandenburg" case he, with two white farmers, was arrested by Kentucky officials for helping the enslaved blacksmith Charles Woodford escape across the Ohio River. The two white men were broken out of jail by a relative; only Wright was brought to court, convicted, and sentenced to five years in the Kentucky Penitentiary. He returned to Corydon after his release and died in 1875.',
                'charges' => 'Aiding the escape of an enslaved blacksmith, Charles Woodford (1857) — the "Bell–Wright" / Brandenburg case.',
                'convicted' => 'Yes — convicted in Kentucky.',
                'sentence' => 'Five years in the Kentucky Penitentiary.',
                'incarceration' => [1857],
            ],
            [
                'name' => 'Hannah Toliver', 'first' => 'Hannah', 'last' => 'Toliver',
                'gender' => 'Female', 'race' => 'Black', 'state' => 'Kentucky', 'era' => '1860s',
                'bio' => 'Hannah Toliver was a free Black woman arrested in 1864 for violating Kentucky\'s law against "enticing a slave," for aiding the escape of an enslaved man held by William Murphy of Kentucky. Tried and found guilty, she was sentenced to seven years in prison; she served seven months and eighteen days before being pardoned by Governor Thomas Bramlette. Her case shows the Underground Railroad still operating during the Civil War — the Emancipation Proclamation had not freed the enslaved in Union-loyal Kentucky.',
                'charges' => '"Enticing a slave" (Kentucky) — aiding the escape of an enslaved man in 1864.',
                'convicted' => 'Yes — tried and found guilty.',
                'sentence' => 'Sentenced to seven years; served seven months and eighteen days before being pardoned by Governor Thomas Bramlette.',
                'incarceration' => [1864],
            ],
            [
                'name' => 'John Cross', 'first' => 'John', 'last' => 'Cross', 'aka' => 'Rev. John Newton Cross',
                'race' => 'White', 'state' => 'Illinois', 'era' => '1840s',
                'birth' => [1797], 'death' => [1885],
                'bio' => 'The Reverend John Newton Cross (1797–1885) was an abolitionist pastor openly defiant of the Fugitive Slave Law, who publicized his Underground Railroad work in the antislavery newspaper The Western Citizen and built UGRR networks across Indiana, Michigan, Illinois, and Iowa. In 1843 he was arrested in Knox County, Illinois for transporting freedom seekers; the case was later dismissed.',
                'charges' => 'Transporting freedom seekers (Knox County, Illinois, 1843) in defiance of the fugitive-slave laws.',
                'convicted' => 'No — arrested and brought to court in 1843; the case was later dismissed.',
                'sentence' => 'None — the case was dismissed.',
                'incarceration' => [1843],
            ],
            [
                'name' => 'Edward T. Sheldon', 'first' => 'Edward', 'last' => 'Sheldon', 'aka' => 'Edward Thompson Sheldon',
                'race' => 'White', 'state' => 'Iowa', 'era' => '1860s',
                'birth' => [1838], 'death' => [1911, 12, 29],
                'bio' => 'Edward Thompson Sheldon (1838–1911) was an Underground Railroad operative in southwest Iowa. In February 1860 he and Newton Woodford were transporting four men (treated as freedom seekers) by covered wagon when a band of pro-slavery men, armed with a warrant, had them arrested for aiding an escape from slavery. Jailed at Glenwood, Iowa and brought to trial, they were acquitted; undeterred, Sheldon soon helped rescue the four men. He later served as a Union Army captain and settled in Colorado.',
                'charges' => 'Aiding four men to escape slavery (southwest Iowa, February 1860).',
                'convicted' => 'No — jailed at Glenwood, Iowa and tried, but acquitted of all charges.',
                'sentence' => 'None — acquitted after being jailed pending trial.',
                'incarceration' => [1860, 2],
            ],
            [
                'name' => 'Robert Fee', 'first' => 'Robert', 'last' => 'Fee', 'aka' => 'Robert E. Fee',
                'race' => 'White', 'state' => 'Ohio', 'era' => '1850s',
                'bio' => 'Robert E. Fee was a member of Clermont County, Ohio\'s most prominent Underground Railroad family and the agent of Vincent Wigglesworth, whose family had been kidnapped and enslaved. His home, frequently surrounded by slave hunters, was a station on the railroad. He was indicted by Pendleton County, Kentucky for "slave stealing."',
                'charges' => 'Slave stealing — indicted by Pendleton County, Kentucky for his Underground Railroad activity.',
                'convicted' => 'Indicted by Pendleton County, Kentucky.',
                'sentence' => 'Indicted for slave stealing.',
            ],
            [
                'name' => 'John Hossack', 'first' => 'John', 'last' => 'Hossack',
                'race' => 'White', 'state' => 'Illinois', 'era' => '1850s',
                'bio' => 'John Hossack was an Ottawa, Illinois grain merchant and Underground Railroad activist who had helped build the Illinois & Michigan Canal. In 1859 he became nationally known after being arrested and tried for violating the Fugitive Slave Law in the rescue of a freedom seeker; his defiant courtroom address on the injustice of the law was widely reprinted.',
                'charges' => 'Violating the 1850 Fugitive Slave Law — for a rescue of a freedom seeker at Ottawa, Illinois (1859).',
                'convicted' => 'Yes — convicted; celebrated for his defiant statement to the court.',
                'sentence' => 'Fined and briefly jailed under the Fugitive Slave Law.',
                'incarceration' => [1859],
            ],
            [
                'name' => 'Daniel Drayton', 'first' => 'Daniel', 'last' => 'Drayton',
                'race' => 'White', 'state' => 'Washington, D.C.', 'era' => '1840s',
                'bio' => 'Daniel Drayton was a captain of the schooner Pearl, which in April 1848 attempted the largest single escape from slavery in U.S. history — carrying more than seventy freedom seekers down the Potomac from Washington before being captured. Convicted and fined enormous sums he could not pay, Drayton remained imprisoned in the Washington jail for over four years until President Fillmore pardoned him in August 1852, after a campaign led by Senator Charles Sumner.',
                'charges' => 'Transporting more than seventy enslaved people toward freedom aboard the schooner Pearl (April 1848).',
                'convicted' => 'Yes — convicted and fined sums he could not pay.',
                'sentence' => 'Held in the Washington jail more than four years; pardoned by President Fillmore in August 1852.',
                'incarceration' => [1848, 4], 'release' => [1852, 8],
            ],
            [
                'name' => 'Edward Sayres', 'first' => 'Edward', 'last' => 'Sayres',
                'race' => 'White', 'state' => 'Washington, D.C.', 'era' => '1840s',
                'bio' => 'Edward Sayres was captain and owner of the schooner Pearl, chartered for the April 1848 mass escape of more than seventy freedom seekers from Washington, DC. Captured with Daniel Drayton, he was convicted, fined, and imprisoned in the Washington jail until pardoned by President Fillmore in 1852.',
                'charges' => 'Aiding the escape of more than seventy enslaved people aboard the schooner Pearl (April 1848).',
                'convicted' => 'Yes — convicted and fined.',
                'sentence' => 'Imprisoned in the Washington jail until pardoned by President Fillmore in 1852.',
                'incarceration' => [1848, 4], 'release' => [1852, 8],
            ],
            [
                'name' => 'Charles Langston', 'first' => 'Charles', 'last' => 'Langston', 'aka' => 'Charles Henry Langston',
                'race' => 'Black', 'state' => 'Ohio', 'era' => '1850s',
                'bio' => 'Charles Henry Langston, a free Black abolitionist and educator (and grandfather of the poet Langston Hughes), was one of the leaders of the 1858 Oberlin–Wellington Rescue, in which black and white townspeople freed the recaptured freedom seeker John Price. Tried in Cleveland and convicted under the Fugitive Slave Act, he delivered a famous address to the court on why he could not respect a law that denied his people justice; he was jailed and fined.',
                'charges' => 'Violating the Fugitive Slave Act — for the 1858 Oberlin–Wellington Rescue of John Price.',
                'convicted' => 'Yes — tried in Cleveland and convicted.',
                'sentence' => 'Twenty days in jail and a fine (a lighter sentence after his celebrated courtroom speech).',
                'incarceration' => [1859],
            ],
            [
                'name' => 'Simeon Bushnell', 'first' => 'Simeon', 'last' => 'Bushnell',
                'race' => 'White', 'state' => 'Ohio', 'era' => '1850s',
                'bio' => 'Simeon Bushnell was an Oberlin, Ohio bookseller and one of the leaders of the 1858 Oberlin–Wellington Rescue of the recaptured freedom seeker John Price, whom he drove to safety. Tried first in Cleveland and convicted under the Fugitive Slave Act, he received the heaviest sentence of the rescuers.',
                'charges' => 'Violating the Fugitive Slave Act — for the 1858 Oberlin–Wellington Rescue of John Price.',
                'convicted' => 'Yes — tried in Cleveland and convicted.',
                'sentence' => 'Sixty days in jail and a $600 fine.',
                'incarceration' => [1859],
            ],
        ];
    }
}
