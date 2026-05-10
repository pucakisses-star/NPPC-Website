<?php

declare(strict_types=1);

/**
 * Add James R. Bennett's "Political Trials and Prisoners in the
 * United States: A Case for Political Defense" as an Article
 * under a "Publications" category. Idempotent — bails out if an
 * article with the same slug already exists.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;

$slug = 'political-trials-and-prisoners-in-the-united-states';

if (Article::where('slug', $slug)->exists()) {
    echo "Article '{$slug}' already exists. Nothing to do.\n";
    return;
}

$category = Category::firstOrCreate(
    ['slug' => 'publications'],
    ['name' => 'Publications']
);

$author = Author::firstOrCreate(
    ['name' => 'James R. Bennett'],
);

$intro = "Because my purpose is practical—to establish the fact of political prisoners and trials as an integral but little acknowledged part of U.S. history and to defend political dissent—I must leave some questions unexplored. I will recount some of the history of political persecution in the United States and suggest some remedies, without systematically analyzing the basic mechanisms of political order and disorder.";

$body = <<<'HTML'
<p style="font-style: italic; opacity: 0.7; margin-bottom: 32px;">Originally published in <em>Social Anarchism</em> #22 (1996).</p>

<p>Because my purpose is practical&mdash;to establish the fact of political prisoners and trials as an integral but little acknowledged part of U.S. history and to defend political dissent&mdash;I must leave some questions unexplored. I will recount some of the history of political persecution in the United States and suggest some remedies, without systematically analyzing the basic mechanisms of political order and disorder, for which one must read a book like Otto Kirchheimer's <em>Political Justice</em> (1961). Why is "political crime" not a legal category in the U.S.? Why has the U.S. not extended the right of political crime parallel to its development of a free speech tradition that protects extreme forms of offensive speech (Walker, 1994)? I cannot pursue these judicial histories. Likewise, the complex issues involved in distinguishing violent from nonviolent political crimes in a democratic polity receive no systematic treatment, though I believe I have explained them sufficiently for my case. To the question of why any state should treat political crimes lightly, particularly in a representative democracy, I give more analysis and answers. For my special purpose is to advocate the separation of political crimes from ordinary criminal activity and to urge the creation of political crime as a distinct legal entity. For while ideally the state represents its nation's cumulative best notions of justice in opposition to the inevitable reflex of power to protect itself, thereby, again ideally, legitimizing its laws as in the service of the whole nation, in practice special interest groups, particularly corporations (Kirchheimer, 1969), control the state and maintain political orthodoxy through apparatuses of reward, propaganda, secrecy, force, and terror (Galbraith, 1983; Bennett 1987, 1991, 1994; Homer 1984), in which laws function to legitimize power.</p>

<p>In such a condition, in which the political heretic is treated just as intolerantly as the religiously orthodox had treated the religious heretic, individuals have historically tried to advance justice and democracy by violating laws, or by advocating the violation of laws, or even by being perceived as violating laws, and always by questioning orthodoxy. Jane Alpert, Oscar Ameringer, John Bach, Joan Baez, Alexander Berkman, Daniel Berrigan, Philip Berrigan, James Bingham, Lynn Bonfield, Ralph Chaplin, Eldridge Cleaver, Angela Davis, David Dellinger, James Ombrowski, the Chicago Seven, the Charlotte Three, the Wilmington Ten, the L.A. Eight, the Puerto Rican independentistas, Black Panthers, conscientious objectors to the wars, socialists following World War I, Japanese-Americans during World War II, socialists and communists following World War II, civil rights protesters&mdash;the roll call of imprisoned dissenters in the U.S. seems endless (Bennett 1995: 267-305). And the list will continue to expand probably as numerously as in the past, since the rule of law is not immune to the infections of power, unless a stronger struggle is made for the popular and juridical acceptance of political dissent even involving violation of laws. What is needed is political justice rather than political arbitrariness, as Otto Kirchheimer (1961) argues: a large, generous political space within which the judge is free to range beyond the confines of local or national laws and to consider international principles, such as those embodied in the Nuremberg trials of Nazi leaders, the United Nations Universal Declaration of Human Rights, and other rights conventions. The pursuit of this goal is the purpose of this essay.</p>

<p>By 1990 the U.S. led the industrial world in per capita imprisonment of its population, over a million in jail or prison, 426 of every 100,000 residents incarcerated, almost double that of the Soviet Union ("U.S. Leads"). Only U.S. client states in the Third World&mdash;El Salvador and South Korea, for example&mdash;had imprisonment rates higher than in the U.S. Fifty percent of the prison population was black in 1990 and still rising (23 percent of the U.S. prison population was black in 1930 and 44 percent in 1980 when blacks constituted 11 percent of the total population), and Native Americans and Latinos also filled prison cells at rates larger than their proportion in society as a whole, their numbers also rising. And President Bush proclaimed as national policy the doubling of the prison population before the end of 1999 (Churchill 1990: 94). A small but qualitatively important segment of prison population (sometimes, as during the Vietnam War and the Civil Rights Movement, thousands) has been the political prisoner. One kind of political prisoner is the person who defies laws for conscience.</p>

<h2>Case Study: Helen Woodson</h2>

<p>The anti-war group headed by Daniel Berrigan, called "Operation Plowshares" or "Silo Pruning Hooks," destroyed government property in symbolic protest against nuclear weapon's production. For slightly damaging the cover of a missile silo in Kansas and for refusing to say she would not repeat the action, one member, Helen Woodson, was sentenced to 17 years in prison (three times the length of the average murder sentence). Because she is the mother of eleven children, seven of them adopted and retarded, the sentence was reduced to twelve years. She refused to appeal, and she declared she will jackhammer the silo again as soon as she is free, which could return her to prison until the year 2001. Mary McGrory calls her an "American prisoner of faith," and quotes Woodson's own self-description as a "Catholic resister mother... doing the Lord's work" of trying to rescue her children, her fellow prisoners, and her fellow citizens from the "'burning world'" of armaments and war. To Helen Woodson the U.S. is a nation "armed and dangerous," which since 1787 "has sent troops into 200 separate wars, only foreign soil. She perceives the country as historically aggressive, as she says, "armed and dangerous." If 100,000 mothers protested, she says, they would not be sent to jail and the country would change its warrior leaders and policies. But they have not, so she must, and be locked up as a political prisoner.</p>

<h2>Political Prisoners in the United States</h2>

<p>Power has been defined as the ability to impose one's will over others. John Kenneth Galbraith examines three ways by which power is enforced. The chief methods in a democracy are compensatory, or domination through especially pecuniary rewards, and conditional, or persuasion or appeal to belief. In capitalism the first line of enforcement is employment, in which employers regulate conformity, in contrast to countries in which jobs are guaranteed. The second line is that of engineering consent&mdash;the intricate, manifold system of indoctrination, from corporate advertising and public relations, to government secrecy and censorship, to education, news, think tanks, films, etc. But compensatory and conditional power finally rest upon the willingness and ability of the ruling groups and institutions to suppress dissent by the force of incarceration.</p>

<p>In common with China, Croatia, Cuba, Egypt, El Salvador, Indonesia, Iran, and Iraq, the U.S. locks up dissenters who in the process of expressing their abhorrence of some action by the government and in adherence to their commitment to some higher law or to the values of their conscience violate a statute or are accused of a violation. From radicals and leftists, to anti-war and draft resisters, to civil rights and anti-racist fighters, to the Puerto Rican independence and sanctuary supporters&mdash;and right-wing dissenters too&mdash;the nation silences dissenters by trial and imprisonment and denies it is political. Just as China invades and occupies Tibet for forty years yet denies "occupation," the U.S. has tried and imprisoned tens of thousands of people politically since World War II yet denies the existence of political prisoners. From Henry David Thoreau's refusal to pay taxes to express his opposition to slavery and the Mexican War (defended in his essay "Civil Disobedience") to the protesters who shut down the San Francisco Bay Bridge to express their opposition to the Gulf War of 1991 (the protest defended upon the Nuremberg Principles and the First Amendment), citizens have refused to obey government rules or laws they believed unjust. During the Civil Rights and the anti-Vietnam War movements, thousands of people were jailed for protesting leaders and laws considered profoundly contrary to truth and justice. These protesters defied authority and were consequently subjected to the force of the state in trials and imprisoning, which the state denies is political.</p>

<p>Brendan Behan in his autobiographical <em>Borstal Boy</em> comments on his arrest and imprisonment for supporting the Irish Republican Army. Confined with common criminals, his reaction applies pointedly to U.S. hypocrisy in recent years (President Bush demanding that Castro free "all political prisoners" while financing torture and murder in terrorist states like Guatemala). The English denied political status to trials and prisoners in order to say that "alone among the empires she had no political prisoners" (271).</p>

<p>Although the civil rights protesters have been vindicated by the virtually universal rejection of the unconstitutional Jim Crow laws, and many citizens have repudiated the Vietnam War (even former Pentagon head, Robert McNamara), government repression of opposition to unconstitutional institutions and laws continues. Only recently were Central American sanctuary advocates prosecuted (Kahn), and at present opponents of nuclear weapons suffer in jails from Washington State to Washington, D.C. Freedom Now (the National Campaign for Amnesty and Human Rights for Political Prisoners) in 1989 listed 132 political prisoners and was investigating sixty additional cases; the Movement Support Network, a project of the Center for Constitutional Rights, recognized more than 100 political prisoners in the same year; and the publication <em>Can't Jail the Spirit</em> listed sixty-seven political prisoners (Rothschild).</p>

<p>But these statistics drastically undercount the actual number of citizens who, having opposed the government by violating laws, or having been accused of violating them, or out of fear that they might violate them, have been arrested and prosecuted. Slavery can be understood partly through the conception of political prisoners and compared to the Jewish Holocaust (Thomas). During World War Two, thousands of West Coast Japanese Americans were sent to concentration camps guilty only of the crime of ancestry (Smith). Tens of thousands of citizens have been prosecuted and imprisoned for their color (<em>Political Prisoners</em> 1981; Shapiro, 1988; Nakell and Hardy, 1987). During the Mayday protest march in Washington, D.C., May 3 to 5, 1971, over 13,000 people were arrested, "virtually all of the arrests" illegal (Goodell: 377). Similarly, the magazine <em>The Nuclear Resister</em> reported 33,110 nuclear resistance arrests in the U.S. and Canada, from 1983 to 1990 ("Nuclear Resistance&mdash;1990"). More than 750 people were arrested during the first weekend of 1991 at the Nevada nuclear weapons test site alone.</p>

<p>"The terms <em>political crime</em> and <em>political criminal</em> are rarely found in the American literature of the social and political sciences, history, criminology, or law. In 1979, <em>Webster's New Collegiate Dictionary</em> for the first time defined the political criminal as one "involv[ed] or charged" with "acts against the government or a political system" (Kittrie and Wedlock, 1986, p. xxxvii). Although Amnesty International originally recognized as "prisoners of conscience" only those who have not used or advocated violence, now they include everyone who has been denied trial within a reasonable time, when trial procedures do not conform to recognized international norms, when prison conditions are cruel, degrading, and inhuman, and all condemned to death (Bennett, 1995; p.7). But as the rapid survey above indicated, countless individuals throughout U.S. history have been arrested in defense of their ideals, while the government has resolutely denied the category "political prisoner" or "prisoner of conscience," thereby preventing a political defense.</p>

<p>Kittrie and Wedlock suggest why: "born of treason and midwived by violent revolution," U.S. leaders have sought "to counter the lessons" of their origins by fostering "the dogma that all evils of the past were the result of the tyrannical monarch, that in a democratic republic obedience to the law was the unquestionable duty of all citizens, and existing political mechanisms were ample for peaceful reform." On the basis of this rationalizing myth, U.S. law has constructed a panoply of prohibitions of politically-motivated conduct&mdash;"from treason and sedition to the education of blacks, from the advocacy of anarchy to voting by women, from office-holding by communists to picketing and striking by workers" (xl-xli). Against the pursuit of life, liberty, and happiness the government has erected a system of negations.</p>

<p>Unjust prohibition, however, inspires challenge. Charles Goodell traces a common pattern throughout U.S. history in his devastating <em>Political Prisoners in America</em> (1973): The government behaves brutally and immorally (Jim Crow, Vietnam), citizens protest in civil disobedience, and the government represses the dissent, hardens its laws, treats disobedients vindictively, and denies political motive. In commenting on the "containment of mass strikes" by working people, Jeremy Brecher observes that "ruling groups call on force... only reluctantly" because violence shatters their "image of benevolence," revealing them instead as "oppressors ready to kill to retain their privileges." Nonetheless, the industrial managers and the state have repeatedly resorted to force; indeed, "an entire history could be written of the apparatus constructed for this purpose," and he describes the general pattern of force mobilized to crush strikes (250-251), of which trials and prisoners are a part.</p>

<p>Frederick Homer's distinction (1984) between government force and "government terror" (terror "from above") helps define political prisoner and trial in the U.S. Here is my summary of several pages of his argument: government terror is any response by a governmental agency to a perceived or imagined threat to the social order, safety, and security of the established majority by a minority or out-group that violates U.S. standards of justice and fairness (due process, equal application of laws; maximum freedom of speech, assembly, and movement; freedom from arbitrary abuse of authority; and minimal use of police violence) and that conveys a general threat to all dissidents.</p>

<p>Steven Barkan (1985) identifies two kinds of political trials and prisoners. A political trial is a criminal proceeding "used to protect or change the existing structure of political power" (p.3). A political prisoner is therefore anyone imprisoned for trying to change existing institutions and laws. The first kind of trial is initiated by the state through its officials. In this kind of prosecution, the state seeks to incarcerate the defendants, to exhaust and overwhelm them by their defense, to thereby divert them from their political pursuits, to discredit their cause, and to frighten off supporters (4-5). Even if the state does not win a conviction, it may win in terms of social control. The Southern Civil Rights movement cases illustrate this kind of trial, for Southern officials used the criminal justice system to harass, arrest, prosecute, and imprison large numbers of the movement.</p>

<p>In the second category of political trials and prisoners, the initiation of criminal proceeding rests with persons who deliberately break the law in acts of civil disobedience. Their purpose is often polemical and educational by attempting to convey to the public the political and moral issues during a trial. The Plowshares nuclear protesters (the Berrigans, Helen Woodson, Molly Rush, and many others) illustrate this kind of challenge to the state. Or their purpose may be to create test cases for appeals to higher courts. For example, Cesar Chavez defied a court injunction against picketing at a melon field in Arizona. Often punishment is severe. Both of Barkan's categories fit Homer's definition of government terrorism.</p>

<p>The People's Law Office in Chicago divides political prisoners into three categories. First, foreign nationals whose opposition to U.S. allies, such as El Salvador, results in detention or imprisonment (Kahn, 1996). Second, members of minorities&mdash;African-Americans, Native Americans&mdash;imprisoned for violating laws in the pursuit of liberation and justice (Kempton, 1973; Churchill and Vander Wall, 1988; Crary, 1995). For example, during one year (1969) of Nixon's COINTELPRO program, thirty-three members of the Black Panther Party were killed by police and many more arrested, tried, and imprisoned (Ola, 1990). And third, Caucasians who have acted in solidarity with oppressed minorities or liberation movements or in opposition to U.S. policies of any kind&mdash;tens of thousands of protesters against racial discrimination or homophobia or sexism or nuclear arms.</p>

<p>Matthew Rothschild distinguishes four kinds of U.S. political prisoners. First, there are those who are prosecuted for "thought crimes," such as members or sympathisers with socialism or communism (Blackstock, 1975; Marzani, 1994; Mitchell, 1970; Talbot and Zheutlin, 1978) or opponents of war (Kohn 1987, Tollefson). Conspiracy laws have been passed to make it a crime to conspire to commit acts, whether or not the acts are carried out (Packer in Chomsky 1970; Katznelson and Kesselman, 1979, pp. 347-8). Recently the charge of seditious conspiracy from a statute of 1862 has been employed to imprison fourteen Puerto Rican independentistas (Fernandez, 1994) and the Ohio 7 (Blunk and Lavasseur, 1990; Liveright and Liveright, 1990). The statute prohibits conspiring to overthrow the government or using force against the government, crimes punishable by up to twenty years in prison. The second category is for those who are framed on nonpolitical charges (Abu-Jamal, Weinglass, Sostre). Rothschild (1989) cites Leonard Peltier, convicted of the 1975 murders of two FBI agents on the Pine Ridge Indian Reservation in South Dakota, and Elmer "Geronimo" Pratt, convicted of murder in 1972 and sentenced to life, many believe because of his leadership in the Black Panther Party (Churchill and Vander Wall). Third are persons who commit politically motivated "symbolic" acts violent only to property. This category at present is represented mainly by nuclear-weapons protestors like Helen Woodson. They have been given lengthy sentences for criminal trespass and destruction of government property, despite their potentially strong First Amendment symbolic speech defense (not allowed, of course). These defendants are sometimes described as thought crime prisoners. And fourth are those who commit acts violent toward people that are politically motivated, which includes a wide range of prisoners.</p>

<p>The definition of political prisoners proposed in <em>Can't Jail the Spirit</em> is "people who have made conscious political decisions, and acted on them, to oppose the United States Government, and who have been incarcerated as a result of those actions." Ward Churchill (1990) defines them as people "motivated by a desire, and often guided by a theory, to transform the social order into something more positive for the oppressed, less profitable for the oppressor" (p. 95), and who violate laws in the pursuit of their ideals.</p>

<p>Goldstein's definition of political repression is clarifying too: "government action which grossly discriminates against persons or organizations viewed as presenting a fundamental challenge to existing power relationships or key governmental policies, because of their perceived political beliefs" (Goldstein, 1977; p. xvi). "I don't know what else to call it but repression when a criminal process is bent from its intended purposes and used to crush dissent" (Goodell, 1973, p. 9). According to a wide range of definitions, the history of the U.S. includes a history of political trials and imprisonment. "Civil disobedients and victims of repression are political prisoners, even in America" (Goodell, 1973; p. 10).</p>

<h2>Bibliography</h2>

<p>Abu-Jamal, Mumia. 1995. <em>Live from Death Row</em>. Reading, MA: Addison-Wesley.</p>
<p>Backiel, Linda. 1988. "Ties That Bind: Left Mustn't Abandon Political Prisoners." <em>Guardian</em> (June 15): 10-11.</p>
<p>Barkan, Steven. 1985. <em>Protesters on Trial: Criminal Justice in the Southern Civil Rights and Vietnam Antiwar Movements</em>. New Brunswick, NJ: Rutgers University Press.</p>
<p>Bedau, Hugo, ed. 1970. <em>Civil Disobedience: Theory and Practice</em>. New York: Pegasus.</p>
<p>Behan, Brendan. 1959. <em>Borstal Boy</em>. New York: Knopf.</p>
<p>Bennett, James R. 1987. <em>Control of Information in the United States: An Annotated Bibliography</em>. Westport, CT: Meckler.</p>
<p>____. 1991. <em>Control of the Media in the United States: An Annotated Bibliography</em>. New York: Garland.</p>
<p>____. 1988. "Censorship by the Reagan Administration." <em>Index on Censorship</em> 17:7 (August): 28-32.</p>
<p>____. 1994. "Control of the Media and the First Amendment." <em>Quarterly Journal of Ideology</em> 17 (3-4): 97-138.</p>
<p>____. 1995. <em>Political Prisoners and Trials: A Worldwide Annotated Bibliography, 1900 through 1993</em>. Jefferson, NC: McFarland.</p>
<p>Bennett, James R., and Barbara McIvor. 1992. "Political Trials and Prisoners in the United States: An Annotated Bibliography." <em>Free Speech Yearbook</em> 30: 173-194.</p>
<p>Berrigan, Daniel. 1987. <em>To Dwell in Peace: An Autobiography</em>. New York: Harper &amp; Row.</p>
<p>Blackstock, N. 1975. <em>Cointelpro: The FBI's Secret War Against Political Freedom</em>. New York: Vintage.</p>
<p>Blunk, Tim, and Raymond Lavasseur, eds. 1990. <em>Hauling up the Morning</em>. East Haven, CT: Red Sea.</p>
<p>Braley, Scott, and Douglas Spalding. 1990. "Parole Nixed: Free Berkman Effort Launched." <em>Guardian</em> 43 (Dec. 19): 5.</p>
<p>Brecher, Jeremy. 1972. <em>Strike!</em> San Francisco: Straight Arrow.</p>
<p><em>Can't Jail the Spirit: Political Prisoners in the U.S., a Collection of Biographies</em>. 1989. Washington, DC: Center for Constitutional Rights.</p>
<p>"Captain Rockwood, the Army and the National News Media." 1995. <em>Treasure State Review</em> 12 (Summer): 8.</p>
<p>Caulfield, Susan. 1991. "Subcultures as Crime: The Theft of Legitimacy of Dissent in the United States." In <em>Crimes by the Capitalist State</em>. Ed. Gregg Barak. Albany: State University of New York Press.</p>
<p>Chevigny, Paul. 1972. <em>Cops and Rebels: A Study of Provocation</em>. New York: Pantheon.</p>
<p>____. 1969. <em>Police Power</em>. New York: Vintage.</p>
<p>Chomsky, Noam et al., eds. 1970. <em>Trials of the Resistance</em>. New York.</p>
<p>Churchill, Ward. 1995. <em>Draconian Measures: A History of FBI Political Repression</em>. Monroe, ME: Common Courage.</p>
<p>____. 1990. "The Third World at Home: Political Prisoners in the U.S." <em>Z Magazine</em> 3 (June): 89-96.</p>
<p>Churchill, Ward, and J. J. Vander Wall. 1988. <em>Agents of Repression: The FBI's Secret Wars Against the Black Panther Party and the American Indian Movement</em>. Boston: South End.</p>
<p>____. 1994. <em>Cages of Steel: The Politics of Imprisonment in the United States</em>. Washington, DC: Maisonneuve.</p>
<p>____. 1990. <em>The COINTELPRO Papers: Documents from the FBI's Secret Wars Against Dissent in the United States</em>. Boston: South End.</p>
<p>Clavir, Judy, and John Spitzer, eds. 1970. <em>The Conspiracy Trial</em>. Indianapolis: Bobbs Merrill.</p>
<p>Cockburn, Alexander. 1995. "Wilderness Society: The Saga of Shame Continues." <em>The Nation</em> (March 6).</p>
<p>Coryell, Schofield. 1967. "The War Crimes Tribunal: Let the People Judge!" <em>The Minority of One</em> 9 (July-Aug.): 14-15.</p>
<p>Crary, David. 1995. "Activists Campaign for Peltier Pardon." <em>Morning News</em> (June 22): 3A.</p>
<p>Day, Samuel, Jr., ed. 1989. <em>Prisoners on Purpose: A Peacemakers' Guide to Jails and Prisons</em>. Madison, WI: Progressive Foundation.</p>
<p>Doyle, Dorothy. 1989. "Activist Exposes FBI's 40 Years of Harassment." <em>Guardian</em> (May 3): 8.</p>
<p>Emerson, Thomas. 1971. <em>The System of Freedom of Expression</em>. New York: Random House.</p>
<p>Epstein, Jason. 1970. <em>Trials of the Resistance</em>. New York: Vintage.</p>
<p>Faulk, John Henry. 1983. <em>Fear on Trial</em>. Austin: University of Texas Press.</p>
<p>Fernandez, Ronald. 1994. <em>Prisoners of Colonialism: The Struggle for Justice in Puerto Rico</em>. Monroe, ME: Common Courage.</p>
<p>Foner, Philip, ed. 1970. <em>The Black Panthers Speak</em>. Philadelphia: Lippincott.</p>
<p><em>Frame Up: The Imprisonment of Martin Sostre</em>. 1974. [Film] Brooklyn, NY: Pacific Street Films.</p>
<p>Galbraith, John Kenneth. 1983. <em>The Anatomy of Power</em>. Boston: Houghton Mifflin.</p>
<p>Garber, Marjorie, and Rebecca Walkowitz, eds. 1995. <em>Secret Agents: The Rosenberg Case, McCarthyism and Fifties America</em>. New York: Routledge.</p>
<p>Gaylin, Willard. 1970. <em>In the Service of Their Country: War Resisters in Prison</em>. New York: Viking.</p>
<p>Georges-Abeyie, Daniel. 1980. "Political Crime and Terrorism." In <em>Crime and Deviance</em>. Graeme Newman, ed. Beverly Hills, CA: Sage.</p>
<p>Ginsberg, Benjamin. 1982. <em>The Consequences of Consent: Elections: Citizen Control and Popular Acquiescence</em>. Reading, MA: Addison-Wesley.</p>
<p>Gioglio, Gerald. 1989. <em>Days of Decision: An Oral History of Conscientious Objectors in the Military During the Vietnam War</em>. Trenton, NJ: Broken Rifle.</p>
<p>Gitlin, Todd. 1971. <em>Campfires of the Resistance: Poetry from the Movement</em>.</p>
<p>Glick, Brian. 1988. "FBI's History of Breaking Movements." <em>Lucha</em> 12 (Mar./Ap.): 4-7.</p>
<p>Glick, Brian. 1989. <em>War at Home: Covert Action Against U.S. Activists and What We Can Do About It</em>. Boston: South End.</p>
<p>Goldstein, Robert. 1978. <em>Political Repression in Modern America from 1870 to the Present</em>. Boston: G. K. Hall.</p>
<p>Goodell, Charles. 1973. <em>Political Prisoners in America</em>. New York: Random House.</p>
<p>Goodman, Walter. 1968. <em>The Committee: The Extraordinary Career of the House Committee on Un-American Activities</em>. New York: Farrar.</p>
<p>Heineman, Kenneth. 1993. <em>Campus Wars: The Peace Movement at American State Universities in the Vietnam Era</em>. New York: New York University Press.</p>
<p>Hoffman, Jan. 1994. "Healing on Parole: Radical Physician A. Berkman Treats Fellow Parolees at El Rio Clinic in the Bronx." <em>New York Times</em> (late ed., Jan. 10): B1-B2.</p>
<p>Homer, Frederick. 1984. "Government Terror in the United States." In Michael Stohl and George Lopez, eds., <em>The State as Terrorist: The Dynamics of Governmental Violence and Repression</em>. Westport, CT: Greenwood.</p>
<p>____. 1983. "Terror in the United States: Three Perspectives." In Michael Stohl, ed., <em>The Politics of Terrorism</em>. 2nd ed., New York and Basel: Dekker.</p>
<p>Jaimes, M. Annette, ed. 1992. <em>The State of Native America: Genocide, Colonization, and Resistance</em>. Boston: South End.</p>
<p>"Judge Frees Black Panther Jailed 19 Years for Attempted Murder: Dhoruba al-Muhahid bin Wahad." 1990. <em>Jet</em> 77 (April 9): 38.</p>
<p>Kahn, Robert. 1996. <em>Other People's Blood: U.S. Immigration Prisons in the Reagan Decade</em>. Boulder, CO: Westview.</p>
<p>Katznelson, Ira, and Mark Kesselman. 1979. "Legal Repression and the Political Trial." <em>The Politics of Power</em>. 2nd ed. New York: Harcourt, Brace Jovanovich.</p>
<p>Keller, William. 1989. <em>The Liberals and J. Edgar Hoover: Rise and Fall of a Domestic Intelligence State</em>. Princeton, NJ: Princeton University Press.</p>
<p>Kempton, Murray. 1973. <em>The Briar Patch: The People of the State of New York v. Lumumba Shakur et al.</em> New York: Dutton.</p>
<p>____. 1994. <em>Rebellions, Perversities, and Main Events</em>. New York: Times Books.</p>
<p>Kirchheimer, Otto. 1961. <em>Political Justice: The Use of Legal Procedures for Political Ends</em>. Princeton, NJ: Princeton University Press.</p>
<p>____. 1969. "In Quest of Sovereignty." <em>Politics, Law, and Social Change: Selected Essays of Otto Kirchheimer</em>. Frederic Burin and Kurt Shell, eds. New York: Columbia University Press.</p>
<p>Kittrie, Nicholas, and Eldon Wedlock, Jr., eds. 1986. <em>The Tree of Liberty: A Documentary History of Rebellion and Political Crime in America</em>. Baltimore: Johns Hopkins University Press.</p>
<p>Knoll, Erwin, and Judith McFadden, eds. 1969. <em>American Militarism 1970</em>. New York: Viking.</p>
<p>____. 1970. <em>War Crimes and the American Conscience</em>. New York: Holt, Rinehart and Winston.</p>
<p>Kohn, Stephen. 1994. <em>American Political Prisoners: Prosecutions under the Espionage and Sedition Acts</em>. Westport, CT: Praeger/Greenwood.</p>
<p>____. 1987. <em>Jailed for Peace</em>. Westport, CT: Praeger.</p>
<p>Korn, Peter. 1991. "Agent Orange in Vietnam: The Persisting Poison." <em>The Nation</em> 252 (April 8): 440-41.</p>
<p>Levy, Howard, M.D., and David Miller. n.d. <em>Going to Jail: The Political Prisoner</em>. New York: Grove.</p>
<p>Lewis, Lionel. 1993. <em>The Cold War and Academic Governance: The Lattimore Case at Johns Hopkins</em>. New York: New York University Press.</p>
<p>Liveright, Herman, and Betty Liveright. 1991. "Layman: Don't Judge Armed Struggle." <em>Guardian</em> 43 (May 15): 7-8.</p>
<p>Lyon, James. 1993. <em>Bertolt Brecht in America</em>. Princeton, NJ: Princeton University Press.</p>
<p>Marzani, Carl. 1994. <em>The Education of a Reluctant Radical</em>. Vol. 4. "From Pentagon to Penitentiary." New York: Topical Books.</p>
<p>May, Gary. 1994. <em>Un-American Activities: The Trials of William Remington</em>. New York: Oxford University Press.</p>
<p>McCarthy, Patrick. 1990. "Ten Years After: The Plowshares Eight." <em>Christianity and Crisis</em> (May 28): 169-70.</p>
<p>McGrory, Mary. 1990. "3 Lessons in Political Courage." <em>Arkansas Gazette</em>, June 19, p. 7B.</p>
<p>____. 1986. "American Prisoner of Faith." <em>Arkansas Gazette</em>, April 23, p. 15A.</p>
<p>McNamara, Robert, with Brian VanDeMark. 1995. <em>In Retrospect: The Tragedy and Lessons of Vietnam</em>. New York: Times/Random House.</p>
<p>Melman, Seymour. 1991. "Military State Capitalism." <em>The Nation</em> 252 (May 20): 649, 664-668.</p>
<p>Melman, Seymour, with Melvyn Baron and Dodge Ely. 1968. <em>In the Name of America</em>. New York: Clergy and Laymen Concerned About Vietnam.</p>
<p>Messerschmidt, Jim. 1983. <em>The Trial of Leonard Peltier</em>. Boston: South End.</p>
<p>Mitchell, David. 1970. <em>1919: Red Mirage</em>. New York: Macmillan.</p>
<p>Mitford, Jessica. 1969. <em>The Trial of Dr. Spock, The Rev. William Sloane Coffin, Jr., Michael Ferber, Mitchell Goodman, and Marcus Raskin</em>. New York: Knopf.</p>
<p>Moorehead, Caroline. 1987. <em>Troublesome People: The Warriors of Pacifism</em>. Bethesda, MD: Adler.</p>
<p>Nakell, Barry, and Kenneth Hardy. 1987. <em>The Arbitrariness of the Death Penalty</em>. Philadelphia: Temple University Press.</p>
<p>Nadelson, Regina. 1972. <em>Who Is Angela Davis? The Biography of a Revolutionary</em>. New York: Wyden.</p>
<p>Norman, Liane. 1989. <em>Hammer of Justice: Molly Rush and the Plowshares Eight</em>. Pittsburgh: Pittsburgh Peace Institute.</p>
<p>"Nuclear Resistance&mdash;1990." 1991. <em>The Nuclear Resister</em> 76 (Feb. 12). Tucson, AZ: The Nuclear Resister.</p>
<p>Ola, Akinshiju C. 1990. "U.S. Convicted: People Imprisoned for Politics." <em>Guardian</em> 43 (Dec. 19): 5.</p>
<p>Perkus, Cathy, ed. 1975. <em>COINTELPRO: The FBI's Secret War on Political Freedom</em>. New York: Monad.</p>
<p>Persico, Joseph. 1995. <em>Nuremberg: Infamy on Trial</em>. New York: Penguin.</p>
<p>Pfost, Donald. 1987. "Reagan's Nicaraguan Policy: A Case Study of Political Deviance and Crime." <em>Crime and Social Justice</em> 27-28: 66-87.</p>
<p><em>Political Prisoners</em>. 1995. Special Issue of <em>The Witness</em> (Jan.-Feb.).</p>
<p><em>Political Prisoners: Racism and the Politics of Imprisonment</em>. 1980. Washington, DC: U.S. Department of Justice, National Minority Advisory Council on Criminal Justice.</p>
<p>Ransby, Barbara. 1990. "A Life of Defiance: Dhoruba on Struggles Past and Present." <em>Guardian</em> (June 8): 10-11.</p>
<p>Rogge, O. John. 1949. <em>Our Vanishing Civil Liberties</em>. New York: Gaer.</p>
<p>Rothschild, Matthew. 1989. "The Crime of Politics." <em>The Progressive</em> 53 (May): 28-31.</p>
<p>Rowan, Carl. 1991. "In Thirst for Order, U.S. Embracing Police State." <em>Arkansas Gazette</em> (April 2): 7B.</p>
<p>Rubenstein, Richard. 1970. <em>Rebels in Eden: Mass Political Violence in the United States</em>. Boston: Little, Brown.</p>
<p>Russell, Bertrand. 1967. <em>War Crimes in Vietnam</em>. New York: Monthly Review Press.</p>
<p>Schaar, John. 1957. <em>Loyalty in America</em>. Berkeley: U of California Press.</p>
<p>Scheutz, Janice. 1985. "Political Trials and Free Speech." <em>Free Speech Yearbook</em> 24: 38-50.</p>
<p>Shapiro, Bruce. 1995. "Kathy Boudin's Prison Odyssey." <em>The Nation</em> (March 20): 380-82.</p>
<p>Shapiro, Herbert. 1988. <em>White Violence and Black Response</em>. Amherst: U of Massachusetts Press.</p>
<p>Shiffrin, Steven. 1990. <em>The First Amendment, Democracy, and Romance</em>. Cambridge, MA: Harvard University Press.</p>
<p>Silver, Isidore, ed. 1974. <em>The Crime Control Establishment</em>. Englewood Cliffs, NJ: Prentice-Hall.</p>
<p>Smith, Bradley. 1981. <em>The Road to Nuremberg</em>. New York: Basic Books.</p>
<p>Smith, Page. 1995. <em>Democracy on Trial: The Japanese-American Evacuation and Relocation in World War II</em>. New York: Simon and Schuster.</p>
<p>Sostre, Martin. 1975. <em>Letters and Quotations</em>. Northhampton, MA: Mother Jones.</p>
<p>Talbot, David, and Barbara Zheutlin. 1978. <em>Creative Differences: Profiles of Hollywood Dissidents</em>. Boston: South End.</p>
<p>Theoharis, Athan, and John Cox. 1988. <em>The Boss: J. Edgar Hoover and the Great American Inquisition</em>. Philadelphia: Temple University Press.</p>
<p>Thomas, Laurence. 1993. <em>Vessels of Evil: American Slavery and the Holocaust</em>. Philadelphia: Temple University Press.</p>
<p>Tollefson, James. 1993. <em>The Strength Not to Fight: An Oral History of Conscientious Objectors of the Vietnam War</em>. Boston: Little, Brown.</p>
<p>Turk, Austin. 1984. "Political Crime." In <em>Major Forms of Crime</em>. Robert Meier, ed. Beverly Hills, CA: Sage: 119-135.</p>
<p>____. 1982. <em>Political Criminality</em>. Beverly Hills, CA: Sage.</p>
<p>"U.S. Leads World in Imprisonment of Its Population." 1991. <em>Arkansas Gazette</em>, Jan. 5, p. 4A.</p>
<p>National Commission on the Causes and Prevention of Violence. 1969. Washington, DC: U.S. Government Printing Office. 2 vols.</p>
<p>Walker, Samuel. 1994. <em>Hate Speech: The History of an American Controversy</em>. Lincoln: University of Nebraska Press.</p>
<p>Weinglass, Leonard. 1994. <em>Race for Justice: Mumia Abu-Jamal's Fight Against the Death Penalty</em>. Monroe, ME: Common Courage.</p>
<p>Westin, Alan, and Barry Mahoney. 1977. "Martin Luther King, Jr., and the Supreme Court." <em>Civil Liberties Review</em> 3: 9-46.</p>
<p>Woodson, Helen. 1988. "Do You Know the Way to Shakopee?" <em>The Witness</em> (February): 19.</p>
HTML;

$article = Article::create([
    'title'        => 'Political Trials and Prisoners in the United States: A Case for Political Defense',
    'slug'         => $slug,
    'author_id'    => $author->id,
    'category_id'  => $category->id,
    'intro'        => $intro,
    'body'         => $body,
    'published_at' => '1996-01-01 00:00:00',
]);

echo "[create] Article id={$article->id}, slug={$article->slug}\n";
echo "         Author: {$author->name} (id={$author->id})\n";
echo "         Category: {$category->name} (id={$category->id})\n";
echo "Done.\n";
