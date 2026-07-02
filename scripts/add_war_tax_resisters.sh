#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_war_tax_resisters.sh
# Eight confirmed American war tax resisters who served jail or prison time,
# spanning 1942 (WWII automobile defense tax) through 2005 (post-9/11 military spending).
# Sources: NWTRCC compiled records, Wikipedia, War Resisters League history.
set +e

cat > /tmp/add_war_tax_resisters.php << 'PHPEOF'
<?php

function addResister(array $data): void
{
    $name = $data['name'];

    if (\App\Models\Prisoner::withoutGlobalScopes()->where('name', $name)->exists()) {
        echo 'SKIP (already exists): ' . $name . PHP_EOL;
        return;
    }

    $fields = [
        'name'        => $name,
        'first_name'  => $data['first_name'],
        'last_name'   => $data['last_name'],
        'description' => $data['description'],
        'state'       => $data['state'],
        'gender'      => $data['gender'],
        'ideologies'  => $data['ideologies'],
        'era'         => $data['era'],
        'in_custody'  => false,
        'released'    => true,
    ];
    if (!empty($data['race']))       $fields['race']       = $data['race'];
    if (!empty($data['birthdate']))  $fields['birthdate']  = $data['birthdate'];
    if (!empty($data['death_date'])) $fields['death_date'] = $data['death_date'];
    if (!empty($data['affiliation'])) $fields['affiliation'] = $data['affiliation'];

    $p = \App\Models\Prisoner::create($fields);

    foreach ($data['cases'] as $c) {
        $instFields = ['name' => $c['inst_name']];
        if (!empty($c['inst_city']))  $instFields['city']  = $c['inst_city'];
        if (!empty($c['inst_state'])) $instFields['state'] = $c['inst_state'];
        $inst = \App\Models\Institution::firstOrCreate($instFields);

        $cf = [
            'institution_id' => $inst->id,
            'charges'        => $c['charges'],
            'convicted'      => $c['convicted'],
            'sentence'       => $c['sentence'],
        ];
        if (!empty($c['arrest_date']))        $cf['arrest_date']        = $c['arrest_date'];
        if (!empty($c['incarceration_date'])) $cf['incarceration_date'] = $c['incarceration_date'];
        if (!empty($c['release_date']))       $cf['release_date']       = $c['release_date'];
        if (isset($c['imprisoned_for_days'])) $cf['imprisoned_for_days'] = $c['imprisoned_for_days'];
        if (!empty($c['judge']))              $cf['judge']              = $c['judge'];
        if (!empty($c['prosecutor']))         $cf['prosecutor']         = $c['prosecutor'];

        $p->cases()->save(new \App\Models\PrisonerCase($cf));
    }

    echo 'Created: ' . $name . PHP_EOL;
}

// ─── 1. Ernest Bromley (WWII automobile defense tax, NC, 1942) ────────────────
addResister([
    'name'       => 'Ernest Bromley',
    'first_name' => 'Ernest',
    'last_name'  => 'Bromley',
    'birthdate'  => '1912-03-14',
    'death_date' => '1997-12-17',
    'description' => <<<'DESC'
Ernest Bromley is considered the first documented modern American war tax resister. A Methodist minister from Bath, North Carolina, he refused to pay the federal automobile use/defense tax stamp required by the government in 1942 — a total of $7.09 for two stamps — redirecting the money to Methodist overseas relief. He was convicted on October 9, 1942, in U.S. District Court in Washington, North Carolina before Judge I.M. Meekins and sentenced to $25 fine or 30 days jail per count. He chose to serve both 30-day terms consecutively (60 days total) rather than pay, stating his act was not tax evasion but principled opposition to war taxation. He lost his minister's position but became a founding force in modern war tax resistance organizing, co-founding Peacemakers in 1948. The IRS attempted to seize the Bromley home in Cincinnati in the 1970s; Peacemakers supporters occupied the property and prevented the auction.
DESC,
    'state'       => 'North Carolina',
    'race'        => 'White',
    'gender'      => 'Male',
    'ideologies'  => ['War tax resistance', 'Christian pacifism', 'Anti-militarism'],
    'affiliation' => ['Peacemakers', 'War Resisters League'],
    'era'         => '1940s',
    'cases' => [[
        'inst_name'          => 'Federal Jail',
        'inst_city'          => 'Washington',
        'inst_state'         => 'North Carolina',
        'charges'            => 'Two counts of willful refusal to pay the federal automobile use/defense tax stamp ($7.09 total — two stamps at $2.09 and $5.00) as principled opposition to World War II military spending',
        'incarceration_date' => '1942-10-09',
        'imprisoned_for_days' => 60,
        'convicted'          => 'Yes — convicted at trial (October 9, 1942)',
        'judge'              => 'U.S. District Judge I.M. Meekins, Eastern District of North Carolina (Washington)',
        'sentence'           => '$25 fine or 30 days jail per count (2 counts); chose to serve 60 days total rather than pay fines',
    ]],
]);

