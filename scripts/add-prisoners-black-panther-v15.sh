#!/usr/bin/env bash
# Political-prisoner cases extracted from The Black Panther newspaper, Vol. 15 (1976),
# read page-by-page (all 30 issues, ~1,290 pages) from the marxists.org archive.
# RUN ON THE SERVER after review. Idempotent: prisoner:add de-dupes by name, so any of
# these figures already in the database are simply skipped. Continue-on-error so
# duplicates/failures don't abort the batch:
set +e

# ===== GROUP A -- U.S. political-prisoner cases (79) =====

# Alfredo Lopez (NY)
php artisan prisoner:add '{"name": "Alfredo Lopez", "first_name": "Alfredo", "last_name": "Lopez", "description": "Puerto Rican Socialist Party Central Committee member and July 4th Coalition coordinator threatened with a grand jury subpoena amid stepped-up FBI harassment of Puerto Rican independence activists. The Black Panther Vol. 15 no.8 (1976). [Source: The Black Panther (Black Panther Party newspaper), v15 no.8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NY", "affiliation": ["Puerto Rican Socialist Party"]}'

# Andre Evans (CA)
php artisan prisoner:add '{"name": "Andre Evans", "first_name": "Andre", "last_name": "Evans", "description": "Los Angeles man, brother of a 17-year-old fatally shot by LAPD officers, arrested and charged with conspiring to kill the officers in a case The Black Panther Vol. 15 no.9 (1976) presents as a false, retaliatory prosecution (his friends'\'' charges were dropped). [Source: The Black Panther (Black Panther Party newspaper), v15 no.9; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA"}'

# Andres Figueroa Cordero (PR)
php artisan prisoner:add '{"name": "Andres Figueroa Cordero", "first_name": "Andres", "last_name": "Cordero", "description": "Puerto Rican nationalist imprisoned in federal prison for the 1954 armed protest at the U.S. Capitol; The Black Panther Vol. 15 no.22 (1976) reports he was gravely ill with cancer and that officials denied Lolita Lebron'\''s request to visit him. [Source: The Black Panther (Black Panther Party newspaper), v15 no.22; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "PR", "affiliation": ["Puerto Rican Nationalist Party"]}'

# Ann Shepard (NC)
php artisan prisoner:add '{"name": "Ann Shepard", "first_name": "Ann", "last_name": "Shepard", "description": "White VISTA volunteer convicted as an accessory and sentenced to 10 years as one of the '\''Wilmington 10'\''; The Black Panther Vol. 15 no.30 (1976) reports she remained jailed pending appeal after the state'\''s key witness recanted. [Source: The Black Panther (Black Panther Party newspaper), v15 no.30; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NC"}'

# Anna Mae Aquash (SD)
php artisan prisoner:add '{"name": "Anna Mae Aquash", "first_name": "Anna", "last_name": "Aquash", "description": "American Indian Movement activist repeatedly arrested and, per John Trudell'\''s account in The Black Panther Vol. 15 (1976), threatened by an FBI agent that she would be dead within a year; she was found shot dead in FBI-connected custody near the Pine Ridge Reservation in Feb. 1976. Deceased. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "SD", "affiliation": ["American Indian Movement"]}'

# Ben Chavis (NC)
php artisan prisoner:add '{"name": "Ben Chavis", "first_name": "Ben", "last_name": "Chavis", "description": "Civil-rights leader and lead defendant of the '\''Wilmington 10,'\'' convicted of arson and conspiracy in the 1971 Wilmington, N.C. racial disturbances and sentenced to 34 years; The Black Panther Vol. 15 no.30 (1976) reports the state'\''s sole eyewitness recanted, admitting his testimony was fabricated under coercion, yet the ten remained jailed. [Source: The Black Panther (Black Panther Party newspaper), v15 no.30; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NC"}'

# Blair Anderson (IL)
php artisan prisoner:add '{"name": "Blair Anderson", "first_name": "Blair", "last_name": "Anderson", "description": "Wounded survivor of the Dec. 4, 1969 police raid on Fred Hampton'\''s Chicago apartment; The Black Panther Vol. 15 no.13 (1976) reports the indictments against the raid survivors were dropped after falsified police lab evidence. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "IL", "affiliation": ["Black Panther Party"]}'

# Bob Yellow Bird (NE)
php artisan prisoner:add '{"name": "Bob Yellow Bird", "first_name": "Bob", "last_name": "Bird", "description": "Nebraska state coordinator of the American Indian Movement; The Black Panther Vol. 15 no.30 (1976) reports that after he called police having been choked and maced by a white man, officers instead beat and arrested him and his wife, charging them with malicious destruction of property; he was released on $1,000 bail. [Source: The Black Panther (Black Panther Party newspaper), v15 no.30; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NE", "affiliation": ["American Indian Movement"]}'

# Bobby Seale (CA)
php artisan prisoner:add '{"name": "Bobby Seale", "first_name": "Bobby", "last_name": "Seale", "description": "Co-founder and chairman of the Black Panther Party, jailed in a series of prosecutions including the Chicago conspiracy trial, where he was bound and gagged in court, and the New Haven case; The Black Panther Vol. 15 no.26 (1976) references his imprisonment during the '\''Free Huey'\'' years. [Source: The Black Panther (Black Panther Party newspaper), v15 no.26; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black Panther Party"]}'

# Carl Vincent Henry (DE)
php artisan prisoner:add '{"name": "Carl Vincent Henry", "first_name": "Carl", "last_name": "Henry", "description": "One of the '\''Smyrna 7'\'' found guilty in Sept. 1976 of escape-related charges at the Delaware Correctional Center; The Black Panther Vol. 15 no.29 (1976) reports the defense committee'\''s charge of guard brutality, isolation and denial of an adequate defense. [Source: The Black Panther (Black Panther Party newspaper), v15 no.29; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "aka": "Nihe Abdul Rahim", "state": "DE"}'

# Charles Wakefield (SC)
php artisan prisoner:add '{"name": "Charles Wakefield", "first_name": "Charles", "last_name": "Wakefield", "description": "Young Black man in Greenville, South Carolina, indicted nine months after the crime and convicted of murder — facing a mandatory death sentence — on the testimony of two jail inmates offered reduced sentences, though he was never positively identified and no weapon was introduced; The Black Panther Vol. 15 no.15 (1976) presents the case as a frame-up. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "SC"}'

# Clarence Norris (AL)
php artisan prisoner:add '{"name": "Clarence Norris", "first_name": "Clarence", "last_name": "Norris", "description": "The last surviving '\''Scottsboro Boy,'\'' one of nine Black youths falsely convicted of raping two white women on an Alabama freight train in 1931 (he spent 15 years imprisoned, five on death row); The Black Panther Vol. 15 (1976) reports he won a full pardon from Alabama after 45 years. [Source: The Black Panther (Black Panther Party newspaper), v15 no.27,29; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "aka": "Willie Norris", "state": "AL"}'

# Connie Wilson (CA)
php artisan prisoner:add '{"name": "Connie Wilson", "first_name": "Connie", "last_name": "Wilson", "description": "A 19-year-old Black high-school student in San Francisco'\''s Hunters Point put on trial for felony voter-registration fraud in what her attorney called a discriminatory frame-up, singled out by the DA as the first target of a '\''voter fraud crackdown.'\'' The Black Panther Vol. 15 no.8 (1976). [Source: The Black Panther (Black Panther Party newspaper), v15 no.8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA"}'

# Craemen Gethers (MA)
php artisan prisoner:add '{"name": "Craemen Gethers", "first_name": "Craemen", "last_name": "Gethers", "description": "Black University of Massachusetts-Amherst student convicted of a 1974 McDonald'\''s robbery and sentenced to 8-12 years at Norfolk prison despite witnesses placing him on crutches at the time; The Black Panther Vol. 15 no.25 (1976) reports his bid for a new trial was denied. [Source: The Black Panther (Black Panther Party newspaper), v15 no.25; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "MA"}'

# Curtis Jordan (DE)
php artisan prisoner:add '{"name": "Curtis Jordan", "first_name": "Curtis", "last_name": "Jordan", "description": "One of the '\''Smyrna 7'\'' at the Delaware Correctional Center who, per the defense committee in The Black Panther Vol. 15 no.29 (1976), were forced to plead guilty to escape charges after being denied the right to present their defense. [Source: The Black Panther (Black Panther Party newspaper), v15 no.29; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "DE"}'

# Darrelle Butler (SD)
php artisan prisoner:add '{"name": "Darrelle Butler", "first_name": "Darrelle", "last_name": "Butler", "description": "American Indian Movement member ('\''Dino'\'') tried with Robert Robideau for the deaths of two FBI agents in the June 1975 Pine Ridge shootout; The Black Panther Vol. 15 no.11 (1976). Both were acquitted in 1976 after the jury accepted a self-defense argument. [Source: The Black Panther (Black Panther Party newspaper), v15 no.11; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "SD", "affiliation": ["American Indian Movement"]}'

# Darwin Lance Brown (MI)
php artisan prisoner:add '{"name": "Darwin Lance Brown", "first_name": "Darwin", "last_name": "Brown", "description": "Detroit man convicted of first-degree murder in 1966 at age 19 and sentenced to life; The Black Panther Vol. 15 no.26 (1976) reports prosecution witnesses had since admitted they were coerced into lying, yet Michigan refused a new trial. [Source: The Black Panther (Black Panther Party newspaper), v15 no.26; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "MI"}'

# David Johnson (CA)
php artisan prisoner:add '{"name": "David Johnson", "first_name": "David", "last_name": "Johnson", "description": "One of the '\''San Quentin 6'\'' tried over the Aug. 21, 1971 Adjustment Center events; The Black Panther Vol. 15 (1976) reports his public defender argued the only conspiracy was by prison guards who lied to cover up what happened. He was acquitted. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black Panther Party"]}'

# Delbert Tibbs (FL)
php artisan prisoner:add '{"name": "Delbert Tibbs", "first_name": "Delbert", "last_name": "Tibbs", "description": "Black poet, theological student and activist convicted by an all-white jury of a rape and murder near Fort Myers; The Black Panther Vol. 15 no.19 (1976) reports the Florida Supreme Court removed him from death row and ordered a new trial. The charges were ultimately dropped. [Source: The Black Panther (Black Panther Party newspaper), v15 no.19; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "FL"}'

# Dennis Banks (OR)
php artisan prisoner:add '{"name": "Dennis Banks", "first_name": "Dennis", "last_name": "Banks", "description": "American Indian Movement co-founder and defendant in the '\''Loud Hawk'\'' case; The Black Panther Vol. 15 (1976) reports a pretrial victory in which an Oregon judge ruled the dynamite evidence inadmissible, and covers the 1975 Oregon firearms/explosives charges he called '\''another frame-up.'\'' [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "OR", "affiliation": ["American Indian Movement"]}'

# Donald Thigpen (AL)
php artisan prisoner:add '{"name": "Donald Thigpen", "first_name": "Donald", "last_name": "Thigpen", "description": "Alabama prisoner sentenced to death in Aug. 1976 for the death of a white man during an escape attempt at Holman Prison; writing from death row in The Black Panther Vol. 15 no.24 (1976), he called it a state frame-up, maintained his innocence and said his co-defendant was coerced into a plea. His death sentence was on appeal. [Source: The Black Panther (Black Panther Party newspaper), v15 no.24; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "aka": "Cacanja Sange", "state": "AL"}'

# Donnell Moore (NY)
php artisan prisoner:add '{"name": "Donnell Moore", "first_name": "Donnell", "last_name": "Moore", "description": "Former Black Panther Party member and UC Berkeley student who spent about four months in New York'\''s Rikers Island jail after being charged with the murder of a bar co-manager; The Black Panther Vol. 15 (1976) reports he acted in self-defense, was framed, and that a defense committee formed around his case. [Source: The Black Panther (Black Panther Party newspaper), v15 no.17,29; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NY", "affiliation": ["Black Panther Party"]}'

# Earl Brown (MA)
php artisan prisoner:add '{"name": "Earl Brown", "first_name": "Earl", "last_name": "Brown", "description": "Black University of Massachusetts football player convicted in the same 1974 Amherst robbery frame-up as Craemen Gethers; The Black Panther Vol. 15 no.25 (1976) reports a community defense movement won him a reduced 3-5 year sentence with work-release. [Source: The Black Panther (Black Panther Party newspaper), v15 no.25; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "MA"}'

# Earl Gaither (IL)
php artisan prisoner:add '{"name": "Earl Gaither", "first_name": "Earl", "last_name": "Gaither", "description": "Prisoner in the Marion federal penitentiary control unit (one of the '\''Marion Brothers'\''), among Nation of Islam inmates beaten by guards; The Black Panther Vol. 15 no.8 (1976) reports the May 1976 hunger strike and class-action suit against the control unit'\''s behavior-modification regime. [Source: The Black Panther (Black Panther Party newspaper), v15 no.8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "IL", "affiliation": ["Prison movement"]}'

# Elmer Pratt (CA)
php artisan prisoner:add '{"name": "Elmer Pratt", "first_name": "Elmer", "last_name": "Pratt", "description": "Los Angeles Black Panther Party leader convicted of a 1968 Santa Monica murder; The Black Panther Vol. 15 (1976) refers to the conviction, which the Party regarded as an FBI COINTELPRO frame-up. He was imprisoned 27 years before the conviction was vacated in 1997. [Source: The Black Panther (Black Panther Party newspaper), v15 no.25,26; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "aka": "Geronimo Ji-Jaga Pratt", "state": "CA", "affiliation": ["Black Panther Party"]}'

# Ericka Huggins (CT)
php artisan prisoner:add '{"name": "Ericka Huggins", "first_name": "Ericka", "last_name": "Huggins", "description": "Leading Black Panther Party member held for over two years in administrative segregation at Niantic Prison for Women, Connecticut, on New Haven conspiracy charges of which she was ultimately acquitted; The Black Panther Vol. 15 no.3 (1976). [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CT", "affiliation": ["Black Panther Party"]}'

# Faye Brown (NC)
php artisan prisoner:add '{"name": "Faye Brown", "first_name": "Faye", "last_name": "Brown", "description": "22-year-old Black woman on North Carolina'\''s death row for the alleged killing of a state trooper, condemned along with her co-defendants though only one shot was fired; The Black Panther Vol. 15 no.4 (1976) presents her as unjustly sentenced to death. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NC"}'

# Fleeta Drumgo (CA)
php artisan prisoner:add '{"name": "Fleeta Drumgo", "first_name": "Fleeta", "last_name": "Drumgo", "description": "Former '\''Soledad Brother'\'' and one of the final defendants in the '\''San Quentin 6'\'' trial over the Aug. 21, 1971 Adjustment Center events; The Black Panther Vol. 15 (1976) reports his attorney Michael Dufficy attacked the sole witness against him as a '\''willful liar.'\'' He was acquitted. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black Panther Party"]}'

# Fred Bell (TX)
php artisan prisoner:add '{"name": "Fred Bell", "first_name": "Fred", "last_name": "Bell", "description": "Coordinator of the Dallas chapter of the Black Panther Party, target of what The Black Panther Vol. 15 no.5 (1976) describes as stepped-up police harassment including a false ex-felon-in-possession-of-a-weapon charge and manipulated bail. [Source: The Black Panther (Black Panther Party newspaper), v15 no.5; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "TX", "affiliation": ["Black Panther Party"]}'

# Fred Hampton (IL)
php artisan prisoner:add '{"name": "Fred Hampton", "first_name": "Fred", "last_name": "Hampton", "description": "Chairman of the Illinois Black Panther Party and an FBI COINTELPRO target, killed in a pre-dawn police raid on Dec. 4, 1969; The Black Panther Vol. 15 no.7 (1976) covers the multimillion-dollar civil-rights trial over the conspiracy to deprive him and Mark Clark of their civil rights. [Source: The Black Panther (Black Panther Party newspaper), v15 no.7; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "IL", "affiliation": ["Black Panther Party"]}'

# Gary Tyler (LA)
php artisan prisoner:add '{"name": "Gary Tyler", "first_name": "Gary", "last_name": "Tyler", "description": "Black 16-to-17-year-old, one of the youngest people on U.S. death row, singled out from a busload of Black students and convicted by an all-white jury of a 1974 shooting near Destrehan High School; The Black Panther Vol. 15 (1976) reports his new-trial fight after a key witness recanted. Held at Angola (Louisiana State Penitentiary). [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "LA"}'

# Gary Watson (DE)
php artisan prisoner:add '{"name": "Gary Watson", "first_name": "Gary", "last_name": "Watson", "description": "One of the '\''Smyrna 7'\'' at the Delaware Correctional Center who, the defense committee told The Black Panther Vol. 15 no.29 (1976), were forced to plead guilty to escape charges after being told they would not be permitted to present their defense. [Source: The Black Panther (Black Panther Party newspaper), v15 no.29; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "DE"}'

# George Jackson (CA)
php artisan prisoner:add '{"name": "George Jackson", "first_name": "George", "last_name": "Jackson", "description": "Field Marshal of the Black Panther Party, Soledad Brother and revolutionary author, killed Aug. 21, 1971 in San Quentin'\''s Adjustment Center; The Black Panther Vol. 15 (1976), reporting the San Quentin 6 trial, presents his death as a state assassination and cites ex-agent Louis Tackwood'\''s account of a plot to kill him. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black Panther Party"]}'

# Glenn Diamond (AL)
php artisan prisoner:add '{"name": "Glenn Diamond", "first_name": "Glenn", "last_name": "Diamond", "description": "Mobile, Alabama activist (also known as Casmarah Mani), a former member of the prison group Inmates For Action, who The Black Panther Vol. 15 no.5,6 (1976) reports was beaten in an attempted police lynching (an officer tried to hang him from a tree) and then charged with robbery and held at Mt. Meigs Prison. [Source: The Black Panther (Black Panther Party newspaper), v15 no.5,6; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "AL", "affiliation": ["Inmates For Action"]}'

# H. Rap Brown (NY)
php artisan prisoner:add '{"name": "H. Rap Brown", "first_name": "H.", "last_name": "Brown", "description": "Former SNCC chairman imprisoned at Green Haven on a 1971 robbery and assault conviction; The Black Panther Vol. 15 (1976) reports he won parole and that a federal appeals court overturned his federal gun conviction after the trial judge was overheard making a racist remark. [Source: The Black Panther (Black Panther Party newspaper), v15 no.25,29; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "aka": "Jamil Abdullah Al-Amin", "state": "NY", "affiliation": ["SNCC"]}'

# Howard Ay Gibbs (NJ)
php artisan prisoner:add '{"name": "Howard Ay Gibbs", "first_name": "Howard", "last_name": "Gibbs", "description": "Camden, New Jersey Black community anti-drug organizer serving a maximum 80-year sentence at Trenton State Prison after an all-white jury convicted him of murdering a white man; The Black Panther Vol. 15 no.27 (1976) reports the Howard Ay Gibbs Defense Committee'\''s charge that the conviction rested on forged testimony and destroyed transcripts. [Source: The Black Panther (Black Panther Party newspaper), v15 no.27; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NJ"}'

# Hugo Pinell (CA)
php artisan prisoner:add '{"name": "Hugo Pinell", "first_name": "Hugo", "last_name": "Pinell", "description": "One of the '\''San Quentin 6'\''; The Black Panther Vol. 15 (1976) reports his testimony that he saw a guard pull a gun on George Jackson on Aug. 21, 1971. Acquitted of murder in the Adjustment Center case, he remained imprisoned and became one of California'\''s longest-held prisoners in solitary. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black Panther Party"]}'

# J.B. Johnson (MO)
php artisan prisoner:add '{"name": "J.B. Johnson", "first_name": "J.B.", "last_name": "Johnson", "description": "Black St. Louis man facing a life term for the murder of a policeman during a 1970 robbery, in a case his supporters and attorney William Kunstler called a frame-up built on suppressed evidence; The Black Panther Vol. 15 no.8 (1976) reports the National Committee To Free J.B. Johnson. [Source: The Black Panther (Black Panther Party newspaper), v15 no.8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "MO"}'

# James Dixon (IL)
php artisan prisoner:add '{"name": "James Dixon", "first_name": "James", "last_name": "Dixon", "description": "Chicago man jailed 362 days in the Cook County Jail after being convicted of the attempted murder of a police sergeant who, evidence later showed, had shot himself with an illegal '\''pen gun'\''; The Black Panther Vol. 15 no.23 (1976) reports the charges were dropped in Aug. 1976 after an anonymous tip. [Source: The Black Panther (Black Panther Party newspaper), v15 no.23; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "IL"}'

# James Henderson (MI)
php artisan prisoner:add '{"name": "James Henderson", "first_name": "James", "last_name": "Henderson", "description": "One of the '\''Livernois 3,'\'' framed on a charge of fatally beating a white man during the July 1975 Detroit rebellion; The Black Panther Vol. 15 no.3 (1976) reports his second trial ended in a hung jury and that the prosecution admitted witnesses had been coerced. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "MI"}'

# James Jones (AL)
php artisan prisoner:add '{"name": "James Jones", "first_name": "James", "last_name": "Jones", "description": "Mobile, Alabama activist (also known as Sekou Lumpen), a former Inmates For Action member and leader of the People'\''s Community Hall, beaten by police alongside Glenn Diamond and accused of a robbery in a case The Black Panther Vol. 15 no.5,6 (1976) calls a frame-up. [Source: The Black Panther (Black Panther Party newspaper), v15 no.5,6; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "AL", "affiliation": ["Inmates For Action"]}'

# Jimmy Eagle (SD)
php artisan prisoner:add '{"name": "Jimmy Eagle", "first_name": "Jimmy", "last_name": "Eagle", "description": "Young Oglala man indicted with Leonard Peltier in the June 1975 Pine Ridge FBI-agent deaths case; The Black Panther Vol. 15 no.11 (1976). The charges against him were later dropped. [Source: The Black Panther (Black Panther Party newspaper), v15 no.11; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "SD", "affiliation": ["American Indian Movement"]}'

# Jo An Yellow Bird (NE)
php artisan prisoner:add '{"name": "Jo An Yellow Bird", "first_name": "Jo", "last_name": "Bird", "description": "Wife of Nebraska AIM coordinator Bob Yellow Bird; The Black Panther Vol. 15 no.30 (1976) reports she was kicked in the stomach by an arresting officer, causing her to lose her unborn child, and was charged with malicious destruction of property. [Source: The Black Panther (Black Panther Party newspaper), v15 no.30; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NE", "affiliation": ["American Indian Movement"]}'

# John Artis (NJ)
php artisan prisoner:add '{"name": "John Artis", "first_name": "John", "last_name": "Artis", "description": "Co-defendant of Rubin '\''Hurricane'\'' Carter, convicted in 1967 of the Paterson tavern shooting on the testimony of witnesses who later admitted lying; The Black Panther Vol. 15 no.3 (1976) reports the retrial fight. Cleared alongside Carter. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NJ"}'

# Johnny Larry Spain (CA)
php artisan prisoner:add '{"name": "Johnny Larry Spain", "first_name": "Johnny", "last_name": "Spain", "description": "Black Panther Party member and one of the '\''San Quentin 6,'\'' tried for the events of Aug. 21, 1971 in San Quentin'\''s Adjustment Center on the day George Jackson was killed; The Black Panther Vol. 15 (1976) covers the roughly 16-month trial, in which he was defended by attorney Charles Garry. Spain alone among the six was convicted of murder (a conviction later overturned in 1988). [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black Panther Party"]}'

# Johnny Ross (LA)
php artisan prisoner:add '{"name": "Johnny Ross", "first_name": "Johnny", "last_name": "Ross", "description": "Black teenager, once the youngest person on Louisiana'\''s death row, sentenced to die after a one-day trial for allegedly raping a white woman; The Black Panther Vol. 15 no.25 (1976) reports he was beaten and forced to sign a confession he could not read, and that the Southern Poverty Law Center was seeking his release. [Source: The Black Panther (Black Panther Party newspaper), v15 no.25; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "LA"}'

# Kamook Banks (OR)
php artisan prisoner:add '{"name": "Kamook Banks", "first_name": "Kamook", "last_name": "Banks", "description": "American Indian Movement member (wife of Dennis Banks) cleared with the other '\''Loud Hawk'\'' defendants of the 1975 Oregon firearms/explosives charges. The Black Panther Vol. 15 no.6 (1976) renders the name '\''LaMook Banks.'\'' [Source: The Black Panther (Black Panther Party newspaper), v15 no.6; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "OR", "affiliation": ["American Indian Movement"]}'

# Kenneth Loud Hawk (OR)
php artisan prisoner:add '{"name": "Kenneth Loud Hawk", "first_name": "Kenneth", "last_name": "Hawk", "description": "American Indian Movement member and defendant in the long-running '\''Loud Hawk'\'' case; The Black Panther Vol. 15 no.6 (1976) reports a federal judge in Oregon dismissed the firearms/explosives charges against him, Dennis Banks, Russell Redner and Kamook Banks for prosecutorial delay. [Source: The Black Panther (Black Panther Party newspaper), v15 no.6; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "OR", "affiliation": ["American Indian Movement"]}'

# Lee Otis Johnson (TX)
php artisan prisoner:add '{"name": "Lee Otis Johnson", "first_name": "Lee", "last_name": "Johnson", "description": "Former SNCC field secretary serving a 17-year sentence on what The Black Panther Vol. 15 no.3 (1976) calls a burglary frame-up (he had earlier drawn a 30-year term for giving away a marijuana cigarette); the paper reports he led 14 inmates at Ellis Prison, Huntsville, in a failed legal bid to force Texas to investigate prison medical abuse. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "TX", "affiliation": ["SNCC"]}'

# Leonard Peltier (SD)
php artisan prisoner:add '{"name": "Leonard Peltier", "first_name": "Leonard", "last_name": "Peltier", "description": "American Indian Movement member charged in the June 26, 1975 deaths of two FBI agents at the Pine Ridge Reservation; The Black Panther Vol. 15 (1976) reports his arrest and his fight against extradition from Canada, where he was held at Okalla Prison seeking political-refugee status. He was later convicted and became one of the most prominent U.S. political prisoners. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "SD", "affiliation": ["American Indian Movement"]}'

# Lolita Lebron (PR)
php artisan prisoner:add '{"name": "Lolita Lebron", "first_name": "Lolita", "last_name": "Lebron", "description": "Puerto Rican nationalist imprisoned more than 20 years for the 1954 armed protest at the U.S. Capitol; The Black Panther Vol. 15 no.22 (1976) reports federal officials refused to let her visit her gravely ill fellow nationalist Andres Figueroa Cordero. [Source: The Black Panther (Black Panther Party newspaper), v15 no.22; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "PR", "affiliation": ["Puerto Rican Nationalist Party"]}'

# Lorenzo Kom'boa Ervin (GA)
php artisan prisoner:add '{"name": "Lorenzo Kom'\''boa Ervin", "first_name": "Lorenzo", "last_name": "Ervin", "description": "Black activist and former SNCC worker who hijacked a plane to Cuba in 1969 to escape FBI capture; The Black Panther Vol. 15 no.22 (1976) reports he was seized abroad, drugged, returned to the U.S., tried while medicated and given two life sentences at the Atlanta federal penitentiary, and was demanding a new trial. [Source: The Black Panther (Black Panther Party newspaper), v15 no.22; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "GA", "affiliation": ["SNCC"]}'

# Luis Talamantez (CA)
php artisan prisoner:add '{"name": "Luis Talamantez", "first_name": "Luis", "last_name": "Talamantez", "description": "Mexican-born member of the '\''San Quentin 6,'\'' tried over the Aug. 21, 1971 Adjustment Center events; The Black Panther Vol. 15 no.15 (1976) reports the only evidence against him was a guard'\''s claim of hearing Spanish spoken. He was acquitted. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA"}'

# Lureida Torres (NY)
php artisan prisoner:add '{"name": "Lureida Torres", "first_name": "Lureida", "last_name": "Torres", "description": "Puerto Rican Socialist Party member found in contempt of court and facing jail for refusing to answer a federal grand jury The Black Panther Vol. 15 no.8 (1976) describes as a '\''fishing expedition'\'' against the Puerto Rican independence movement under the guise of investigating FALN bombings. [Source: The Black Panther (Black Panther Party newspaper), v15 no.8; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NY", "affiliation": ["Puerto Rican Socialist Party"]}'

# Mark Clark (IL)
php artisan prisoner:add '{"name": "Mark Clark", "first_name": "Mark", "last_name": "Clark", "description": "Peoria-area Illinois Black Panther Party leader killed alongside Fred Hampton in the Dec. 4, 1969 police raid in Chicago, a COINTELPRO operation; The Black Panther Vol. 15 no.7 (1976). [Source: The Black Panther (Black Panther Party newspaper), v15 no.7; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "IL", "affiliation": ["Black Panther Party"]}'

# Mark Cook (WA)
php artisan prisoner:add '{"name": "Mark Cook", "first_name": "Mark", "last_name": "Cook", "description": "Black prison activist convicted by an all-white jury in Seattle over a Jan. 1976 armed robbery of a Tukwila bank and the escape of a George Jackson Brigade member; The Black Panther Vol. 15 no.13 (1976) reports the frame-up, including testimony that Seattle police offered the chief witness $20,000 to convict him. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "WA"}'

# Michael Sturdevant (WI)
php artisan prisoner:add '{"name": "Michael Sturdevant", "first_name": "Michael", "last_name": "Sturdevant", "description": "Leader of the Menominee Warrior Society convicted by an all-white jury as the alleged leader of the Jan. 1975 occupation of the vacant Alexian Brothers novitiate at Gresham, Wisconsin (an action seeking a treaty hearing that drew 2,000+ National Guard); The Black Panther Vol. 15 no.4 (1976). [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "WI", "affiliation": ["Menominee Warrior Society"]}'

# Nate Saunsoci (NE)
php artisan prisoner:add '{"name": "Nate Saunsoci", "first_name": "Nate", "last_name": "Saunsoci", "description": "Native American who, per The Black Panther Vol. 15 no.5 (1976), had spent seven years in juvenile and adult institutions in Nebraska without ever being tried, over a $600 burglary allegedly committed when he was 10; the ACLU sued on his behalf. [Source: The Black Panther (Black Panther Party newspaper), v15 no.5; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NE", "affiliation": ["Native American movement"]}'

# Ola Mae Davis (WI)
php artisan prisoner:add '{"name": "Ola Mae Davis", "first_name": "Ola", "last_name": "Davis", "description": "Black Milwaukee woman convicted of perjury by a circuit-court jury for her inquest testimony that a patrolman shot 16-year-old Jerry Brookshire in the back (Dec. 24, 1974), after she challenged the official '\''accidental'\'' ruling; The Black Panther Vol. 15 no.3 (1976) presents the prosecution as retaliation. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "WI"}'

# Oscar Jordan (DE)
php artisan prisoner:add '{"name": "Oscar Jordan", "first_name": "Oscar", "last_name": "Jordan", "description": "One of the '\''Smyrna 7'\'' brothers at the Delaware Correctional Center who, per the defense committee in The Black Panther Vol. 15 no.29 (1976), were forced to plead guilty to escape charges amid what it called racist and inhumane treatment. [Source: The Black Panther (Black Panther Party newspaper), v15 no.29; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "DE"}'

# Pedro Albizu Campos (PR)
php artisan prisoner:add '{"name": "Pedro Albizu Campos", "first_name": "Pedro", "last_name": "Campos", "description": "Leader of the Puerto Rican Nationalist Party, imprisoned for decades (and subjected to radiation experiments) for the independence struggle; honored in The Black Panther Vol. 15 no.7 (1976) as the Puerto Rican independence patriot after whom a New York school was renamed. [Source: The Black Panther (Black Panther Party newspaper), v15 no.7; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "PR", "affiliation": ["Puerto Rican independence movement"]}'

# Ramon Chacon (TX)
php artisan prisoner:add '{"name": "Ramon Chacon", "first_name": "Ramon", "last_name": "Chacon", "description": "Chicano activist known for organizing for the rights of Mexican people in Texas'\'' Rio Grande Valley, arrested on what The Black Panther Vol. 15 no.2 (1976) calls false gun-smuggling charges and detained at the Topo Chico prison in Mexico, seen as a joint U.S.-Mexican effort to entrap him. [Source: The Black Panther (Black Panther Party newspaper), v15 no.2; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "TX", "affiliation": ["Chicano movement"]}'

# Randolph Jennings (NC)
php artisan prisoner:add '{"name": "Randolph Jennings", "first_name": "Randolph", "last_name": "Jennings", "description": "Black Panther Party member and one of the '\''High Point 4,'\'' who ran a Free Breakfast program targeted by the FBI'\''s COINTELPRO before a 1971 police raid; The Black Panther Vol. 15 no.30 (1976) reports he was released after serving more than six years of a 7-to-10-year sentence on a trumped-up assault-on-an-officer charge. [Source: The Black Panther (Black Panther Party newspaper), v15 no.30; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "NC", "affiliation": ["Black Panther Party"]}'

# Raymond Peoples (MI)
php artisan prisoner:add '{"name": "Raymond Peoples", "first_name": "Raymond", "last_name": "Peoples", "description": "One of the '\''Livernois 3,'\'' framed for the death of Marian Pyszko during the July 1975 Detroit rebellion; The Black Panther Vol. 15 no.3 (1976) reports his second murder trial ended in a hung jury. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "MI"}'

# Ricardo Chavez-Ortiz (CA)
php artisan prisoner:add '{"name": "Ricardo Chavez-Ortiz", "first_name": "Ricardo", "last_name": "Chavez-Ortiz", "description": "Mexican man sentenced to 20 years for a 1972 political airline hijacking in which he took no ransom and asked only to broadcast the injustices facing minorities; The Black Panther Vol. 15 no.13 (1976) reports the Committee to Free Ricardo Chavez-Ortiz and a motion to modify his sentence and release him. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA"}'

# Robert Heard (CA)
php artisan prisoner:add '{"name": "Robert Heard", "first_name": "Robert", "last_name": "Heard", "description": "Black Panther Party member reported convicted in 1975 on false charges arising from the local and federal police harassment of Huey Newton and the BPP in 1974; The Black Panther Vol. 15 no.27 (1976). [Source: The Black Panther (Black Panther Party newspaper), v15 no.27; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black Panther Party"]}'

# Robert Robideau (SD)
php artisan prisoner:add '{"name": "Robert Robideau", "first_name": "Robert", "last_name": "Robideau", "description": "American Indian Movement member tried in the June 1975 shootout on the Pine Ridge Reservation in which two FBI agents died; The Black Panther Vol. 15 no.11 (1976) covers the federal trial (with Darrelle Butler) in which the government sought the death penalty. He and Butler were acquitted on self-defense grounds in 1976. [Source: The Black Panther (Black Panther Party newspaper), v15 no.11; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "SD", "affiliation": ["American Indian Movement"]}'

# Ronald Jordan (MI)
php artisan prisoner:add '{"name": "Ronald Jordan", "first_name": "Ronald", "last_name": "Jordan", "description": "One of the '\''Livernois 3,'\'' framed for the death of Marian Pyszko during the July 1975 Detroit rebellion; The Black Panther Vol. 15 no.3 (1976) reports his second murder trial ended in a hung jury. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "MI"}'

# Ronald Payne (DE)
php artisan prisoner:add '{"name": "Ronald Payne", "first_name": "Ronald", "last_name": "Payne", "description": "One of the '\''Smyrna 7,'\'' Black prisoners at the Delaware Correctional Center prosecuted over a May 1976 escape attempt; The Black Panther Vol. 15 no.29 (1976) reports the defense committee'\''s charge that they were subjected to guard brutality and racist convictions and denied a defense that a white co-defendant was allowed. [Source: The Black Panther (Black Panther Party newspaper), v15 no.29; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "aka": "Tayari", "state": "DE"}'

# Ronald Satchel (IL)
php artisan prisoner:add '{"name": "Ronald Satchel", "first_name": "Ronald", "last_name": "Satchel", "description": "Illinois Black Panther Party member shot four times surviving the Dec. 4, 1969 police raid on Fred Hampton'\''s apartment; The Black Panther Vol. 15 no.13 (1976) reports the attempted-murder and weapons indictments against him were dropped after police lab evidence was shown to be falsified, and he became a plaintiff in the $47.7 million civil-rights suit. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "aka": "Ronald '\''Doc'\'' Satchel", "state": "IL", "affiliation": ["Black Panther Party"]}'

