#!/usr/bin/env bash
# Political-prisoner cases extracted from The Black Panther newspaper, Vol. 19 (1979),
# read page-by-page from the marxists.org archive. RUN ON THE SERVER after review.
# Idempotent: prisoner:add de-dupes by name (many of these iconic figures may already
# exist and will simply be skipped). Continue-on-error so duplicates don't stop the batch:
set +e

# ===== GROUP A -- U.S. political-prisoner cases (46) =====

# Andres Figueroa Cordero (PR)
php artisan prisoner:add '{"name": "Andres Figueroa Cordero", "first_name": "Andres", "last_name": "Cordero", "description": "Puerto Rican nationalist imprisoned for the 1954 armed protest inside the U.S. House of Representatives; he served 23 years before release in 1977 due to cancer and died in 1979. The Black Panther Vol. 19 no.3 (1979) obituary; protesters demanded release of the remaining imprisoned nationalists. [Source: The Black Panther (Black Panther Party newspaper), v19 no.3; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "PR", "affiliation": ["Puerto Rican independence movement"]}'

# Bobby Seale (CT)
php artisan prisoner:add '{"name": "Bobby Seale", "first_name": "Bobby", "last_name": "Seale", "description": "Co-founder and Chairman of the Black Panther Party. The Black Panther Vol. 19 no.8 (1979) recounts the 1971 New Haven prosecution tying him and Ericka Huggins to the death of BPP member Alex Rackley; both were acquitted after the trial ended in a hung jury, amid FBI COINTELPRO dirty tricks. [Source: The Black Panther (Black Panther Party newspaper), v19 no.8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CT", "affiliation": ["Black Panther Party"]}'

# Carl Bass (AL)
php artisan prisoner:add '{"name": "Carl Bass", "first_name": "Carl", "last_name": "Bass", "description": "Black Alabama prisoner serving a life term who lost both legs to gangrene after being denied medical care in custody. The Black Panther Vol. 19 no.10,11 (1979) reports he escaped Draper prison, was recaptured in 1979, fought extradition from Oregon with ACLU help, and sued Alabama corrections officials. [Source: The Black Panther (Black Panther Party newspaper), v19 no.10,11; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "AL", "affiliation": ["Prison movement"]}'

# Case Johnson (GA)
php artisan prisoner:add '{"name": "Case Johnson", "first_name": "Case", "last_name": "Johnson", "description": "One of the six '\''Reidsville Brothers'\'' facing the death penalty over the July 23, 1978 Reidsville prison rebellion in Georgia. The Black Panther Vol. 19 no.8 (1979). [Source: The Black Panther (Black Panther Party newspaper), v19 no.8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "GA", "affiliation": ["Reidsville Brothers"]}'

# Charles 2X Beasley (AL)
php artisan prisoner:add '{"name": "Charles 2X Beasley", "first_name": "Charles", "last_name": "Beasley", "description": "One of the Atmore-Holman Brothers (IFA), sentenced to 15 years in the framed prosecution over the 1974 Atmore-Holman prison rebellion. The Black Panther Vol. 19 no.10,11 (1979). [Source: The Black Panther (Black Panther Party newspaper), v19 no.10,11; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "AL", "affiliation": ["Inmates For Action"]}'

# Christopher McIntosh (GA)
php artisan prisoner:add '{"name": "Christopher McIntosh", "first_name": "Christopher", "last_name": "McIntosh", "description": "One of four Black protesters arrested at the Harris Neck, Georgia, land-reclamation protest against land seized from Black families in 1942; released on recognizance. The Black Panther Vol. 19 no.7 (1979). [Source: The Black Panther (Black Panther Party newspaper), v19 no.7; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "GA"}'

