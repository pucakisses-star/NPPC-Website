#!/usr/bin/env bash
# Political-prisoner cases from The Black Panther newspaper, Vol. 17 (1977), read page-by-page
# from the marxists.org archive (all 28 issues, ~780 pages). RUN ON THE SERVER after review.
# Idempotent: prisoner:add de-dupes by name (recurring cases already added from Vols 18-19 are skipped).
set +e

# ===== GROUP A -- U.S. political-prisoner cases (63) =====

# Ali Shokri (WA)
php artisan prisoner:add '{"name": "Ali Shokri", "first_name": "Ali", "last_name": "Shokri", "description": "Former Iranian Air Force member who defected to the U.S. in 1973 to escape political persecution and then faced deportation; The Black Panther Vol. 17 no.1 (1977) reports an international campaign (CAIFI) to defend him against being returned to the Shah'\''s Iran. [Source: The Black Panther (Black Panther Party newspaper), v17 no.1; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "WA"}'

# Angela Davis (CA)
php artisan prisoner:add '{"name": "Angela Davis", "first_name": "Angela", "last_name": "Davis", "description": "Black Communist and prison-movement activist placed on the FBI'\''s most-wanted list and jailed on charges tied to the 1970 Marin County Courthouse events; The Black Panther Vol. 17 no.20 (1977) references the FBI COINTELPRO effort around her case. She was acquitted of all charges in 1972. [Source: The Black Panther (Black Panther Party newspaper), v17 no.20; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black liberation movement"]}'

# Assata Shakur (NY)
php artisan prisoner:add '{"name": "Assata Shakur", "first_name": "Assata", "last_name": "Shakur", "description": "Black activist and alleged Black Liberation Army member facing what The Black Panther Vol. 17 no.19 (1977) calls fabricated murder charges; attorney William Kunstler cited Church Committee findings that the FBI sought to '\''pile charge upon charge'\'' on BLA members. Convicted in the 1973 New Jersey Turnpike case, she escaped in 1979 and was granted asylum in Cuba. [Source: The Black Panther (Black Panther Party newspaper), v17 no.19; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NY", "affiliation": ["Black Liberation Army"]}'

# Bob Duren (CA)
php artisan prisoner:add '{"name": "Bob Duren", "first_name": "Bob", "last_name": "Duren", "description": "Coordinator of the Southern California Black Panther Party chapter, subjected through 1977 to LAPD surveillance and repeated arrests on charges The Black Panther Vol. 17 no.20,24 (1977) calls false (resisting arrest, inciting a riot, a marijuana charge later reduced). [Source: The Black Panther (Black Panther Party newspaper), v17 no.20,24; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black Panther Party"]}'

# Bobby Bishop (CA)
php artisan prisoner:add '{"name": "Bobby Bishop", "first_name": "Bobby", "last_name": "Bishop", "description": "Black Marine defendant among the '\''Camp Pendleton 14'\'' charged after an alleged confrontation with a suspected KKK gathering at Camp Pendleton in 1976; The Black Panther Vol. 17 no.1 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.1; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Camp Pendleton 14"]}'

# Carol Crooks (NY)
php artisan prisoner:add '{"name": "Carol Crooks", "first_name": "Carol", "last_name": "Crooks", "description": "Imprisoned prisoners'\''-rights activist at Bedford Hills, NY who initiated legal suits for Black and Third World women inmates and won an injunction against male guards; The Black Panther Vol. 17 no.13 (1977) reports the '\''Solidarity With the Sisters Inside'\'' campaign for her parole. [Source: The Black Panther (Black Panther Party newspaper), v17 no.13; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NY", "affiliation": ["Prison movement"]}'

# Clarence Copens (CA)
php artisan prisoner:add '{"name": "Clarence Copens", "first_name": "Clarence", "last_name": "Copens", "description": "Black Marine co-defendant among the '\''Camp Pendleton 14,'\'' facing courts-martial over the 1976 confrontation with a suspected KKK meeting at Camp Pendleton. The Black Panther Vol. 17 no.13 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.13; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Camp Pendleton 14"]}'

# Curtis Jones (CA)
php artisan prisoner:add '{"name": "Curtis Jones", "first_name": "Curtis", "last_name": "Jones", "description": "Black Marine corporal, one of the '\''Camp Pendleton 14'\'' court-martialed after defending themselves against Ku Klux Klan attacks at Camp Pendleton in 1976; sentenced to three months at hard labor, demoted and fined. The Black Panther Vol. 17 no.3 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.3; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Camp Pendleton 14"]}'

# David Hilliard (CA)
php artisan prisoner:add '{"name": "David Hilliard", "first_name": "David", "last_name": "Hilliard", "description": "Black Panther Party Chief of Staff, sentenced to 1–10 years and imprisoned at Vacaville on an assault-on-an-officer charge arising from the April 6, 1968 Oakland police ambush in which Bobby Hutton was killed — charges The Black Panther Vol. 17 no.23,26 (1977) calls trumped-up. [Source: The Black Panther (Black Panther Party newspaper), v17 no.23,26; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black Panther Party"]}'

# Donald Hunter (CA)
php artisan prisoner:add '{"name": "Donald Hunter", "first_name": "Donald", "last_name": "Hunter", "description": "Black Marine defendant among the '\''Camp Pendleton 14,'\'' on trial in the racist courts-martial arising from Black Marines'\'' resistance to KKK activity on base in 1976. The Black Panther Vol. 17 no.13 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.13; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Camp Pendleton 14"]}'

# Eldridge Cleaver (CA)
php artisan prisoner:add '{"name": "Eldridge Cleaver", "first_name": "Eldridge", "last_name": "Cleaver", "description": "Black Panther Party Minister of Information, whose parole was revoked without a hearing after the April 6, 1968 Oakland police ambush that killed Bobby Hutton; The Black Panther Vol. 17 no.12 (1977) recounts (in Huey Newton'\''s memoir) his re-imprisonment at Vacaville and subsequent flight into exile. [Source: The Black Panther (Black Panther Party newspaper), v17 no.12; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black Panther Party"]}'

# Eldson McGhee (GA)
php artisan prisoner:add '{"name": "Eldson McGhee", "first_name": "Eldson", "last_name": "McGhee", "description": "Black Vietnam veteran sentenced to life plus five years for an alleged armed bank-robbery kidnapping despite no victim identifying him and no prior record; The Black Panther Vol. 17 no.8 (1977) reports the Eldson McGhee Support Committee seeking his release. [Source: The Black Panther (Black Panther Party newspaper), v17 no.8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "GA"}'

# Ethel Rosenberg (NY)
php artisan prisoner:add '{"name": "Ethel Rosenberg", "first_name": "Ethel", "last_name": "Rosenberg", "description": "Executed with her husband Julius on June 19, 1953 after conviction for conspiracy to commit espionage, a case widely regarded as a Cold War frame-up. The Black Panther Vol. 17 no.1 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.1; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NY"}'

# Eva Kutas (CA)
php artisan prisoner:add '{"name": "Eva Kutas", "first_name": "Eva", "last_name": "Kutas", "description": "Activist serving a two-year term for conspiracy and harboring an escaped federal prisoner, listed by Amnesty International among U.S. political-prisoner cases in The Black Panther Vol. 17 no.21 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.21; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA"}'

# Gary Tyler (LA)
php artisan prisoner:add '{"name": "Gary Tyler", "first_name": "Gary", "last_name": "Tyler", "description": "Louisiana youth convicted of murder at age 16 after a 1974 school-desegregation confrontation, in a case widely condemned as a frame-up; listed by The Black Panther Vol. 17 no.8 (1977) among political prisoners the Party supported. He was finally released in 2016. [Source: The Black Panther (Black Panther Party newspaper), v17 no.8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "LA", "affiliation": ["Black liberation movement"]}'

# Glen White (CA)
php artisan prisoner:add '{"name": "Glen White", "first_name": "Glen", "last_name": "White", "description": "Black Marine, the only one of the '\''Camp Pendleton 14'\'' acquitted of the charges arising from Black Marines'\'' 1976 resistance to KKK activity at Camp Pendleton. The Black Panther Vol. 17 no.18 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.18; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Camp Pendleton 14"]}'

# Greg Franklin (CA)
php artisan prisoner:add '{"name": "Greg Franklin", "first_name": "Greg", "last_name": "Franklin", "description": "Black Panther Party member beaten and arrested with Robert Kendrick by LAPD in April 1977 on false charges of assault and battery on a peace officer. The Black Panther Vol. 17 no.24 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.24; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black Panther Party"]}'

# Gregory Coffey (CA)
php artisan prisoner:add '{"name": "Gregory Coffey", "first_name": "Gregory", "last_name": "Coffey", "description": "Black Marine among the '\''Camp Pendleton 14'\'' charged with assault and conspiracy after Black Marines organized against KKK activity on base in 1976. The Black Panther Vol. 17 no.8 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Camp Pendleton 14"]}'

# Henderson Watson (GA)
php artisan prisoner:add '{"name": "Henderson Watson", "first_name": "Henderson", "last_name": "Watson", "description": "One of the '\''Dawson Five,'\'' young Black men in Dawson, Georgia charged with the 1976 murder of a white store customer in what the defense called a '\''plantation-style'\'' frame-up built on coerced confessions. The Black Panther Vol. 17 no.11 (1977); charges were later dropped. [Source: The Black Panther (Black Panther Party newspaper), v17 no.11; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "GA", "affiliation": ["Dawson Five"]}'

# Herman Fletcher (CA)
php artisan prisoner:add '{"name": "Herman Fletcher", "first_name": "Herman", "last_name": "Fletcher", "description": "Marine sergeant, one of the '\''Camp Pendleton 14,'\'' who pleaded guilty under pressure to charges stemming from the 1976 confrontation with a suspected KKK meeting at Camp Pendleton. The Black Panther Vol. 17 no.18 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.18; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Camp Pendleton 14"]}'

# Inez Garcia (CA)
php artisan prisoner:add '{"name": "Inez Garcia", "first_name": "Inez", "last_name": "Garcia", "description": "Woman prosecuted for killing a man who had participated in raping her, in a landmark self-defense case; referenced in The Black Panther Vol. 17 no.17 (1977). Her initial conviction was overturned and she was acquitted at retrial in 1977. [Source: The Black Panther (Black Panther Party newspaper), v17 no.17; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA"}'

# Irving Flores (PR)
php artisan prisoner:add '{"name": "Irving Flores", "first_name": "Irving", "last_name": "Flores", "description": "Puerto Rican nationalist imprisoned for the 1954 armed attack on the U.S. House of Representatives; one of five Nationalists who refused to seek pardons implying acceptance of U.S. rule over Puerto Rico. The Black Panther Vol. 17 no.13 (1977). Released in 1979. [Source: The Black Panther (Black Panther Party newspaper), v17 no.13; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "PR", "affiliation": ["Puerto Rican independence movement"]}'

# J.D. Davenport (GA)
php artisan prisoner:add '{"name": "J.D. Davenport", "first_name": "J.D.", "last_name": "Davenport", "description": "One of the '\''Dawson Five'\'' charged with the 1976 murder of a white store customer in Dawson, Georgia; the prosecution'\''s case rested on confessions the defendants said were coerced at gunpoint. The Black Panther Vol. 17 no.11 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.11; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "GA", "affiliation": ["Dawson Five"]}'

# James Jackson Jr. (GA)
php artisan prisoner:add '{"name": "James Jackson Jr.", "first_name": "James", "last_name": "Jr.", "description": "One of the '\''Dawson Five'\'' ('\''Junior'\'' Jackson) charged with the 1976 murder of a white store customer in Dawson, Georgia; an officer testified a pistol was put between his eyes during interrogation. The Black Panther Vol. 17 no.11 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.11; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "GA", "affiliation": ["Dawson Five"]}'

# James McClain (CA)
php artisan prisoner:add '{"name": "James McClain", "first_name": "James", "last_name": "McClain", "description": "San Quentin prisoner on trial for allegedly assaulting a guard when Jonathan Jackson entered the Marin County courtroom on Aug. 7, 1970; killed in the ensuing shootout with prison guards. The Black Panther Vol. 17 no.9 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.9; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Prison movement"]}'

# James Thornwell ()
php artisan prisoner:add '{"name": "James Thornwell", "first_name": "James", "last_name": "Thornwell", "description": "Black U.S. Army private secretly dosed with LSD by Army intelligence in 1961 and then charged with stealing classified documents; The Black Panther Vol. 17 no.17 (1977) reports he denied the charges and planned to sue the government over the drug experimentation. [Source: The Black Panther (Black Panther Party newspaper), v17 no.17; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]"}'

# John Artis (NJ)
php artisan prisoner:add '{"name": "John Artis", "first_name": "John", "last_name": "Artis", "description": "Co-defendant of Rubin '\''Hurricane'\'' Carter, convicted in the 1966 Lafayette Bar and Grill triple murder in Paterson, NJ, in a prosecution long regarded as a frame-up. The Black Panther Vol. 17 no.4 (1977). The convictions were overturned in 1985. [Source: The Black Panther (Black Panther Party newspaper), v17 no.4; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NJ"}'

# John Cluchette (CA)
php artisan prisoner:add '{"name": "John Cluchette", "first_name": "John", "last_name": "Cluchette", "description": "One of the Soledad Brothers, tried with George Jackson and Fleeta Drumgo on what The Black Panther Vol. 17 no.17 (1977) calls a trumped-up charge of murdering a Soledad prison guard in 1970. [Source: The Black Panther (Black Panther Party newspaper), v17 no.17; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Soledad Brothers"]}'

# Johnny Jackson (GA)
php artisan prisoner:add '{"name": "Johnny Jackson", "first_name": "Johnny", "last_name": "Jackson", "description": "One of the '\''Dawson Five,'\'' Black youths charged with the 1976 murder of a white store customer in Dawson, Georgia; the defendants said they were miles away drawing water at the time. The Black Panther Vol. 17 no.11 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.11; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "GA", "affiliation": ["Dawson Five"]}'

# Johnny McRea ()
php artisan prisoner:add '{"name": "Johnny McRea", "first_name": "Johnny", "last_name": "McRea", "description": "Black U.S. Army veteran court-martialed over the July 1971 uprising of Black GIs against racist conditions in South Korea; sentenced to three years and a bad-conduct discharge. The Black Panther Vol. 17 no.19 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.19; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]"}'

# Johnny Ross (LA)
php artisan prisoner:add '{"name": "Johnny Ross", "first_name": "Johnny", "last_name": "Ross", "description": "Black youth sentenced to death at age 16 for rape in Louisiana; The Black Panther Vol. 17 no.14 (1977) reports his death sentence was reduced to 20 years after the U.S. Supreme Court struck down the death penalty for rape, with Southern Poverty Law Center attorneys pursuing a new trial. [Source: The Black Panther (Black Panther Party newspaper), v17 no.14; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "LA"}'

# Johnson Cole (MI)
php artisan prisoner:add '{"name": "Johnson Cole", "first_name": "Johnson", "last_name": "Cole", "description": "Head of the Detroit White Panther Party sentenced to 9.5–10 years for giving two marijuana cigarettes to an undercover officer, a bust his attorney (Sheldon Otis) said was used to stifle his political activities. The Black Panther Vol. 17 no.20 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.20; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "MI", "affiliation": ["White Panther Party"]}'

# Jose Medina (CA)
php artisan prisoner:add '{"name": "Jose Medina", "first_name": "Jose", "last_name": "Medina", "description": "Chicano activist who spoke in support of the Camp Pendleton 14 and was himself fighting deportation to Mexico. The Black Panther Vol. 17 no.1 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.1; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA"}'

# Julius Rosenberg (NY)
php artisan prisoner:add '{"name": "Julius Rosenberg", "first_name": "Julius", "last_name": "Rosenberg", "description": "Executed on June 19, 1953 after conviction for conspiracy to commit espionage (allegedly passing atomic secrets), a case The Black Panther Vol. 17 no.1 (1977) presents as a Cold War frame-up; his sons Michael and Robert Meeropol worked to reopen it. [Source: The Black Panther (Black Panther Party newspaper), v17 no.1; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NY"}'

# Larry Roberson (IL)
php artisan prisoner:add '{"name": "Larry Roberson", "first_name": "Larry", "last_name": "Roberson", "description": "Chicago chapter Black Panther Party member shot by Chicago police in July 1969, then held under police guard and, the paper says, harassed and beaten in the hospital before he died on Sept. 4, 1969; memorialized as a fallen comrade in The Black Panther Vol. 17 no.13 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.13; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "IL", "affiliation": ["Black Panther Party"]}'

# Lolita Lebron (PR)
php artisan prisoner:add '{"name": "Lolita Lebron", "first_name": "Lolita", "last_name": "Lebron", "description": "Puerto Rican nationalist imprisoned for the 1954 armed protest inside the U.S. House of Representatives; The Black Panther Vol. 17 no.13 (1977) reports she and the other Nationalists refused to personally request pardons because doing so would imply accepting U.S. sovereignty over Puerto Rico. She was released in 1979. [Source: The Black Panther (Black Panther Party newspaper), v17 no.13; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "PR", "affiliation": ["Puerto Rican independence movement"]}'

# Lorenzo Komboa Ervin (GA)
php artisan prisoner:add '{"name": "Lorenzo Komboa Ervin", "first_name": "Lorenzo", "last_name": "Ervin", "description": "Former SNCC activist serving a life sentence at the Atlanta Federal Penitentiary for the 1969 hijacking of a plane to Cuba in protest of the Vietnam War; The Black Panther Vol. 17 no.1 (1977) reports his '\''Appeal to African Heads of State'\'' to the OAU condemning U.S. repression of the Black liberation movement. [Source: The Black Panther (Black Panther Party newspaper), v17 no.1; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "GA", "affiliation": ["Black liberation movement"]}'

# Lucious Amerson (AL)
php artisan prisoner:add '{"name": "Lucious Amerson", "first_name": "Lucious", "last_name": "Amerson", "description": "The first Black sheriff in Alabama since Reconstruction (Macon County/Tuskegee), indicted on charges The Black Panther Vol. 17 no.9 (1977) calls a trumped-up framing of a Black elected official. [Source: The Black Panther (Black Panther Party newspaper), v17 no.9; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "AL"}'

# Marshall Conway (MD)
php artisan prisoner:add '{"name": "Marshall Conway", "first_name": "Marshall", "last_name": "Conway", "description": "Baltimore Black Panther Party leader and member of the Maryland Penitentiary Intercommunal Survival Collective; The Black Panther Vol. 17 no.17 (1977) reports he was among five inmates scapegoated and severely beaten by guards after a 1973 prison incident. Known as Marshall '\''Eddie'\'' Conway, he was imprisoned for decades before his release in 2014. [Source: The Black Panther (Black Panther Party newspaper), v17 no.17; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "MD", "affiliation": ["Black Panther Party"]}'

# Marty Dixon ()
php artisan prisoner:add '{"name": "Marty Dixon", "first_name": "Marty", "last_name": "Dixon", "description": "Black U.S. Army veteran court-martialed as a '\''ring leader'\'' of a July 1971 uprising of Black GIs against racist conditions at a base in South Korea; sentenced to four years hard labor at Fort Leavenworth and a dishonorable discharge. The Black Panther Vol. 17 no.19 (1977) reports his fight to upgrade the discharge. [Source: The Black Panther (Black Panther Party newspaper), v17 no.19; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]"}'

# Michael Cetawayo Tabor (NY)
php artisan prisoner:add '{"name": "Michael Cetawayo Tabor", "first_name": "Michael", "last_name": "Tabor", "description": "Black Panther Party member and one of the '\''Panther 21'\'' (New York 21), arrested in 1969 on charges of conspiring to bomb New York sites; The Black Panther Vol. 17 no.21 (1977) recounts that the defendants spent months jailed on $100,000 bail before the trial defendants were acquitted of all counts in 1971. [Source: The Black Panther (Black Panther Party newspaper), v17 no.21; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NY", "affiliation": ["Black Panther Party"]}'

# Molly Dougherty (CA)
php artisan prisoner:add '{"name": "Molly Dougherty", "first_name": "Molly", "last_name": "Dougherty", "description": "Black Panther Party supporter jailed for contempt (the maximum penalty) after refusing to testify in what she called the frame-up of Huey P. Newton. The Black Panther Vol. 17 no.20,21,23 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.20,21,23; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black Panther Party"]}'

# Morton Sobell (NY)
php artisan prisoner:add '{"name": "Morton Sobell", "first_name": "Morton", "last_name": "Sobell", "description": "Co-defendant convicted in the Rosenberg espionage case and imprisoned for nearly 19 years, in a prosecution The Black Panther Vol. 17 no.1 (1977) ties to the 1950s anticommunist witch-hunt. [Source: The Black Panther (Black Panther Party newspaper), v17 no.1; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NY"}'

# Ola Mae Davis (WI)
php artisan prisoner:add '{"name": "Ola Mae Davis", "first_name": "Ola", "last_name": "Davis", "description": "Elderly, blind, diabetic Black woman imprisoned in Wisconsin on perjury and welfare-fraud charges The Black Panther Vol. 17 no.15 (1977) calls a frame-up in retaliation for her testimony that she saw a white Milwaukee policeman shoot a 16-year-old Black youth (Jerry Brookshire) in the back in 1974; her home was firebombed twice. [Source: The Black Panther (Black Panther Party newspaper), v17 no.15; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "WI"}'

# Oscar Collazo (PR)
php artisan prisoner:add '{"name": "Oscar Collazo", "first_name": "Oscar", "last_name": "Collazo", "description": "Puerto Rican nationalist imprisoned for the 1950 armed attempt on President Truman'\''s life; The Black Panther Vol. 17 no.13 (1977) reports he refused to personally request a pardon. Released in 1979. [Source: The Black Panther (Black Panther Party newspaper), v17 no.13; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "PR", "affiliation": ["Puerto Rican independence movement"]}'

# Otis Johnson (TX)
php artisan prisoner:add '{"name": "Otis Johnson", "first_name": "Otis", "last_name": "Johnson", "description": "Prisoner sentenced to 17 years for allegedly stealing $17 and a TV set in Texas, listed by Amnesty International among U.S. prisoners jailed for their beliefs or political-group involvement, per The Black Panther Vol. 17 no.21 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.21; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "TX"}'

# Rafael Cancel Miranda (PR)
php artisan prisoner:add '{"name": "Rafael Cancel Miranda", "first_name": "Rafael", "last_name": "Miranda", "description": "Puerto Rican nationalist imprisoned for the 1954 armed attack on the U.S. House of Representatives; The Black Panther Vol. 17 no.13 (1977) reports he refused to request a pardon as a matter of principle. Released in 1979. [Source: The Black Panther (Black Panther Party newspaper), v17 no.13; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "PR", "affiliation": ["Puerto Rican independence movement"]}'

# Richard Lake (AL)
php artisan prisoner:add '{"name": "Richard Lake", "first_name": "Richard", "last_name": "Lake", "description": "Founder/leader of the Atmore-Holman prison group Inmates For Action (known as '\''Mafundi'\''), held in solitary confinement over 62 days for his political beliefs and, per The Black Panther Vol. 17 no.25 (1977), targeted with frame-up marijuana and gun charges. [Source: The Black Panther (Black Panther Party newspaper), v17 no.25; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "AL", "affiliation": ["Inmates For Action"]}'

# Ricky McGivery (CA)
php artisan prisoner:add '{"name": "Ricky McGivery", "first_name": "Ricky", "last_name": "McGivery", "description": "Black Marine, one of the '\''Camp Pendleton 14'\'' charged with assault and conspiracy over an alleged Nov. 13, 1976 confrontation with white Marines believed to be holding a KKK meeting; The Black Panther Vol. 17 no.1 (1977) reports defendants faced up to 21–72 years while no Klan members were charged. [Source: The Black Panther (Black Panther Party newspaper), v17 no.1; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Camp Pendleton 14"]}'

# Robert F. Williams (NC)
php artisan prisoner:add '{"name": "Robert F. Williams", "first_name": "Robert", "last_name": "Williams", "description": "Former Monroe, NC NAACP president and advocate of armed Black self-defense (author of '\''Negroes with Guns'\'') who spent eight years in political exile in Cuba, China and elsewhere to escape a kidnapping charge the movement considered a frame-up. The Black Panther Vol. 17 no.14 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.14; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NC", "affiliation": ["Black liberation movement"]}'

# Robert Kendrick (CA)
php artisan prisoner:add '{"name": "Robert Kendrick", "first_name": "Robert", "last_name": "Kendrick", "description": "Black Panther Party member ('\''Sharief'\'') beaten and arrested by LAPD in 1977 on charges The Black Panther Vol. 17 no.24 (1977) calls false, including assault on a peace officer and inciting a riot. [Source: The Black Panther (Black Panther Party newspaper), v17 no.24; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black Panther Party"]}'

# Robert Wesley Wells (CA)
php artisan prisoner:add '{"name": "Robert Wesley Wells", "first_name": "Robert", "last_name": "Wells", "description": "Longtime Black California prison activist and client of attorney Charles Garry, cited in The Black Panther Vol. 17 no.17 (1977) among the era'\''s political prisoners. [Source: The Black Panther (Black Panther Party newspaper), v17 no.17; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Prison movement"]}'

# Ron Kovic (CA)
php artisan prisoner:add '{"name": "Ron Kovic", "first_name": "Ron", "last_name": "Kovic", "description": "Paralyzed Vietnam veteran, antiwar activist and author of '\''Born on the Fourth of July'\''; The Black Panther Vol. 17 no.7 (1977) reports he was dumped from his wheelchair and arrested by plainclothes LAPD officers at a West Coast antiwar protest, having earlier disrupted the 1972 Republican National Convention. [Source: The Black Panther (Black Panther Party newspaper), v17 no.7; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Vietnam Veterans Against the War"]}'

# Roosevelt Watson (GA)
php artisan prisoner:add '{"name": "Roosevelt Watson", "first_name": "Roosevelt", "last_name": "Watson", "description": "One of the '\''Dawson Five,'\'' young Black men in Terrell County, Georgia charged with the 1976 murder of a white ranch foreman and facing the death penalty; The Black Panther Vol. 17 no.7 (1977) reports Watson said he confessed only after officers threatened to shoot, castrate and electrocute him. Defense attorney Millard Farmer; charges were later dropped. [Source: The Black Panther (Black Panther Party newspaper), v17 no.7; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "GA", "affiliation": ["Dawson Five"]}'

# Rubin Carter (NJ)
php artisan prisoner:add '{"name": "Rubin Carter", "first_name": "Rubin", "last_name": "Carter", "description": "Former top-ranked middleweight boxer ('\''Hurricane'\'') convicted, with John Artis, of the 1966 triple murder at the Lafayette Bar and Grill in Paterson, NJ — a case widely condemned as a racist frame-up. The Black Panther Vol. 17 no.4 (1977) covers his post-retrial appeal; his conviction was overturned in 1985. [Source: The Black Panther (Black Panther Party newspaper), v17 no.4; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NJ"}'

# Ruchell Magee (CA)
php artisan prisoner:add '{"name": "Ruchell Magee", "first_name": "Ruchell", "last_name": "Magee", "description": "Longtime imprisoned Black revolutionary, one of the last survivors of the Aug. 7, 1970 Marin County Courthouse rebellion led by 17-year-old Jonathan Jackson. The Black Panther Vol. 17 no.9 (1977) recounts the courtroom events; Magee remained imprisoned for decades as a political prisoner. [Source: The Black Panther (Black Panther Party newspaper), v17 no.9; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Prison movement"]}'

# Shirley Herlth (NC)
php artisan prisoner:add '{"name": "Shirley Herlth", "first_name": "Shirley", "last_name": "Herlth", "description": "Black prisoner at the North Carolina Women'\''s Prison charged with assault after defending a fellow Black prisoner against a racist attack by white inmates (who were not charged); The Black Panther Vol. 17 no.13 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.13; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NC"}'

# Sid Welsh (AZ)
php artisan prisoner:add '{"name": "Sid Welsh", "first_name": "Sid", "last_name": "Welsh", "description": "Longtime American Indian Movement organizer convicted by an all-white jury in Indio, California of illegal possession of an explosive device — a case The Black Panther Vol. 17 no.3 (1977) describes as an evidence-free FBI frame-up over a device found at the Parker, Arizona BIA building; he was granted a new trial after two months in jail. [Source: The Black Panther (Black Panther Party newspaper), v17 no.3; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "AZ", "affiliation": ["American Indian Movement"]}'

# T.J. Reddy (NC)
php artisan prisoner:add '{"name": "T.J. Reddy", "first_name": "T.J.", "last_name": "Reddy", "description": "North Carolina Black activist (one of the '\''Charlotte Three'\'') sentenced to 20 years for a 1968 arson, listed by Amnesty International and reported in The Black Panther Vol. 17 no.21 (1977) as a political prisoner. [Source: The Black Panther (Black Panther Party newspaper), v17 no.21; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NC", "affiliation": ["Charlotte Three"]}'

# Tenola Gamble (CA)
php artisan prisoner:add '{"name": "Tenola Gamble", "first_name": "Tenola", "last_name": "Gamble", "description": "Young Black Oakland man convicted of a 1977 burglary despite his half-brother'\''s confession to the crime and alibi witnesses; The Black Panther Vol. 17 no.19 (1977) reports his appeal after a judge denied a new trial. [Source: The Black Panther (Black Panther Party newspaper), v17 no.19; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA"}'

# Thomas Wansley (VA)
php artisan prisoner:add '{"name": "Thomas Wansley", "first_name": "Thomas", "last_name": "Wansley", "description": "Black activist whose rape convictions as a teenager in early-1960s Lynchburg, Virginia (including a death sentence, later overturned) inspired a national '\''Free Thomas Wansley'\'' movement; The Black Panther Vol. 17 no.18 (1977) reports fresh burglary-tool charges against him were dismissed for lack of evidence. Defended by William Kunstler. [Source: The Black Panther (Black Panther Party newspaper), v17 no.18; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "VA", "affiliation": ["Black liberation movement"]}'

# Wendy Yoshimura (CA)
php artisan prisoner:add '{"name": "Wendy Yoshimura", "first_name": "Wendy", "last_name": "Yoshimura", "description": "Japanese-American radical activist convicted in connection with her association with Patricia Hearst during 1975; The Black Panther Vol. 17 no.28 (1977) names her among imprisoned activists. [Source: The Black Panther (Black Panther Party newspaper), v17 no.28; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA"}'

# William Christmas (CA)
php artisan prisoner:add '{"name": "William Christmas", "first_name": "William", "last_name": "Christmas", "description": "San Quentin prisoner present in the Marin County courtroom in support of James McClain on Aug. 7, 1970; killed in the shootout during Jonathan Jackson'\''s attempted courthouse action. The Black Panther Vol. 17 no.9 (1977). [Source: The Black Panther (Black Panther Party newspaper), v17 no.9; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Prison movement"]}'

# ===== GROUP B -- INTERNATIONAL, commented out (3) =====
# NPPC is a U.S. coalition; commented for review. Vol. 17 also documented many more international
# cases (see dossier): the mass South African detentions/bannings, Zimbabwe (Rhodesia) nationalists
# executed under the Law and Order Act, Namibia (SWAPO), Philippine NPA figures, Chile, and the
# West German Baader-Meinhof prisoners.

# Beyers Naude
# php artisan prisoner:add '{"name": "Beyers Naude", "first_name": "Beyers", "last_name": "Naude", "description": "Afrikaner anti-apartheid minister and founder of the Christian Institute, banned for five years in October 1977 and barred from meeting more than one person or being quoted. The Black Panther Vol. 17 no.27 (1977). INTERNATIONAL (South Africa). [Source: The Black Panther (Black Panther Party newspaper), v17 no.27; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Christian Institute"]}'

# Donald Woods
# php artisan prisoner:add '{"name": "Donald Woods", "first_name": "Donald", "last_name": "Woods", "description": "White South African newspaper editor (East London Daily Dispatch) and close friend of Steve Biko, banned by the apartheid regime in the October 1977 crackdown. The Black Panther Vol. 17 no.20,27,28 (1977). INTERNATIONAL (South Africa). [Source: The Black Panther (Black Panther Party newspaper), v17 no.20,27,28; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]"}'

# Zeph Mothopeng
# php artisan prisoner:add '{"name": "Zeph Mothopeng", "first_name": "Zeph", "last_name": "Mothopeng", "description": "Pan Africanist Congress leader held incommunicado without trial under South Africa'\''s Terrorism Act after the 1976 Soweto uprising, a veteran of Robben Island. The Black Panther Vol. 17 no.17 (1977). INTERNATIONAL (South Africa). [Source: The Black Panther (Black Panther Party newspaper), v17 no.17; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Pan Africanist Congress"]}'

