#!/usr/bin/env bash
# Political-prisoner cases extracted from The Black Panther newspaper, Vol. 13 (Feb.-Sept. 1975),
# read page-by-page (all 30 issues, ~1,110 pages) from the marxists.org archive.
# RUN ON THE SERVER after review. Idempotent: prisoner:add de-dupes by name, so any of these
# figures already in the database (e.g. from the Vol. 14-19 imports) are simply skipped.
# Continue-on-error so duplicates/failures don't abort the batch:
set +e

# ===== GROUP A -- U.S. political-prisoner cases (41) =====

# Alberto Ortiz (CA)
php artisan prisoner:add '{"name": "Alberto Ortiz", "first_name": "Alberto", "last_name": "Ortiz", "description": "One of '\''Los Tres del Barrio,'\'' Chicano activists convicted in 1972 of assaulting an undercover federal narcotics agent they had confronted as a heroin pusher; The Black Panther Vol. 13 no.12 (1975) reports his appeal bail was secretly revoked and he was seized by an LAPD SWAT team and jailed under maximum security. [Source: The Black Panther (Black Panther Party newspaper), v13 no.12; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Chicano movement"]}'

# Alf Hill (KS)
php artisan prisoner:add '{"name": "Alf Hill", "first_name": "Alf", "last_name": "Hill", "description": "One of the '\''Leavenworth Brothers'\'' held in solitary and prosecuted over the July 1973 Leavenworth federal penitentiary uprising; The Black Panther Vol. 13 no.4 (1975). [Source: The Black Panther (Black Panther Party newspaper), v13 no.4; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "KS"}'

# Alfred Jasper (KS)
php artisan prisoner:add '{"name": "Alfred Jasper", "first_name": "Alfred", "last_name": "Jasper", "description": "One of the '\''Leavenworth Brothers'\'' held in solitary confinement since the 1973 Leavenworth federal penitentiary uprising; The Black Panther Vol. 13 no.4 (1975). [Source: The Black Panther (Black Panther Party newspaper), v13 no.4; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "aka": "Jazz", "state": "KS"}'

# Anthony Paradise (AL)
php artisan prisoner:add '{"name": "Anthony Paradise", "first_name": "Anthony", "last_name": "Paradise", "description": "One of the nine '\''Atmore-Holman Brothers'\'' (IFA), charged with first-degree murder of a Holman prison guard; The Black Panther Vol. 13 (1975) reports the charge was dropped by a directed not-guilty verdict after the state'\''s chief witness contradicted himself. [Source: The Black Panther (Black Panther Party newspaper), v13 no.1,3; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "aka": "Bectemba", "state": "AL", "affiliation": ["Inmates For Action"]}'

# B.J. Brooks (FL)
php artisan prisoner:add '{"name": "B.J. Brooks", "first_name": "B.J.", "last_name": "Brooks", "description": "Pensacola, Florida civil-rights leader convicted by an all-white jury of felony '\''extortion'\'' for leading a march protesting a sheriff'\''s deputy'\''s killing of a young Black man (Wendell Blackwell); The Black Panther Vol. 13 (1975) reports he and Rev. H.K. Matthews faced prison terms of up to 15 years in what it called reactionary repression of the Pensacola movement. [Source: The Black Panther (Black Panther Party newspaper), v13 no.20,21; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "aka": "Rev. B.J. Brooks", "state": "FL"}'

# Bobby Ray Hines (NC)
php artisan prisoner:add '{"name": "Bobby Ray Hines", "first_name": "Bobby", "last_name": "Hines", "description": "One of the '\''Tarboro 3,'\'' young Black North Carolina men originally sentenced to death for the rape of a white woman; The Black Panther Vol. 13 no.15 (1975) reports the state Supreme Court set aside the convictions and the three received suspended sentences ending their confinement. [Source: The Black Panther (Black Panther Party newspaper), v13 no.15; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NC"}'

# Cameron Bishop (CO)
php artisan prisoner:add '{"name": "Cameron Bishop", "first_name": "Cameron", "last_name": "Bishop", "description": "Anti-war radical and former FBI ten-most-wanted fugitive, arrested in March 1975 in Rhode Island and charged with the 1969 sabotage bombings of four defense/electrical towers in Colorado; The Black Panther Vol. 13 no.8 (1975) reports his wife called the trial political and said he could face over 100 years. [Source: The Black Panther (Black Panther Party newspaper), v13 no.8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CO"}'

# Charles Beasley (AL)
php artisan prisoner:add '{"name": "Charles Beasley", "first_name": "Charles", "last_name": "Beasley", "description": "One of the nine '\''Atmore-Holman Brothers'\'' (IFA), the last of the group set to be tried, facing assault and murder charges arising from the 1974 Alabama prison rebellions; The Black Panther Vol. 13 (1975). [Source: The Black Panther (Black Panther Party newspaper), v13 no.1,3,15; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "aka": "Mumina", "state": "AL", "affiliation": ["Inmates For Action"]}'

# Charles Joe Pernasilice (NY)
php artisan prisoner:add '{"name": "Charles Joe Pernasilice", "first_name": "Charles", "last_name": "Pernasilice", "description": "Native American '\''Attica Brother'\'' tried with John Hill (Dacajeweiah) for the death of guard William Quinn during the 1971 Attica rebellion; The Black Panther Vol. 13 (1975) reports the defense called it a state frame-up and that he was convicted of a lesser second-degree assault charge. [Source: The Black Panther (Black Panther Party newspaper), v13 no.3,4,6,7,8,13; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NY"}'

# Clayton Moore (CA)
php artisan prisoner:add '{"name": "Clayton Moore", "first_name": "Clayton", "last_name": "Moore", "description": "One of four Black people stopped on a freeway, handcuffed and made to stand trial for a Jan. 1975 San Jose jewelry-store robbery though searches of the individuals, car and home turned up no evidence and the identifying witness made seven mistaken identifications; The Black Panther Vol. 13 no.15 (1975) reports a defense committee formed to stop the '\''judicial railroading.'\'' [Source: The Black Panther (Black Panther Party newspaper), v13 no.15; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA"}'

# Clifton Wiggins (MD)
php artisan prisoner:add '{"name": "Clifton Wiggins", "first_name": "Clifton", "last_name": "Wiggins", "description": "One of the '\''Maryland Penitentiary Five,'\'' sentenced to five years of segregation and convicted of some charges over the guard-provoked Maryland Penitentiary disturbance; The Black Panther Vol. 13 no.3 (1975). [Source: The Black Panther (Black Panther Party newspaper), v13 no.3; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "MD"}'

# Dennise Broadnex (CA)
php artisan prisoner:add '{"name": "Dennise Broadnex", "first_name": "Dennise", "last_name": "Broadnex", "description": "One of four Black people framed, per The Black Panther Vol. 13 no.15 (1975), for the Jan. 1975 Morton'\''s Jewelry Store robbery in San Jose despite no incriminating evidence and a witness who made seven misidentifications. [Source: The Black Panther (Black Panther Party newspaper), v13 no.15; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA"}'

# Donald Taylor (CA)
php artisan prisoner:add '{"name": "Donald Taylor", "first_name": "Donald", "last_name": "Taylor", "description": "One of four Black people stopped on the freeway, handcuffed and made to stand trial for the Jan. 1975 Morton'\''s Jewelry Store robbery in San Jose with no evidence against them; The Black Panther Vol. 13 no.15 (1975). [Source: The Black Panther (Black Panther Party newspaper), v13 no.15; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA"}'

# Eddie Sanchez (KS)
php artisan prisoner:add '{"name": "Eddie Sanchez", "first_name": "Eddie", "last_name": "Sanchez", "description": "Chicano prisoner and activist who, The Black Panther Vol. 13 (1975) reports, survived involuntary Anectine drug experiments and a scheduled experimental lobotomy at Soledad and was later charged at the Marion and Leavenworth federal prisons with assaults the paper calls trumped-up, arising from his self-defense; the man he allegedly stabbed signed an affidavit that Sanchez was not the attacker. [Source: The Black Panther (Black Panther Party newspaper), v13 no.3,10,13; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "KS", "affiliation": ["Chicano movement"]}'

# Edward Ellis (AL)
php artisan prisoner:add '{"name": "Edward Ellis", "first_name": "Edward", "last_name": "Ellis", "description": "One of the nine Alabama '\''Atmore-Holman Brothers,'\'' Inmates For Action (IFA) prison organizers charged with murder after the 1974 Atmore-Holman rebellions; The Black Panther Vol. 13 (1975) reports the murder charges against him (from a March 1974 Holman incident) were dropped after the state'\''s evidence collapsed, as he had been handcuffed in his cell during the alleged incident. [Source: The Black Panther (Black Panther Party newspaper), v13 no.1,3,15; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "aka": "Akto Baki", "state": "AL", "affiliation": ["Inmates For Action"]}'

# Elaine Brown (CA)
php artisan prisoner:add '{"name": "Elaine Brown", "first_name": "Elaine", "last_name": "Brown", "description": "Chairwoman of the Black Panther Party, charged in Aug. 1975 with a drug-possession offense that The Black Panther Vol. 13 no.27 (1975) calls a deliberately concocted frame-up to discredit her and intimidate the San Quentin 6 defense, for which she was a potential witness; the Party said phone records proved she was not even at the prison that day. [Source: The Black Panther (Black Panther Party newspaper), v13 no.27; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black Panther Party"]}'

# Ella Davis (TX)
php artisan prisoner:add '{"name": "Ella Davis", "first_name": "Ella", "last_name": "Davis", "description": "Young Black woman in Nacogdoches, Texas charged with assault on a police officer — facing up to ten years — after she armed herself to defend her family from officers who, The Black Panther Vol. 13 no.25 (1975) reports, abused and beat her when she came to the station to report her brother'\''s shooting. [Source: The Black Panther (Black Panther Party newspaper), v13 no.25; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "TX"}'

# Gary Lawton (CA)
php artisan prisoner:add '{"name": "Gary Lawton", "first_name": "Gary", "last_name": "Lawton", "description": "Riverside, California community activist charged in the 1971 killing of two policemen in what The Black Panther Vol. 13 no.15 (1975) calls a trumped-up frame-up; after years of prosecution he was acquitted and made his first public appearance at the San Quentin 6 coalition conference. [Source: The Black Panther (Black Panther Party newspaper), v13 no.15; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA"}'

# George Robinson (CA)
php artisan prisoner:add '{"name": "George Robinson", "first_name": "George", "last_name": "Robinson", "description": "Black Panther Party member held in the Alameda County Jail, Oakland, on what The Black Panther Vol. 13 no.29 (1975) calls a trumped-up murder charge. [Source: The Black Panther (Black Panther Party newspaper), v13 no.29; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black Panther Party"]}'

# Grover McCorvery (AL)
php artisan prisoner:add '{"name": "Grover McCorvery", "first_name": "Grover", "last_name": "McCorvery", "description": "One of the nine '\''Atmore-Holman Brothers'\'' (IFA) prison organizers; The Black Panther Vol. 13 (1975) reports the assault charge against him from the 1974 Atmore rebellion was dropped for lack of evidence, part of a pattern of repeated prosecutions of the Alabama prisoner-activists. [Source: The Black Panther (Black Panther Party newspaper), v13 no.1,6; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "aka": "Sitting Bull", "state": "AL", "affiliation": ["Inmates For Action"]}'

# H.K. Matthews (FL)
php artisan prisoner:add '{"name": "H.K. Matthews", "first_name": "H.K.", "last_name": "Matthews", "description": "Pensacola civil-rights leader convicted with Rev. B.J. Brooks of '\''extortion'\'' over a march protesting Deputy Doug Raines'\'' killing of Wendell Blackwell; The Black Panther Vol. 13 (1975) reports he faced up to 15 years and remarked, '\''That'\''s what happens when you are Black in this community.'\'' [Source: The Black Panther (Black Panther Party newspaper), v13 no.20,21; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "aka": "Rev. H.K. Matthews", "state": "FL"}'

# Jesse Lee Walston (NC)
php artisan prisoner:add '{"name": "Jesse Lee Walston", "first_name": "Jesse", "last_name": "Walston", "description": "One of the '\''Tarboro 3,'\'' originally sentenced to death for rape in North Carolina; The Black Panther Vol. 13 no.15 (1975) reports his death sentence was set aside and he received a suspended six-year sentence after a negotiated plea. [Source: The Black Panther (Black Panther Party newspaper), v13 no.15; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NC"}'

# Jesse Lopez (KS)
php artisan prisoner:add '{"name": "Jesse Lopez", "first_name": "Jesse", "last_name": "Lopez", "description": "One of the '\''Leavenworth Brothers'\'' held in isolation and charged over the 1973 Leavenworth federal penitentiary rebellion; The Black Panther Vol. 13 no.4 (1975). [Source: The Black Panther (Black Panther Party newspaper), v13 no.4; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "KS"}'

# Jessie Clanzy (AL)
php artisan prisoner:add '{"name": "Jessie Clanzy", "first_name": "Jessie", "last_name": "Clanzy", "description": "One of the nine '\''Atmore-Holman Brothers'\'' (IFA) prison organizers charged over the 1974 Alabama prison rebellions; The Black Panther Vol. 13 (1975). [Source: The Black Panther (Black Panther Party newspaper), v13 no.1,15; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "aka": "Tuba", "state": "AL", "affiliation": ["Inmates For Action"]}'

# Joe W. Rhone (CA)
php artisan prisoner:add '{"name": "Joe W. Rhone", "first_name": "Joe", "last_name": "Rhone", "description": "Black Taft, California junior-college football player arrested after defending himself with a pool-cue case from a knife-wielding mob of white youths; The Black Panther Vol. 13 no.17 (1975) reports police charged him while the Kern County district attorney refused to charge his white attackers. [Source: The Black Panther (Black Panther Party newspaper), v13 no.17; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA"}'

# Joseph Murchison (TX)
php artisan prisoner:add '{"name": "Joseph Murchison", "first_name": "Joseph", "last_name": "Murchison", "description": "Black man convicted and sentenced to life for the alleged attempted rape of a white college student in Nacogdoches, Texas; The Black Panther Vol. 13 no.26 (1975) presents the case as a racist frame-up after he and a friend gave the woman a ride and she mistakenly thought he had made advances. [Source: The Black Panther (Black Panther Party newspaper), v13 no.26; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "TX"}'

# Juan Fernandez (CA)
php artisan prisoner:add '{"name": "Juan Fernandez", "first_name": "Juan", "last_name": "Fernandez", "description": "One of '\''Los Tres del Barrio,'\'' Chicano activists convicted of assaulting an undercover federal narcotics agent; The Black Panther Vol. 13 no.12 (1975) reports that after FBI agents put a gun to his mother'\''s head he surrendered and was jailed under maximum security when his bail was revoked. [Source: The Black Panther (Black Panther Party newspaper), v13 no.12; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Chicano movement"]}'

# Larry James Pinkney (CA)
php artisan prisoner:add '{"name": "Larry James Pinkney", "first_name": "Larry", "last_name": "Pinkney", "description": "Former co-chairman of the San Francisco Black Caucus and a Black Student Union leader, framed (per The Black Panther Vol. 13 no.11, 1975) on attempted-murder and rape charges arising from a 1973 incident in which he tried to stop a rape; he fled to Sweden, where the Swedish Parliament temporarily blocked his extradition to the U.S. [Source: The Black Panther (Black Panther Party newspaper), v13 no.11; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA"}'

# Lincoln Heard (AL)
php artisan prisoner:add '{"name": "Lincoln Heard", "first_name": "Lincoln", "last_name": "Heard", "description": "One of the nine '\''Atmore-Holman Brothers'\'' (IFA) prison organizers; The Black Panther Vol. 13 (1975) reports he was convicted of assault in the 1974 Atmore rebellion and sentenced to 20 years, in a prosecution the defense said rested on the men'\''s participation in rebellions rather than direct evidence. [Source: The Black Panther (Black Panther Party newspaper), v13 no.1,6; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "aka": "Makou Salih", "state": "AL", "affiliation": ["Inmates For Action"]}'

# Marshall Conway (MD)
php artisan prisoner:add '{"name": "Marshall Conway", "first_name": "Marshall", "last_name": "Conway", "description": "Baltimore Black Panther Party member and one of the '\''Maryland Penitentiary Five,'\'' held on the prison'\''s lock-up/'\''death wing'\'' and, per The Black Panther Vol. 13 (1975), convicted in a guard-stabbing disturbance case (with 101 years added by the judge) despite an acquittal on the underlying charge. He was imprisoned 44 years before his 2014 release. [Source: The Black Panther (Black Panther Party newspaper), v13 no.1,3,7; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "aka": "Eddie Conway", "state": "MD", "affiliation": ["Black Panther Party"]}'

# Odell Bennett (KS)
php artisan prisoner:add '{"name": "Odell Bennett", "first_name": "Odell", "last_name": "Bennett", "description": "One of the '\''Leavenworth Brothers,'\'' 26 inmates held in solitary since a July 1973 uprising at the Leavenworth federal penitentiary; The Black Panther Vol. 13 no.4 (1975) reports seven Black and Brown inmates faced kidnapping, assault and murder charges and that Bennett wrote appealing for support against the prison'\''s behavior-modification regime. [Source: The Black Panther (Black Panther Party newspaper), v13 no.4; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "KS"}'

# R.A. Cooks (TX)
php artisan prisoner:add '{"name": "R.A. Cooks", "first_name": "R.A.", "last_name": "Cooks", "description": "56-year-old West Dallas man charged with attempted capital murder for firing a shotgun at police officers who, The Black Panther Vol. 13 no.16 (1975) reports, were brutalizing his 18-year-old son during an arrest; he then surrendered, and the Black Panther Party argued his self-defense was justified under Texas law. [Source: The Black Panther (Black Panther Party newspaper), v13 no.16; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "TX"}'

# Ralph Moore (TN)
php artisan prisoner:add '{"name": "Ralph Moore", "first_name": "Ralph", "last_name": "Moore", "description": "Coordinator of the Chattanooga, Tennessee Black Panther Party chapter and a candidate for public office, convicted of felony '\''extortion'\'' for leading a 1972 community boycott of a Red Food supermarket; The Black Panther Vol. 13 (1975) reports the Tennessee Supreme Court upheld the two-year sentence, and that he was jailed and blocked from running for office. [Source: The Black Panther (Black Panther Party newspaper), v13 no.1,13,16; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "TN", "affiliation": ["Black Panther Party"]}'

# Robert Austin (MD)
php artisan prisoner:add '{"name": "Robert Austin", "first_name": "Robert", "last_name": "Austin", "description": "One of the '\''Maryland Penitentiary Five,'\'' sentenced to five years of segregation over the Maryland Penitentiary disturbance; The Black Panther Vol. 13 no.3 (1975) reports the Five filed a $17.5 million damage suit against the warden and guards who attacked them. [Source: The Black Panther (Black Panther Party newspaper), v13 no.3; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "MD"}'

# Robert Carson (NY)
php artisan prisoner:add '{"name": "Robert Carson", "first_name": "Robert", "last_name": "Carson", "description": "Brooklyn community activist and former director of Brooklyn CORE, sentenced to up to seven years for a kidnapping conviction he tied to his long conflict with the police; The Black Panther Vol. 13 no.1 (1975). [Source: The Black Panther (Black Panther Party newspaper), v13 no.1; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "aka": "Sonny Carson", "state": "NY"}'

# Robert Foulks (MD)
php artisan prisoner:add '{"name": "Robert Foulks", "first_name": "Robert", "last_name": "Foulks", "description": "One of the '\''Maryland Penitentiary Five,'\'' Black Panther-supported prisoners prosecuted over a guard-provoked disturbance; The Black Panther Vol. 13 no.3 (1975) reports the trouble began when guards beat a handcuffed prisoner in front of him and then beat him when he protested, and that he was sentenced to five years'\'' segregation. [Source: The Black Panther (Black Panther Party newspaper), v13 no.3; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "MD"}'

# Robert Steele Collier (NY)
php artisan prisoner:add '{"name": "Robert Steele Collier", "first_name": "Robert", "last_name": "Collier", "description": "Black liberation activist and former '\''Panther 21'\'' defendant (acquitted in 1971) who had earlier been imprisoned over the 1965 Statue of Liberty bomb-plot case; The Black Panther Vol. 13 no.25 (1975) reports a New York justice dismissed a later weapons-conspiracy indictment against him because it stemmed from unconstitutional police (BOSS) infiltration of his group. [Source: The Black Panther (Black Panther Party newspaper), v13 no.25; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NY", "affiliation": ["Black Panther Party"]}'

# Rodolfo Sanchez (CA)
php artisan prisoner:add '{"name": "Rodolfo Sanchez", "first_name": "Rodolfo", "last_name": "Sanchez", "description": "One of '\''Los Tres del Barrio,'\'' Chicano activists convicted of assaulting an undercover federal agent posing as a heroin dealer in the barrio; The Black Panther Vol. 13 no.12 (1975) reports his appeal bail was revoked without warning and he was arrested and jailed in the Los Angeles County Jail. [Source: The Black Panther (Black Panther Party newspaper), v13 no.12; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Chicano movement"]}'

# Scott Camil (FL)
php artisan prisoner:add '{"name": "Scott Camil", "first_name": "Scott", "last_name": "Camil", "description": "Vietnam Veterans Against the War organizer and one of the '\''Gainesville 8'\'' (acquitted of conspiracy to disrupt the 1972 Republican convention); The Black Panther Vol. 13 no.12 (1975) reports he was shot in the back at near-point-blank range by a DEA agent who never identified himself, and that eyewitnesses contradicted the officers'\'' account. [Source: The Black Panther (Black Panther Party newspaper), v13 no.12; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "FL", "affiliation": ["Vietnam Veterans Against the War"]}'

# Vernon Brown (NC)
php artisan prisoner:add '{"name": "Vernon Brown", "first_name": "Vernon", "last_name": "Brown", "description": "One of the '\''Tarboro 3,'\'' originally sentenced to death for rape in North Carolina; The Black Panther Vol. 13 no.15 (1975) reports his conviction was overturned on a prosecution error and he received a suspended six-year sentence. [Source: The Black Panther (Black Panther Party newspaper), v13 no.15; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NC"}'

# Virgil Ricky Jones (CA)
php artisan prisoner:add '{"name": "Virgil Ricky Jones", "first_name": "Virgil", "last_name": "Jones", "description": "One of four Black people the paper says were framed for the Jan. 1975 Morton'\''s Jewelry Store robbery in San Jose despite no evidence being found; The Black Panther Vol. 13 no.15 (1975). [Source: The Black Panther (Black Panther Party newspaper), v13 no.15; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA"}'

# ===== GROUP B -- INTERNATIONAL, commented out (10) =====
# NPPC is a U.S. (National) coalition; these foreign cases (Rhodesia/Zimbabwe, Chile, Iran,
# Israel, Kenya, Dominica, French Guiana, Palau) are left commented for you to include or skip.

# Canaan Banana (Rhodesia)
# php artisan prisoner:add '{"name": "Canaan Banana", "first_name": "Canaan", "last_name": "Banana", "description": "Former vice-president of the African National Council of Zimbabwe, arrested by Rhodesian police on his arrival from exile to take part in the push for majority rule; The Black Panther Vol. 13 no.17 (1975). He later became the first President of Zimbabwe. [Source: The Black Panther (Black Panther Party newspaper), v13 no.17; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["African National Council"]}'

# Gholamhossein Sa'edi (Iran)
# php artisan prisoner:add '{"name": "Gholamhossein Sa'\''edi", "first_name": "Gholamhossein", "last_name": "Sa'\''edi", "description": "Iranian playwright and physician held over three months in solitary confinement and tortured at Tehran'\''s Evin Prison under the Shah for political dissent, developing a heart condition; The Black Panther Vol. 13 no.8 (1975) reports his release. [Source: The Black Panther (Black Panther Party newspaper), v13 no.8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]"}'

# Guy Lamaze (French Guiana)
# php artisan prisoner:add '{"name": "Guy Lamaze", "first_name": "Guy", "last_name": "Lamaze", "description": "Secretary-general of MOGUYDE and a Cayenne teacher, one of eight French Guianan independence militants imprisoned in Paris in 1975 and released after a month; The Black Panther Vol. 13 no.1 (1975). [Source: The Black Panther (Black Panther Party newspaper), v13 no.1; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["MOGUYDE"]}'

# Israel Shahak (Israel)
# php artisan prisoner:add '{"name": "Israel Shahak", "first_name": "Israel", "last_name": "Shahak", "description": "Hebrew University professor and chairman of the Israeli League for Human and Civil Rights, investigated by the Israeli government for possible treason over his advocacy of equal treatment for Palestinians; The Black Panther Vol. 13 no.12 (1975). [Source: The Black Panther (Black Panther Party newspaper), v13 no.12; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Israeli League for Human and Civil Rights"]}'

# Jean Mariemma (French Guiana)
# php artisan prisoner:add '{"name": "Jean Mariemma", "first_name": "Jean", "last_name": "Mariemma", "description": "Lawyer and member of MOGUYDE (the Movement to Decolonize Guiana), one of eight French Guianan independence leaders held a month in Paris'\''s Sante Prison before being released for lack of evidence; The Black Panther Vol. 13 no.1 (1975). [Source: The Black Panther (Black Panther Party newspaper), v13 no.1; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["MOGUYDE"]}'

# Josiah Kariuki (Kenya)
# php artisan prisoner:add '{"name": "Josiah Kariuki", "first_name": "Josiah", "last_name": "Kariuki", "description": "Popular Kenyan political leader and outspoken critic of President Jomo Kenyatta'\''s government, murdered in March 1975 in a killing that fueled banned student demonstrations; The Black Panther Vol. 13 no.16 (1975). [Source: The Black Panther (Black Panther Party newspaper), v13 no.16; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]"}'

# Laura Allende (Chile)
# php artisan prisoner:add '{"name": "Laura Allende", "first_name": "Laura", "last_name": "Allende", "description": "Chilean congresswoman and sister of the assassinated President Salvador Allende, arrested by the military junta in Nov. 1974 for alleged conspiracy with the MIR and held until she was among 95 political prisoners freed into exile in Mexico; The Black Panther Vol. 13 no.6 (1975). [Source: The Black Panther (Black Panther Party newspaper), v13 no.6; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]"}'

# Moses Uludong (Palau (Micronesia))
# php artisan prisoner:add '{"name": "Moses Uludong", "first_name": "Moses", "last_name": "Uludong", "description": "Palauan independence activist and editor of the outlawed newspaper Tia Belau, deported from the U.S. on what The Black Panther Vol. 13 no.19 (1975) calls false charges of plotting to assassinate a U.S. official. [Source: The Black Panther (Black Panther Party newspaper), v13 no.19; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]"}'

# Ndabaningi Sithole (Rhodesia)
# php artisan prisoner:add '{"name": "Ndabaningi Sithole", "first_name": "Ndabaningi", "last_name": "Sithole", "description": "President of the Zimbabwe African National Union (ZANU) who had already spent about ten years in Rhodesian detention, re-arrested in 1975 by the Smith regime on assassination-plot charges his allies called fabricated; The Black Panther Vol. 13 (1975). [Source: The Black Panther (Black Panther Party newspaper), v13 no.4,5,7,9,10,14; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Zimbabwe African National Union"]}'

# Rosie Douglas (Dominica)
# php artisan prisoner:add '{"name": "Rosie Douglas", "first_name": "Rosie", "last_name": "Douglas", "description": "Dominican Black Power activist and longtime Canadian resident ordered deported from Canada as '\''subversive'\'' and facing danger if returned to Dominica, where his book on conditions there was banned; The Black Panther Vol. 13 no.17 (1975). He later became Prime Minister of Dominica. [Source: The Black Panther (Black Panther Party newspaper), v13 no.17; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]"}'