# Rubin Carter (NJ)
php artisan prisoner:add '{"name": "Rubin Carter", "first_name": "Rubin", "last_name": "Carter", "description": "Former middleweight boxer convicted of a 1966 Paterson tavern shooting; The Black Panther Vol. 15 (1976) reports the retrial fight after prosecution witnesses admitted in 1974 that they had lied. His conviction was ultimately overturned in 1985. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "aka": "Rubin '\''Hurricane'\'' Carter", "state": "NJ"}'

# Ruchell Magee (CA)
php artisan prisoner:add '{"name": "Ruchell Magee", "first_name": "Ruchell", "last_name": "Magee", "description": "Imprisoned survivor of the Aug. 7, 1970 Marin County Courthouse escape attempt led by Jonathan Jackson; The Black Panther Vol. 15 no.3 (1976). A jailhouse lawyer, he became a long-held political prisoner and cause célèbre of the prison movement. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA"}'

# Russell Means (SD)
php artisan prisoner:add '{"name": "Russell Means", "first_name": "Russell", "last_name": "Means", "description": "American Indian Movement leader who faced a string of prosecutions after the 1973 Wounded Knee occupation; The Black Panther Vol. 15 no.19 (1976) reports a Rapid City jury acquitted him of the most serious charge in a 1975 barroom killing. [Source: The Black Panther (Black Panther Party newspaper), v15 no.19; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "SD", "affiliation": ["American Indian Movement"]}'