// ─── 2. Katsuki James Otsuka (Cold War tax resistance, Indiana, 1949) ─────────
addResister([
    'name'       => 'Katsuki James Otsuka',
    'first_name' => 'Katsuki',
    'last_name'  => 'Otsuka',
    'birthdate'  => '1921-01-22',
    'death_date' => '1984-05-25',
    'description' => <<<'DESC'
Katsuki James Otsuka was a Japanese American Nisei from Richmond, Indiana who had been forcibly interned at the Tule Lake War Relocation Center under Executive Order 9066 during World War II. A Quaker and member of the War Resisters League and Peacemakers, he refused to pay 29% of his income tax (the portion he calculated was dedicated to military spending), withholding $4.50, redirecting it to peace organizations. Arrested and taken to U.S. District Court in Indianapolis on August 19, 1949 — alongside fellow Peacemaker Eroseanna Robinson — he was sentenced to 90 days and a $100 fine, which he also refused to pay, resulting in additional jail time. He served approximately five months total at the Federal Correctional Institution in Ashland, Kentucky, and was released on January 15, 1950. Upon release, he pledged to continue his resistance.
DESC,
    'state'       => 'Indiana',
    'race'        => 'Asian',
    'gender'      => 'Male',
    'ideologies'  => ['War tax resistance', 'Pacifism', 'Anti-militarism'],
    'affiliation' => ['Peacemakers', 'War Resisters League', 'Religious Society of Friends'],
    'era'         => '1940s',
    'cases' => [[
        'inst_name'          => 'Federal Correctional Institution Ashland',
        'inst_city'          => 'Ashland',
        'inst_state'         => 'Kentucky',
        'charges'            => 'Contempt of court for refusing to pay federal income taxes (withheld $4.50, the 29% military portion of his tax liability) and refusing to turn over financial records to the IRS',
        'arrest_date'        => '1949-08-19',
        'release_date'       => '1950-01-15',
        'imprisoned_for_days' => 149,
        'convicted'          => 'Yes',
        'sentence'           => '90 days federal prison + $100 fine (refused to pay fine, serving additional time); total approximately 5 months',
    ]],
]);

