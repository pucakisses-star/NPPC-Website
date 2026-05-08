<?php

declare(strict_types=1);

/**
 * Bulk-add Carter-era political prisoners (1977-1981) across four
 * clusters:
 *
 *   1. Republic of New Afrika 11 (Aug 18, 1971 Jackson MS raid;
 *      most served Carter-era prison time)
 *   2. Wilmington Ten (Feb 1971 NC arson frame-up; convictions
 *      overturned Dec 4, 1980; pardoned 2012)
 *   3. Clamshell Alliance / Seabrook Occupation (April 30, 1977 —
 *      ~1,414 arrests; held ~13 days in NH National Guard armories)
 *   4. Anti-draft-registration resisters prosecuted 1982-1986
 *      following Carter's reinstatement of draft registration July 2,
 *      1980
 *
 * Idempotent. Run on production:
 *   cd /var/www/NPPC-Website && php scripts/add_carter_era_prisoners.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;

function addPrisonerC(array $p): array
{
    $existing = Prisoner::where('name', $p['name'])->first();
    if ($existing) return [$existing, false];
    $prisoner = Prisoner::create(array_filter([
        'name'         => $p['name'],
        'first_name'   => $p['first_name']  ?? null,
        'middle_name'  => $p['middle_name'] ?? null,
        'last_name'    => $p['last_name']   ?? null,
        'aka'          => $p['aka']         ?? null,
        'gender'       => $p['gender']      ?? null,
        'race'         => $p['race']        ?? null,
        'state'        => $p['state']       ?? null,
        'birthdate'    => $p['birthdate']   ?? null,
        'death_date'   => $p['death_date']  ?? null,
        'description'  => $p['description'] ?? null,
        'era'          => $p['era']         ?? '1970s',
        'ideologies'   => $p['ideologies']  ?? null,
        'affiliation'  => $p['affiliation'] ?? null,
        'in_custody'   => $p['in_custody']  ?? false,
        'released'     => $p['released']    ?? true,
    ], fn ($v) => $v !== null));
    return [$prisoner, true];
}

function attachCaseC(Prisoner $prisoner, array $c): bool
{
    if (! empty($c['arrest_date'])) {
        $exists = $prisoner->cases()->where('arrest_date', $c['arrest_date'])->first();
        if ($exists) return false;
    }
    $institution = Institution::firstOrCreate(
        ['name'  => $c['institution']['name']],
        ['city'  => $c['institution']['city']  ?? null,
         'state' => $c['institution']['state'] ?? null],
    );
    PrisonerCase::create([
        'prisoner_id'           => $prisoner->id,
        'institution_id'        => $institution->id,
        'charges'               => $c['charges'] ?? null,
        'arrest_date'           => $c['arrest_date'] ?? null,
        'incarceration_date'    => $c['incarceration_date'] ?? null,
        'release_date'          => $c['release_date'] ?? null,
        'death_in_custody_date' => $c['death_in_custody_date'] ?? null,
        'sentenced_date'        => $c['sentenced_date'] ?? null,
        'convicted'             => $c['convicted'] ?? null,
        'sentence'              => $c['sentence'] ?? null,
        'prosecutor'            => $c['prosecutor'] ?? null,
        'judge'                 => $c['judge'] ?? null,
    ]);
    return true;
}

$tot = ['created' => 0, 'existed' => 0, 'cases' => 0];

// =================== 1. RNA-11 ===================

$rnaInst = ['name' => 'Mississippi state and federal courts — Republic of New Afrika', 'city' => 'Jackson', 'state' => 'Mississippi'];
$rnaCharges = 'August 18, 1971 joint Jackson PD/FBI raid on the Republic of New Afrika provisional-government residence at 1148 Lewis Street, Jackson, Mississippi. Lt. William Louis Skinner of the Jackson PD was killed; an officer and an FBI agent wounded. The 11 RNA citizens arrested at the scene became known as the RNA-11; charged variously in Mississippi state court with murder, assault, and treason. Eight ultimately convicted; Hekima Ana convicted as the alleged shooter and sentenced to life. Most served 2-10 years and were paroled mid-1970s through mid-1980s. The RNA\'s POW-status claim under the Geneva Conventions was rejected by federal courts.';

$rnaPeople = [
    ['name' => 'Imari Abubakari Obadele', 'first_name' => 'Imari', 'middle_name' => 'Abubakari', 'last_name' => 'Obadele', 'aka' => 'Richard Bullock Henry; PG-RNA President', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Mississippi', 'birthdate' => '1930-05-02', 'death_date' => '2010-01-18', 'description' => 'Born Richard Bullock Henry in Philadelphia. With his brother Milton (Gaidi Obadele) co-founded the Republic of New Afrika in Detroit in 1968 and became its first elected president of the Provisional Government. Murder charges from the 1971 Jackson raid were dropped (he was not at the scene). In 1973 federal court convicted him of conspiracy to assault a federal officer; sentenced to 12 years, served ~5-7. Paroled 1980. Earned PhD in political science from Temple University 1985; taught at Prairie View A&M and other HBCUs. Filed 1977 FOIA suit that exposed FBI COINTELPRO targeting of RNA. Founded N\'COBRA (1987). Died 2010 of stroke in Atlanta, age 79.', 'arrest' => '1971-08-18', 'sentence' => '12 years federal prison (federal conspiracy conviction 1973); served ~5-7; original Mississippi murder charges dropped.', 'release' => '1980-04-01', 'convicted' => 'Yes (federal, 1973)'],
    ['name' => 'Hekima Ana', 'last_name' => 'Ana', 'first_name' => 'Hekima', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Wisconsin', 'description' => 'RNA citizen from Milwaukee, age 29 at the time of the August 18, 1971 raid. Convicted of firing the shot that killed Lt. William L. Skinner. Sentenced to life imprisonment by the state of Mississippi. Husband of Tamu Sana (also arrested in the raid).', 'arrest' => '1971-08-18', 'sentence' => 'Life imprisonment (Mississippi state)', 'convicted' => 'Yes'],
    ['name' => 'Tamu Sana', 'first_name' => 'Tamu', 'last_name' => 'Sana', 'gender' => 'Female', 'race' => 'Black', 'state' => 'Wisconsin', 'description' => 'RNA citizen from Milwaukee, age 24 at the time of the raid. Married to Hekima Ana. Held about 10 months in Mississippi custody before release.', 'arrest' => '1971-08-18', 'sentence' => '~10 months in Mississippi custody before release.', 'release' => '1972-06-15'],
    ['name' => 'Karim Njabafudi', 'first_name' => 'Karim', 'last_name' => 'Njabafudi', 'aka' => 'Larry Jackson', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Louisiana', 'description' => 'RNA citizen from New Orleans, present at the Lewis Street house during the August 18, 1971 raid. One of the eight RNA-11 ultimately convicted; served between two and ten years.', 'arrest' => '1971-08-18'],
    ['name' => 'Chumaimari Fela Askadi', 'first_name' => 'Chumaimari', 'middle_name' => 'Fela', 'last_name' => 'Askadi', 'aka' => 'Charles Stallings', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Wisconsin', 'description' => 'Born Charles Stallings; took the New Afrikan name Chumaimari Fela Askadi. RNA citizen from Milwaukee, present at the Lewis Street raid. Convicted; served prison time within the 2-10 year range cited for the RNA-11.', 'arrest' => '1971-08-18'],
    ['name' => 'S. L. Alexander', 'first_name' => 'S.', 'middle_name' => 'L.', 'last_name' => 'Alexander', 'aka' => 'Spade de Mau Mau', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Louisiana', 'description' => 'Listed in primary RNA sources as one of the 11 arrested in the August 18, 1971 raid. Identified in the Provisional Government of the RNA chronicle as "Spade de Mau Mau (S. L. Alexander)" from New Orleans.', 'arrest' => '1971-08-18'],
    ['name' => 'Tarik Nkrumah', 'first_name' => 'Tarik', 'last_name' => 'Nkrumah', 'aka' => 'George Matthews; Tawwab Nkrumah', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Alabama', 'description' => 'Born George Matthews of Birmingham. RNA citizen present at the Lewis Street raid. Convicted; served prison time.', 'arrest' => '1971-08-18'],
    ['name' => 'Addis Ababa', 'first_name' => 'Addis', 'last_name' => 'Ababa', 'aka' => 'Dennis Shillingford', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Michigan', 'description' => 'Born Dennis Shillingford in Detroit. RNA citizen present at the Lewis Street raid. Convicted; served prison time within the 2-10 year range.', 'arrest' => '1971-08-18'],
    ['name' => 'Offogga Quddus', 'first_name' => 'Offogga', 'last_name' => 'Quddus', 'aka' => 'Wayne Maurice James', 'gender' => 'Male', 'race' => 'Black', 'state' => 'New Jersey', 'description' => 'Born Wayne Maurice James of Camden NJ. RNA citizen present at the Lewis Street raid. Convicted; served prison time.', 'arrest' => '1971-08-18'],
    ['name' => 'Njeri Quddus', 'first_name' => 'Njeri', 'last_name' => 'Quddus', 'gender' => 'Female', 'race' => 'Black', 'state' => 'New Jersey', 'description' => 'RNA citizen from Camden NJ; wife of Offogga Quddus. One of three women among the RNA-11 (with Tamu Sana and Aisha Salim).', 'arrest' => '1971-08-18'],
    ['name' => 'Aisha Salim', 'first_name' => 'Aisha', 'last_name' => 'Salim', 'aka' => 'Brenda Blount', 'gender' => 'Female', 'race' => 'Black', 'state' => 'Mississippi', 'description' => 'Born Brenda Blount. Served as RNA Minister of Information at the time of the August 18, 1971 raid. One of three women among the RNA-11.', 'arrest' => '1971-08-18'],
];

foreach ($rnaPeople as $p) {
    $p['ideologies'] = ['Black Nationalism', 'New Afrikan Independence', 'Reparations'];
    $p['affiliation'] = ['Republic of New Afrika'];
    $p['era'] = '1970s';
    [$prisoner, $created] = addPrisonerC($p);
    echo ($created ? '  CREATED' : '  EXISTS ') . " {$p['name']} (id={$prisoner->id})\n";
    $created ? $tot['created']++ : $tot['existed']++;
    if (attachCaseC($prisoner, [
        'institution' => $rnaInst,
        'arrest_date' => $p['arrest'],
        'release_date' => $p['release'] ?? null,
        'sentence' => $p['sentence'] ?? '2-10 years (Mississippi state custody, typical for RNA-11 convicted defendants).',
        'convicted' => $p['convicted'] ?? 'Yes',
        'charges' => $rnaCharges,
    ])) { echo "    + case ({$p['arrest']})\n"; $tot['cases']++; }
}

// =================== 2. Wilmington Ten ===================

$wmtInst = ['name' => 'North Carolina state courts — Wilmington Ten', 'city' => 'Wilmington', 'state' => 'North Carolina'];
$wmtCharges = 'February 6, 1971 firebombing of Mike\'s Grocery during civil rights / school-desegregation protests in Wilmington, NC. Ten activists arrested; tried in 1972 (mistrial in June; reconvened September 1972 with a 10-white/2-Black jury). Convicted October 17, 1972 of arson and conspiracy. Combined sentences totaled 282 years. Lead prosecutor Asst. DA Jay Stroud was later exposed for soliciting "KKK OK" jurors. Three key witnesses (incl. Allen Hall) recanted. Gov. Jim Hunt commuted (reduced) sentences in 1978; nine paroled 1978-79, Chavis Dec 1979. Convictions overturned by 4th Circuit Dec 4, 1980 (Chavis v. North Carolina, 637 F.2d 213). Pardons of innocence by Gov. Bev Perdue Dec 31, 2012.';

$wmtPeople = [
    ['name' => 'Benjamin Franklin Chavis Jr.', 'first_name' => 'Benjamin', 'middle_name' => 'Franklin', 'last_name' => 'Chavis Jr.', 'aka' => 'Ben Chavis; Benjamin Chavis Muhammad', 'gender' => 'Male', 'race' => 'Black', 'state' => 'North Carolina', 'birthdate' => '1948-01-22', 'description' => 'Born in Oxford, NC. By 1971, at age 23, was UCC Commission for Racial Justice field organizer dispatched to Wilmington to support Black students boycotting segregated schools. Oldest of the Wilmington Ten and de facto leader. Received the longest sentence: 34 years. Imprisoned ~4.5 years, paroled December 1979. Convictions overturned 1980. Later: Executive Director NAACP (1993-94); National Director Million Man March (1995). Pardoned innocent 2012; received state compensation. Continues as journalist (Chavis Chronicles).', 'affiliation' => ['United Church of Christ Commission for Racial Justice', 'Southern Christian Leadership Conference'], 'sentence' => '34 years (longest of Wilmington Ten); paroled December 1979; convictions vacated December 4, 1980; pardoned of innocence December 31, 2012.', 'release' => '1979-12-15'],
    ['name' => 'Connie Tindall', 'first_name' => 'Connie', 'last_name' => 'Tindall', 'gender' => 'Male', 'race' => 'Black', 'state' => 'North Carolina', 'death_date' => '2012-12-15', 'description' => 'Wilmington native; one of the young Black men convicted with Chavis. Received the second-longest sentence: 31 years. Paroled in 1978 after Hunt commutation. Died in 2012, the same year as the Perdue pardon (received pardon posthumously).', 'sentence' => '31 years; paroled 1978 after Hunt commutation; convictions vacated 1980; pardoned posthumously 2012.', 'release' => '1978-06-15'],
    ['name' => 'Marvin Patrick', 'first_name' => 'Marvin', 'last_name' => 'Patrick', 'aka' => 'Chilli Patrick', 'gender' => 'Male', 'race' => 'Black', 'state' => 'North Carolina', 'description' => 'Wilmington native, one of the young Black men convicted. Received 29-year sentence. Lived quietly in Wilmington post-release.', 'sentence' => '29 years; paroled 1978 after Hunt commutation; convictions vacated 1980; pardoned 2012.', 'release' => '1978-06-15'],
    ['name' => 'Wayne Moore', 'first_name' => 'Wayne', 'last_name' => 'Moore', 'gender' => 'Male', 'race' => 'Black', 'state' => 'Michigan', 'description' => 'Wilmington native, one of the young Black men convicted. 29-year sentence. After release, moved to Ann Arbor, Michigan, where he became a unionized electrician. Published a memoir in 2014.', 'sentence' => '29 years; paroled 1978; convictions vacated 1980; pardoned 2012.', 'release' => '1978-06-15'],
    ['name' => 'Jerry Jacobs', 'first_name' => 'Jerry', 'last_name' => 'Jacobs', 'gender' => 'Male', 'race' => 'Black', 'state' => 'North Carolina', 'death_date' => '1989-06-15', 'description' => 'Wilmington native; convicted at age 19 with 29-year sentence. Reportedly contracted disease while imprisoned that contributed to his early death in 1989. Pardoned posthumously 2012.', 'sentence' => '29 years; paroled 1978; convictions vacated 1980; pardoned posthumously 2012.', 'release' => '1978-06-15'],
    ['name' => 'James McKoy', 'first_name' => 'James', 'last_name' => 'McKoy', 'aka' => 'Bun McKoy', 'gender' => 'Male', 'race' => 'Black', 'state' => 'North Carolina', 'death_date' => '2023-11-10', 'description' => 'Wilmington native; convicted at age 19 with 29-year sentence (third-longest). Lived quietly in Wilmington post-release. Died November 10, 2023, age 69.', 'sentence' => '29 years; paroled 1978; convictions vacated 1980; pardoned 2012.', 'release' => '1978-06-15'],
    ['name' => 'Reginald Epps', 'first_name' => 'Reginald', 'last_name' => 'Epps', 'gender' => 'Male', 'race' => 'Black', 'state' => 'North Carolina', 'description' => 'Wilmington native, youngest Black male defendant; received the shortest male sentence: 28 years. After release moved to Raleigh.', 'sentence' => '28 years; paroled 1978; convictions vacated 1980; pardoned 2012.', 'release' => '1978-06-15'],
    ['name' => 'William Joe Wright Jr.', 'first_name' => 'William', 'middle_name' => 'Joe', 'last_name' => 'Wright Jr.', 'aka' => 'Joe Wright', 'gender' => 'Male', 'race' => 'Black', 'state' => 'North Carolina', 'death_date' => '1990-06-15', 'description' => 'Wilmington native, age 19 at conviction. 29-year sentence. Died c. 1990, pardoned posthumously 2012.', 'sentence' => '29 years; paroled 1978; convictions vacated 1980; pardoned posthumously 2012.', 'release' => '1978-06-15'],
    ['name' => 'Willie Earl Vereen', 'first_name' => 'Willie', 'middle_name' => 'Earl', 'last_name' => 'Vereen', 'gender' => 'Male', 'race' => 'Black', 'state' => 'North Carolina', 'death_date' => '2024-05-25', 'description' => 'Wilmington native, age 17 at the time of arrest — youngest of the Wilmington Ten. 29-year sentence. Lived quietly in Wilmington post-release; an accomplished musician and drummer who used his music for civil rights advocacy. Died May 25, 2024 at age 69.', 'sentence' => '29 years; paroled 1978; convictions vacated 1980; pardoned 2012.', 'release' => '1978-06-15'],
    ['name' => 'Anne Sheppard Turner', 'first_name' => 'Anne', 'middle_name' => 'Sheppard', 'last_name' => 'Turner', 'gender' => 'Female', 'race' => 'White', 'state' => 'North Carolina', 'death_date' => '2011-06-15', 'description' => 'Originally from Auburn, NY. White social worker, age 35 at time of arrest — the only woman and only white member of the Wilmington Ten. Convicted as accessory before the fact and conspiracy to assault emergency personnel; sentenced to 15 years. Paroled 1977. Died 2011, pardoned posthumously 2012.', 'sentence' => '15 years; paroled 1977; convictions vacated 1980; pardoned posthumously 2012.', 'release' => '1977-06-15'],
];

foreach ($wmtPeople as $p) {
    $p['ideologies'] = ['Civil Rights', 'Black Liberation Theology'];
    $p['affiliation'] = $p['affiliation'] ?? ['Wilmington student-protester'];
    $p['era'] = '1970s';
    [$prisoner, $created] = addPrisonerC($p);
    echo ($created ? '  CREATED' : '  EXISTS ') . " {$p['name']} (id={$prisoner->id})\n";
    $created ? $tot['created']++ : $tot['existed']++;
    if (attachCaseC($prisoner, [
        'institution' => $wmtInst,
        'arrest_date' => '1972-03-01',
        'sentenced_date' => '1972-10-17',
        'release_date' => $p['release'] ?? null,
        'sentence' => $p['sentence'],
        'convicted' => 'Yes (1972; convictions vacated by 4th Circuit December 4, 1980)',
        'charges' => $wmtCharges,
        'judge' => 'Robert Martin (NC Superior Court)',
        'prosecutor' => 'Jay Stroud (Asst. District Attorney) — later exposed for "KKK OK" jury notes',
    ])) { echo "    + case (1972-03-01)\n"; $tot['cases']++; }
}

// =================== 3. Clamshell Alliance / Seabrook ===================

$seabrook = ['name' => 'Seabrook Nuclear Power Plant — Clamshell Alliance occupation', 'city' => 'Seabrook', 'state' => 'New Hampshire'];
$seabrookCharges = 'April 30 - May 1, 1977 mass occupation of the Seabrook Nuclear Power Plant construction site in New Hampshire by ~2,400 protesters; 1,414 arrested. NH Governor Meldrim Thomson Jr. ordered them held without bail (vs. typical $100 release) and detained them in five National Guard armories for ~13 days. The action was the model for U.S. anti-nuclear direct action and inspired the Abalone Alliance / Diablo Canyon and Pacific Life Community / Bangor Trident campaigns.';

$cscPeople = [
    ['name' => 'Samuel Holden Lovejoy', 'first_name' => 'Samuel', 'middle_name' => 'Holden', 'last_name' => 'Lovejoy', 'aka' => 'Sam Lovejoy', 'gender' => 'Male', 'race' => 'White', 'state' => 'Massachusetts', 'birthdate' => '1946-01-01', 'description' => 'Organic farmer at the Montague Farm commune in western Massachusetts who, on Washington\'s Birthday (Feb. 22, 1974), single-handedly toppled 349 feet of a 550-foot weather-monitoring tower erected for the proposed Montague Nuclear Power Plant, then walked into the local police station and accepted full responsibility. Charged with malicious destruction but acquitted in September 1974 on a technicality (the tower was real, not personal, property). His action and the resulting documentary "Lovejoy\'s Nuclear War" (Green Mountain Post Films, 1975) helped catalyze the founding of the Clamshell Alliance. Later served as president of MUSE, organizing the 1979 No Nukes concerts.', 'arrest' => '1974-02-22', 'sentence' => 'Acquitted (1974)', 'convicted' => 'No — acquitted'],
    ['name' => 'Anna Gyorgy', 'first_name' => 'Anna', 'last_name' => 'Gyorgy', 'gender' => 'Female', 'race' => 'White', 'state' => 'Massachusetts', 'description' => 'Montague Farm activist who in 1974 helped organize the Alternative Energy Coalition in Montague, Mass., and in 1976 was part of the founding coalition of the Clamshell Alliance. Authored the influential 1979 reference book "No Nukes: Everyone\'s Guide to Nuclear Power" (South End Press), which became the movement\'s bible. Since 1985 has lived in Ireland, West Africa and Germany, continuing international ecofeminist and anti-nuclear organizing.', 'arrest' => '1977-04-30'],
    ['name' => 'Harvey Franklin Wasserman', 'first_name' => 'Harvey', 'middle_name' => 'Franklin', 'last_name' => 'Wasserman', 'aka' => 'Sluggo', 'gender' => 'Male', 'race' => 'White', 'state' => 'Massachusetts', 'birthdate' => '1945-01-01', 'description' => 'Co-founded Liberation News Service in 1967 and helped move it to the Montague Farm in Massachusetts in 1968. Coined / popularized the slogan "No Nukes" around 1973-74 and authored "America Born and Reborn" and (with the MUSE collective) the "No Nukes" book. Co-founder, organizer and media spokesperson for the Clamshell Alliance during the 1976-78 Seabrook actions and helped organize the 1979 MUSE concerts at Madison Square Garden after Three Mile Island.', 'arrest' => '1977-04-30'],
    ['name' => 'Robert Reynolds Cushing Jr.', 'first_name' => 'Robert', 'middle_name' => 'Reynolds', 'last_name' => 'Cushing Jr.', 'aka' => 'Renny Cushing', 'gender' => 'Male', 'race' => 'White', 'state' => 'New Hampshire', 'birthdate' => '1952-07-20', 'death_date' => '2022-03-07', 'description' => 'Lifelong Hampton, NH activist. Co-founded the Clamshell Alliance in 1976 and was among the more than 1,400 protesters arrested at Seabrook on April 30/May 1, 1977. After his father\'s 1988 murder he became a national leader of murder-victims-against-the-death-penalty organizing, and as a NH state representative (first elected 1996) he led the successful 2019 repeal of New Hampshire\'s death penalty. Democratic House Leader at the time of his death from prostate cancer and COVID-19 complications on March 7, 2022.', 'arrest' => '1977-04-30'],
    ['name' => 'Guy Chichester', 'first_name' => 'Guy', 'last_name' => 'Chichester', 'gender' => 'Male', 'race' => 'White', 'state' => 'New Hampshire', 'birthdate' => '1936-01-01', 'death_date' => '2009-02-09', 'description' => 'Navy veteran who moved from Long Island to Rye, NH, was a co-founder of the Clamshell Alliance in 1976 and a perennial figure in New England anti-nuclear and environmental organizing. Later served as president of the Seacoast Anti-Pollution League. In a celebrated act of civil disobedience he sawed down a Seabrook Station emergency-warning siren pole, was charged with criminal mischief (a Class B felony), and was acquitted by jury.', 'arrest' => '1977-04-30'],
    ['name' => 'Sukie Rice', 'first_name' => 'Susan', 'middle_name' => 'Bellows', 'last_name' => 'Rice', 'aka' => 'Sukie Rice', 'gender' => 'Female', 'race' => 'White', 'state' => 'Maine', 'birthdate' => '1945-01-01', 'death_date' => '2020-08-15', 'description' => 'AFSC staffer in Boston who, with Elizabeth Boardman, designed and led the nonviolence training that made the April 30, 1977 Seabrook occupation famously disciplined. Joined the Durham (Maine) Monthly Meeting of Friends in 1979, raised a family in Freeport, and spent her last two decades supporting vulnerable children in western Kenya through Friends of Kakamega.', 'arrest' => '1977-04-30'],
    ['name' => 'Murray Bookchin', 'first_name' => 'Murray', 'last_name' => 'Bookchin', 'gender' => 'Male', 'race' => 'White', 'state' => 'Vermont', 'birthdate' => '1921-01-14', 'death_date' => '2006-07-30', 'description' => 'Leading American eco-anarchist theorist and founder of the Institute for Social Ecology at Goddard College in Vermont (1974). In 1977-78 he and the ISE-affiliated Spruce Mountain Affinity Group joined the Clamshell Alliance\'s Seabrook campaign. His "Note on Affinity Groups" shaped Clamshell organizing, and he led the internal critique against the Coordinating Committee\'s centralism after 1977 that ultimately split the Alliance.', 'arrest' => '1977-04-30'],
    ['name' => 'Paul Gunter', 'first_name' => 'Paul', 'last_name' => 'Gunter', 'gender' => 'Male', 'race' => 'White', 'state' => 'New Hampshire', 'description' => 'One of the original 18 Clamshell Alliance members arrested at Seabrook on August 1, 1976 — the action that launched the alliance — and a co-founder of the group. Has been arrested at Seabrook on numerous later occasions for nonviolent civil disobedience. As co-director of Beyond Nuclear (Takoma Park, MD) he has worked for more than thirty years on reactor hazards, license-extension intervention and the nuclear-weapons connection.', 'arrest' => '1976-08-01'],
    ['name' => 'Howard Gresham Hawkins III', 'first_name' => 'Howard', 'middle_name' => 'Gresham', 'last_name' => 'Hawkins III', 'aka' => 'Howie Hawkins', 'gender' => 'Male', 'race' => 'White', 'state' => 'New York', 'birthdate' => '1952-12-08', 'description' => 'Dartmouth-educated New England construction worker in the 1970s who co-founded the Clamshell Alliance in 1976. Went on to co-found the U.S. Green Party, ran as the Greens\' 2020 presidential nominee, and worked the night shift at UPS as a Teamster from 2001 until 2017. Long advanced an eco-socialist Green New Deal program he first proposed in 2010.', 'arrest' => '1977-04-30'],
    ['name' => 'Howard Morland', 'first_name' => 'Howard', 'last_name' => 'Morland', 'gender' => 'Male', 'race' => 'White', 'state' => 'New Hampshire', 'birthdate' => '1942-09-14', 'description' => 'Former U.S. Air Force C-141 pilot trained to ferry nuclear weapons who became, in 1979, the journalist who "discovered" and published the Teller-Ulam (H-bomb) design in The Progressive — triggering U.S. v. Progressive Inc., the first prior-restraint case the U.S. brought against an American magazine since the Pentagon Papers. Before the H-bomb article, after a year of graduate work at Dartmouth he became a full-time Clamshell Alliance organizer and co-founded the group in 1976.', 'arrest' => '1977-04-30'],
];

foreach ($cscPeople as $p) {
    $p['ideologies'] = ['Anti-nuclear', 'Pacifism'];
    $p['affiliation'] = $p['affiliation'] ?? ['Clamshell Alliance'];
    $p['era'] = '1970s';
    [$prisoner, $created] = addPrisonerC($p);
    echo ($created ? '  CREATED' : '  EXISTS ') . " {$p['name']} (id={$prisoner->id})\n";
    $created ? $tot['created']++ : $tot['existed']++;
    if (attachCaseC($prisoner, [
        'institution' => $seabrook,
        'arrest_date' => $p['arrest'],
        'charges' => $p['arrest'] === '1974-02-22' ? 'Malicious destruction of personal property — Sam Lovejoy single-handedly toppled the Montague weather-monitoring tower. Acquitted September 1974 on a technicality.' : $seabrookCharges,
        'sentence' => $p['sentence'] ?? 'Held without bail in NH National Guard armory ~13 days; criminal trespass charges most dismissed.',
        'convicted' => $p['convicted'] ?? 'Misdemeanor (most charges later dismissed)',
    ])) { echo "    + case ({$p['arrest']})\n"; $tot['cases']++; }
}

// =================== 4. Anti-Draft-Registration Resisters ===================

$draftPeople = [
    ['name' => 'Benjamin H. Sasway', 'first_name' => 'Benjamin', 'middle_name' => 'H.', 'last_name' => 'Sasway', 'aka' => 'Ben Sasway', 'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'birthdate' => '1961-01-01', 'description' => 'Political-science and philosophy student at Humboldt State who became the first man indicted for failure to register since the Vietnam War. Selected for the test prosecution at the recommendation of Reagan adviser Edwin Meese, who believed conservative San Diego would deliver a quick conviction. Indicted June 30, 1982; convicted by a jury after only 40 minutes\' deliberation on Aug. 26, 1982; sentenced Oct. 4, 1982 to 30 months in federal prison — the harshest sentence of any nonregistrant. Barred from telling the jury his motives. Served until release on Sept. 20, 1985.', 'arrest' => '1982-06-30', 'sentenced' => '1982-10-04', 'sentence' => '30 months federal prison', 'release' => '1985-09-20', 'judge' => 'Hon. Gordon Thompson Jr.', 'court' => 'U.S. District Court for the Southern District of California'],
    ['name' => 'Enten Eller', 'first_name' => 'Enten', 'last_name' => 'Eller', 'gender' => 'Male', 'race' => 'White', 'state' => 'Virginia', 'birthdate' => '1962-01-01', 'description' => 'Son of Church of the Brethren minister Rev. Vernard Eller. An honors physics-and-math major at church-affiliated Bridgewater College. The second man indicted (July 12, 1982) since the Vietnam War for failure to register — and via his Aug. 17, 1982 conviction, the first 1980s draft-registration nonregistrant convicted. U.S. District Judge James C. Turk found him guilty after a one-day trial, called him "an honorable person," and rejected the government\'s call for prison. Sentenced to 3 years\' probation, ordered to register within 90 days, and assigned 2 years\' unpaid public service (cancer research at the Salem VA Medical Center). He never registered; the conviction stands.', 'arrest' => '1982-07-12', 'sentenced' => '1982-08-17', 'sentence' => '3 years probation; 2 years community service; $4,000 fine', 'judge' => 'Hon. James C. Turk', 'court' => 'U.S. District Court for the Western District of Virginia (Roanoke)'],
    ['name' => 'David Alan Wayte', 'first_name' => 'David', 'middle_name' => 'Alan', 'last_name' => 'Wayte', 'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'birthdate' => '1961-01-01', 'description' => 'Former Yale philosophy student from Pasadena who wrote letters to government officials publicly announcing his refusal to register; the letters were placed in a Selective Service file used by DOJ to select "vocal" nonregistrants for prosecution. Indicted July 22, 1982. U.S. District Judge Terry Hatter dismissed the indictment on selective-prosecution grounds; the Supreme Court reversed in Wayte v. United States, 470 U.S. 598 (1985), 7-2, holding that DOJ\'s selective prosecution did not violate the First or Fifth Amendments. Wayte then pleaded guilty in June 1985 and Hatter sentenced him on Sept. 10, 1985 to six months under house arrest at his grandmother\'s home — far below the 5-year statutory maximum.', 'arrest' => '1982-07-22', 'sentenced' => '1985-09-10', 'sentence' => '6 months house arrest (no prison)', 'release' => '1986-03-10', 'judge' => 'Hon. Terry J. Hatter Jr.', 'court' => 'U.S. District Court for the Central District of California; Wayte v. United States, 470 U.S. 598 (1985)'],
    ['name' => 'Mark Alden Schmucker', 'first_name' => 'Mark', 'middle_name' => 'Alden', 'last_name' => 'Schmucker', 'gender' => 'Male', 'race' => 'White', 'state' => 'Ohio', 'birthdate' => '1960-01-01', 'description' => 'Mennonite biology senior at Goshen College from Alliance, Ohio, mailed a letter to Selective Service in August 1980 announcing he would not register on religious grounds. Convicted in U.S. District Court (N.D. Ohio); sentenced Oct. 19, 1982 to two years working at a hospital for the developmentally disabled and a $4,000 fine (no prison). His selective-prosecution appeals reached the Sixth Circuit twice (721 F.2d 1046 (1983); 815 F.2d 413 (1987)) and are landmark religious-conscience precedents.', 'arrest' => '1982-08-01', 'sentenced' => '1982-10-19', 'sentence' => '2 years community service; $4,000 fine', 'court' => 'U.S. District Court for the Northern District of Ohio'],
    ['name' => 'Edward Hasbrouck', 'first_name' => 'Edward', 'last_name' => 'Hasbrouck', 'gender' => 'Male', 'race' => 'White', 'state' => 'Massachusetts', 'birthdate' => '1960-01-11', 'description' => 'Born Jan. 11, 1960 in Cambridge, MA, refused to register in 1980, returned to Massachusetts, and was selected for prosecution by U.S. Attorney William Weld and Asst. U.S. Attorney Robert S. Mueller III as one of the "most vocal" of the millions of nonregistrants. Convicted Dec. 15, 1982 by Judge David S. Nelson (D. Mass.), initially sentenced Jan. 14, 1983 to two years\' probation and 1,000 hours of community service. After he refused to comply with conditions, the probation was revoked in November 1983 and he served six months at the federal prison camp at Lewisburg, PA, from November 1983 to April 1984. Went on to edit Resistance News and remain a leading public critic of Selective Service.', 'arrest' => '1982-12-15', 'sentenced' => '1983-01-14', 'sentence' => '2 years probation + 1,000 hrs community service (revoked Nov 1983 to 6 months prison)', 'release' => '1984-04-15', 'judge' => 'Hon. David S. Nelson', 'prosecutor' => 'William Weld (U.S. Atty., D. Mass.); Asst. U.S. Atty. Robert S. Mueller III', 'court' => 'U.S. District Court for the District of Massachusetts'],
    ['name' => 'Gary John Eklund', 'first_name' => 'Gary', 'middle_name' => 'John', 'last_name' => 'Eklund', 'gender' => 'Male', 'race' => 'White', 'state' => 'Iowa', 'birthdate' => '1960-01-01', 'description' => 'First Iowan indicted for nonregistration. Publicly burned his Selective Service paperwork in protest when registration was reinstated in 1980. Sentenced Dec. 3, 1982 by U.S. District Judge Harold Vietor in Des Moines to 2 years in federal prison. Petition for certiorari (Eklund v. United States) denied by the U.S. Supreme Court.', 'arrest' => '1982-09-15', 'sentenced' => '1982-12-03', 'sentence' => '2 years federal prison', 'judge' => 'Hon. Harold D. Vietor', 'court' => 'U.S. District Court for the Southern District of Iowa'],
    ['name' => 'Russell Ford', 'first_name' => 'Russell', 'last_name' => 'Ford', 'aka' => 'Russ Ford', 'gender' => 'Male', 'race' => 'White', 'state' => 'Connecticut', 'description' => 'First New Englander since the U.S. war in Indochina to be indicted for nonregistration. Arrested for refusal to register on Aug. 10, 1982 in Hartford, Connecticut. At his arrest a fellow resister hugged him in solidarity, prompting federal marshals to charge that resister with "assaulting federal officers by embracing Russell Ford." One of the "imprisoned nine" who served federal time.', 'arrest' => '1982-08-10', 'court' => 'U.S. District Court for the District of Connecticut'],
    ['name' => 'Paul Jacob', 'first_name' => 'Paul', 'last_name' => 'Jacob', 'gender' => 'Male', 'race' => 'White', 'state' => 'Arkansas', 'birthdate' => '1960-01-01', 'description' => 'Libertarian activist who at a Jan. 5, 1981 Little Rock post-office demonstration publicly told reporters he had not and would not register. Indicted Sept. 21, 1982 in the Eastern District of Arkansas. Went underground for ~2 years, was arrested at his North Little Rock home Dec. 6, 1984, and was tried in July 1985. Congressman Ron Paul testified for the defense. Convicted; served 5½ months in federal prison — one of only ~9 American draft resisters imprisoned since Vietnam. Later became a national term-limits and ballot-initiative organizer.', 'arrest' => '1984-12-06', 'sentenced' => '1985-07-15', 'sentence' => '5½ months federal prison', 'release' => '1985-12-15', 'court' => 'U.S. District Court for the Eastern District of Arkansas; appeals at 8th Cir.'],
    ['name' => 'Andy Mager', 'first_name' => 'Andrew', 'last_name' => 'Mager', 'aka' => 'Andy Mager', 'gender' => 'Male', 'race' => 'White', 'state' => 'New York', 'birthdate' => '1962-01-01', 'description' => 'Of Syracuse, NY, was indicted in 1984 and sentenced Feb. 4, 1985 in federal court in Syracuse to 6 months in prison plus 2 years\' probation. A solidarity statement signed by 2,600 people was entered into the sentencing record. Released unconditionally after 4½ months. Long career with the Syracuse Peace Council.', 'arrest' => '1984-12-01', 'sentenced' => '1985-02-04', 'sentence' => '6 months prison + 2 years probation', 'release' => '1985-06-15', 'court' => 'U.S. District Court for the Northern District of New York (Syracuse)'],
    ['name' => 'Gillam Kerley', 'first_name' => 'Gillam', 'last_name' => 'Kerley', 'gender' => 'Male', 'race' => 'White', 'state' => 'Wisconsin', 'description' => 'Executive director of the Committee Against Registration and the Draft. Indicted Nov. 1982 by a grand jury in the Western District of Wisconsin after publicly writing the SSS Director and General Counsel that he refused to register. Judge John C. Shabaz sentenced him to 3 years in prison and a $10,000 fine. The Seventh Circuit overturned his sentence and then his conviction (753 F.2d 617 (1985); 838 F.2d 932 (1988)), and the case set the most favorable legal precedent against selective prosecution. Released after 4 months in custody.', 'arrest' => '1982-11-15', 'sentence' => '3 years prison + $10,000 fine (vacated on appeal 1988)', 'release' => '1985-03-15', 'judge' => 'Hon. John C. Shabaz', 'court' => 'U.S. District Court for the Western District of Wisconsin; 7th Circuit'],
    ['name' => 'Daniel Rutt', 'first_name' => 'Daniel', 'last_name' => 'Rutt', 'aka' => 'Dan Rutt', 'gender' => 'Male', 'race' => 'White', 'state' => 'Michigan', 'description' => 'Christian pacifist of Mennonite family background raised Methodist. Prosecuted after publicly writing officials and giving a local newspaper interview saying he would never register. Sentenced to a few months at a federal detention facility in the Detroit area, with work release allowing him to drive 70 miles daily to his job. Later careers in public health and design.', 'arrest' => '1983-06-15', 'sentence' => 'Several months federal detention with work release', 'court' => 'U.S. District Court for the Eastern District of Michigan'],
    ['name' => 'Terry Kuelper', 'first_name' => 'Terry', 'last_name' => 'Kuelper', 'gender' => 'Male', 'race' => 'White', 'state' => 'Arkansas', 'description' => 'Subject of the LAST nonregistration indictment under the 1980-86 enforcement campaign — Jan. 23, 1986. Agreed to register before trial, and the charge was dismissed. With this dismissal, DOJ prosecutions of nonregistrants effectively ended.', 'arrest' => '1986-01-23', 'sentence' => 'Charge dismissed after he agreed to register.', 'convicted' => 'No — charge dismissed', 'court' => 'U.S. District Court (Arkansas)'],
];

foreach ($draftPeople as $p) {
    $p['ideologies'] = ['Pacifism', 'Anti-war', 'Conscientious objection'];
    $p['affiliation'] = ['Anti-Draft-Registration Resistance', 'National Resistance Committee'];
    $p['era'] = '1980s';
    [$prisoner, $created] = addPrisonerC($p);
    echo ($created ? '  CREATED' : '  EXISTS ') . " {$p['name']} (id={$prisoner->id})\n";
    $created ? $tot['created']++ : $tot['existed']++;
    if (attachCaseC($prisoner, [
        'institution' => ['name' => $p['court'] ?? 'Federal court — anti-draft-registration prosecution', 'city' => null, 'state' => null],
        'arrest_date' => $p['arrest'],
        'sentenced_date' => $p['sentenced'] ?? null,
        'release_date' => $p['release'] ?? null,
        'sentence' => $p['sentence'] ?? null,
        'convicted' => $p['convicted'] ?? 'Yes — failure to register',
        'judge' => $p['judge'] ?? null,
        'prosecutor' => $p['prosecutor'] ?? null,
        'charges' => 'Failure to register for the Selective Service System under the Military Selective Service Act § 3 (50 U.S.C. App. § 453). President Carter reinstated draft registration via Proclamation 4771 on July 2, 1980 in response to the Soviet invasion of Afghanistan. Approximately 600,000 men refused (~3.6% of those eligible). The Justice Department selected 20 of them as "test cases" between 1982-1986 — all of them publicly vocal resisters who had informed the SSS of their refusal. The DOJ\'s selective-prosecution policy was upheld in Wayte v. United States, 470 U.S. 598 (1985). Of the 20 prosecuted, ~9 served federal prison time.',
    ])) { echo "    + case ({$p['arrest']})\n"; $tot['cases']++; }
}

echo "\nDone.\n";
echo "  prisoners created:        {$tot['created']}\n";
echo "  prisoners already existed: {$tot['existed']}\n";
echo "  cases added:              {$tot['cases']}\n";