# Russell Redner (OR)
php artisan prisoner:add '{"name": "Russell Redner", "first_name": "Russell", "last_name": "Redner", "description": "American Indian Movement member cleared, with Kenneth Loud Hawk and Dennis Banks, of 1975 Oregon firearms and explosives charges dismissed for the prosecution'\''s failure to present a case. The Black Panther Vol. 15 no.6 (1976). [Source: The Black Panther (Black Panther Party newspaper), v15 no.6; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "OR", "affiliation": ["American Indian Movement"]}'

# Sam Bell (MA)
php artisan prisoner:add '{"name": "Sam Bell", "first_name": "Sam", "last_name": "Bell", "description": "Black man imprisoned at the Deer Island House of Correction for defending himself and his baby daughter during a wave of white racist attacks on Black families in East Boston; The Black Panther Vol. 15 no.17 (1976) reports a pattern of police jailing the Black victims rather than their attackers. [Source: The Black Panther (Black Panther Party newspaper), v15 no.17; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "MA"}'

# Sterling Hobbs Fatir (DE)
php artisan prisoner:add '{"name": "Sterling Hobbs Fatir", "first_name": "Sterling", "last_name": "Fatir", "description": "One of the '\''Smyrna 7'\'' at the Delaware Correctional Center who, per the defense committee in The Black Panther Vol. 15 no.29 (1976), were forced to plead guilty to escape charges after being denied the right to present their defense. [Source: The Black Panther (Black Panther Party newspaper), v15 no.29; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "DE"}'