// ─── 3. Rev. Maurice F. McCrackin (Korean War / Cold War tax resistance, OH, 1958)
addResister([
    'name'       => 'Maurice McCrackin',
    'first_name' => 'Maurice',
    'last_name'  => 'McCrackin',
    'birthdate'  => '1905-01-01',
    'description' => <<<'DESC'
Rev. Maurice F. McCrackin was a Cincinnati, Ohio Presbyterian minister and civil rights activist widely considered one of the most important figures in the modern American war tax resistance movement. Beginning during the Korean War, he refused to file or pay federal income taxes, citing his Christian pacifist opposition to war spending. By 1957 he owed $979.61 in back taxes. When the IRS summoned him to court, he practiced total noncooperation — refusing to walk, speak, or acknowledge the court's authority — and was convicted of contempt in U.S. District Court in Cincinnati before Judge John H. Druffel in December 1958. He was sentenced to 6 months in federal prison and fined $250. He served approximately five to six months at Allenwood Federal Prison in Pennsylvania and was released in May 1959. The Presbyterian Church defrocked him as a result of his conviction; the General Assembly formally reinstated him with an apology in 1987. He also spent periods in jail for civil rights activities and for refusing to testify against a former prisoner he had visited. He died in 1997 after decades as one of Cincinnati's most beloved radical ministers.
DESC,
    'state'       => 'Ohio',
    'race'        => 'White',
    'gender'      => 'Male',
    'ideologies'  => ['War tax resistance', 'Christian pacifism', 'Anti-militarism', 'Civil rights'],
    'affiliation' => ['Peacemakers', 'Congress of Racial Equality'],
    'era'         => '1950s',
    'cases' => [[
        'inst_name'          => 'Allenwood Federal Prison',
        'inst_city'          => 'Montgomery',
        'inst_state'         => 'Pennsylvania',
        'charges'            => 'Contempt of court for failing to respond to an IRS summons after years of refusing to file or pay federal income taxes during and after the Korean War; practiced total noncooperation with court proceedings',
        'incarceration_date' => '1958-12-01',
        'release_date'       => '1959-05-01',
        'imprisoned_for_days' => 152,
        'convicted'          => 'Yes — contempt of court (December 1958)',
        'judge'              => 'U.S. District Judge John H. Druffel, Southern District of Ohio',
        'sentence'           => '6 months federal prison; $250 fine',
    ]],
]);

// ─── 4. Eroseanna Robinson (Cold War tax resistance, Illinois, 1960) ──────────
addResister([
    'name'       => 'Eroseanna Robinson',
    'first_name' => 'Eroseanna',
    'last_name'  => 'Robinson',
    'description' => <<<'DESC'
Eroseanna "Sis" Robinson was a Black Chicago social worker, recreational director, and amateur track athlete who refused to pay federal income taxes for the years 1954–1958 as principled opposition to military spending. A member of Peacemakers and the War Resisters League, she was arrested on January 27, 1960 and charged with contempt of court for refusing to pay taxes and refusing to turn over financial records to the IRS. Convicted in federal court in Chicago before Judge Edwin A. Robson, she was sentenced to one year and one day. She was held first at Cook County Jail and then transferred to Alderson Federal Prison in West Virginia. From her first day of incarceration she engaged in a total hunger strike, refusing all food, and was force-fed throughout. Prison officials released her early after approximately three months, citing the burden on medical staff. Robinson was also a pioneering civil rights activist — in 1959 she remained seated during the national anthem at an outdoor track event at Soldier Field, and in 1961 she was arrested alongside Wally and Juanita Nelson during the Route 40 Freedom Riders restaurant desegregation campaign in Maryland. She died in 1976.
DESC,
    'state'       => 'Illinois',
    'race'        => 'Black',
    'gender'      => 'Female',
    'ideologies'  => ['War tax resistance', 'Pacifism', 'Anti-militarism', 'Civil rights'],
    'affiliation' => ['Peacemakers', 'War Resisters League'],
    'era'         => '1960s',
    'cases' => [[
        'inst_name'          => 'Alderson Federal Prison',
        'inst_city'          => 'Alderson',
        'inst_state'         => 'West Virginia',
        'charges'            => 'Contempt of court for refusing to pay federal income taxes for 1954–1958 and refusing to turn over financial records to the IRS',
        'arrest_date'        => '1960-01-27',
        'imprisoned_for_days' => 108,
        'convicted'          => 'Yes — contempt of court',
        'judge'              => 'U.S. District Judge Edwin A. Robson, Northern District of Illinois',
        'sentence'           => 'One year and one day; released early after approximately three months following a sustained hunger strike',
    ]],
]);

