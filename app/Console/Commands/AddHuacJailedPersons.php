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
 * People imprisoned as a direct result of the House Un-American Activities
 * Committee (HUAC) — the congressional inquisition of the Red Scare that jailed
 * witnesses who refused, on First Amendment grounds, to answer its questions or
 * surrender their organizations’ records. Drawn from the HUAC Wikipedia article
 * and cross-checked (by name) as not already in the database:
 *
 *   The Hollywood Ten (contempt 1947, jailed 1950–51):
 *     John Howard Lawson, Dalton Trumbo, Alvah Bessie, Lester Cole,
 *     Ring Lardner Jr., Albert Maltz, Samuel Ornitz, Adrian Scott,
 *     Herbert Biberman, Edward Dmytryk
 *   Joint Anti-Fascist Refugee Committee board (contempt 1947, jailed 1950):
 *     Dr. Edward K. Barsky, Helen R. Bryan, Howard Fast,
 *     Edwin Berry Burgum, Lyman R. Bradley
 *   Other 1947 HUAC contempt cases:
 *     Eugene Dennis, Leon Josephson, Gerhart Eisler
 *   Ku Klux Klan officials jailed for contempt (1969), included for factual
 *   completeness of “people jailed by HUAC”:
 *     Robert Shelton, Robert Scoggin, J. Robert “Bob” Jones
 *
 * Sourced to the HUAC and Hollywood-blacklist records, Barsky v. United States
 * (167 F.2d 241), Joint Anti-Fascist Refugee Committee v. McGrath, and the
 * standard biographies. Idempotent — create-or-update by name: fills the
 * (often pre-seeded, empty) existing record and rebuilds its single case.
 */
class AddHuacJailedPersons extends Command
{
    protected $signature = 'prisoners:add-huac-jailed';

    protected $description = 'Add people jailed by the House Un-American Activities Committee (Hollywood Ten, JAFRC board, and other contempt cases)';

    /** Shared framing for the Hollywood Ten. First %s = “NAME, a ...”. */
    private const TEN = '%s was one of the “Hollywood Ten,” the screenwriters, directors and producers who in October 1947 were subpoenaed before the House Un-American Activities Committee at the outset of its investigation into alleged Communist influence in the motion-picture industry. All ten refused to answer the committee’s central question — “Are you now, or have you ever been, a member of the Communist Party?” — arguing that the inquiry violated their First Amendment rights of belief and association. On November 24, 1947 the House cited them for contempt of Congress; they were convicted the following year, and after John Howard Lawson’s and Trumbo’s appeals failed as test cases and the Supreme Court declined to hear them in 1950, all ten served federal prison terms. Every one of them was blacklisted by the studios under the “Waldorf Statement.”';

    private const TEN_CHARGES = 'Contempt of Congress — for refusing, on First Amendment grounds, to tell the House Un-American Activities Committee whether they were or had been members of the Communist Party.';

    private const TEN_CONVICTED = 'Yes — convicted of contempt of Congress (1948); the Supreme Court declined to review the convictions in 1950.';

    /** JAFRC board members (three months, $500). First %s = “NAME, a ...”. */
    private const JAFRC = '%s was one of the officers and executive-board members of the Joint Anti-Fascist Refugee Committee (JAFRC) — the New York relief organization that aided refugees of the Spanish Civil War and survivors of Nazi concentration camps — who were jailed for defying the House Un-American Activities Committee. Subpoenaed in 1946, the board refused to hand over the committee’s records and lists of donors, and in June 1947 a federal jury convicted all of them of contempt of Congress. Their appeal, Barsky v. United States, was rejected, and after the Supreme Court declined to intervene they surrendered in 1950 to serve their sentences.';

    private const JAFRC_CHARGES = 'Contempt of Congress — for refusing to surrender the Joint Anti-Fascist Refugee Committee’s books, records and lists of contributors to the House Un-American Activities Committee.';

    private const JAFRC_CONVICTED = 'Yes — convicted of contempt of Congress (June 1947); conviction affirmed in Barsky v. United States and left standing by the Supreme Court.';

