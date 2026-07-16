<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Backfills verified birth/death dates (and a few source notes) for early
 * labor, Communist, civil-rights, Indigenous, antiwar, and Alien-and-Sedition-
 * era defendants. Dates come from a curated research pass; approximate years
 * are stored at year precision so the card does not render a false day.
 *
 * Policy per field:
 *   - empty            → set the provided value;
 *   - coarser existing → upgrade only when the new value is finer AND consistent
 *     (e.g. an existing month-precision 1980-08 becomes 1980-08-02);
 *   - conflicting      → left as-is UNLESS explicitly flagged (fb/fd), which are
 *     the two corrections called out in the source list (Leonard Crow Dog's
 *     death, Deskaheh's death).
 *
 * Source/discrepancy notes are appended to the description once (idempotent).
 * age recomputes automatically on save. Safe to re-run.
 */
final class BackfillPrisonerDates extends Command
{
    protected $signature = 'prisoners:backfill-dates';

    protected $description = 'Backfill verified birth/death dates and source notes (fill-or-upgrade)';

    /** @var array<int,array{slug:string,names:string[],b:?array,d:?array,note:?string,fb:bool,fd:bool}> */
    private const ENTRIES = [
        ['slug' => 'william-bross-lloyd', 'names' => ['William Bross Lloyd'], 'b' => [1875, 2, 24], 'd' => [1946, 6, 30], 'note' => 'A New York Public Library finding aid gives a death date of June 20, 1946; this record uses June 30, 1946.', 'fb' => false, 'fd' => false],
        ['slug' => 'clinton-rickard', 'names' => ['Clinton Rickard'], 'b' => [1882, 5, 19], 'd' => [1971, 6, 14], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'alexander-howat', 'names' => ['Alexander Howat'], 'b' => [1876, 9, 10], 'd' => [1945, 12, 10], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'waldo-frank', 'names' => ['Waldo Frank'], 'b' => [1889, 8, 25], 'd' => [1967, 1, 9], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'david-lasser', 'names' => ['David Lasser'], 'b' => [1902, 3, 20], 'd' => [1996, 5, 5], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'alfred-m-bingham', 'names' => ['Alfred M. Bingham', 'Alfred Bingham'], 'b' => [1905, 2, 20], 'd' => [1998, 11, 2], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'carl-marzani', 'names' => ['Carl Marzani'], 'b' => [1912, 3, 4], 'd' => [1994, 12, 11], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'septima-clark', 'names' => ['Septima Poinsette Clark', 'Septima Clark'], 'b' => [1898, 5, 3], 'd' => [1987, 12, 15], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'dirk-struik', 'names' => ['Dirk Jan Struik', 'Dirk Struik'], 'b' => [1894, 9, 30], 'd' => [2000, 10, 21], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'paul-sweezy', 'names' => ['Paul M. Sweezy', 'Paul Sweezy'], 'b' => [1910, 4, 10], 'd' => [2004, 2, 27], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'hazel-wolf', 'names' => ['Hazel Wolf'], 'b' => [1898, 3, 10], 'd' => [2000, 1, 19], 'note' => 'One secondary source gives a death date of January 24, 2000; this record uses January 19, 2000 (University of Washington and contemporaneous coverage).', 'fb' => false, 'fd' => false],
        ['slug' => 'william-bichsel', 'names' => ['Father William "Bix" Bichsel', 'William Bichsel', 'Bill Bichsel'], 'b' => [1928, 5, 26], 'd' => [2015, 2, 28], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'frank-lamere', 'names' => ['Frank LaMere'], 'b' => [1950, 3, 1], 'd' => [2019, 6, 16], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'debra-white-plume', 'names' => ['Debra White Plume'], 'b' => [1954, 8, 20], 'd' => [2020, 11, 10], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'leonard-crow-dog', 'names' => ['Leonard Crow Dog'], 'b' => [1942, 8, 18], 'd' => [2021, 6, 5], 'note' => 'Some reporting gives June 6, 2021; this record uses June 5, 2021 (National Indian Gaming Association memorial).', 'fb' => false, 'fd' => true],
        ['slug' => 'mary-crow-dog', 'names' => ['Mary Brave Bird', 'Mary Crow Dog'], 'b' => [1954, 9, 26], 'd' => [2013, 2, 14], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'jerry-zawada', 'names' => ['Father Jerome "Jerry" Zawada', 'Jerry Zawada', 'Father Jerry Zawada'], 'b' => [1937, 4, 28], 'd' => [2017, 7, 25], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'karl-yoneda', 'names' => ['Karl Goso Yoneda', 'Karl Yoneda'], 'b' => [1906, 7, 15], 'd' => [1999, 5, 8], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'max-shachtman', 'names' => ['Max Shachtman'], 'b' => [1904, 9, 10], 'd' => [1972, 11, 4], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'john-brophy', 'names' => ['John Brophy'], 'b' => [1883, 11, 6], 'd' => [1963, 2, 19], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'anthony-haswell', 'names' => ['Anthony Haswell'], 'b' => [1756, 4, 6], 'd' => [1816, 5, 26], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'thomas-cooper', 'names' => ['Thomas Cooper'], 'b' => [1759, 10, 22], 'd' => [1839, 5, 11], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'william-duane', 'names' => ['William Duane'], 'b' => [1760, 5, 17], 'd' => [1835, 11, 24], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'donald-ogden-stewart', 'names' => ['Donald Ogden Stewart'], 'b' => [1894, 11, 30], 'd' => [1980, 8, 2], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'ella-winter', 'names' => ['Ella Winter'], 'b' => [1898, 3, 17], 'd' => [1980, 8, 5], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'deskaheh', 'names' => ['Deskaheh', 'Levi General'], 'b' => [1873], 'd' => [1925, 6, 25], 'note' => 'Death also reported as June 27, 1925; no reliable exact birth month/day is established (birth year 1873).', 'fb' => false, 'fd' => true],
        ['slug' => 'tillie-olsen', 'names' => ['Tillie Olsen'], 'b' => [1912, 1, 14], 'd' => [2007, 1, 1], 'note' => 'Her birth year is disputed — some authoritative sources give January 14, 1913; this record uses 1912.', 'fb' => false, 'fd' => false],
        ['slug' => 'tillie-paul', 'names' => ['Tillie Paul Tamaree', 'Tillie Paul'], 'b' => [1863, 1, 18], 'd' => [1952, 8, 20], 'note' => 'Wrangell historical material notes her exact birth date is uncertain; commonly given as January 18, 1863.', 'fb' => false, 'fd' => false],
        ['slug' => 'chief-leschi', 'names' => ['Chief Leschi', 'Leschi'], 'b' => [1808], 'd' => [1858, 2, 19], 'note' => 'Birth year approximate (circa 1808).', 'fb' => false, 'fd' => false],
        ['slug' => 'lyda-conley', 'names' => ['Lyda Conley', 'Eliza Burton Conley'], 'b' => [1869], 'd' => [1946, 5, 28], 'note' => 'Birth year approximate (circa 1869).', 'fb' => false, 'fd' => false],
        ['slug' => 'dahteste', 'names' => ['Dahteste'], 'b' => [1860], 'd' => [1955], 'note' => 'Birth and death years approximate; no dependable exact month/day found.', 'fb' => false, 'fd' => false],
        ['slug' => 'charles-langston', 'names' => ['Charles Henry Langston', 'Charles Langston'], 'b' => [1817, 8, 31], 'd' => [1892, 12, 14], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'spencer-kellogg-brown', 'names' => ['Spencer Kellogg Brown'], 'b' => [1842, 8, 17], 'd' => [1863, 9, 25], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'isaac-w-k-handy', 'names' => ['Isaac William Ker Handy', 'Isaac W. K. Handy'], 'b' => [1815, 12, 14], 'd' => [1878, 6, 14], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'john-kehoe', 'names' => ['John "Black Jack" Kehoe', 'John Kehoe'], 'b' => [1837, 7, 3], 'd' => [1878, 12, 18], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'timothy-webster', 'names' => ['Timothy Webster'], 'b' => [1822, 3, 12], 'd' => [1862, 4, 29], 'note' => 'Pinkerton\'s institutional history gives April 30, 1862; this record uses April 29, 1862.', 'fb' => false, 'fd' => false],
        ['slug' => 'pryce-lewis', 'names' => ['Pryce Lewis'], 'b' => [1831, 2, 13], 'd' => [1911, 12, 6], 'note' => 'The St. Lawrence University collection gives an 1835 birth year; this record uses 1831 (published biography).', 'fb' => false, 'fd' => false],
        ['slug' => 'deane-mowrer', 'names' => ['Deane Mowrer'], 'b' => [1906, 7, 19], 'd' => [1989, 8, 4], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'george-willoughby', 'names' => ['George Willoughby'], 'b' => [1914, 12, 9], 'd' => [2010, 1, 5], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'joffre-stewart', 'names' => ['Joffre Lamar Stewart', 'Joffre Stewart'], 'b' => [1925, 4, 17], 'd' => [2019, 3, 12], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'corbett-bishop', 'names' => ['Corbett Bishop'], 'b' => [1906, 3, 9], 'd' => [1961, 5, 17], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'joseph-gelders', 'names' => ['Joseph Sidney Gelders', 'Joseph Gelders'], 'b' => [1898, 11, 20], 'd' => [1950, 3, 1], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'charles-greenlee', 'names' => ['Charles Lee Greenlee', 'Charles Greenlee'], 'b' => [1933, 6, 4], 'd' => [2012, 4, 18], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'kintpuash', 'names' => ['Kintpuash', 'Captain Jack'], 'b' => [1837], 'd' => [1873, 10, 3], 'note' => 'Birth year approximate (circa 1837).', 'fb' => false, 'fd' => false],
        ['slug' => 'crazy-horse', 'names' => ['Crazy Horse', 'Tȟašúŋke Witkó'], 'b' => [1840], 'd' => [1877, 9, 5], 'note' => 'Birth year approximate (circa 1840); exact birth date unknown.', 'fb' => false, 'fd' => false],
        ['slug' => 'alexander-campbell', 'names' => ['Alexander Campbell'], 'b' => [1833], 'd' => [1877, 6, 21], 'note' => 'Birth year approximate (circa 1833).', 'fb' => false, 'fd' => false],
        ['slug' => 'gullah-jack', 'names' => ['Gullah Jack Pritchard', 'Gullah Jack', 'Jack Pritchard'], 'b' => null, 'd' => [1822, 7, 12], 'note' => 'Born in the final years of the 18th century; no defensible exact birth date survives.', 'fb' => false, 'fd' => false],
        ['slug' => 'franklin-bache', 'names' => ['Franklin Bache', 'Benjamin Franklin Bache', 'Benjamin Bache'], 'b' => [1769, 8, 12], 'd' => [1798, 9, 10], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'william-chaplin', 'names' => ['William Chaplin', 'William Lawrence Chaplin', 'William L. Chaplin'], 'b' => [1796, 10, 27], 'd' => [1871, 4, 28], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'george-houser', 'names' => ['George Mills Houser', 'George Houser'], 'b' => [1916, 6, 2], 'd' => [2015, 8, 19], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'ralph-digia', 'names' => ['Ralph DiGia'], 'b' => [1914, 12, 13], 'd' => [2008, 2, 1], 'note' => null, 'fb' => false, 'fd' => true],
        ['slug' => 'james-peck', 'names' => ['James Peck'], 'b' => [1914, 12, 19], 'd' => [1993, 7, 12], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'harry-van-arsdale', 'names' => ['Harry Van Arsdale Jr.', 'Harry Van Arsdale'], 'b' => [1905, 11, 23], 'd' => [1986, 2, 16], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'anne-braden', 'names' => ['Anne McCarty Braden', 'Anne Braden'], 'b' => [1924, 7, 28], 'd' => [2006, 3, 6], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'david-mcreynolds', 'names' => ['David Ernest McReynolds', 'David McReynolds'], 'b' => [1929, 10, 25], 'd' => [2018, 8, 17], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'william-lorenzo-patterson', 'names' => ['William Lorenzo Patterson', 'William L. Patterson'], 'b' => [1891, 8, 27], 'd' => [1980, 3, 5], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'alphaeus-hunton', 'names' => ['William Alphaeus Hunton Jr.', 'Alphaeus Hunton', 'W. Alphaeus Hunton'], 'b' => [1903, 9, 18], 'd' => [1970, 1, 13], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'harry-sacher', 'names' => ['Harry Sacher'], 'b' => [1902, 8, 22], 'd' => [1963, 5, 22], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'calvin-woods', 'names' => ['Bishop Calvin Wallace Woods Sr.', 'Calvin Wallace Woods', 'Calvin Woods'], 'b' => [1933, 9, 13], 'd' => [2025, 8, 15], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'caroline-decker', 'names' => ['Caroline Decker Gladstein', 'Caroline Decker'], 'b' => [1912, 4, 26], 'd' => [1992, 5, 17], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'crisanto-evangelista', 'names' => ['Crisanto Evangelista'], 'b' => [1888, 11, 1], 'd' => [1942, 6, 2], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'thomas-banyacya', 'names' => ['Thomas Banyacya', 'Thomas Jenkins'], 'b' => [1909, 6, 2], 'd' => [1999, 2, 6], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'bernie-whitebear', 'names' => ['Bernie Whitebear', 'Bernard Reyes'], 'b' => [1937, 9, 27], 'd' => [2000, 7, 16], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'helen-john', 'names' => ['Helen John'], 'b' => [1937, 9, 30], 'd' => [2017, 11, 5], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'john-patrick-liteky', 'names' => ['John Patrick Liteky', 'John Liteky'], 'b' => [1940, 3, 26], 'd' => [2008, 7, 23], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'michael-lerner', 'names' => ['Michael Lerner'], 'b' => [1943, 2, 11], 'd' => [2024, 8, 28], 'note' => 'Some reference works give February 7, 1943; this record uses February 11 (contemporary Jewish press obituary).', 'fb' => false, 'fd' => false],
        ['slug' => 'mary-dann', 'names' => ['Mary Dann'], 'b' => [1923, 1, 2], 'd' => [2005, 4, 22], 'note' => 'Mary Dann did not publicly disclose her exact age; the January 2, 1923 birth date comes from biographical records and should be treated as approximate.', 'fb' => false, 'fd' => false],
        ['slug' => 'carrie-dann', 'names' => ['Carrie Dann'], 'b' => [1933, 12, 9], 'd' => [2021, 1, 1], 'note' => 'Carrie Dann had no birth certificate; relatives estimated her birth between roughly 1932 and 1934, so the December 9, 1933 date (Nevada records) is disputed.', 'fb' => false, 'fd' => false],
        ['slug' => 'ellen-moves-camp', 'names' => ['Ellen Moves Camp'], 'b' => [1930, 9, 25], 'd' => [2008, 4, 5], 'note' => 'Sources alternate between 1930 and 1931 for her birth year; this record uses September 25, 1930 (her obituary gives her age as 77 at death).', 'fb' => false, 'fd' => false],
        ['slug' => 'claude-c-williams', 'names' => ['Claude Clossey Williams', 'Claude C. Williams', 'Claude Williams'], 'b' => [1895], 'd' => [1979, 6, 28], 'note' => 'The Walter P. Reuther Library finding aid gives a death date of June 28, 1979; other references give June 29. No reliable exact birth month/day is established.', 'fb' => false, 'fd' => false],
        ['slug' => 'anni-rainbow', 'names' => ['Anni Rainbow'], 'b' => null, 'd' => [2022, 6, 24], 'note' => 'Her exact birth date was not located — born circa 1948–1949 (obituary reported age 73 at death). Death date per the Menwith Hill campaign newsletter.', 'fb' => false, 'fd' => true],
        ['slug' => 'aunt-molly-jackson', 'names' => ['Aunt Molly Jackson', 'Mary Magdalene Garland'], 'b' => [1880], 'd' => [1960, 9, 1], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'bayard-rustin', 'names' => ['Bayard Rustin'], 'b' => [1912, 3, 17], 'd' => [1987, 8, 24], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'benjamin-j-davis-jr', 'names' => ['Benjamin J. Davis Jr.', 'Benjamin J. Davis'], 'b' => [1903, 9, 8], 'd' => [1964, 8, 22], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'howard-fast', 'names' => ['Howard Fast'], 'b' => [1914, 11, 11], 'd' => [2003, 3, 12], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'claudia-jones', 'names' => ['Claudia Vera Jones', 'Claudia Jones'], 'b' => [1915, 2, 21], 'd' => [1964, 12, 24], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'john-gates', 'names' => ['John Gates', 'Solomon Regenstreif'], 'b' => [1913, 9, 28], 'd' => [1992, 5, 23], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'pettis-perry', 'names' => ['Pettis Perry'], 'b' => [1897, 1, 4], 'd' => [1965, 7, 24], 'note' => null, 'fb' => false, 'fd' => true],
        ['slug' => 'lawrence-ferlinghetti', 'names' => ['Lawrence Ferlinghetti'], 'b' => [1919, 3, 24], 'd' => [2021, 2, 22], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'shigeyoshi-murao', 'names' => ['Shigeyoshi Murao', 'Shig Murao'], 'b' => [1926, 12, 8], 'd' => [1999, 10, 18], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'frederick-vanderbilt-field', 'names' => ['Frederick Vanderbilt Field', 'Frederick V. Field'], 'b' => [1905, 4, 13], 'd' => [2000, 2, 1], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'barrows-dunham', 'names' => ['Barrows Dunham'], 'b' => [1905, 10, 10], 'd' => [1995, 11, 14], 'note' => 'Several online authority records display November 19, 1995; this record uses November 14 (contemporary Philadelphia Inquirer obituary).', 'fb' => false, 'fd' => false],
        ['slug' => 'theodore-parker', 'names' => ['Theodore Parker'], 'b' => [1810, 8, 24], 'd' => [1860, 5, 10], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'thomas-wentworth-higginson', 'names' => ['Thomas Wentworth Higginson'], 'b' => [1823, 12, 22], 'd' => [1911, 5, 9], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'wendell-phillips', 'names' => ['Wendell Phillips'], 'b' => [1811, 11, 29], 'd' => [1884, 2, 2], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'thomas-garrett', 'names' => ['Thomas Garrett'], 'b' => [1789, 8, 21], 'd' => [1871, 1, 25], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'samuel-d-burris', 'names' => ['Samuel D. Burris', 'Samuel Burris'], 'b' => [1813, 10, 14], 'd' => [1863, 12, 3], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'john-hunn', 'names' => ['John Hunn Sr.', 'John Hunn'], 'b' => [1818, 6, 25], 'd' => [1894, 7, 6], 'note' => null, 'fb' => false, 'fd' => false],
        ['slug' => 'richard-eells', 'names' => ['Dr. Richard Eells', 'Richard Eells'], 'b' => [1800, 2, 23], 'd' => [1846, 10, 4], 'note' => 'The February 23, 1800 birth date appears in cemetery and genealogical records; institutional histories give only 1800. Death (October 4, 1846) is firmly documented.', 'fb' => false, 'fd' => false],
        ['slug' => 'castner-hanway', 'names' => ['Castner Hanway'], 'b' => [1821], 'd' => [1893, 5, 26], 'note' => 'No exact birth month/day is established (birth year 1821); death date May 26, 1893 per an early historical account.', 'fb' => false, 'fd' => false],
    ];

    private const RANK = ['year' => 1, 'month' => 2, 'day' => 3];

    public function handle(): int
    {
        $changed = 0;
        $missing = [];

        foreach (self::ENTRIES as $e) {
            $p = $this->resolve($e['slug'], $e['names']);
            if (! $p) {
                $missing[] = $e['names'][0];

                continue;
            }

            $log = [];
            if ($e['b']) {
                $this->applyDate($p, 'birthdate', $e['b'], $e['fb'], $log);
            }
            if ($e['d']) {
                $this->applyDate($p, 'death_date', $e['d'], $e['fd'], $log);
            }
            if ($e['note']) {
                $desc = (string) $p->description;
                if (mb_stripos($desc, $e['note']) === false) {
                    $p->description = trim($desc) === ''
                        ? $e['note']
                        : rtrim($desc)."\n\nNote: ".$e['note'];
                    $log[] = '  note appended';
                }
            }

            if ($log) {
                $p->save();
                $this->info("{$p->name}:");
                foreach ($log as $l) {
                    $this->line($l);
                }
                $changed++;
            } else {
                $this->line("{$p->name}: nothing to change.");
            }
        }

        Cache::forget(PrisonerApiController::cacheKey());

        $this->info("\nDone. Updated {$changed} record(s).");
        if ($missing) {
            $this->warn('Not found ('.count($missing).'): '.implode(', ', $missing));
        }

        return self::SUCCESS;
    }

    /**
     * @param  array{0:int,1?:int,2?:int}  $parts
     * @param  string[]  $log
     */
    private function applyDate(Prisoner $p, string $field, array $parts, bool $force, array &$log): void
    {
        $y = $parts[0];
        $m = $parts[1] ?? null;
        $d = $parts[2] ?? null;
        $newPrec = $d ? 'day' : ($m ? 'month' : 'year');

        $cur = $p->{$field};
        $curPrec = $p->date_precision[$field] ?? null;

        $set = false;
        if ($cur === null) {
            $set = true;
        } elseif ($force) {
            $set = true;
        } elseif ($curPrec !== null && self::RANK[$newPrec] > self::RANK[$curPrec]
            && (int) $cur->format('Y') === $y
            && ($curPrec === 'year' || (int) $cur->format('n') === ($m ?? 0))) {
            $set = true; // finer, consistent → upgrade
        }

        if (! $set) {
            $log[] = "  {$field}: kept existing (".$cur->format('Y-m-d').', prec '.($curPrec ?? '?').')';

            return;
        }

        $p->setPartialDate($field, $y, $m, $d);
        $log[] = "  {$field} => ".sprintf('%04d-%02d-%02d', $y, $m ?: 1, $d ?: 1)." ({$newPrec})";
    }

    /**
     * @param  string[]  $names
     */
    private function resolve(string $slug, array $names): ?Prisoner
    {
        $p = Prisoner::withUnderReview()->where('slug', $slug)->first();
        if ($p) {
            return $p;
        }
        foreach ($names as $name) {
            $p = Prisoner::withUnderReview()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
            if ($p) {
                return $p;
            }
        }

        return null;
    }
}
