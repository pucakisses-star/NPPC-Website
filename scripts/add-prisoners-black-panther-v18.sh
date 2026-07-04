#!/usr/bin/env bash
# Political-prisoner cases from The Black Panther newspaper, Vol. 18 (1978), read page-by-page
# from the marxists.org archive (issues no. 1-8, 12, 17, 26, 28, 29; 300 pages).
# RUN ON THE SERVER after review. Idempotent: prisoner:add de-dupes by name.
set +e

# ===== GROUP A -- U.S. political-prisoner cases (41) =====

# Al McSurely (KY)
php artisan prisoner:add '{"name": "Al McSurely", "first_name": "Al", "last_name": "McSurely", "description": "Civil-rights organizer in the eastern Kentucky coal belt arrested with his wife Margaret on sedition charges; their home was raided, papers seized by Sen. John McClellan'\''s subcommittee, and their house later bombed. The Black Panther Vol. 18 no.7 (1978) covers their decade-long case reaching the U.S. Supreme Court. [Source: The Black Panther (Black Panther Party newspaper), v18 no.7; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "KY"}'

# Ann Shepard Turner (NC)
php artisan prisoner:add '{"name": "Ann Shepard Turner", "first_name": "Ann", "last_name": "Turner", "description": "The only white member of the Wilmington Ten, convicted in the trumped-up 1971 Wilmington, NC firebombing/conspiracy case; paroled around 1977. The Black Panther Vol. 18 no.2 (1978). [Source: The Black Panther (Black Panther Party newspaper), v18 no.2; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NC", "affiliation": ["Wilmington Ten"]}'

# Ben Chavis (NC)
php artisan prisoner:add '{"name": "Ben Chavis", "first_name": "Ben", "last_name": "Chavis", "description": "Civil-rights organizer (United Church of Christ Commission for Racial Justice) and leader of the Wilmington Ten, convicted on trumped-up charges of firebombing a white-owned store during 1971 racial unrest in Wilmington, NC. Originally sentenced to 25–29 years. The Black Panther Vol. 18 no.2 (1978); Amnesty International classed the group as political prisoners. The convictions were overturned in 1980 and the group pardoned as innocent in 2012. [Source: The Black Panther (Black Panther Party newspaper), v18 no.2; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NC", "affiliation": ["Wilmington Ten"]}'

# Bobby Hutton (CA)
php artisan prisoner:add '{"name": "Bobby Hutton", "first_name": "Bobby", "last_name": "Hutton", "description": "The first recruit and treasurer of the Black Panther Party, among those arrested at the May 2, 1967 armed protest at the California legislature in Sacramento; on April 6, 1968 he was shot and killed by Oakland police after surrendering. Memorialized as a fallen comrade in The Black Panther Vol. 18 no.12 (1978). [Source: The Black Panther (Black Panther Party newspaper), v18 no.12; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black Panther Party"]}'

# Charles Jennings (IL)
php artisan prisoner:add '{"name": "Charles Jennings", "first_name": "Charles", "last_name": "Jennings", "description": "Black Joliet/Stateville inmate, one of the '\''Statesville Four,'\'' acquitted after a week-long trial of the trumped-up murder of a white prison guard; The Black Panther Vol. 18 no.4 (1978) reports the state'\''s star witness admitted prisoners were offered favorable parole to cooperate. [Source: The Black Panther (Black Panther Party newspaper), v18 no.4; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "IL", "affiliation": ["Prison movement"]}'

# Cheryl Todd (GA)
php artisan prisoner:add '{"name": "Cheryl Todd", "first_name": "Cheryl", "last_name": "Todd", "description": "Black woman who, with Dessie Woods, was the target of an attempted rape by Ronnie Horne on June 16, 1975; charged in the case and given a suspended sentence. The Black Panther Vol. 18 no.17 (1978). [Source: The Black Panther (Black Panther Party newspaper), v18 no.17; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "GA"}'