// ─── 5. Arthur Evans (nuclear/Cold War tax resistance, Colorado, 1963) ─────────
addResister([
    'name'       => 'Arthur Evans',
    'first_name' => 'Arthur',
    'last_name'  => 'Evans',
    'birthdate'  => '1920-02-21',
    'death_date' => '2009-03-10',
    'description' => <<<'DESC'
Arthur Evans was a Denver, Colorado Quaker physician and lifelong pacifist who refused all or part of his federal income taxes every year from the early 1940s onward, donating the refused amounts (and sometimes double) to charitable causes. In 1963 he was sentenced to 90 days in Jefferson County Jail in Colorado for contempt of court after refusing to provide financial records to the Internal Revenue Service. He served the full 90-day term. He was one of only six people confirmed imprisoned for war tax resistance in the United States between 1945 and 1965. In a 1962 AP interview he stated: "I won't pay the tax for war voluntarily. This way the responsibility for committing the immoral act is on the government."
DESC,
    'state'       => 'Colorado',
    'race'        => 'White',
    'gender'      => 'Male',
    'ideologies'  => ['War tax resistance', 'Pacifism', 'Anti-nuclear', 'Anti-militarism'],
    'affiliation' => ['Religious Society of Friends', 'American Friends Service Committee', 'Fellowship of Reconciliation'],
    'era'         => '1960s',
    'cases' => [[
        'inst_name'          => 'Jefferson County Jail',
        'inst_state'         => 'Colorado',
        'charges'            => 'Contempt of court for refusing to provide financial records to the Internal Revenue Service after years of refusing all or part of federal income taxes on grounds of opposition to military and nuclear weapons spending',
        'imprisoned_for_days' => 90,
        'convicted'          => 'Yes — contempt of court (1963)',
        'sentence'           => '90 days jail',
    ]],
]);

// ─── 6. Karl H. Meyer (Vietnam War W-4 resistance, Illinois, 1971) ────────────
addResister([
    'name'       => 'Karl Meyer',
    'first_name' => 'Karl',
    'last_name'  => 'Meyer',
    'birthdate'  => '1937-01-01',
    'description' => <<<'DESC'
Karl H. Meyer is a Chicago-based Catholic Worker activist and lifelong war tax resister who developed and popularized the W-4 exemption inflation strategy — claiming excessive dependents on withholding certificates to eliminate tax withholding at the source — as a tool of war tax resistance during the Vietnam War era. He also wrote the first leaflet on telephone excise tax refusal in 1966, sparking a mass movement. In 1971 he was charged in U.S. District Court in Chicago on a five-count criminal information for falsely and fraudulently filing W-4 withholding exemption certificates; three counts were dropped and he entered a nolo contendere plea on two counts before Judge Joseph Sam Perry. He was sentenced to 24 months and served 9 months at the Federal Correctional Institution in Sandstone, Minnesota, then received parole for the remaining 15 months. At Sandstone, approximately a dozen conscientious objectors and war resisters were imprisoned simultaneously; musicians Steve Goodman, Bonnie Koloc, and John Prine performed for them there. Meyer has been arrested over 50 times across decades of activism. He later founded the Nashville Greenlands community in Tennessee in 1997.
DESC,
    'state'       => 'Illinois',
    'race'        => 'White',
    'gender'      => 'Male',
    'ideologies'  => ['War tax resistance', 'Catholic Worker', 'Pacifism', 'Anti-Vietnam War'],
    'affiliation' => ['Catholic Worker Movement'],
    'era'         => '1970s',
    'cases' => [[
        'inst_name'          => 'Federal Correctional Institution Sandstone',
        'inst_city'          => 'Sandstone',
        'inst_state'         => 'Minnesota',
        'charges'            => 'Five counts of falsely and fraudulently filing W-4 withholding exemption certificates (claiming excessive dependents to eliminate federal income tax withholding) — three counts dropped; nolo contendere on two counts',
        'incarceration_date' => '1971-05-07',
        'imprisoned_for_days' => 274,
        'convicted'          => 'Yes — nolo contendere (2 of 5 counts; May 7, 1971)',
        'judge'              => 'U.S. District Judge Joseph Sam Perry, Northern District of Illinois',
        'sentence'           => '24 months federal prison; served 9 months at FCI Sandstone then 15 months parole',
    ]],
]);

