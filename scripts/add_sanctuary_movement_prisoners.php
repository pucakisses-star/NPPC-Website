<?php

declare(strict_types=1);

/**
 * Bulk-add the prosecuted defendants of the 1980s U.S. Sanctuary
 * Movement: the eight Tucson Sanctuary Trial convictions in U.S. v.
 * Aguilar (D. Ariz. 1986), Jim Corbett (acquitted), and Stacey Lynn
 * Merkt (separately prosecuted in S.D. Texas, 1984 and 1985).
 *
 * Background: The Sanctuary Movement was a national network of churches,
 * synagogues, and individuals (peak ~500 congregations) that openly
 * sheltered, transported, and provided legal aid to Salvadoran and
 * Guatemalan refugees fleeing U.S.-backed military violence in the
 * 1980s, in deliberate violation of federal immigration law. The
 * Reagan-era DOJ ran "Operation Sojourner," using paid informants
 * wearing wires inside churches; eleven sanctuary workers stood trial
 * in Phoenix beginning November 1985 before Judge Earl Carroll, and
 * eight were convicted on May 1, 1986. Sentences were uniformly
 * probation (no jail) for the Tucson defendants; Stacey Merkt actually
 * served 179 days in federal prison in 1987 while pregnant and was
 * Amnesty International's first U.S. "prisoner of conscience"
 * designation since 1979.
 *
 * Run on production:
 *   cd /var/www/NPPC-Website && php scripts/add_sanctuary_movement_prisoners.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;

$tucsonInstitution = [
    'name'  => 'U.S. District Court, District of Arizona (Phoenix) — U.S. v. Aguilar',
    'city'  => 'Phoenix',
    'state' => 'Arizona',
];

$tucsonChargesBase = 'U.S. v. Aguilar (D. Ariz. 1986), the Tucson Sanctuary Trial. Indicted January 1985 on conspiracy and alien-smuggling/transporting/harboring charges (8 U.S.C. § 1324) following Operation Sojourner — an FBI/INS undercover operation in which paid informant Jesus Cruz wore a recording device into church meetings and Bible studies. Trial began November 1985 in Phoenix before Judge Earl H. Carroll; defense motions to introduce evidence on refugees\' fear of persecution and on international refugee law were denied. Verdict May 1, 1986: eight convicted, three acquitted. Sentenced July 1–2, 1986 to probation only; the prosecution did not seek incarceration.';

$tucsonProsecutor = 'U.S. Attorney for the District of Arizona';
$tucsonJudge      = 'Hon. Earl H. Carroll';

$texasMerktInstitution = [
    'name'  => 'U.S. District Court, Southern District of Texas (Brownsville/Corpus Christi)',
    'city'  => 'Brownsville',
    'state' => 'Texas',
];

$people = [
    [
        'name'         => 'John M. Fife',
        'first_name'   => 'John', 'middle_name' => 'M.', 'last_name' => 'Fife',
        'aka'          => 'Reverend John Fife',
        'gender'       => 'Male', 'race' => 'White',
        'state'        => 'Arizona',
        'birthdate'    => '1940-01-01',
        'description'  => 'Raised in Western Pennsylvania and trained at Pittsburgh Theological Seminary, Fife became pastor of Southside Presbyterian Church in Tucson in 1969 and served there until 2005. On March 24, 1982, he and his congregation publicly declared the church a sanctuary for Central American refugees, an act widely regarded as the launch of the U.S. Sanctuary Movement. After conviction in U.S. v. Aguilar he continued his border-justice work, served as Moderator of the Presbyterian Church (USA) General Assembly in 1992, and co-founded No More Deaths / No Más Muertes in 2004.',
        'affiliation'  => ['Sanctuary Movement','Presbyterian Church (USA)','Southside Presbyterian Church (Tucson)','No More Deaths (later)'],
        'cases' => [[
            'institution' => $tucsonInstitution,
            'charges'     => $tucsonChargesBase . ' Fife was convicted on three counts of conspiracy and aiding-and-abetting transportation of undocumented refugees.',
            'arrest_date'    => '1985-01-14',
            'sentenced_date' => '1986-07-01',
            'convicted'      => 'Yes — May 1, 1986 jury verdict (3 counts)',
            'sentence'       => 'Five years probation on each of three counts (concurrent); jail term suspended.',
            'prosecutor'     => $tucsonProsecutor,
            'judge'          => $tucsonJudge,
        ]],
    ],
    [
        'name'         => 'Darlene Ann Nicgorski',
        'first_name'   => 'Darlene', 'middle_name' => 'Ann', 'last_name' => 'Nicgorski',
        'aka'          => 'Sister Darlene Nicgorski, SSSF',
        'gender'       => 'Female', 'race' => 'White',
        'state'        => 'Arizona',
        'birthdate'    => '1943-11-19',
        'death_date'   => '2017-02-28',
        'description'  => 'Born in Milwaukee, Wisconsin, Nicgorski entered the School Sisters of St. Francis in 1966 and made final profession in 1974. After her Franciscan mentor Father Tulio Maruzzo was assassinated in Guatemala in July 1981, she came to Arizona to coordinate the national Sanctuary Movement\'s referral network for Guatemalan and Salvadoran refugees from Phoenix. She was the most prominent Catholic woman religious in the prosecution. She left the SSSF in 1987 and worked the rest of her life as a peace and women\'s-rights activist; her papers are at the Claremont Colleges Library. She died Feb 28, 2017 in Pomona, California, aged 73.',
        'affiliation'  => ['Sanctuary Movement','Roman Catholic Church','School Sisters of St. Francis (former)'],
        'cases' => [[
            'institution' => $tucsonInstitution,
            'charges'     => $tucsonChargesBase . ' Nicgorski was convicted of conspiracy plus two counts each of aiding-and-abetting transportation and harboring under 8 U.S.C. § 1324(a)(2) and (a)(3).',
            'arrest_date'    => '1985-01-14',
            'sentenced_date' => '1986-07-01',
            'convicted'      => 'Yes — May 1, 1986 jury verdict (5 counts)',
            'sentence'       => 'Five years probation; suspended jail term.',
            'prosecutor'     => $tucsonProsecutor,
            'judge'          => $tucsonJudge,
        ]],
    ],
    [
        'name'         => 'Anthony Clark',
        'first_name'   => 'Anthony', 'last_name' => 'Clark',
        'aka'          => 'Father Anthony "Tony" Clark',
        'gender'       => 'Male', 'race' => 'White',
        'state'        => 'Arizona',
        'description'  => 'Anthony Clark was the Roman Catholic pastor of Sacred Heart of Jesus Parish in Nogales, Arizona — the U.S.-side counterpart to Father Quiñones\'s parish across the border. Government tapes captured him in June 1984 destroying immigration documents and giving false southern-Mexican identities to Central Americans crossing through his parish. After conviction he stated, "I attempted to follow the law and not break it. I am not guilty before God and the good people of this land."',
        'affiliation'  => ['Sanctuary Movement','Roman Catholic Diocese of Tucson','Sacred Heart Parish (Nogales AZ)'],
        'cases' => [[
            'institution' => $tucsonInstitution,
            'charges'     => $tucsonChargesBase . ' Clark was convicted under 8 U.S.C. § 1324(a)(3) for harboring Jose Ruben Torres (per Ninth Circuit opinion 883 F.2d 662).',
            'arrest_date'    => '1985-01-14',
            'sentenced_date' => '1986-07-01',
            'convicted'      => 'Yes — May 1, 1986 jury verdict',
            'sentence'       => 'Three years probation.',
            'prosecutor'     => $tucsonProsecutor,
            'judge'          => $tucsonJudge,
        ]],
    ],
    [
        'name'         => 'Ramón Dagoberto Quiñones',
        'first_name'   => 'Ramón', 'middle_name' => 'Dagoberto', 'last_name' => 'Quiñones',
        'aka'          => 'Father Quiñones',
        'gender'       => 'Male', 'race' => 'Latino/Hispanic',
        'state'        => 'Mexico',
        'description'  => 'Quiñones was a Mexican Catholic priest in Nogales, Sonora, who ran a refugee feeding program at his church for Central Americans transiting north and helped coordinate the cross-border passage with U.S. counterparts. The only Mexican national among the eleven defendants, he was the Sanctuary Movement\'s most important point of entry into Mexico. Operation Sojourner informant Jesus Cruz infiltrated the network in 1984 by approaching Quiñones with a truckload of fruit. Quiñones decried the U.S. operation\'s reach into Mexico as a sovereignty violation.',
        'affiliation'  => ['Sanctuary Movement','Roman Catholic Church (Diocese of Hermosillo)','Parish in Nogales, Sonora'],
        'cases' => [[
            'institution' => $tucsonInstitution,
            'charges'     => $tucsonChargesBase . ' Quiñones was convicted of conspiracy and aiding-and-abetting violations of 8 U.S.C. § 1324.',
            'arrest_date'    => '1985-01-14',
            'sentenced_date' => '1986-07-01',
            'convicted'      => 'Yes — May 1, 1986 jury verdict',
            'sentence'       => 'Five years probation, unsupervised because U.S. probation officers had no jurisdiction in Mexico.',
            'prosecutor'     => $tucsonProsecutor,
            'judge'          => $tucsonJudge,
        ]],
    ],
    [
        'name'         => 'María del Socorro Pardo Viuda de Aguilar',
        'first_name'   => 'María del Socorro',
        'middle_name'  => 'Pardo Viuda',
        'last_name'    => 'de Aguilar',
        'aka'          => 'Socorro Aguilar',
        'gender'       => 'Female', 'race' => 'Latino/Hispanic',
        'state'        => 'Mexico',
        'birthdate'    => '1927-01-01',
        'description'  => 'Aguilar was a Catholic laywoman and widow ("viuda") from Nogales, Sonora, who sheltered Central American refugees in her home as part of Father Quiñones\'s parish network. She was the named lead defendant solely because her surname came first alphabetically in the indictment caption — hence the case name U.S. v. Aguilar. She voluntarily entered the U.S. in February 1985 to face charges. During the trial she became a symbol of lay Catholic devotion to the movement, famously placing a rose in the crown of thorns on a Christ figure outside the courthouse before entering for the verdict.',
        'affiliation'  => ['Sanctuary Movement','Roman Catholic laywoman','Nogales (Sonora) parish refugee shelter'],
        'cases' => [[
            'institution' => $tucsonInstitution,
            'charges'     => $tucsonChargesBase . ' Aguilar was convicted under 8 U.S.C. § 1324(a)(1) for "bringing in" Ana Benavidez (per Ninth Circuit opinion 883 F.2d 662).',
            'arrest_date'    => '1985-02-20',
            'sentenced_date' => '1986-07-01',
            'convicted'      => 'Yes — May 1, 1986 jury verdict',
            'sentence'       => 'Five years probation; no jail time.',
            'prosecutor'     => $tucsonProsecutor,
            'judge'          => $tucsonJudge,
        ]],
    ],
    [
        'name'         => 'Philip M. Willis-Conger',
        'first_name'   => 'Philip', 'middle_name' => 'M.', 'last_name' => 'Willis-Conger',
        'aka'          => 'Phil Willis-Conger',
        'gender'       => 'Male', 'race' => 'White',
        'state'        => 'Arizona',
        'description'  => 'Willis-Conger ran the Tucson Ecumenical Council\'s Task Force on Central America (TEC-TFCA), the day-to-day operational arm of the sanctuary network in Tucson. He coordinated logistics, refugee placements, and inter-church communications. After the trial he and his wife Ellen pursued seminary degrees (he earned an M.Div. from Pacific School of Religion 1986–1989); he later worked in non-profit management and housing in Santa Barbara.',
        'affiliation'  => ['Sanctuary Movement','United Methodist Church','Tucson Ecumenical Council Task Force on Central America'],
        'cases' => [[
            'institution' => $tucsonInstitution,
            'charges'     => $tucsonChargesBase . ' Willis-Conger was convicted of conspiracy and aiding-and-abetting illegal entry under 8 U.S.C. § 1324.',
            'arrest_date'    => '1985-01-14',
            'sentenced_date' => '1986-07-02',
            'convicted'      => 'Yes — May 1, 1986 jury verdict',
            'sentence'       => 'Five years probation.',
            'prosecutor'     => $tucsonProsecutor,
            'judge'          => $tucsonJudge,
        ]],
    ],
    [
        'name'         => 'Margaret Jean Hutchison',
        'first_name'   => 'Margaret', 'middle_name' => 'Jean', 'last_name' => 'Hutchison',
        'aka'          => 'Peggy Hutchison',
        'gender'       => 'Female', 'race' => 'White',
        'state'        => 'Arizona',
        'birthdate'    => '1955-01-01',
        'description'  => 'Hutchison was a Methodist young-adult border-ministry worker assigned to Tucson Metropolitan Ministry, where she handled logistics for the movement\'s refugee-assistance work and was simultaneously a graduate student in Middle East Studies at the University of Arizona. She was the first Tucson defendant sentenced and became a public spokesperson for the convicted, vowing to defy probation conditions barring further sanctuary work. She co-authored the 1986 book "Sanctuary: The New Underground Railroad" with Renny Golden and Michael McConnell.',
        'affiliation'  => ['Sanctuary Movement','United Methodist Church','Tucson Metropolitan Ministry'],
        'cases' => [[
            'institution' => $tucsonInstitution,
            'charges'     => $tucsonChargesBase . ' Hutchison was convicted of conspiracy and transportation violations under 8 U.S.C. § 1324.',
            'arrest_date'    => '1985-01-14',
            'sentenced_date' => '1986-07-01',
            'convicted'      => 'Yes — May 1, 1986 jury verdict',
            'sentence'       => 'Five years probation.',
            'prosecutor'     => $tucsonProsecutor,
            'judge'          => $tucsonJudge,
        ]],
    ],
    [
        'name'         => 'Wendy LeWin',
        'first_name'   => 'Wendy', 'last_name' => 'LeWin',
        'gender'       => 'Female', 'race' => 'White',
        'state'        => 'Arizona',
        'birthdate'    => '1959-01-01',
        'description'  => 'LeWin was the youngest defendant — 26 at trial — and the most peripheral, having worked with the Central American Refugee Project. She was identified at trial when a Salvadoran refugee witness pointed her out as the woman who had helped him. She received a lighter (3-year) probation sentence than the principal organizers. Reported by UPI as Unitarian-Universalist at the time of sentencing.',
        'affiliation'  => ['Sanctuary Movement','Unitarian Universalist Association','Central American Refugee Project'],
        'cases' => [[
            'institution' => $tucsonInstitution,
            'charges'     => $tucsonChargesBase . ' LeWin was convicted under 8 U.S.C. § 1324(a)(2) for transporting the Morelos family.',
            'arrest_date'    => '1985-01-14',
            'sentenced_date' => '1986-07-02',
            'convicted'      => 'Yes — May 1, 1986 jury verdict',
            'sentence'       => 'Three years probation.',
            'prosecutor'     => $tucsonProsecutor,
            'judge'          => $tucsonJudge,
        ]],
    ],
    [
        'name'         => 'James A. Corbett',
        'first_name'   => 'James', 'middle_name' => 'A.', 'last_name' => 'Corbett',
        'aka'          => 'Jim Corbett',
        'gender'       => 'Male', 'race' => 'White',
        'state'        => 'Arizona',
        'birthdate'    => '1933-10-08',
        'death_date'   => '2001-08-02',
        'description'  => 'Born in Casper, Wyoming, Corbett earned a BA from Colgate and an MA in philosophy from Harvard before turning to ranching, goat-herding, and library work. In May 1981 he encountered a Salvadoran refugee detained by the Border Patrol and began smuggling refugees across the Sonoran Desert; in March 1982 he and Rev. John Fife declared Southside Presbyterian Church a public sanctuary, launching the national Sanctuary Movement. He was the most prominent of the three defendants acquitted in U.S. v. Aguilar. He died of cerebellar paraneoplastic syndrome at his Cascabel ranch in 2001 at age 67. His books "Goatwalking" (1991) and the posthumous "Sanctuary for All Life" remain standard texts in the movement.',
        'affiliation'  => ['Sanctuary Movement (co-founder)','Religious Society of Friends (Quaker)','Pima Friends Meeting (Tucson)'],
        'cases' => [[
            'institution' => $tucsonInstitution,
            'charges'     => $tucsonChargesBase . ' Corbett was indicted on conspiracy and harboring/transporting counts under 8 U.S.C. § 1324 and acquitted of all charges by the jury on May 1, 1986.',
            'arrest_date'    => '1985-01-14',
            'sentenced_date' => null,
            'convicted'      => 'No — acquitted of all charges, May 1, 1986',
            'sentence'       => 'None — acquitted.',
            'prosecutor'     => $tucsonProsecutor,
            'judge'          => $tucsonJudge,
        ]],
    ],
    [
        'name'         => 'Stacey Lynn Merkt',
        'first_name'   => 'Stacey', 'middle_name' => 'Lynn', 'last_name' => 'Merkt',
        'gender'       => 'Female', 'race' => 'White',
        'state'        => 'Texas',
        'birthdate'    => '1953-01-01',
        'description'  => 'A native of Richmond, California, Merkt joined the Bijou House intentional Christian community in Colorado Springs before moving to South Texas in 1982 as a lay volunteer at Casa Oscar Romero, a Catholic-diocesan refugee shelter run by Jack Elder near the Mexican border. She was the first U.S. Sanctuary Movement worker convicted: in February 1984 a federal jury in the Southern District of Texas convicted her of three counts of transporting Salvadorans (Judge Filemón Vela suspended a 90-day sentence and gave her two years probation). On Feb 21, 1985 she and Jack Elder were convicted of conspiracy to transport; she was sentenced to 179 days in federal prison and served the term in 1987 while pregnant. Amnesty International designated her a "prisoner of conscience" — the first American so designated since 1979. After her release she co-founded RAICES (Refugee and Immigrant Center for Education and Legal Services) with Elder.',
        'affiliation'  => ['Sanctuary Movement','Casa Oscar Romero (Brownsville Diocese refugee shelter)','Bijou House (former, Colorado Springs)','RAICES (later)'],
        'cases' => [
            [
                'institution' => $texasMerktInstitution,
                'charges'     => 'United States v. Merkt (S.D. Tex. 1984): three felony counts of transporting undocumented Salvadoran refugees (8 U.S.C. § 1324). The first U.S. Sanctuary Movement worker convicted.',
                'arrest_date'    => '1984-02-22',
                'sentenced_date' => '1984-05-14',
                'convicted'      => 'Yes — May 1984 jury verdict',
                'sentence'       => '90-day sentence suspended; two years probation.',
                'prosecutor'     => 'U.S. Attorney for the Southern District of Texas',
                'judge'          => 'Hon. Filemón B. Vela',
            ],
            [
                'institution' => $texasMerktInstitution,
                'charges'     => 'United States v. Merkt et al. (S.D. Tex. 1985): conspiracy to transport undocumented refugees (8 U.S.C. §§ 371, 1324) — co-defendants included Casa Oscar Romero director Jack Elder. Affirmed at 764 F.2d 266 (5th Cir. 1985).',
                'arrest_date'    => '1985-02-21',
                'sentenced_date' => '1985-04-19',
                'incarceration_date' => '1987-01-01',
                'release_date'   => '1987-06-29',
                'convicted'      => 'Yes — Feb 21, 1985 jury verdict',
                'sentence'       => '179 days federal prison; served in 1987 while pregnant. Designated an Amnesty International "prisoner of conscience."',
                'prosecutor'     => 'U.S. Attorney for the Southern District of Texas',
                'judge'          => 'Hon. Hayden W. Head',
            ],
        ],
    ],
];

$created = 0; $skipped = 0; $casesAdded = 0;

foreach ($people as $p) {
    $existing = Prisoner::where('name', $p['name'])->first();
    if ($existing) {
        $prisoner = $existing;
        echo "  EXISTS  {$p['name']} (id={$prisoner->id})\n";
        $skipped++;
    } else {
        $prisoner = Prisoner::create([
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
            'era'          => '1980s',
            'ideologies'   => ['Pacifism','Christian peace activism','Anti-imperialism','Refugee rights'],
            'affiliation'  => $p['affiliation'] ?? ['Sanctuary Movement'],
            'in_custody'   => false,
            'released'     => true,
        ]);
        echo "  CREATED {$p['name']} (id={$prisoner->id})\n";
        $created++;
    }

    foreach ($p['cases'] as $c) {
        $arrestDate = $c['arrest_date'];
        $existingCase = $prisoner->cases()->where('arrest_date', $arrestDate)->first();
        if ($existingCase) {
            echo "    case exists (arrest_date={$arrestDate})\n";
            continue;
        }

        $institution = Institution::firstOrCreate(
            ['name' => $c['institution']['name']],
            ['city' => $c['institution']['city'] ?? null, 'state' => $c['institution']['state'] ?? null],
        );

        PrisonerCase::create([
            'prisoner_id'        => $prisoner->id,
            'institution_id'     => $institution->id,
            'charges'            => $c['charges'],
            'arrest_date'        => $arrestDate,
            'incarceration_date' => $c['incarceration_date'] ?? null,
            'release_date'       => $c['release_date'] ?? null,
            'sentenced_date'     => $c['sentenced_date'] ?? null,
            'convicted'          => $c['convicted'],
            'sentence'           => $c['sentence'],
            'prosecutor'         => $c['prosecutor'] ?? null,
            'judge'              => $c['judge'] ?? null,
        ]);
        echo "    + case (arrest_date={$arrestDate})\n";
        $casesAdded++;
    }
}

echo "\nDone. created={$created}, already-existed={$skipped}, cases-added={$casesAdded}\n";