# Verlina Brewer (IL)
php artisan prisoner:add '{"name": "Verlina Brewer", "first_name": "Verlina", "last_name": "Brewer", "description": "Wounded survivor of the Dec. 4, 1969 police raid on Fred Hampton'\''s Chicago apartment; The Black Panther Vol. 15 no.13 (1976) reports the indictments against the raid survivors were dropped after the police lab evidence was found to be falsified. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "IL", "affiliation": ["Black Panther Party"]}'

# Wilbur Shabazz (DE)
php artisan prisoner:add '{"name": "Wilbur Shabazz", "first_name": "Wilbur", "last_name": "Shabazz", "description": "One of the '\''Smyrna 7'\'' convicted of escape-related charges at the Delaware Correctional Center; The Black Panther Vol. 15 no.29 (1976) reports the defense committee'\''s charge that the attacks on him were tied to his ideology and politics. [Source: The Black Panther (Black Panther Party newspaper), v15 no.29; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "DE"}'

# Willie Tate (CA)
php artisan prisoner:add '{"name": "Willie Tate", "first_name": "Willie", "last_name": "Tate", "description": "One of the '\''San Quentin 6'\'' tried for the Aug. 21, 1971 Adjustment Center events surrounding George Jackson'\''s death; The Black Panther Vol. 15 (1976). He was acquitted of the assault and conspiracy charges. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "state": "CA", "affiliation": ["Black Panther Party"]}'