    /**
     * Ku Klux Klan officials jailed for contempt of HUAC. Included for factual
     * completeness of “people jailed by HUAC” — not as sympathetic subjects.
     * First %s = “NAME, the ...”.
     */
    private const KLAN = '%s was one of the Ku Klux Klan officials jailed for contempt of Congress after refusing to cooperate with the House Un-American Activities Committee, which in 1965–66 turned its investigative machinery on the Klan amid the era’s wave of racist terror against the civil-rights movement. Subpoenaed to produce the Klan’s membership rolls and financial records, he invoked the Fifth Amendment and refused; in 1966 the House cited seven Klan leaders for contempt. Unlike the left-wing witnesses HUAC had jailed in the 1940s, these were officers of a violent white-supremacist organization — but the committee’s use of the contempt power against them followed the same pattern, and the convictions were upheld, sending several Klan leaders to federal prison in 1969.';

    private const KLAN_CHARGES = 'Contempt of Congress — for refusing to produce the Ku Klux Klan’s membership and financial records subpoenaed by the House Un-American Activities Committee.';

    private const KLAN_CONVICTED = 'Yes — cited for contempt of Congress in 1966 and convicted; the conviction was upheld on appeal.';

    public function handle(): int
    {
        // Institutions (only where the placement is well documented).
        $ashland = Institution::firstOrCreate(['name' => 'Federal Correctional Institution, Ashland'], ['city' => 'Ashland', 'state' => 'Kentucky']);
        $danbury = Institution::firstOrCreate(['name' => 'Federal Correctional Institution, Danbury'], ['city' => 'Danbury', 'state' => 'Connecticut']);
        $petersburg = Institution::firstOrCreate(['name' => 'Federal Reformatory, Petersburg'], ['city' => 'Petersburg', 'state' => 'Virginia']);
        $atlanta = Institution::firstOrCreate(['name' => 'United States Penitentiary, Atlanta'], ['city' => 'Atlanta', 'state' => 'Georgia']);
        $texarkana = Institution::firstOrCreate(['name' => 'Federal Correctional Institution, Texarkana'], ['city' => 'Texarkana', 'state' => 'Texas']);
        $latuna = Institution::firstOrCreate(['name' => 'Federal Correctional Institution, La Tuna'], ['city' => 'Anthony', 'state' => 'Texas']);

        $people = [
            // ---- The Hollywood Ten ----
            [
                'name' => 'John Howard Lawson', 'first' => 'John', 'middle' => 'Howard', 'last' => 'Lawson',
                'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'era' => '1940s',
                'ideologies' => ['Communism', 'Free speech', 'Anti-fascism'],
                'affiliation' => ['Hollywood Ten', 'Screen Writers Guild', 'Communist Party USA'],
                'bio' => sprintf(self::TEN, 'John Howard Lawson, a screenwriter and playwright who had been the first president of the Screen Writers Guild and was regarded as the intellectual “dean” of the group').' Lawson’s combative testimony — gaveled down by chairman J. Parnell Thomas — became the emblem of the confrontation, and his conviction was carried through the courts as one of the two test cases for all ten.',
                'charges' => self::TEN_CHARGES, 'convicted' => self::TEN_CONVICTED,
                'sentence' => 'One year in federal prison and a $1,000 fine, served in 1950 at the Federal Correctional Institution in Ashland, Kentucky.',
                'institution_id' => $ashland->id,
            ],
            [
                'name' => 'Dalton Trumbo', 'first' => 'Dalton', 'last' => 'Trumbo',
                'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'era' => '1940s',
                'ideologies' => ['Communism', 'Free speech', 'Anti-fascism'],
                'affiliation' => ['Hollywood Ten', 'Screen Writers Guild', 'Communist Party USA'],
                'bio' => sprintf(self::TEN, 'Dalton Trumbo, at the time one of the highest-paid screenwriters in Hollywood and the author of the antiwar novel “Johnny Got His Gun”').' His conviction was the second of the two test cases. After his release Trumbo kept working under pseudonyms and fronts — winning two Academy Awards in secret — and his open screen credits for “Spartacus” and “Exodus” in 1960 are widely credited with breaking the blacklist.',
                'charges' => self::TEN_CHARGES, 'convicted' => self::TEN_CONVICTED,
                'sentence' => 'One year in federal prison (of which he served about ten months) and a $1,000 fine, at the Federal Correctional Institution in Ashland, Kentucky.',
                'institution_id' => $ashland->id,
            ],
            [
                'name' => 'Alvah Bessie', 'first' => 'Alvah', 'last' => 'Bessie',
                'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'era' => '1940s',
                'ideologies' => ['Communism', 'Free speech', 'Anti-fascism'],
                'affiliation' => ['Hollywood Ten', 'Screen Writers Guild', 'Communist Party USA', 'Abraham Lincoln Brigade'],
                'bio' => sprintf(self::TEN, 'Alvah Bessie, a screenwriter and novelist who had volunteered with the Abraham Lincoln Brigade to fight fascism in the Spanish Civil War').' Blacklisted for the rest of his Hollywood career, he later worked as a stagehand and wrote “Inquisition in Eden,” a memoir of the ordeal.',
                'charges' => self::TEN_CHARGES, 'convicted' => self::TEN_CONVICTED,
                'sentence' => 'One year in federal prison and a $1,000 fine, served in 1950.',
                'institution_id' => null,
            ],
            [
                'name' => 'Lester Cole', 'first' => 'Lester', 'last' => 'Cole',
                'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'era' => '1940s',
                'ideologies' => ['Communism', 'Free speech', 'Anti-fascism'],
                'affiliation' => ['Hollywood Ten', 'Screen Writers Guild', 'Communist Party USA'],
                'bio' => sprintf(self::TEN, 'Lester Cole, a screenwriter and a founding member of the Screen Writers Guild').' He served his term at the federal prison in Danbury, Connecticut — where, by a notorious irony, a fellow inmate was J. Parnell Thomas, the former HUAC chairman who had jailed him and was himself now imprisoned for payroll fraud.',
                'charges' => self::TEN_CHARGES, 'convicted' => self::TEN_CONVICTED,
                'sentence' => 'One year in federal prison and a $1,000 fine, served at the Federal Correctional Institution in Danbury, Connecticut.',
                'institution_id' => $danbury->id,
            ],
            [
                'name' => 'Ring Lardner Jr.', 'first' => 'Ring', 'middle' => 'Wilmer', 'last' => 'Lardner',
                'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'era' => '1940s',
                'ideologies' => ['Communism', 'Free speech', 'Anti-fascism'],
                'affiliation' => ['Hollywood Ten', 'Screen Writers Guild', 'Communist Party USA'],
                'bio' => sprintf(self::TEN, 'Ring Lardner Jr., a screenwriter and son of the humorist Ring Lardner, who had already won an Academy Award for “Woman of the Year”').' Asked whether he was a Communist, he famously answered, “I could answer that, but I’d hate myself in the morning.” He served his sentence at Danbury alongside Lester Cole, resumed writing under pseudonyms, and two decades later won a second Oscar for “M*A*S*H.”',
                'charges' => self::TEN_CHARGES, 'convicted' => self::TEN_CONVICTED,
                'sentence' => 'One year in federal prison and a $1,000 fine, served at the Federal Correctional Institution in Danbury, Connecticut.',
                'institution_id' => $danbury->id,
            ],
            [
                'name' => 'Albert Maltz', 'first' => 'Albert', 'last' => 'Maltz',
                'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'era' => '1940s',
                'ideologies' => ['Communism', 'Free speech', 'Anti-fascism'],
                'affiliation' => ['Hollywood Ten', 'Screen Writers Guild', 'Communist Party USA'],
                'bio' => sprintf(self::TEN, 'Albert Maltz, an award-winning short-story writer, novelist and screenwriter').' Blacklisted after his release, he lived and worked for years in exile in Mexico and did not receive open screen credit again until the 1970s.',
                'charges' => self::TEN_CHARGES, 'convicted' => self::TEN_CONVICTED,
                'sentence' => 'One year in federal prison and a $1,000 fine, served in 1950.',
                'institution_id' => null,
            ],
            [
                'name' => 'Samuel Ornitz', 'first' => 'Samuel', 'last' => 'Ornitz',
                'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'era' => '1940s',
                'ideologies' => ['Communism', 'Free speech', 'Anti-fascism'],
                'affiliation' => ['Hollywood Ten', 'Screen Writers Guild', 'Communist Party USA'],
                'bio' => sprintf(self::TEN, 'Samuel Ornitz, a novelist and screenwriter and a founding member of the Screen Writers Guild').' In poor health, he served his term and was blacklisted for the remainder of his life; he died in 1957.',
                'charges' => self::TEN_CHARGES, 'convicted' => self::TEN_CONVICTED,
                'sentence' => 'One year in federal prison and a $1,000 fine, served in 1950.',
                'institution_id' => null,
            ],
            [
                'name' => 'Adrian Scott', 'first' => 'Adrian', 'last' => 'Scott',
                'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'era' => '1940s',
                'ideologies' => ['Communism', 'Free speech', 'Anti-fascism'],
                'affiliation' => ['Hollywood Ten', 'Communist Party USA'],
                'bio' => sprintf(self::TEN, 'Adrian Scott, the producer of the acclaimed anti-antisemitism film noir “Crossfire” (1947), which was in theaters even as HUAC subpoenaed him').' The only producer among the Ten, he was blacklisted for the rest of his career and worked afterward under his wife’s name.',
                'charges' => self::TEN_CHARGES, 'convicted' => self::TEN_CONVICTED,
                'sentence' => 'One year in federal prison and a $1,000 fine, served in 1950.',
                'institution_id' => null,
            ],
            [
                'name' => 'Herbert Biberman', 'first' => 'Herbert', 'last' => 'Biberman',
                'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'era' => '1940s',
                'ideologies' => ['Communism', 'Free speech', 'Anti-fascism'],
                'affiliation' => ['Hollywood Ten', 'Communist Party USA'],
                'bio' => sprintf(self::TEN, 'Herbert Biberman, a screenwriter and director').' After his release he defied the blacklist by independently producing and directing “Salt of the Earth” (1954), a landmark pro-labor film made largely by blacklisted artists that was suppressed on its release.',
                'charges' => self::TEN_CHARGES, 'convicted' => self::TEN_CONVICTED,
                'sentence' => 'Six months in federal prison and a $500 fine (a lighter term than the other eight), served in 1950.',
                'institution_id' => null,
            ],
            [
                'name' => 'Edward Dmytryk', 'first' => 'Edward', 'last' => 'Dmytryk',
                'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'era' => '1940s',
                'ideologies' => ['Communism', 'Free speech', 'Anti-fascism'],
                'affiliation' => ['Hollywood Ten', 'Communist Party USA'],
                'bio' => sprintf(self::TEN, 'Edward Dmytryk, the director of “Murder, My Sweet” and the Oscar-nominated “Crossfire”').' He, too, went to prison for contempt — but partway through his sentence he changed course, and in 1951 he appeared again before HUAC as a cooperative witness, admitted his former Party membership and named more than twenty others, becoming the only one of the Ten to renounce his stand and be removed from the blacklist.',
                'charges' => self::TEN_CHARGES, 'convicted' => self::TEN_CONVICTED,
                'sentence' => 'Six months in federal prison and a $500 fine, served in 1950; he was released after serving part of the term and afterward cooperated with the committee.',
                'institution_id' => null,
            ],

            // ---- Joint Anti-Fascist Refugee Committee board ----
            [
                'name' => 'Edward K. Barsky', 'first' => 'Edward', 'middle' => 'K.', 'last' => 'Barsky',
                'gender' => 'Male', 'race' => 'White', 'state' => 'New York', 'era' => '1940s',
                'ideologies' => ['Anti-fascism', 'Communism', 'Free speech'],
                'affiliation' => ['Joint Anti-Fascist Refugee Committee', 'Abraham Lincoln Brigade'],
                'bio' => sprintf(self::JAFRC, 'Dr. Edward K. Barsky, a New York surgeon who had organized and led the American Medical Bureau’s volunteer hospitals for the Spanish Republic during the Civil War and served as chairman of the JAFRC').' As chairman he received the heaviest sentence. His persecution did not end with prison: New York regents suspended his medical license for six months, a punishment the Supreme Court upheld in Barsky v. Board of Regents (1954).',
                'charges' => self::JAFRC_CHARGES, 'convicted' => self::JAFRC_CONVICTED,
                'sentence' => 'Six months in federal prison and a $500 fine, served in 1950 at the Federal Reformatory in Petersburg, Virginia.',
                'institution_id' => $petersburg->id,
            ],
            [
                'name' => 'Helen R. Bryan', 'first' => 'Helen', 'middle' => 'R.', 'last' => 'Bryan',
                'gender' => 'Female', 'race' => 'White', 'state' => 'New York', 'era' => '1940s',
                'ideologies' => ['Anti-fascism', 'Free speech'],
                'affiliation' => ['Joint Anti-Fascist Refugee Committee'],
                'bio' => sprintf(self::JAFRC, 'Helen R. Bryan, the executive secretary of the JAFRC').' She refused to divulge the committee’s records and donor lists and served three months in prison for it.',
                'charges' => self::JAFRC_CHARGES, 'convicted' => self::JAFRC_CONVICTED,
                'sentence' => 'Three months in federal prison and a $500 fine, served in 1950.',
                'institution_id' => null,
            ],
            [
                'name' => 'Howard Fast', 'first' => 'Howard', 'last' => 'Fast',
                'gender' => 'Male', 'race' => 'White', 'state' => 'New York', 'era' => '1940s',
                'ideologies' => ['Communism', 'Anti-fascism', 'Free speech'],
                'affiliation' => ['Joint Anti-Fascist Refugee Committee', 'Communist Party USA'],
                'bio' => sprintf(self::JAFRC, 'Howard Fast, the best-selling novelist (author of “Citizen Tom Paine” and “Freedom Road”) and a member of the JAFRC board').' Denied a mainstream publisher after his imprisonment and blacklisting, he wrote his novel “Spartacus” while behind bars and in its aftermath and published it himself — the book Stanley Kubrick and Dalton Trumbo would later film in the movie that helped break the blacklist.',
                'charges' => self::JAFRC_CHARGES, 'convicted' => self::JAFRC_CONVICTED,
                'sentence' => 'Three months in federal prison and a $500 fine, served in 1950.',
                'institution_id' => null,
            ],
            [
                'name' => 'Edwin Berry Burgum', 'first' => 'Edwin', 'middle' => 'Berry', 'last' => 'Burgum',
                'gender' => 'Male', 'race' => 'White', 'state' => 'New York', 'era' => '1940s',
                'ideologies' => ['Anti-fascism', 'Communism', 'Free speech'],
                'affiliation' => ['Joint Anti-Fascist Refugee Committee'],
                'bio' => sprintf(self::JAFRC, 'Edwin Berry Burgum, a professor of English at New York University and a literary critic who sat on the JAFRC board').' His stand cost him more than his liberty: NYU suspended and ultimately dismissed him for invoking the Fifth Amendment before a later Senate inquiry.',
                'charges' => self::JAFRC_CHARGES, 'convicted' => self::JAFRC_CONVICTED,
                'sentence' => 'Three months in federal prison and a $500 fine, served in 1950.',
                'institution_id' => null,
            ],
            [
                'name' => 'Lyman R. Bradley', 'first' => 'Lyman', 'middle' => 'R.', 'last' => 'Bradley',
                'gender' => 'Male', 'race' => 'White', 'state' => 'New York', 'era' => '1940s',
                'ideologies' => ['Anti-fascism', 'Free speech'],
                'affiliation' => ['Joint Anti-Fascist Refugee Committee'],
                'bio' => sprintf(self::JAFRC, 'Lyman R. Bradley, chairman of the German department at New York University and a treasurer of the JAFRC').' Like his colleague Edwin Berry Burgum, he was fired by NYU in the wake of the case, becoming one of the earliest academics purged during the McCarthy era.',
                'charges' => self::JAFRC_CHARGES, 'convicted' => self::JAFRC_CONVICTED,
                'sentence' => 'Three months in federal prison and a $500 fine, served in 1950.',
                'institution_id' => null,
            ],

            // ---- Other 1947 HUAC contempt cases ----
            [
                'name' => 'Eugene Dennis', 'first' => 'Eugene', 'last' => 'Dennis',
                'gender' => 'Male', 'race' => 'White', 'state' => 'New York', 'era' => '1940s',
                'ideologies' => ['Communism', 'Free speech'],
                'affiliation' => ['Communist Party USA'],
                'bio' => 'Eugene Dennis was the General Secretary of the Communist Party USA. In 1947 he refused to appear before the House Un-American Activities Committee, challenging the legitimacy of its inquiry into his politics, and was cited and convicted of contempt of Congress. The Supreme Court let the conviction stand, and in 1950 he served a one-year term at the federal penitentiary in Atlanta. His imprisonment for contempt was only the beginning of his ordeal: he was simultaneously a lead defendant in the 1949 Foley Square trial, where the Party’s leaders were convicted under the Smith Act of conspiring to advocate the overthrow of the government, and after the Supreme Court affirmed those convictions in Dennis v. United States (1951) he returned to prison for a five-year sentence.',
                'charges' => 'Contempt of Congress — for refusing to appear before the House Un-American Activities Committee in 1947.',
                'convicted' => 'Yes — convicted of contempt of Congress; conviction upheld by the Supreme Court in 1950.',
                'sentence' => 'One year in federal prison, served in 1950 at the United States Penitentiary in Atlanta, Georgia.',
                'institution_id' => $atlanta->id,
            ],
            [
                'name' => 'Leon Josephson', 'first' => 'Leon', 'last' => 'Josephson',
                'gender' => 'Male', 'race' => 'White', 'state' => 'New York', 'era' => '1940s',
                'ideologies' => ['Communism', 'Anti-fascism', 'Free speech'],
                'affiliation' => ['Communist Party USA'],
                'bio' => 'Leon Josephson was a New York attorney and Communist Party activist — an anti-fascist who in the 1930s had been involved in efforts to obtain passports for agents working against Nazi Germany. Summoned before the House Un-American Activities Committee in 1947, he refused even to be sworn in, telling the committee, “I refuse to be sworn,” and declaring that he would not testify until the courts had ruled on the committee’s legality. He was cited and convicted of contempt of Congress and sentenced to a year in federal prison, one of the first defendants jailed in HUAC’s postwar drive.',
                'charges' => 'Contempt of Congress — for refusing to be sworn or to testify before the House Un-American Activities Committee in 1947.',
                'convicted' => 'Yes — convicted of contempt of Congress (1947).',
                'sentence' => 'One year in federal prison.',
                'institution_id' => null,
            ],
            [
                'name' => 'Gerhart Eisler', 'first' => 'Gerhart', 'last' => 'Eisler',
                'gender' => 'Male', 'race' => 'White', 'state' => 'New York', 'era' => '1940s',
                'ideologies' => ['Communism', 'Anti-fascism', 'Free speech'],
                'affiliation' => ['Communist Party USA', 'Communist International'],
                'bio' => 'Gerhart Eisler was a German-born Communist and former Comintern functionary whom the House Un-American Activities Committee publicized as the secret “number one Communist” directing the Party in the United States. Called before the committee in February 1947, he refused to be sworn until he could first make a statement, and was cited for contempt of Congress; he was separately charged with passport fraud. Convicted, and free on bail while his appeals were pending, in 1949 he stowed away aboard the Polish ocean liner Batory and escaped to East Germany, where he became a senior official of the new German Democratic Republic and its chief of radio broadcasting until his death in 1968.',
                'charges' => 'Contempt of Congress — for refusing to be sworn before the House Un-American Activities Committee (February 1947) — together with a separate passport-fraud charge.',
                'convicted' => 'Yes — convicted of contempt of Congress; he fled the country in 1949 while free on bail pending appeal.',
                'sentence' => 'Sentenced for contempt of Congress; escaped to East Germany in 1949 before serving the term.',
                'institution_id' => null,
                'in_exile' => true,
                'currently_in_exile' => false,
                'released' => false,
                'birth' => [1897, 2, 20],
                'death' => [1968, 3, 21],
                'in_exile_since' => [1949, 5],   // fled aboard the MS Batory, May 1949
                'end_of_exile' => [1968, 3, 21], // remained in exile until his death
                'photo' => 'gerhart-eisler.jpg',
            ],

            // ---- Ku Klux Klan officials jailed for contempt (1969) ----
            [
                'name' => 'Robert Shelton', 'first' => 'Robert', 'middle' => 'Marvin', 'last' => 'Shelton',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Alabama', 'era' => '1960s',
                'ideologies' => ['White supremacy'],
                'affiliation' => ['United Klans of America', 'Ku Klux Klan'],
                'bio' => sprintf(self::KLAN, 'Robert Shelton, the Imperial Wizard of the United Klans of America — then the largest Klan organization in the country').' He was found guilty and sentenced to a year in prison and a $1,000 fine; after exhausting his appeals he served nine months at a federal prison in Texarkana, Texas, in 1969.',
                'charges' => self::KLAN_CHARGES, 'convicted' => self::KLAN_CONVICTED,
                'sentence' => 'One year in federal prison and a $1,000 fine; served nine months at the federal prison in Texarkana, Texas, in 1969.',
                'institution_id' => $texarkana->id,
            ],
            [
                'name' => 'Robert Scoggin', 'first' => 'Robert', 'last' => 'Scoggin',
                'gender' => 'Male', 'race' => 'White', 'state' => 'South Carolina', 'era' => '1960s',
                'ideologies' => ['White supremacy'],
                'affiliation' => ['United Klans of America', 'Ku Klux Klan'],
                'bio' => sprintf(self::KLAN, 'Robert Scoggin, the Grand Dragon of the South Carolina realm of the United Klans of America').' He refused to comply and was sentenced to a year for contempt of Congress, which he began serving at the federal prison at La Tuna, Texas, in April 1969.',
                'charges' => self::KLAN_CHARGES, 'convicted' => self::KLAN_CONVICTED,
                'sentence' => 'One year in federal prison, served beginning April 1969 at the federal prison at La Tuna, Texas.',
                'institution_id' => $latuna->id,
            ],
            [
                'name' => 'J. Robert Jones', 'first' => 'J.', 'middle' => 'Robert', 'last' => 'Jones', 'aka' => 'Bob Jones',
                'gender' => 'Male', 'race' => 'White', 'state' => 'North Carolina', 'era' => '1960s',
                'ideologies' => ['White supremacy'],
                'affiliation' => ['United Klans of America', 'Ku Klux Klan'],
                'bio' => sprintf(self::KLAN, 'J. Robert “Bob” Jones, the Grand Dragon of the North Carolina realm of the United Klans of America — which under his leadership had become one of the most active Klan organizations in the country').' Cited for contempt along with Shelton and Scoggin, he was convicted and sentenced to a year in federal prison.',
                'charges' => self::KLAN_CHARGES, 'convicted' => self::KLAN_CONVICTED,
                'sentence' => 'One year in federal prison for contempt of Congress.',
                'institution_id' => null,
            ],
        ];

        DB::transaction(function () use ($people) {
            foreach ($people as $p) {
                // Many of these people were pre-seeded as empty stubs (no bio,
                // no case). Fill the existing record instead of skipping it;
                // only create a new one when truly absent. fill() touches only
                // the listed columns, so any photo/birthdate already present is
                // preserved.
                $existing = Prisoner::withUnderReview()->where('name', $p['name'])->first();
                $prisoner = $existing ?? new Prisoner(['name' => $p['name']]);

                $prisoner->fill([
                    'name' => $p['name'],
                    'first_name' => $p['first'] ?? null,
                    'middle_name' => $p['middle'] ?? null,
                    'last_name' => $p['last'] ?? null,
                    'aka' => $p['aka'] ?? null,
                    'description' => $p['bio'],
                    'gender' => $p['gender'] ?? null,
                    'race' => $p['race'] ?? null,
                    'state' => $p['state'] ?? null,
                    'era' => $p['era'] ?? null,
                    'ideologies' => $p['ideologies'] ?? [],
                    'affiliation' => $p['affiliation'] ?? [],
                    'in_custody' => false,
                    'released' => $p['released'] ?? true,
                    'in_exile' => $p['in_exile'] ?? false,
                    'currently_in_exile' => $p['currently_in_exile'] ?? false,
                    'awaiting_trial' => false,
                ]);
                if (! empty($p['birth'])) {
                    $prisoner->setPartialDate('birthdate', ...$p['birth']);
                }
                if (! empty($p['death'])) {
                    $prisoner->setPartialDate('death_date', ...$p['death']);
                }
                $prisoner->save();

                // Attach a bundled portrait (non-free) if the record has none.
                if (! empty($p['photo'])) {
                    $src = database_path('data/photos/nonfree/'.$p['photo']);
                    if (is_file($src) && empty($prisoner->photo)) {
                        Storage::disk('public')->makeDirectory('prisoners');
                        Storage::disk('public')->put('prisoners/'.$p['photo'], file_get_contents($src));
                        $prisoner->photo = 'prisoners/'.$p['photo'];
                        $prisoner->save();
                    }
                }

                // Rebuild the single contempt case so re-runs (and the empty
                // stub's placeholder case) collapse to exactly one.
                $prisoner->cases()->delete();
                $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->fill([
                    'prisoner_id' => $prisoner->id,
                    'institution_id' => $p['institution_id'] ?? null,
                    'charges' => $p['charges'],
                    'convicted' => $p['convicted'],
                    'sentence' => $p['sentence'],
                ]);
                if (! empty($p['in_exile_since'])) {
                    $case->setPartialDate('in_exile_since', ...$p['in_exile_since']);
                }
                if (! empty($p['end_of_exile'])) {
                    $case->setPartialDate('end_of_exile', ...$p['end_of_exile']);
                }
                $case->save();

                $this->info(($existing ? 'Filled: ' : 'Added: ').$prisoner->name.' (slug: '.$prisoner->slug.')');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