# Connie Tindall (NC)
php artisan prisoner:add '{"name": "Connie Tindall", "first_name": "Connie", "last_name": "Tindall", "description": "One of the Wilmington Ten, convicted on trumped-up firebombing/conspiracy charges from the 1971 Wilmington, NC unrest (original 27-year sentence). The Black Panther Vol. 18 no.2 (1978). Convictions overturned 1980; group pardoned 2012. [Source: The Black Panther (Black Panther Party newspaper), v18 no.2; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NC", "affiliation": ["Wilmington Ten"]}'

# Delbert Africa (PA)
php artisan prisoner:add '{"name": "Delbert Africa", "first_name": "Delbert", "last_name": "Africa", "description": "A leader of the Philadelphia MOVE organization, which The Black Panther Vol. 18 no.8 (1978) reports was under police siege by the Rizzo administration, with members accused of weapons offenses and two convicted federally of making explosive devices. He was later beaten by police at MOVE'\''s 1978 eviction and imprisoned for decades. [Source: The Black Panther (Black Panther Party newspaper), v18 no.8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "PA", "affiliation": ["MOVE"]}'

# Delia Gonzalez (TX)
php artisan prisoner:add '{"name": "Delia Gonzalez", "first_name": "Delia", "last_name": "Gonzalez", "description": "A 39-year-old Chicana activist in Del Rio, Texas facing a 13-count federal indictment (up to 65 years) for allegedly inducing Mexican nationals to enter the U.S., a prosecution the paper calls trumped-up. The Black Panther Vol. 18 no.2 (1978). [Source: The Black Panther (Black Panther Party newspaper), v18 no.2; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "TX"}'

# Dennis Banks (SD)
php artisan prisoner:add '{"name": "Dennis Banks", "first_name": "Dennis", "last_name": "Banks", "description": "Co-founder of the American Indian Movement. The Black Panther Vol. 18 no.26 (1978) reports his arrest and the refusal of California Gov. Edmund Brown to extradite him to South Dakota on a '\''trumped-up'\'' Custer County conviction for rioting and assault stemming from 1973 AIM protests. [Source: The Black Panther (Black Panther Party newspaper), v18 no.26; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "SD", "affiliation": ["American Indian Movement"]}'