# ===== GROUP B -- INTERNATIONAL, commented out (25) =====
# NPPC is a U.S. (National) coalition; these foreign cases (South Africa, Rhodesia/Zimbabwe,
# Chile, Argentina, Palestine, Northern Ireland) are left commented for you to include or skip.

# Ahmed Hamzeh (Palestine)
# php artisan prisoner:add '{"name": "Ahmed Hamzeh", "first_name": "Ahmed", "last_name": "Hamzeh", "description": "Leading member of the Palestinian National Front and Hebron mayoral candidate, expelled from the occupied West Bank by the Israeli government on the eve of the 1976 elections; The Black Panther Vol. 15 no.18 (1976). [Source: The Black Panther (Black Panther Party newspaper), v15 no.18; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Palestinian National Front"]}'

# Alan Hendrickse (South Africa)
# php artisan prisoner:add '{"name": "Alan Hendrickse", "first_name": "Alan", "last_name": "Hendrickse", "description": "Leading '\''Colored'\'' South African political figure detained without charge for nearly a month; The Black Panther Vol. 15 no.25 (1976). [Source: The Black Panther (Black Panther Party newspaper), v15 no.25; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]"}'

# Aubrey Mokoape (International)
# php artisan prisoner:add '{"name": "Aubrey Mokoape", "first_name": "Aubrey", "last_name": "Mokoape", "description": "One of nine South African Students'\'' Organization (SASO) members tried under the Terrorism Act at the Pretoria Supreme Court, arrested for organizing a commemoration of Sharpeville Day; The Black Panther Vol. 15 no.13 (1976). [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["South African Students'\'' Organization"]}'