# David Johnson (CA)
php artisan prisoner:add '{"name": "David Johnson", "first_name": "David", "last_name": "Johnson", "description": "One of the San Quentin 6, convicted of assault on guards in the Aug. 21, 1971 San Quentin events. The Black Panther Vol. 19 no.10-11 (1979) reports his appeal challenging an illegal photographic identification. [Source: The Black Panther (Black Panther Party newspaper), v19 no.10,11; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["San Quentin 6"]}'

# Edgar Timmons, Jr. (GA)
php artisan prisoner:add '{"name": "Edgar Timmons, Jr.", "first_name": "Edgar", "last_name": "Jr.", "description": "One of four Black protesters arrested by U.S. marshals for occupying Harris Neck, Georgia, land taken from Black families by the federal government in 1942; released on recognizance. The Black Panther Vol. 19 no.7 (1979). [Source: The Black Panther (Black Panther Party newspaper), v19 no.7; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "GA"}'

# Elmer Geronimo Pratt (CA)
php artisan prisoner:add '{"name": "Elmer Geronimo Pratt", "first_name": "Elmer", "last_name": "Pratt", "description": "Former leader of the Southern California chapter of the Black Panther Party, a COINTELPRO target imprisoned on a framed murder charge; referenced in The Black Panther Vol. 19 no.10 (1979). His conviction was vacated in 1997. [Source: The Black Panther (Black Panther Party newspaper), v19 no.10; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black Panther Party"]}'

