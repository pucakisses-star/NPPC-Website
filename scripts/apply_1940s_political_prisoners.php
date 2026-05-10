<?php

declare(strict_types=1);

/**
 * 1940s gap-fill: 8 new political prisoners.
 *
 *   A. Japanese American internment landmark cases (5):
 *      - Fred Korematsu (Korematsu v. US, 1944)
 *      - Mitsuye Endo (Ex parte Endo, 1944)
 *      - Gordon Hirabayashi (Hirabayashi v. US, 1943)
 *      - Minoru Yasui (Yasui v. US, 1943)
 *      - Frank Emi (Heart Mountain Fair Play Committee leader)
 *
 *   B. WWII conscientious objectors (3):
 *      - Ralph DiGia (War Resisters League; 3 yr federal)
 *      - James Peck (later Freedom Rider; 18 mo federal)
 *      - George Houser (CORE / FOR co-founder; 1 yr federal)
 *
 * Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use App\Models\Institution;

$updates = 0; $creates = 0; $unchanged = 0;

$inst = function (string $name, ?string $city = null, ?string $state = null): Institution {
    return Institution::firstOrCreate(['name' => $name], ['city' => $city, 'state' => $state]);
};

$createPrisoner = function (string $name, array $attrs) use (&$creates): ?Prisoner {
    if (Prisoner::where('name', $name)->exists()) {
        echo "  [skip]   {$name} — already exists\n";
        return Prisoner::where('name', $name)->first();
    }
    $tokens = preg_split('/\s+/', $name);
    $first  = $tokens[0] ?? '';
    $last   = count($tokens) > 1 ? implode(' ', array_slice($tokens, 1)) : '';
    $defaults = [
        'name'        => $name,
        'first_name'  => $first,
        'last_name'   => $last,
        'era'         => '1940s',
        'in_custody'  => false,
        'released'    => true,
    ];
    $p = Prisoner::create(array_merge($defaults, $attrs));
    echo "  [add]    {$name}\n";
    $creates++;
    return $p;
};

$ensureCase = function (Prisoner $p, array $attrs) use (&$updates, &$unchanged): void {
    $key = $attrs['arrest_date'] ?? null;
    if ($key && $p->cases()->where('arrest_date', $key)->exists()) {
        echo "  [ok]     {$p->name} — case already present for {$key}\n";
        $unchanged++;
        return;
    }
    PrisonerCase::create(array_merge(['prisoner_id' => $p->id], $attrs));
    echo "  [update] {$p->name} — added case ({$key})\n";
    $updates++;
};

// ============================================================
// A. Japanese American internment landmark cases
// ============================================================
echo "\n--- A. Japanese American internment ---\n";

$korematsu = $createPrisoner('Fred Korematsu', [
    'description' => "Fred Toyosaburo Korematsu (1919-2005) was a Japanese-American welder in Oakland, California who refused to comply with Civilian Exclusion Order No. 34 and remained at large rather than report for forced relocation. Arrested in San Leandro on May 30, 1942 and convicted in U.S. District Court for the Northern District of California; sentenced to 5 years' probation and held at the Tanforan Assembly Center and then the Topaz War Relocation Center. The U.S. Supreme Court affirmed the conviction 6-3 in Korematsu v. United States, 323 U.S. 214 (1944), one of the most-criticized rulings in modern American constitutional history. The conviction was vacated by writ of coram nobis on November 10, 1983 by U.S. District Judge Marilyn Hall Patel after the Commission on Wartime Relocation and Internment of Civilians showed the government had suppressed evidence undermining the \"military necessity\" rationale. President Clinton awarded him the Presidential Medal of Freedom in 1998. The Supreme Court formally repudiated Korematsu in dicta in Trump v. Hawaii, 585 U.S. ___ (2018).",
    'birthdate'   => '1919-01-30',
    'death_date'  => '2005-03-30',
    'race'        => 'Asian',
    'gender'      => 'Male',
    'state'       => 'California',
    'ideologies'  => ['Civil rights','Civil libertarian'],
    'affiliation' => null,
]);
if ($korematsu) {
    $i = $inst('Tanforan Assembly Center / Topaz War Relocation Center', 'Topaz', 'Utah');
    $ensureCase($korematsu, [
        'institution_id'     => $i->id,
        'charges'            => 'Violation of Civilian Exclusion Order No. 34 issued under Executive Order 9066 (Feb. 19, 1942) — refused to report for forced relocation from a designated military area on the West Coast.',
        'arrest_date'        => '1942-05-30',
        'sentenced_date'     => '1942-09-08',
        'incarceration_date' => '1942-05-30',
        'release_date'       => '1945-01-02',
        'sentence'           => '5 years probation; held at the Tanforan Assembly Center then the Topaz War Relocation Center until released after Ex parte Endo on January 2, 1945. Conviction vacated by U.S. District Judge Marilyn Hall Patel via writ of coram nobis on November 10, 1983.',
        'convicted'          => 'Yes — vacated 1983 (Korematsu v. United States, 323 U.S. 214 (1944) affirmed by the U.S. Supreme Court, later repudiated in Trump v. Hawaii, 2018)',
    ]);
}

$endo = $createPrisoner('Mitsuye Endo', [
    'description' => "Mitsuye Endo Tsutsumi (1920-2006) was a Japanese-American clerk for the California Department of Motor Vehicles when she was forcibly relocated under Executive Order 9066 in 1942. While interned at the Tule Lake War Relocation Center and later the Topaz Camp, she filed a habeas corpus petition challenging her detention. The case became Ex parte Mitsuye Endo, 323 U.S. 283 (1944), in which the U.S. Supreme Court unanimously held that the War Relocation Authority had no statutory authority to detain a concededly loyal American citizen. The decision, announced one day after Korematsu, ended mass internment of loyal Japanese-Americans. Endo was held in the camps for nearly 2.5 years.",
    'birthdate'   => '1920-05-10',
    'death_date'  => '2006-04-14',
    'race'        => 'Asian',
    'gender'      => 'Female',
    'state'       => 'California',
    'ideologies'  => ['Civil rights','Civil libertarian'],
]);
if ($endo) {
    $i = $inst('Tule Lake War Relocation Center / Topaz War Relocation Center', 'Topaz', 'Utah');
    $ensureCase($endo, [
        'institution_id'     => $i->id,
        'charges'            => 'Forced relocation under Executive Order 9066 — civil-administrative detention, not a criminal charge. Endo was the petitioner who challenged the detention.',
        'arrest_date'        => '1942-05-19',
        'incarceration_date' => '1942-05-19',
        'release_date'       => '1945-01-02',
        'sentence'           => 'Detained ~2 years 7 months at Tanforan Assembly Center, Tule Lake War Relocation Center, and Topaz War Relocation Center until released after Ex parte Endo, 323 U.S. 283 (1944).',
        'convicted'          => 'No — civil habeas petitioner who prevailed at the U.S. Supreme Court',
    ]);
}

$hirabayashi = $createPrisoner('Gordon Hirabayashi', [
    'description' => "Gordon Kiyoshi Hirabayashi (1918-2012) was a University of Washington senior and Quaker pacifist who refused to comply with the curfew and exclusion orders imposed on Japanese-Americans in 1942. He turned himself in to the FBI on May 16, 1942. Convicted in U.S. District Court for the Western District of Washington of violating both the curfew order and the exclusion order; sentenced to 90 days on each count, served at Tucson Federal Honor Camp (Arizona) and McNeil Island Penitentiary. The U.S. Supreme Court unanimously affirmed his curfew conviction in Hirabayashi v. United States, 320 U.S. 81 (1943) and declined to address the exclusion-order count. His convictions were vacated by writ of coram nobis on September 24, 1987 by Judge Donald Voorhees of the Western District of Washington. President Obama awarded him the Presidential Medal of Freedom in 2012, the same year he died.",
    'birthdate'   => '1918-04-23',
    'death_date'  => '2012-01-02',
    'race'        => 'Asian',
    'gender'      => 'Male',
    'state'       => 'Washington',
    'ideologies'  => ['Civil rights','Civil libertarian','Pacifism'],
    'affiliation' => ['Religious Society of Friends (Quakers)'],
]);
if ($hirabayashi) {
    $i = $inst('Tucson Federal Honor Camp / USP McNeil Island', 'Tucson', 'Arizona');
    $ensureCase($hirabayashi, [
        'institution_id'     => $i->id,
        'charges'            => 'Violation of the curfew imposed by Public Proclamation No. 3 (March 24, 1942) and the exclusion order imposed by Civilian Exclusion Order No. 57 — both issued under Executive Order 9066.',
        'arrest_date'        => '1942-05-16',
        'sentenced_date'     => '1942-10-20',
        'incarceration_date' => '1942-10-20',
        'release_date'       => '1943-04-20',
        'sentence'           => '90 days on each of two counts; served concurrently at Tucson Federal Honor Camp and USP McNeil Island. Convictions vacated by writ of coram nobis on September 24, 1987.',
        'convicted'          => 'Yes — vacated 1987 (Hirabayashi v. United States, 320 U.S. 81 (1943) affirmed by the U.S. Supreme Court)',
    ]);
}

$yasui = $createPrisoner('Minoru Yasui', [
    'description' => "Minoru Yasui (1916-1986) was the first Japanese-American attorney admitted to the Oregon State Bar and a U.S. Army Reserve second lieutenant. After the issuance of Executive Order 9066 he attempted to test the curfew imposed on Japanese-Americans by deliberately violating it on the evening of March 28, 1942 in Portland and turning himself in at a Portland police station. Convicted in U.S. District Court for the District of Oregon (Judge James Alger Fee); sentenced to 1 year in federal custody and a \$5,000 fine. Held at the Multnomah County jail in solitary confinement for 9 months and then at Minidoka War Relocation Center. The U.S. Supreme Court affirmed the curfew conviction in Yasui v. United States, 320 U.S. 115 (1943) (decided same day as Hirabayashi). His conviction was vacated by writ of coram nobis in 1986 shortly before his death. President Obama awarded him the Presidential Medal of Freedom posthumously in 2015.",
    'birthdate'   => '1916-10-19',
    'death_date'  => '1986-11-12',
    'race'        => 'Asian',
    'gender'      => 'Male',
    'state'       => 'Oregon',
    'ideologies'  => ['Civil rights','Civil libertarian'],
]);
if ($yasui) {
    $i = $inst('Multnomah County Jail / Minidoka War Relocation Center', 'Hunt', 'Idaho');
    $ensureCase($yasui, [
        'institution_id'     => $i->id,
        'charges'            => 'Violation of the curfew imposed by Public Proclamation No. 3 under Executive Order 9066. Test case deliberately committed to challenge the order.',
        'arrest_date'        => '1942-03-28',
        'sentenced_date'     => '1942-11-16',
        'incarceration_date' => '1942-11-16',
        'release_date'       => '1943-08-16',
        'sentence'           => '1 year + \$5,000 fine; served 9 months in solitary confinement at the Multnomah County jail before transfer to Minidoka. Conviction vacated by coram nobis 1986. Medal of Freedom posthumously 2015.',
        'convicted'          => 'Yes — vacated 1986 (Yasui v. United States, 320 U.S. 115 (1943) affirmed by the U.S. Supreme Court)',
    ]);
}

$emi = $createPrisoner('Frank Emi', [
    'description' => "Frank Seishi Emi (1916-2010) was a Los Angeles grocer interned at the Heart Mountain War Relocation Center in Wyoming. In January 1944 the U.S. government began drafting Japanese-American men out of the camps. Emi co-founded the Heart Mountain Fair Play Committee, which counseled draft resistance unless internees' constitutional rights were restored first. Eighty-five Heart Mountain internees refused induction; sixty-three were convicted in the largest mass draft-resistance trial in U.S. history (June 1944). Emi and six other Fair Play Committee leaders were separately convicted on October 26, 1944 of conspiring to counsel draft resistance and sentenced to 4 years federal prison; held at USP Leavenworth. The Tenth Circuit reversed the leaders' convictions on December 26, 1946. President Truman pardoned the 63 resisters on December 24, 1947.",
    'birthdate'   => '1916-09-23',
    'death_date'  => '2010-12-01',
    'race'        => 'Asian',
    'gender'      => 'Male',
    'state'       => 'Wyoming',
    'ideologies'  => ['Civil rights','Civil libertarian'],
    'affiliation' => ['Heart Mountain Fair Play Committee'],
]);
if ($emi) {
    $i = $inst('USP Leavenworth', 'Leavenworth', 'Kansas');
    $ensureCase($emi, [
        'institution_id'     => $i->id,
        'charges'            => 'Conspiracy to counsel draft resistance — under 18 U.S.C. § 11 (1940) and the Selective Training and Service Act of 1940. Co-leader of the Heart Mountain Fair Play Committee.',
        'arrest_date'        => '1944-07-21',
        'sentenced_date'     => '1944-10-26',
        'incarceration_date' => '1944-10-26',
        'release_date'       => '1946-12-26',
        'sentence'           => '4 years federal prison at USP Leavenworth. Conviction reversed by the Tenth Circuit Court of Appeals on December 26, 1946. The 63 Heart Mountain draft resisters themselves were pardoned by President Truman on December 24, 1947.',
        'convicted'          => 'Yes — reversed 1946 (10th Cir.)',
    ]);
}

// ============================================================
// B. WWII conscientious objectors
// ============================================================
echo "\n--- B. WWII conscientious objectors ---\n";

$digia = $createPrisoner('Ralph DiGia', [
    'description' => "Ralph DiGia (1914-2008) was a New York City accountant and lifelong pacifist who refused to register for the draft in World War II. Convicted in U.S. District Court for the Southern District of New York; served three years of a 3-year federal sentence (1942-1945) at federal prison camps including Lewisburg, Danbury, and Petersburg. Co-founded the War Resisters League's full-time staff after release; participated in the 1947 \"Journey of Reconciliation\" (precursor to the Freedom Rides) and the 1951 Paris-to-Moscow peace bicycle ride. Worked at the WRL national office until his death.",
    'birthdate'   => '1914-12-13',
    'death_date'  => '2008-02-19',
    'race'        => 'White',
    'gender'      => 'Male',
    'state'       => 'New York',
    'ideologies'  => ['Pacifism','Anti-militarism','War Resister'],
    'affiliation' => ['War Resisters League'],
]);
if ($digia) {
    $i = $inst('USP Lewisburg / FCI Danbury / FCI Petersburg', 'Lewisburg', 'Pennsylvania');
    $ensureCase($digia, [
        'institution_id'     => $i->id,
        'charges'            => 'Selective Training and Service Act of 1940 — refusal to register for the draft.',
        'arrest_date'        => '1942-06-15',
        'incarceration_date' => '1942-06-15',
        'release_date'       => '1945-06-15',
        'sentence'           => '3 years federal prison; served at Lewisburg, Danbury, and Petersburg.',
        'convicted'          => 'Yes — federal conviction',
    ]);
}

$peck = $createPrisoner('James Peck', [
    'description' => "James \"Jim\" Peck (1914-1993) was a New York-born pacifist, civil rights activist, and journalist. As a WWII conscientious objector he refused induction in 1942; convicted under the Selective Training and Service Act and served 18 months at federal prison camps including Danbury. After the war he was one of 16 participants in the 1947 Journey of Reconciliation organized by CORE and FOR — the precursor to the Freedom Rides. He was again a Freedom Rider in 1961 and was severely beaten by Klansmen in Birmingham, Alabama on Mother's Day 1961, requiring 53 stitches. Long-time editor of the CORE-lator.",
    'birthdate'   => '1914-12-19',
    'death_date'  => '1993-07-12',
    'race'        => 'White',
    'gender'      => 'Male',
    'state'       => 'New York',
    'ideologies'  => ['Pacifism','Civil rights','War Resister'],
    'affiliation' => ['War Resisters League','Congress of Racial Equality (CORE)','Fellowship of Reconciliation (FOR)'],
]);
if ($peck) {
    $i = $inst('FCI Danbury', 'Danbury', 'Connecticut');
    $ensureCase($peck, [
        'institution_id'     => $i->id,
        'charges'            => 'Selective Training and Service Act of 1940 — refusal of induction.',
        'arrest_date'        => '1942-12-15',
        'incarceration_date' => '1942-12-15',
        'release_date'       => '1944-06-15',
        'sentence'           => '18 months federal prison at FCI Danbury.',
        'convicted'          => 'Yes — federal conviction',
    ]);
}

$houser = $createPrisoner('George Houser', [
    'description' => "George Mills Houser (1916-2015) was a Methodist minister and pacifist who in 1940, while a student at Union Theological Seminary, refused to register for the draft despite the seminary's offer of an automatic exemption. He and seven Union Seminary classmates (\"the Union Eight\") were convicted of violating the Selective Training and Service Act of 1940 and sentenced to 1 year each at FCI Danbury. After release he co-founded the Congress of Racial Equality (CORE) in Chicago in 1942 with James Farmer, Bernice Fisher, and others; led the 1947 Journey of Reconciliation; and from 1953 led the American Committee on Africa supporting decolonization and anti-apartheid struggles.",
    'birthdate'   => '1916-06-02',
    'death_date'  => '2015-08-19',
    'race'        => 'White',
    'gender'      => 'Male',
    'state'       => 'New York',
    'ideologies'  => ['Pacifism','Civil rights','Anti-imperialism','War Resister'],
    'affiliation' => ['Congress of Racial Equality (CORE)','Fellowship of Reconciliation (FOR)','American Committee on Africa'],
]);
if ($houser) {
    $i = $inst('FCI Danbury', 'Danbury', 'Connecticut');
    $ensureCase($houser, [
        'institution_id'     => $i->id,
        'charges'            => 'Selective Training and Service Act of 1940 — refused to register for the draft as one of "the Union Eight" Union Theological Seminary students.',
        'arrest_date'        => '1940-10-16',
        'sentenced_date'     => '1940-11-14',
        'incarceration_date' => '1940-11-14',
        'release_date'       => '1941-09-09',
        'sentence'           => '1 year federal prison at FCI Danbury (~9 months served before parole).',
        'convicted'          => 'Yes — federal conviction',
    ]);
}

echo "\nDone. updates={$updates}, creates={$creates}, unchanged={$unchanged}\n";