# Dennis Goodwin (NY)
php artisan prisoner:add '{"name": "Dennis Goodwin", "first_name": "Dennis", "last_name": "Goodwin", "description": "Black man convicted of second-degree murder by an all-white jury in Elmira, N.Y. (arrested March 7, 1977) in a case a co-defendant'\''s letter to The Black Panther Vol. 18 no.5 (1978) describes as a frame-up, with negative forensic evidence and discredited paid witnesses. [Source: The Black Panther (Black Panther Party newspaper), v18 no.5; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NY"}'

# Dessie Woods (GA)
php artisan prisoner:add '{"name": "Dessie Woods", "first_name": "Dessie", "last_name": "Woods", "description": "Black woman sentenced to 22 years in Georgia for killing, with his own gun, a white insurance salesman (Ronnie Horne) who tried to rape her and Cheryl Todd on June 16, 1975 — a case that became a national cause. The Black Panther Vol. 18 no.17 (1978) reports her habeas petition and allegations she was drugged, beaten, and held in isolation; she was freed in 1981. [Source: The Black Panther (Black Panther Party newspaper), v18 no.17; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "GA", "affiliation": ["Black liberation movement"]}'

# Donald Thigpen (AL)
php artisan prisoner:add '{"name": "Donald Thigpen", "first_name": "Donald", "last_name": "Thigpen", "description": "Alabama prisoner who faced a March 10, 1978 execution date alongside Johnny Imani Harris; at Harris'\''s request the same defense lawyers took his case. The Black Panther Vol. 18 no.6 (1978). [Source: The Black Panther (Black Panther Party newspaper), v18 no.6; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "AL", "affiliation": ["Prison movement"]}'

# Eddie Page (CA)
php artisan prisoner:add '{"name": "Eddie Page", "first_name": "Eddie", "last_name": "Page", "description": "A Black Marine private, the Pendleton 14 defendant given the harshest sentence after an August 1977 court-martial (two years plus bad-conduct discharge), in a case that drew national protest. The Black Panther Vol. 18 no.6 (1978) reports he was released after five months and his sentence reduced. [Source: The Black Panther (Black Panther Party newspaper), v18 no.6; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Pendleton 14"]}'

# Filipina Narciso (MI)
php artisan prisoner:add '{"name": "Filipina Narciso", "first_name": "Filipina", "last_name": "Narciso", "description": "Filipino nurse at the Ann Arbor VA hospital convicted in 1977 (with Leonora Perez) of poisoning patients, in a prosecution widely seen as a frame-up. The Black Panther Vol. 18 no.4 (1978) reports the guilty verdicts were overturned and charges dropped after about two years, freeing her. [Source: The Black Panther (Black Panther Party newspaper), v18 no.4; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "MI"}'

# George Brown ()
php artisan prisoner:add '{"name": "George Brown", "first_name": "George", "last_name": "Brown", "description": "One of the '\''Fleury 4,'\'' Black American activists tried in France after the 1972 Detroit-to-Algiers hijacking; the French court found it a political action rather than a crime. The Black Panther Vol. 18 no.29 (1978). [Source: The Black Panther (Black Panther Party newspaper), v18 no.29; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Black liberation movement"]}'

# Hayward Brown (MI)
php artisan prisoner:add '{"name": "Hayward Brown", "first_name": "Hayward", "last_name": "Brown", "description": "Detroit Black youth who, with two friends, fought Detroit'\''s notorious STRESS police unit; captured after a massive manhunt and tried, he was acquitted after attorney Ken Cockrel put the police on trial. The Black Panther Vol. 18 no.3 (1978). [Source: The Black Panther (Black Panther Party newspaper), v18 no.3; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "MI"}'

# Hekima Ana (MS)
php artisan prisoner:add '{"name": "Hekima Ana", "first_name": "Hekima", "last_name": "Ana", "description": "Vice President of the Republic of New Afrika, convicted of first-degree murder for the death of a Jackson, Mississippi police lieutenant during the Aug. 18, 1971 FBI/police raid on the RNA; The Black Panther Vol. 18 no.5 (1978) reports the FBI could not support its palm-print claim central to the conviction. [Source: The Black Panther (Black Panther Party newspaper), v18 no.5; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "MS", "affiliation": ["Republic of New Afrika"]}'

# Imari Obadele (MS)
php artisan prisoner:add '{"name": "Imari Obadele", "first_name": "Imari", "last_name": "Obadele", "description": "President of the Republic of New Afrika (RNA). After the Aug. 18, 1971 FBI/police raid on the RNA in Jackson, Mississippi, he was among the '\''RNA-11'\'' charged with conspiracy to assault federal officers and sentenced to 12 years; imprisoned at the Atlanta Penitentiary. The Black Panther Vol. 18 no.5 (1978) details the COINTELPRO campaign against the RNA. [Source: The Black Panther (Black Panther Party newspaper), v18 no.5; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "MS", "affiliation": ["Republic of New Afrika"]}'

# James Edward Garrett (MS)
php artisan prisoner:add '{"name": "James Edward Garrett", "first_name": "James", "last_name": "Garrett", "description": "A 25-year-old Black man arrested by Marshall County, Mississippi deputies on a 1975 armed-robbery charge who was found dead in his cell (hands and feet bound) on Jan. 21, 1978; officials ruled suicide, but the United League of Marshall County disputed it and demanded a federal probe. The Black Panther Vol. 18 no.7 (1978). [Source: The Black Panther (Black Panther Party newspaper), v18 no.7; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "MS"}'

# James McCoy (NC)
php artisan prisoner:add '{"name": "James McCoy", "first_name": "James", "last_name": "McCoy", "description": "One of the Wilmington Ten, convicted on trumped-up firebombing/conspiracy charges from the 1971 Wilmington, NC unrest. The Black Panther Vol. 18 no.2 (1978). Convictions overturned 1980; group pardoned 2012. [Source: The Black Panther (Black Panther Party newspaper), v18 no.2; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NC", "affiliation": ["Wilmington Ten"]}'

# Jean McNair ()
php artisan prisoner:add '{"name": "Jean McNair", "first_name": "Jean", "last_name": "McNair", "description": "One of the '\''Fleury 4,'\'' tried in France after the 1972 Detroit-to-Algiers hijacking to flee U.S. racism; a French court deemed it a political act. Two years of the women'\''s five-year sentences were suspended. The Black Panther Vol. 18 no.29 (1978). [Source: The Black Panther (Black Panther Party newspaper), v18 no.29; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Black liberation movement"]}'

# Jerry Jacobs (NC)
php artisan prisoner:add '{"name": "Jerry Jacobs", "first_name": "Jerry", "last_name": "Jacobs", "description": "One of the Wilmington Ten, convicted on trumped-up firebombing/conspiracy charges from the 1971 Wilmington, NC unrest. The Black Panther Vol. 18 no.2 (1978). Convictions overturned 1980; group pardoned 2012. [Source: The Black Panther (Black Panther Party newspaper), v18 no.2; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NC", "affiliation": ["Wilmington Ten"]}'

# JoAnne Little (NC)
php artisan prisoner:add '{"name": "JoAnne Little", "first_name": "JoAnne", "last_name": "Little", "description": "Black North Carolina woman who became a national cause after killing a jailer who sexually assaulted her (acquitted 1975 on self-defense). The Black Panther Vol. 18 no.17 (1978) covers her subsequent prison-escape trial in Raleigh, with attorney William Kunstler seeking removal to federal court. [Source: The Black Panther (Black Panther Party newspaper), v18 no.17; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NC", "affiliation": ["Black liberation movement"]}'

# Joseph Waddell (NC)
php artisan prisoner:add '{"name": "Joseph Waddell", "first_name": "Joseph", "last_name": "Waddell", "description": "Black Panther Party member known as '\''Joe-Dell,'\'' who The Black Panther Vol. 18 no.29 (1978) says was serving a 25–30 year sentence on trumped-up armed-robbery charges when he died in custody at Central Prison in Raleigh on June 13, 1972 — his death ruled a '\''heart attack'\'' but attributed by the paper to prison officials. [Source: The Black Panther (Black Panther Party newspaper), v18 no.29; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NC", "affiliation": ["Black Panther Party"]}'

# Joseph Waller (FL)
php artisan prisoner:add '{"name": "Joseph Waller", "first_name": "Joseph", "last_name": "Waller", "description": "Chairman of the African People'\''s Socialist Party (later known as Omali Yeshitela). The Black Panther Vol. 18 no.8 (1978) reports that shots were fired at him during a San Francisco speaking engagement, and that days later he was arrested on federal counterfeit-currency charges he called a COINTELPRO frame-up. [Source: The Black Panther (Black Panther Party newspaper), v18 no.8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "FL", "affiliation": ["African People'\''s Socialist Party"]}'

# Joyce Tillerson ()
php artisan prisoner:add '{"name": "Joyce Tillerson", "first_name": "Joyce", "last_name": "Tillerson", "description": "One of the '\''Fleury 4,'\'' tried in France after the 1972 Detroit-to-Algiers hijacking to flee U.S. racism; sentenced to five years with two suspended. The Black Panther Vol. 18 no.29 (1978). [Source: The Black Panther (Black Panther Party newspaper), v18 no.29; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Black liberation movement"]}'

# Leonora Perez (MI)
php artisan prisoner:add '{"name": "Leonora Perez", "first_name": "Leonora", "last_name": "Perez", "description": "Filipino nurse at the Ann Arbor VA hospital convicted in 1977 with Filipina Narciso of poisoning patients; the trumped-up charges were dropped and the convictions overturned, freeing her after about two years. The Black Panther Vol. 18 no.4 (1978). [Source: The Black Panther (Black Panther Party newspaper), v18 no.4; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "MI"}'

# Madelyn Fletcher (MI)
php artisan prisoner:add '{"name": "Madelyn Fletcher", "first_name": "Madelyn", "last_name": "Fletcher", "description": "Black policewoman in Flint, Michigan prosecuted after an armed confrontation the paper attributes to threats by white male officers; the prosecution collapsed after attorney Ken Cockrel exposed the department'\''s racism and sexism. The Black Panther Vol. 18 no.3 (1978). [Source: The Black Panther (Black Panther Party newspaper), v18 no.3; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "MI"}'

# Margaret McSurely (KY)
php artisan prisoner:add '{"name": "Margaret McSurely", "first_name": "Margaret", "last_name": "McSurely", "description": "Civil-rights organizer arrested with her husband Al on sedition charges after organizing in eastern Kentucky; home raided, papers seized, and house bombed. The Black Panther Vol. 18 no.7 (1978); their case reached the U.S. Supreme Court. [Source: The Black Panther (Black Panther Party newspaper), v18 no.7; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "KY"}'

# Marvin Patrick (NC)
php artisan prisoner:add '{"name": "Marvin Patrick", "first_name": "Marvin", "last_name": "Patrick", "description": "One of the Wilmington Ten, convicted on trumped-up firebombing/conspiracy charges from the 1971 Wilmington, NC unrest (original 25-year sentence). The Black Panther Vol. 18 no.2 (1978). Convictions overturned 1980; group pardoned 2012. [Source: The Black Panther (Black Panther Party newspaper), v18 no.2; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NC", "affiliation": ["Wilmington Ten"]}'

# Melvin McNair ()
php artisan prisoner:add '{"name": "Melvin McNair", "first_name": "Melvin", "last_name": "McNair", "description": "One of the '\''Fleury 4'\'' — Black American activists who in 1972 hijacked a plane from Detroit to Algiers to escape U.S. racism. Per The Black Panther Vol. 18 no.29 (1978), after fighting U.S. extradition they were tried in France, where the court ruled the hijacking a political act, and sentenced to about five years. [Source: The Black Panther (Black Panther Party newspaper), v18 no.29; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Black liberation movement"]}'

# Paul Skyhorse (CA)
php artisan prisoner:add '{"name": "Paul Skyhorse", "first_name": "Paul", "last_name": "Skyhorse", "description": "Native American activist tried with Richard Mohawk in the '\''Skyhorse–Mohawk'\'' case on charges of fatally stabbing a cab driver, widely regarded as an FBI-tainted frame-up. The Black Panther Vol. 18 no.2,6 (1978) reports the drunk state of the prosecution'\''s star witness; both were acquitted in 1978. [Source: The Black Panther (Black Panther Party newspaper), v18 no.2,6; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["American Indian Movement"]}'

# Reginald Epps (NC)
php artisan prisoner:add '{"name": "Reginald Epps", "first_name": "Reginald", "last_name": "Epps", "description": "One of the Wilmington Ten, convicted on trumped-up firebombing/conspiracy charges from the 1971 Wilmington, NC unrest. The Black Panther Vol. 18 no.2 (1978). Convictions overturned 1980; group pardoned 2012. [Source: The Black Panther (Black Panther Party newspaper), v18 no.2; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NC", "affiliation": ["Wilmington Ten"]}'

# Richard Mohawk (CA)
php artisan prisoner:add '{"name": "Richard Mohawk", "first_name": "Richard", "last_name": "Mohawk", "description": "Native American activist tried with Paul Skyhorse on trumped-up charges of fatally stabbing a cab driver near Los Angeles. The Black Panther Vol. 18 no.2,6 (1978); both were acquitted in 1978 after a long trial marked by a discredited prosecution witness. [Source: The Black Panther (Black Panther Party newspaper), v18 no.2,6; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["American Indian Movement"]}'

# Robert Heard (CA)
php artisan prisoner:add '{"name": "Robert Heard", "first_name": "Robert", "last_name": "Heard", "description": "Black Panther Party member and co-defendant of Huey P. Newton in the May 11, 1978 Seacliff (Santa Cruz) Mediterranean Lounge incident, charged with attempted murder, assault, and being an ex-felon in possession of a firearm; freed on bail. The Black Panther Vol. 18 no.17 (1978). [Source: The Black Panther (Black Panther Party newspaper), v18 no.17; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black Panther Party"]}'

# Robert Houchens (IL)
php artisan prisoner:add '{"name": "Robert Houchens", "first_name": "Robert", "last_name": "Houchens", "description": "Imprisoned organizer of the Black Culture Society at Marion federal prison (known as '\''Hodari'\''), placed in segregation and denied parole and banned from the BCS after writing to the press criticizing the prison administration. The Black Panther Vol. 18 no.6 (1978). [Source: The Black Panther (Black Panther Party newspaper), v18 no.6; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "IL", "affiliation": ["Prison movement"]}'

# Stanton Story (PA)
php artisan prisoner:add '{"name": "Stanton Story", "first_name": "Stanton", "last_name": "Story", "description": "Black man convicted and sentenced to death for the 1974 shooting of a Pittsburgh police officer, in a case The Black Panther Vol. 18 no.7 (1978) describes as a frame-up amid racist hysteria; the Pennsylvania Supreme Court ordered a new trial over prejudicial evidence. [Source: The Black Panther (Black Panther Party newspaper), v18 no.7; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "PA"}'

# Wayne Moore (NC)
php artisan prisoner:add '{"name": "Wayne Moore", "first_name": "Wayne", "last_name": "Moore", "description": "One of the Wilmington Ten, convicted on trumped-up firebombing/conspiracy charges from the 1971 Wilmington, NC unrest. The Black Panther Vol. 18 no.2 (1978). Convictions overturned 1980; group pardoned 2012. [Source: The Black Panther (Black Panther Party newspaper), v18 no.2; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NC", "affiliation": ["Wilmington Ten"]}'

# William Wright (NC)
php artisan prisoner:add '{"name": "William Wright", "first_name": "William", "last_name": "Wright", "description": "One of the Wilmington Ten, convicted on trumped-up firebombing/conspiracy charges from the 1971 Wilmington, NC unrest. The Black Panther Vol. 18 no.2 (1978). Convictions overturned 1980; group pardoned 2012. [Source: The Black Panther (Black Panther Party newspaper), v18 no.2; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NC", "affiliation": ["Wilmington Ten"]}'

# Willie Earl Vereen (NC)
php artisan prisoner:add '{"name": "Willie Earl Vereen", "first_name": "Willie", "last_name": "Vereen", "description": "One of the Wilmington Ten, convicted on trumped-up firebombing/conspiracy charges from the 1971 Wilmington, NC unrest. The Black Panther Vol. 18 no.2 (1978). Convictions overturned 1980; group pardoned 2012. [Source: The Black Panther (Black Panther Party newspaper), v18 no.2; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NC", "affiliation": ["Wilmington Ten"]}'

# ===== GROUP B -- INTERNATIONAL, commented out (9) =====
# NPPC is a U.S. coalition; these anti-colonial/anti-apartheid cases are left commented for review.
# (Additional international mentions -- Namibia SWAPO detainees, other Philippine PPP figures,
#  Rhodesia, Nicaragua's Chamorro -- are catalogued in the dossier but not scripted here.)

# Benigno Aquino
# php artisan prisoner:add '{"name": "Benigno Aquino", "first_name": "Benigno", "last_name": "Aquino", "description": "Leading opponent of the Marcos dictatorship, held as a political prisoner at Ft. Bonifacio under martial law for six years. The Black Panther Vol. 18 no.12 (1978). INTERNATIONAL (Philippines). [Source: The Black Panther (Black Panther Party newspaper), v18 no.12; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]"}'

# Fatima Meer
# php artisan prisoner:add '{"name": "Fatima Meer", "first_name": "Fatima", "last_name": "Meer", "description": "President of the South African Black Women'\''s Federation, detained in 1976 and subsequently banned. The Black Panther Vol. 18 no.2 (1978). INTERNATIONAL (South Africa). [Source: The Black Panther (Black Panther Party newspaper), v18 no.2; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Black Women'\''s Federation"]}'

# Nelson Mandela
# php artisan prisoner:add '{"name": "Nelson Mandela", "first_name": "Nelson", "last_name": "Mandela", "description": "Imprisoned President of the African National Congress, serving a life sentence on Robben Island for anti-apartheid activity; cited among South African political prisoners. The Black Panther Vol. 18 no.2,5 (1978). INTERNATIONAL (South Africa). [Source: The Black Panther (Black Panther Party newspaper), v18 no.2,5; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["African National Congress"]}'

# Ngugi wa Thiong'o
# php artisan prisoner:add '{"name": "Ngugi wa Thiong'\''o", "first_name": "Ngugi", "last_name": "Thiong'\''o", "description": "Kenyan novelist and University of Nairobi literature chair detained without charge under public-security laws after his political play was banned. The Black Panther Vol. 18 no.4 (1978). INTERNATIONAL (Kenya). [Source: The Black Panther (Black Panther Party newspaper), v18 no.4; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]"}'

# Percy Qoboza
# php artisan prisoner:add '{"name": "Percy Qoboza", "first_name": "Percy", "last_name": "Qoboza", "description": "Editor of the banned Black South African newspapers The World and Weekend World, detained without trial in the apartheid regime'\''s Oct. 19, 1977 crackdown. The Black Panther Vol. 18 no.1,7 (1978). INTERNATIONAL (South Africa). [Source: The Black Panther (Black Panther Party newspaper), v18 no.1,7; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]"}'

# Robert Sobukwe
# php artisan prisoner:add '{"name": "Robert Sobukwe", "first_name": "Robert", "last_name": "Sobukwe", "description": "Founder of the Pan Africanist Congress of Azania, jailed after the 1960 Sharpeville anti-pass campaign and held on Robben Island under the special '\''Sobukwe clause'\'' for years, then restricted to Kimberley; died Feb. 27, 1978. The Black Panther Vol. 18 no.7,8 (1978). INTERNATIONAL (South Africa). [Source: The Black Panther (Black Panther Party newspaper), v18 no.7,8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Pan Africanist Congress"]}'

# Saida Menebhi
# php artisan prisoner:add '{"name": "Saida Menebhi", "first_name": "Saida", "last_name": "Menebhi", "description": "Moroccan activist who died in prison during a six-week hunger strike by political prisoners protesting torture and conditions. The Black Panther Vol. 18 no.1 (1978). INTERNATIONAL (Morocco). [Source: The Black Panther (Black Panther Party newspaper), v18 no.1; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]"}'

# Steve Biko
# php artisan prisoner:add '{"name": "Steve Biko", "first_name": "Steve", "last_name": "Biko", "description": "Founder of the South African Black Consciousness Movement, killed in security-police detention on Sept. 12, 1977; an inquest cleared the police. The Black Panther Vol. 18 no.1,2,4,6 (1978). INTERNATIONAL (South Africa). [Source: The Black Panther (Black Panther Party newspaper), v18 no.1,2,4,6; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Black Consciousness Movement"]}'

# Winnie Mandela
# php artisan prisoner:add '{"name": "Winnie Mandela", "first_name": "Winnie", "last_name": "Mandela", "description": "Anti-apartheid leader and Black Women'\''s Federation member, detained in 1976 and banished to Brandfort under a banning order; given suspended sentences for trivial '\''violations.'\'' The Black Panther Vol. 18 no.2,5 (1978). INTERNATIONAL (South Africa). [Source: The Black Panther (Black Panther Party newspaper), v18 no.2,5; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["African National Congress"]}'