# Ericka Huggins (CT)
php artisan prisoner:add '{"name": "Ericka Huggins", "first_name": "Ericka", "last_name": "Huggins", "description": "Black Panther Party member arrested with 13 other New Haven Panthers ('\''New Haven 14'\'') on murder/kidnapping charges in the Alex Rackley case, described by the Party as trumped-up and driven by an FBI informant. Tried with Bobby Seale in 1971 and acquitted. The Black Panther Vol. 19 no.7-8 (1979). [Source: The Black Panther (Black Panther Party newspaper), v19 no.7,8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CT", "affiliation": ["Black Panther Party"]}'

# Ernest Graham (CA)
php artisan prisoner:add '{"name": "Ernest Graham", "first_name": "Ernest", "last_name": "Graham", "description": "Black prison activist tried with Eugene Allen for the 1973 stabbing death of a Deuel Vocational Institute guard. The Black Panther Vol. 19 no.2,6,10,11 (1979): their death sentences were overturned by the California Supreme Court over racially biased jury selection; a third trial followed. [Source: The Black Panther (Black Panther Party newspaper), v19 no.2,6,10,11; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Prison movement"]}'

# Eugene Allen (CA)
php artisan prisoner:add '{"name": "Eugene Allen", "first_name": "Eugene", "last_name": "Allen", "description": "Black prison activist tried with Ernest Graham for the 1973 stabbing death of a Deuel Vocational Institute guard. The Black Panther Vol. 19 no.2,6,10,11 (1979) reports the California Supreme Court overturned their all-white-jury convictions (People v. Wheeler) for exclusion of Black jurors; they faced a third trial. [Source: The Black Panther (Black Panther Party newspaper), v19 no.2,6,10,11; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Prison movement"]}'

# Fleeta Drumgo (CA)
php artisan prisoner:add '{"name": "Fleeta Drumgo", "first_name": "Fleeta", "last_name": "Drumgo", "description": "One of the San Quentin 6 (and a Soledad Brother), acquitted of charges from the Aug. 21, 1971 San Quentin events. The Black Panther Vol. 19 no.10 (1979). [Source: The Black Panther (Black Panther Party newspaper), v19 no.10; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["San Quentin 6"]}'

# Forest Jordan (GA)
php artisan prisoner:add '{"name": "Forest Jordan", "first_name": "Forest", "last_name": "Jordan", "description": "One of the six '\''Reidsville Brothers'\'' facing the death penalty over the July 23, 1978 Reidsville prison rebellion in Georgia. The Black Panther Vol. 19 no.8 (1979). [Source: The Black Panther (Black Panther Party newspaper), v19 no.8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "GA", "affiliation": ["Reidsville Brothers"]}'

# Frank X. Moore (AL)
php artisan prisoner:add '{"name": "Frank X. Moore", "first_name": "Frank", "last_name": "Moore", "description": "Atmore prisoner indicted in the 1974 Atmore-Holman rebellion who was '\''found hanging'\'' in the Escambia County jail before his 1975 trial; a fellow prisoner swore officials had planned his death. Deceased. The Black Panther Vol. 19 no.3 (1979). [Source: The Black Panther (Black Panther Party newspaper), v19 no.3; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "AL", "affiliation": ["Inmates For Action"]}'

# George Chagina Dobbins (AL)
php artisan prisoner:add '{"name": "George Chagina Dobbins", "first_name": "George", "last_name": "Dobbins", "description": "Black inmate activist and IFA member killed in custody amid the repression following the Jan. 18, 1974 Atmore-Holman prison rebellion; an affidavit alleged wardens participated in his killing. Deceased. The Black Panther Vol. 19 no.3,11 (1979). [Source: The Black Panther (Black Panther Party newspaper), v19 no.3,11; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "AL", "affiliation": ["Inmates For Action"]}'

# George Jackson (CA)
php artisan prisoner:add '{"name": "George Jackson", "first_name": "George", "last_name": "Jackson", "description": "Black Panther Party Field Marshal and leader of the prison movement, author of '\''Soledad Brother.'\'' The Black Panther Vol. 19 no.10 (1979) states he was set up and killed by San Quentin guards on Aug. 21, 1971; his death was the basis of the San Quentin 6 prosecution. [Source: The Black Panther (Black Panther Party newspaper), v19 no.10; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black Panther Party"]}'

# Gregory Felix (CA)
php artisan prisoner:add '{"name": "Gregory Felix", "first_name": "Gregory", "last_name": "Felix", "description": "Arrested by Oakland Police vice-squad officers in the NAACP office in connection with the Melvin Black police-killing case; The Black Panther Vol. 19 no.8 (1979). [Source: The Black Panther (Black Panther Party newspaper), v19 no.8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA"}'

# Grover McCorvey (AL)
php artisan prisoner:add '{"name": "Grover McCorvey", "first_name": "Grover", "last_name": "McCorvey", "description": "One of the Atmore-Holman Brothers (IFA), convicted in the framed prosecution over the Jan. 18, 1974 Atmore-Holman prison rebellion in Alabama. The Black Panther Vol. 19 no.3,10,11 (1979); name also rendered '\''McGorvey'\'' and '\''Sitting Bull.'\'' [Source: The Black Panther (Black Panther Party newspaper), v19 no.3,10,11; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "AL", "affiliation": ["Inmates For Action"]}'

# Hector Marroquin (TX)
php artisan prisoner:add '{"name": "Hector Marroquin", "first_name": "Hector", "last_name": "Marroquin", "description": "Former Mexican student activist and Socialist Workers Party member fighting deportation after the U.S. denied him political asylum; The Black Panther Vol. 19 no.6 (1979) reports he was framed in Mexico for a 1974 murder following the 1968 Tlatelolco massacre, with documents alleging illegal FBI involvement. [Source: The Black Panther (Black Panther Party newspaper), v19 no.6; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "TX", "affiliation": ["Socialist Workers Party"]}'

# Hercules Anderson (GA)
php artisan prisoner:add '{"name": "Hercules Anderson", "first_name": "Hercules", "last_name": "Anderson", "description": "One of four Black protesters arrested at the Harris Neck, Georgia, land-reclamation protest; released on recognizance. The Black Panther Vol. 19 no.7 (1979). [Source: The Black Panther (Black Panther Party newspaper), v19 no.7; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "GA"}'

# Huey P. Newton (CA)
php artisan prisoner:add '{"name": "Huey P. Newton", "first_name": "Huey", "last_name": "Newton", "description": "Co-founder and leader of the Black Panther Party. As reported across The Black Panther Vol. 19 (1979), he was prosecuted in Oakland for the 1974 murder of Kathleen Smith, a case the Party described as a political frame-up built on paid testimony. Two trials ended in mistrials with juries voting overwhelmingly to acquit, and all charges were dismissed on Sept. 27, 1979. [Source: The Black Panther (Black Panther Party newspaper), v19 no.2,3,6,7,8,9,10,11; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black Panther Party"]}'

# Hugo Pinell (CA)
php artisan prisoner:add '{"name": "Hugo Pinell", "first_name": "Hugo", "last_name": "Pinell", "description": "One of the San Quentin 6, charged in the Aug. 21, 1971 San Quentin events surrounding George Jackson'\''s death; convicted of assault on guards. The Black Panther Vol. 19 no.10-11 (1979) reports his appeal on grounds of juror bias. [Source: The Black Panther (Black Panther Party newspaper), v19 no.10,11; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["San Quentin 6"]}'

# James Collins (GA)
php artisan prisoner:add '{"name": "James Collins", "first_name": "James", "last_name": "Collins", "description": "One of the six '\''Reidsville Brothers,'\'' Black Georgia prisoners charged with killing a guard and two inmates during the July 23, 1978 Reidsville prison rebellion and facing the death penalty; The Black Panther Vol. 19 no.8 (1979) reports he was stabbed by white inmates amid alleged official collusion. [Source: The Black Panther (Black Panther Party newspaper), v19 no.8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "GA", "affiliation": ["Reidsville Brothers"]}'

# Jerome Singleton (NY)
php artisan prisoner:add '{"name": "Jerome Singleton", "first_name": "Jerome", "last_name": "Singleton", "description": "A 30-year-old Black student in New York City accused of slashing an undercover '\''decoy'\'' policeman; The Black Panther Vol. 19 no.6 (1979) reports Judge Bruce Wright released him without bail given his lack of record and community ties, sparking a political controversy. [Source: The Black Panther (Black Panther Party newspaper), v19 no.6; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NY"}'

# Jesse Tuba Clancy (AL)
php artisan prisoner:add '{"name": "Jesse Tuba Clancy", "first_name": "Jesse", "last_name": "Clancy", "description": "One of the Atmore-Holman Brothers (IFA), convicted in the framed prosecution over the 1974 Atmore-Holman prison rebellion. The Black Panther Vol. 19 no.10,11 (1979); name also spelled '\''Claney.'\'' [Source: The Black Panther (Black Panther Party newspaper), v19 no.10,11; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "AL", "affiliation": ["Inmates For Action"]}'

# Jessie Whitaker (GA)
php artisan prisoner:add '{"name": "Jessie Whitaker", "first_name": "Jessie", "last_name": "Whitaker", "description": "One of the six '\''Reidsville Brothers'\'' facing the death penalty over the July 23, 1978 Reidsville prison rebellion; the first of the six brought to trial. The Black Panther Vol. 19 no.8 (1979). [Source: The Black Panther (Black Panther Party newspaper), v19 no.8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "GA", "affiliation": ["Reidsville Brothers"]}'

# Johnny Imani Harris (AL)
php artisan prisoner:add '{"name": "Johnny Imani Harris", "first_name": "Johnny", "last_name": "Harris", "description": "Leader of the Atmore-Holman Brothers and co-founder of Inmates For Action (IFA). The Black Panther Vol. 19 no.3,10,11 (1979) reports he was sentenced to death on a framed charge of killing a guard during the Jan. 18, 1974 Atmore-Holman prison rebellion, and that a new motion cited an eyewitness placing him elsewhere. [Source: The Black Panther (Black Panther Party newspaper), v19 no.3,10,11; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "AL", "affiliation": ["Inmates For Action"]}'

# Johnny Spain (CA)
php artisan prisoner:add '{"name": "Johnny Spain", "first_name": "Johnny", "last_name": "Spain", "description": "Black Panther Party member and one of the San Quentin 6, the only one convicted of murder in the Aug. 21, 1971 events tied to George Jackson'\''s death. The Black Panther Vol. 19 no.10-11 (1979) reports his appeal (juror bias, prejudicial shackling). His murder conviction was later overturned in 1988. [Source: The Black Panther (Black Panther Party newspaper), v19 no.10,11; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black Panther Party"]}'

# Khalil Islam (NY)
php artisan prisoner:add '{"name": "Khalil Islam", "first_name": "Khalil", "last_name": "Islam", "description": "Convicted (as Thomas 15X Johnson) in the 1965 assassination of Malcolm X. The Black Panther Vol. 19 no.7,9 (1979) reports his petition, with Muhammad Abdul Aziz, to the Congressional Black Caucus asserting his innocence. He was exonerated (posthumously) in 2021. [Source: The Black Panther (Black Panther Party newspaper), v19 no.7,9; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NY", "affiliation": ["Nation of Islam"]}'

# Leonard Peltier (SD)
php artisan prisoner:add '{"name": "Leonard Peltier", "first_name": "Leonard", "last_name": "Peltier", "description": "American Indian Movement activist convicted in the deaths of two FBI agents at Pine Ridge in 1975, a case supporters call a frame-up. The Black Panther Vol. 19 no.3 (1979) reports a Supreme Court vigil by his defense committee. His sentence was commuted in 2025. [Source: The Black Panther (Black Panther Party newspaper), v19 no.3; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "SD", "affiliation": ["American Indian Movement"]}'

# Lincoln Heard (AL)
php artisan prisoner:add '{"name": "Lincoln Heard", "first_name": "Lincoln", "last_name": "Heard", "description": "Named among the Atmore prisoners falsely indicted alongside Johnny Imani Harris in the killing of a guard during the 1974 Atmore-Holman prison rebellion. The Black Panther Vol. 19 no.3 (1979). [Source: The Black Panther (Black Panther Party newspaper), v19 no.3; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "AL", "affiliation": ["Inmates For Action"]}'

# Lonnie McLucas (CT)
php artisan prisoner:add '{"name": "Lonnie McLucas", "first_name": "Lonnie", "last_name": "McLucas", "description": "Black Panther Party member prosecuted in the New Haven (Alex Rackley) case in 1970; The Black Panther Vol. 19 no.8 (1979) reports the FBI printed fake BPP flyers ('\''Panther Trial News'\'') to prejudice his trial. [Source: The Black Panther (Black Panther Party newspaper), v19 no.8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CT", "affiliation": ["Black Panther Party"]}'

# Luis Talamantez (CA)
php artisan prisoner:add '{"name": "Luis Talamantez", "first_name": "Luis", "last_name": "Talamantez", "description": "One of the San Quentin 6, acquitted of charges from the Aug. 21, 1971 San Quentin events. The Black Panther Vol. 19 no.10 (1979) spells the name '\''Talamentez'\''. [Source: The Black Panther (Black Panther Party newspaper), v19 no.10; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["San Quentin 6"]}'

# Moses Evans (GA)
php artisan prisoner:add '{"name": "Moses Evans", "first_name": "Moses", "last_name": "Evans", "description": "One of the six '\''Reidsville Brothers,'\'' Black Georgia prisoners selectively prosecuted and facing the death penalty over the July 23, 1978 Reidsville prison rebellion. The Black Panther Vol. 19 no.8 (1979). [Source: The Black Panther (Black Panther Party newspaper), v19 no.8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "GA", "affiliation": ["Reidsville Brothers"]}'

# Muhammad Abdul Aziz (NY)
php artisan prisoner:add '{"name": "Muhammad Abdul Aziz", "first_name": "Muhammad", "last_name": "Aziz", "description": "Convicted (as Norman 3X Butler) in the 1965 assassination of Malcolm X. The Black Panther Vol. 19 no.7,9 (1979) reports he and Khalil Islam petitioned the Congressional Black Caucus for an investigation, citing the confessed killer'\''s statement that they were innocent. He was exonerated in 2021. [Source: The Black Panther (Black Panther Party newspaper), v19 no.7,9; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NY", "affiliation": ["Nation of Islam"]}'

# Oscar Gamba Johnson (AL)
php artisan prisoner:add '{"name": "Oscar Gamba Johnson", "first_name": "Oscar", "last_name": "Johnson", "description": "One of the Atmore-Holman Brothers (IFA), sentenced to 50 years in the framed prosecution over the 1974 Atmore-Holman prison rebellion. The Black Panther Vol. 19 no.3,10,11 (1979). [Source: The Black Panther (Black Panther Party newspaper), v19 no.3,10,11; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "AL", "affiliation": ["Inmates For Action"]}'

# Robert Earl May, Jr. (MS)
php artisan prisoner:add '{"name": "Robert Earl May, Jr.", "first_name": "Robert", "last_name": "Jr.", "description": "A 14-year-old Black youth sentenced to 48 years (four consecutive 12-year terms) at Parchman Prison in Mississippi after he and three friends were, per The Black Panther Vol. 19 no.2 (1979), forced to plead guilty to armed robbery, with no parole eligibility under state law. [Source: The Black Panther (Black Panther Party newspaper), v19 no.2; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "MS", "affiliation": ["Prison movement"]}'

# Stephen Bingham (CA)
php artisan prisoner:add '{"name": "Stephen Bingham", "first_name": "Stephen", "last_name": "Bingham", "description": "Attorney for George Jackson. The Black Panther Vol. 19 no.10 (1979) describes as absurd the prosecution'\''s '\''gun-in-a-wig'\'' theory that he smuggled a pistol to Jackson on Aug. 21, 1971. He surfaced years later and was acquitted in 1986. [Source: The Black Panther (Black Panther Party newspaper), v19 no.10; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Prison movement"]}'

# Ted Clark (GA)
php artisan prisoner:add '{"name": "Ted Clark", "first_name": "Ted", "last_name": "Clark", "description": "Atlanta civil-rights activist (Rev.), one of four Black protesters arrested at the Harris Neck, Georgia, land-reclamation protest; released on recognizance. The Black Panther Vol. 19 no.7 (1979). [Source: The Black Panther (Black Panther Party newspaper), v19 no.7; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "GA"}'

# Terrence Johnson (MD)
php artisan prisoner:add '{"name": "Terrence Johnson", "first_name": "Terrence", "last_name": "Johnson", "description": "A 16-year-old Black youth in Prince George'\''s County, Maryland who, per The Black Panther Vol. 19 no.6,8 (1979), shot two police officers in self-defense after being beaten in custody; acquitted of first-degree murder but convicted of manslaughter and a handgun charge and sentenced to 25 years. [Source: The Black Panther (Black Panther Party newspaper), v19 no.6,8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "MD"}'

# Tommy Dotson (AL)
php artisan prisoner:add '{"name": "Tommy Dotson", "first_name": "Tommy", "last_name": "Dotson", "description": "One of the Atmore-Holman Brothers; The Black Panther Vol. 19 no.10,11 (1979) reports he faced electrocution on a murder charge the Party said he had no connection to, and that he was beaten by four guards in March 1974. [Source: The Black Panther (Black Panther Party newspaper), v19 no.10,11; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "AL", "affiliation": ["Inmates For Action"]}'

# Tommy Lee Hines (AL)
php artisan prisoner:add '{"name": "Tommy Lee Hines", "first_name": "Tommy", "last_name": "Hines", "description": "A mentally disabled Black man sentenced to 30 years for the alleged rape of a white woman in Decatur, Alabama, in a case widely condemned as a frame-up; The Black Panther Vol. 19 no.8 (1979) reports KKK members attacked demonstrators marching over his case. [Source: The Black Panther (Black Panther Party newspaper), v19 no.8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "AL"}'

# Warren E. Sumlin, Sr. (AR)
php artisan prisoner:add '{"name": "Warren E. Sumlin, Sr.", "first_name": "Warren", "last_name": "Sr.", "description": "Bay Area Black man on death row at Cummins prison in Arkansas for a first-degree murder he said he did not commit, calling himself a victim of a local political struggle. Convicted by a jury of 11 whites and one Black; won a stay of execution. The Black Panther Vol. 19 no.9 (1979). [Source: The Black Panther (Black Panther Party newspaper), v19 no.9; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "AR", "affiliation": ["Prison movement"]}'

# Willie Tate (CA)
php artisan prisoner:add '{"name": "Willie Tate", "first_name": "Willie", "last_name": "Tate", "description": "One of the San Quentin 6, acquitted of charges arising from the Aug. 21, 1971 San Quentin events. The Black Panther Vol. 19 no.10 (1979). [Source: The Black Panther (Black Panther Party newspaper), v19 no.10; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["San Quentin 6"]}'

# Yvonne Wanrow (WA)
php artisan prisoner:add '{"name": "Yvonne Wanrow", "first_name": "Yvonne", "last_name": "Wanrow", "description": "Colville Indian woman prosecuted for killing a child molester in defense of children; her landmark self-defense case saw a 1973 all-white-jury conviction overturned. The Black Panther Vol. 19 no.7 (1979) reports the murder charges were dismissed and she pleaded to reduced charges with probation. [Source: The Black Panther (Black Panther Party newspaper), v19 no.7; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "WA", "affiliation": ["Native American movement"]}'

# ===== GROUP B -- INTERNATIONAL (South Africa), commented out (4) =====
# NPPC is a U.S. (National) coalition; these anti-apartheid cases are left commented
# for you to include or skip. Un-comment to add.

# Azael Phiri (South Africa)
# php artisan prisoner:add '{"name": "Azael Phiri", "first_name": "Azael", "last_name": "Phiri", "description": "South African Black student leader arrested near Soweto in early 1979 with Ewan Maphana. The Black Panther Vol. 19 no.2 (1979). INTERNATIONAL. [Source: The Black Panther (Black Panther Party newspaper), v19 no.2; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Soweto students"]}'

# Ewan Maphana (South Africa)
# php artisan prisoner:add '{"name": "Ewan Maphana", "first_name": "Ewan", "last_name": "Maphana", "description": "President of the Soweto Students League, arrested by South African police near Soweto in early 1979. The Black Panther Vol. 19 no.2 (1979). INTERNATIONAL. [Source: The Black Panther (Black Panther Party newspaper), v19 no.2; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Soweto Students League"]}'

# Sechaba Daniel Montsitsi (South Africa)
# php artisan prisoner:add '{"name": "Sechaba Daniel Montsitsi", "first_name": "Sechaba", "last_name": "Montsitsi", "description": "Former chairman of the banned Soweto Student Representative Council, given eight years (four suspended) in South Africa'\''s first sedition trial in 30 years for leading the 1976 Soweto uprising; testified he was tortured. The Black Panther Vol. 19 no.2,7 (1979). INTERNATIONAL. [Source: The Black Panther (Black Panther Party newspaper), v19 no.2,7; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Soweto Student Representative Council"]}'

# Wilson Twala (South Africa)
# php artisan prisoner:add '{"name": "Wilson Twala", "first_name": "Wilson", "last_name": "Twala", "description": "South African student, 15 at the time of the 1976 Soweto uprising, among 11 students convicted of sedition. The Black Panther Vol. 19 no.7 (1979). INTERNATIONAL. [Source: The Black Panther (Black Panther Party newspaper), v19 no.7; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Soweto students"]}'