# David Rabkin (South Africa)
# php artisan prisoner:add '{"name": "David Rabkin", "first_name": "David", "last_name": "Rabkin", "description": "White South African journalist charged under the Terrorism and Internal Security Acts for producing pamphlets for the banned ANC, facing a mandatory five-year sentence; The Black Panther Vol. 15 no.25 (1976). [Source: The Black Panther (Black Panther Party newspaper), v15 no.25; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["African National Congress"]}'

# Donal Lamont (Rhodesia)
# php artisan prisoner:add '{"name": "Donal Lamont", "first_name": "Donal", "last_name": "Lamont", "description": "Roman Catholic bishop and outspoken critic of Rhodesia'\''s white-minority government, charged under the Law and Order Maintenance Act with failing to report the presence of Black liberation forces and facing a possible death sentence; The Black Panther Vol. 15 no.21 (1976). [Source: The Black Panther (Black Panther Party newspaper), v15 no.21; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]"}'

# Dumisani Mbatha (South Africa)
# php artisan prisoner:add '{"name": "Dumisani Mbatha", "first_name": "Dumisani", "last_name": "Mbatha", "description": "16-year-old arrested after a Soweto student protest march who died in jail; The Black Panther Vol. 15 no.29 (1976) reports his funeral triggered further unrest. [Source: The Black Panther (Black Panther Party newspaper), v15 no.29; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]"}'