// ─── 7. Randy Kehler (contempt arising from tax resistance, Massachusetts, 1991)
addResister([
    'name'       => 'Randy Kehler',
    'first_name' => 'Randy',
    'last_name'  => 'Kehler',
    'birthdate'  => '1944-07-16',
    'death_date' => '2024-07-21',
    'description' => <<<'DESC'
Gordon Randall "Randy" Kehler was a Massachusetts pacifist and anti-nuclear activist who served as Executive Director of the National Nuclear Weapons Freeze Campaign from 1981 to 1984. Beginning in 1977, he refused to pay federal income taxes, donating the refused amounts to charity. In 1989 the IRS seized his family home in Colrain, Massachusetts. Kehler returned to the property after a court ordered him to leave, and on December 3, 1991 he was arrested for contempt of court for violating that order. He was sentenced to 6 months in Hampshire County Jail in Northampton, Massachusetts and was released approximately February 12, 1992, after the house was sold at public auction. His case was documented in the 1997 film "An Act of Conscience." Kehler had previously served 22 months for draft resistance during the Vietnam War. His 1969 announcement — delivered at a conference while accepting a prison sentence for draft resistance — was what convinced Daniel Ellsberg to leak the Pentagon Papers. He died on July 21, 2024.
DESC,
    'state'       => 'Massachusetts',
    'race'        => 'White',
    'gender'      => 'Male',
    'ideologies'  => ['War tax resistance', 'Anti-nuclear', 'Anti-militarism', 'Pacifism'],
    'affiliation' => ['National Nuclear Weapons Freeze Campaign', 'Pioneer Valley War Tax Resisters'],
    'era'         => '1990s',
    'cases' => [[
        'inst_name'          => 'Hampshire County Jail',
        'inst_city'          => 'Northampton',
        'inst_state'         => 'Massachusetts',
        'charges'            => 'Contempt of court for returning to his Colrain, Massachusetts home after a court order following IRS seizure of the property arising from years of war tax resistance (refusing federal income taxes since 1977)',
        'arrest_date'        => '1991-12-03',
        'release_date'       => '1992-02-12',
        'imprisoned_for_days' => 71,
        'convicted'          => 'Yes — contempt of court',
        'sentence'           => '6 months jail; released after approximately 2.5 months when home was sold at public auction (February 12, 1992)',
    ]],
]);

// ─── 8. J. Tony Serra (post-9/11 war tax resistance, California, 2005) ─────────
addResister([
    'name'       => 'J. Tony Serra',
    'first_name' => 'Tony',
    'last_name'  => 'Serra',
    'birthdate'  => '1934-02-11',
    'description' => <<<'DESC'
J. Tony Serra is a legendary San Francisco criminal defense attorney known for representing Black Panthers, the Hells Angels, and other counterculture clients, and for famously wearing dashikis to court. He openly refused to pay federal income taxes for decades, explicitly stating his refusal as political protest against U.S. military spending and wars. In 2005 he was convicted of willful failure to pay approximately $44,000 in federal taxes for the years 1998–1999 and sentenced to 10 months in federal prison. He served 9 months in a federal facility and one month in a halfway house. Asked at sentencing whether he would pay taxes in the future, he replied that he would not change his views. He has continued practicing law into his 80s, remaining one of the most prominent figures in American criminal defense.
DESC,
    'state'       => 'California',
    'race'        => 'White',
    'gender'      => 'Male',
    'ideologies'  => ['War tax resistance', 'Civil liberties', 'Anti-militarism'],
    'era'         => '2000s',
    'cases' => [[
        'inst_name'          => 'Federal Bureau of Prisons',
        'charges'            => 'Willful failure to pay federal income taxes — $44,000 owed for tax years 1998–1999; Serra stated his refusal was principled political protest against U.S. military spending',
        'imprisoned_for_days' => 274,
        'convicted'          => 'Yes — jury verdict (2005)',
        'sentence'           => '10 months federal prison (served 9 months in federal facility + 1 month halfway house)',
    ]],
]);

echo PHP_EOL . 'Done.' . PHP_EOL;
PHPEOF

echo "Adding 8 war tax resisters..."
php artisan tinker --execute="require '/tmp/add_war_tax_resisters.php';"
echo "Script complete."