# Edgardo Enriquez Espinosa (International)
# php artisan prisoner:add '{"name": "Edgardo Enriquez Espinosa", "first_name": "Edgardo", "last_name": "Espinosa", "description": "Leading member of the Chilean MIR, held and tortured in an Argentine military prison with fears he would be handed to Chile'\''s junta; The Black Panther Vol. 15 no.4 (1976). [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Movement of the Revolutionary Left (MIR)"]}'

# Herbert Chitepo (Zimbabwe)
# php artisan prisoner:add '{"name": "Herbert Chitepo", "first_name": "Herbert", "last_name": "Chitepo", "description": "Secretary general of the Zimbabwe African National Union (ZANU), assassinated by car bombing amid the liberation struggle; The Black Panther Vol. 15 no.23 (1976). [Source: The Black Panther (Black Panther Party newspaper), v15 no.23; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Zimbabwe African National Union"]}'

# Jackie Mashabane (South Africa)
# php artisan prisoner:add '{"name": "Jackie Mashabane", "first_name": "Jackie", "last_name": "Mashabane", "description": "Black student who died mysteriously in South African police custody in 1976; The Black Panther Vol. 15 no.29 (1976) reports police fired on the crowd of over 10,000 at his funeral. [Source: The Black Panther (Black Panther Party newspaper), v15 no.29; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]"}'

# Jairus Kgoking (International)
# php artisan prisoner:add '{"name": "Jairus Kgoking", "first_name": "Jairus", "last_name": "Kgoking", "description": "Prominent Sowetan and SASO member detained amid the security-police sweep after the June 16, 1976 Soweto uprising; The Black Panther Vol. 15 no.15 (1976). [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["South African Students'\'' Organization"]}'

# John Kani (South Africa)
# php artisan prisoner:add '{"name": "John Kani", "first_name": "John", "last_name": "Kani", "description": "Tony Award-winning Black South African actor arrested by Transkei authorities in Oct. 1976 after a performance of '\''Sizwe Banzi Is Dead'\'' for remarks calling the '\''homeland'\'' a dumping ground; The Black Panther Vol. 15 (1976). [Source: The Black Panther (Black Panther Party newspaper), v15 no.28,29; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]"}'

# Joshua Nkomo (Zimbabwe)
# php artisan prisoner:add '{"name": "Joshua Nkomo", "first_name": "Joshua", "last_name": "Nkomo", "description": "ZAPU founder and internal ANC leader held some eleven years in detention by Rhodesia'\''s Smith regime; The Black Panther Vol. 15 no.27 (1976). [Source: The Black Panther (Black Panther Party newspaper), v15 no.27; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Zimbabwe African People'\''s Union"]}'

# Maire Drumm (Northern Ireland)
# php artisan prisoner:add '{"name": "Maire Drumm", "first_name": "Maire", "last_name": "Drumm", "description": "Vice-president of Provisional Sinn Fein arrested under emergency laws after protesting a British plan to strip imprisoned IRA members of special-category status; The Black Panther Vol. 15 no.19 (1976). [Source: The Black Panther (Black Panther Party newspaper), v15 no.19; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Provisional Sinn Fein"]}'

# Masobiya Mdluli (International)
# php artisan prisoner:add '{"name": "Masobiya Mdluli", "first_name": "Masobiya", "last_name": "Mdluli", "description": "African National Congress activist, 50, arrested March 18, 1976 and dead in police custody the next morning with signs of torture — reported as one of some 30 detainees to die under interrogation in the period; The Black Panther Vol. 15 no.14 (1976). [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["African National Congress"]}'

# Nelson Mandela (International)
# php artisan prisoner:add '{"name": "Nelson Mandela", "first_name": "Nelson", "last_name": "Mandela", "description": "Imprisoned African National Congress leader, referenced in The Black Panther Vol. 15 no.14 (1976)'\''s coverage of the anti-apartheid upsurge and his wife Winnie Mandela. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["African National Congress"]}'

# Orlando Letelier (Chile)
# php artisan prisoner:add '{"name": "Orlando Letelier", "first_name": "Orlando", "last_name": "Letelier", "description": "Former foreign minister of Chile'\''s Allende government, imprisoned and tortured for two years after the 1973 coup and then assassinated by a Washington, D.C. car bomb in 1976; The Black Panther Vol. 15 no.25 (1976). [Source: The Black Panther (Black Panther Party newspaper), v15 no.25; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]"}'

# Raimundo Gleyzer (Argentina)
# php artisan prisoner:add '{"name": "Raimundo Gleyzer", "first_name": "Raimundo", "last_name": "Gleyzer", "description": "Argentine documentary filmmaker arrested and disappeared in 1976 shortly after denouncing political repression in his country; The Black Panther Vol. 15 no.17 (1976). [Source: The Black Panther (Black Panther Party newspaper), v15 no.17; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]"}'

# Robert Mugabe (Zimbabwe)
# php artisan prisoner:add '{"name": "Robert Mugabe", "first_name": "Robert", "last_name": "Mugabe", "description": "ZANU leader and political commander of ZIPA held about ten years in detention by Rhodesia'\''s Smith regime until his release the previous year; The Black Panther Vol. 15 no.27 (1976). [Source: The Black Panther (Black Panther Party newspaper), v15 no.27; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Zimbabwe African National Union"]}'

# Robert Sobukwe (International)
# php artisan prisoner:add '{"name": "Robert Sobukwe", "first_name": "Robert", "last_name": "Sobukwe", "description": "Founder and leader of the Pan Africanist Congress, referenced in The Black Panther Vol. 15 no.14 (1976)'\''s article on the Sharpeville Massacre and PAC'\''s anti-pass Positive Action Campaign. [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Pan Africanist Congress"]}'

# Solwandle Ngudle (International)
# php artisan prisoner:add '{"name": "Solwandle Ngudle", "first_name": "Solwandle", "last_name": "Ngudle", "description": "South African political prisoner held in solitary under the Terrorism Act; police claimed he hanged himself, and a judge blocked evidence that he died of torture and banned his statements posthumously; The Black Panther Vol. 15 no.14 (1976). [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["anti-apartheid movement"]}'

# Thomas Manthatha (International)
# php artisan prisoner:add '{"name": "Thomas Manthatha", "first_name": "Thomas", "last_name": "Manthatha", "description": "Executive of the Black People'\''s Convention arrested in a simultaneous police raid during the post-Soweto detentions; The Black Panther Vol. 15 no.13 (1976). [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["Black People'\''s Convention"]}'

# Tsietsi Mashinini (South Africa)
# php artisan prisoner:add '{"name": "Tsietsi Mashinini", "first_name": "Tsietsi", "last_name": "Mashinini", "description": "19-year-old Soweto student-council leader and organizer of the 1976 anti-apartheid protests, hunted by the security police with a reward on his head; The Black Panther Vol. 15 (1976). [Source: The Black Panther (Black Panther Party newspaper), v15 no.23,24; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]"}'

# Victor Gallingi (International)
# php artisan prisoner:add '{"name": "Victor Gallingi", "first_name": "Victor", "last_name": "Gallingi", "description": "Official of the Catholic Bishops'\'' Conference of Southern Africa arrested by security police in Pretoria during the mass detentions following the Soweto rebellion; The Black Panther Vol. 15 no.13 (1976). [Source: The Black Panther (Black Panther Party newspaper), Vol. 15 (1976); digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["anti-apartheid movement"]}'

# Winnie Mandela (South Africa)
# php artisan prisoner:add '{"name": "Winnie Mandela", "first_name": "Winnie", "last_name": "Mandela", "description": "Member of Soweto'\''s Black Parents Association and wife of imprisoned ANC leader Nelson Mandela, arrested amid a South African government crackdown on Black leaders; The Black Panther Vol. 15 no.20 (1976). [Source: The Black Panther (Black Panther Party newspaper), v15 no.20; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]", "affiliation": ["African National Congress"]}'

# Winston Ntshona (South Africa)
# php artisan prisoner:add '{"name": "Winston Ntshona", "first_name": "Winston", "last_name": "Ntshona", "description": "Award-winning Black South African actor arrested with John Kani over remarks made during their play '\''Sizwe Banzi Is Dead'\''; The Black Panther Vol. 15 (1976). [Source: The Black Panther (Black Panther Party newspaper), v15 no.28,29; digitized at marxists.org — https://www.marxists.org/history/usa/pubs/black-panther/index.htm]"}'

