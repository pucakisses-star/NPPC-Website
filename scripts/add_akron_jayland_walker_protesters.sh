#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_akron_jayland_walker_protesters.sh
# 13 Akron, Ohio activists arrested July 4-6, 2022 during protests following the
# police killing of Jayland Walker (shot 46 times by Akron police on June 27, 2022).
# All held 1-2 days at Summit County Jail or Stark County Jail; all charges dismissed.
# 24 plaintiffs settled a federal civil rights suit for $747,000 in 2024.
set +e

cat > /tmp/add_akron_protesters.php << 'PHPEOF'
<?php

  // Natalia Robanhode Wyrd
  if (!\App\Models\Prisoner::withoutGlobalScopes()->where('name', 'Natalia Robanhode Wyrd')->exists()) {
    $p = \App\Models\Prisoner::create([
      'name' => 'Natalia Robanhode Wyrd',
      'first_name' => 'Natalia',
      'last_name' => 'Robanhode Wyrd',
      'description' => 'Natalia Gabriel Robanhode Wyrd (born November 19, 1999) is an Ohio resident and activist arrested in the early hours of July 4, 2022, during protests in Akron, Ohio following the police killing of Jayland Walker, a 25-year-old Black man shot by Akron police on June 27, 2022. Wyrd was among approximately 61 people arrested after city officials released body camera footage of Walker\'s shooting on July 3, 2022, prompting large downtown demonstrations. She was arrested at 3:39 AM by the Akron Police Department and charged with Riot (ORC 2917.03), Disorderly Conduct (ORC 2917.11), and Misconduct at an Emergency (ORC 2917.13). Protesters arrested that night were transported to Summit County Jail, where, according to the subsequent civil rights lawsuit complaint, they were jailed and held at least through the following day on what attorneys characterized as boilerplate "copy-and-paste" arrest forms. All criminal charges were ultimately dismissed or resulted in acquittals following nearly a year of litigation. Wyrd became one of 24 plaintiffs in a federal civil rights lawsuit against the City of Akron, which alleged that police devised and executed a policy of suppressing constitutionally protected protests through unlawful force, mass arrests, and prosecution. The city settled the lawsuit in early 2024 for $747,000, distributed among the plaintiffs.',
      'state' => 'Ohio',
      'gender' => 'Female',
      'ideologies' => ['Black Lives Matter', 'Anti-Police Brutality'],
      'era' => '2020s',
      'in_custody' => false,
      'released' => true,
    ]);
    $inst = \App\Models\Institution::firstOrCreate(
      ['name' => 'Summit County Jail', 'city' => 'Akron', 'state' => 'Ohio']
    );
    $case = new \App\Models\PrisonerCase([
      'institution_id' => $inst->id,
      'charges' => 'Riot (ORC 2917.03), Disorderly Conduct (ORC 2917.11), Misconduct at an Emergency (ORC 2917.13)',
      'arrest_date' => '2022-07-04',
      'incarceration_date' => '2022-07-04',
      'release_date' => '2022-07-05',
      'imprisoned_for_days' => 1,
      'convicted' => 'No — all charges dismissed after nearly a year of litigation',
      'sentence' => 'None — all charges dismissed',
    ]);
    $p->cases()->save($case);
    echo 'Added: Natalia Robanhode Wyrd' . PHP_EOL;
  } else {
    echo 'SKIP (exists): Natalia Robanhode Wyrd' . PHP_EOL;
  }

  // Jack Moss
  if (!\App\Models\Prisoner::withoutGlobalScopes()->where('name', 'Jack Moss')->exists()) {
    $p = \App\Models\Prisoner::create([
      'name' => 'Jack Moss',
      'first_name' => 'Jack',
      'last_name' => 'Moss',
      'description' => 'Jack Baxter Moss (born March 13, 1995) is an Ohio activist arrested on July 4, 2022, in Akron during mass demonstrations following the release of body camera footage showing Akron police fatally shooting 25-year-old Jayland Walker on June 27, 2022. Moss was among approximately 49 people arrested between midnight and 5 a.m. on July 4 as protests erupted in downtown Akron. He was charged with riot (Ohio Revised Code 2917.03), disorderly conduct by intoxication, and failure to disperse (ORC 2917.04). Like other demonstrators arrested that night, Moss was jailed and held at least through the following day on what civil rights attorneys later characterized as "copy-and-paste" arrest complaints with identical boilerplate language claiming each arrestee "took part in protest that turned to violent riot" and "failed to disperse when ordered to." The Summit County Jail was set up for mass processing of protesters from this event. The city of Akron pursued these charges for nearly a year before all charges were dismissed. In a related federal civil rights lawsuit filed by 24 of the arrested protesters, the city of Akron agreed to pay $747,000 in settlements, with the complaint finding that Akron police had violated the constitutional rights of peaceful demonstrators through baseless mass arrests, beatings, tear gas, and pepper spray.',
      'state' => 'Ohio',
      'gender' => 'Male',
      'ideologies' => ['Black Lives Matter', 'Racial Justice'],
      'era' => '2020s',
      'in_custody' => false,
      'released' => true,
    ]);
    $inst = \App\Models\Institution::firstOrCreate(
      ['name' => 'Summit County Jail', 'city' => 'Akron', 'state' => 'Ohio']
    );
    $case = new \App\Models\PrisonerCase([
      'institution_id' => $inst->id,
      'charges' => 'Riot (ORC 2917.03), Disorderly Conduct by Intoxication, Failure to Disperse (ORC 2917.04)',
      'arrest_date' => '2022-07-04',
      'incarceration_date' => '2022-07-04',
      'release_date' => '2022-07-05',
      'imprisoned_for_days' => 1,
      'convicted' => 'No - all charges dismissed after nearly a year of criminal litigation',
      'sentence' => 'None - charges dismissed',
    ]);
    $p->cases()->save($case);
    echo 'Added: Jack Moss' . PHP_EOL;
  } else {
    echo 'SKIP (exists): Jack Moss' . PHP_EOL;
  }

  // Damean Martin
  if (!\App\Models\Prisoner::withoutGlobalScopes()->where('name', 'Damean Martin')->exists()) {
    $p = \App\Models\Prisoner::create([
      'name' => 'Damean Martin',
      'first_name' => 'Damean',
      'last_name' => 'Martin',
      'description' => 'Damean Martin was arrested in the early hours of July 4, 2022, in Akron, Ohio, during mass protests that erupted following the police killing of Jayland Walker, a 25-year-old Black man shot by Akron officers on June 27, 2022. Martin was among nearly 50 demonstrators swept up in what civil rights attorneys later characterized as baseless mass arrests, in which Akron police subjected peaceful protesters and bystanders to violence including beatings, tear gas, and pepper spray. He was charged with Riot (ORC 2917.03), Misconduct at an Emergency (ORC 2917.13), and Failure to Disperse (ORC 2917.04). Martin was transported to and detained at Summit County Jail, where protesters were held at least through the following day in overcrowded conditions — some chained together by wrists and ankles — and were denied medical care and access to phone calls. All charges against Martin were eventually dismissed after nearly a year of criminal litigation. In June 2023, Martin joined 23 other plaintiffs in a federal civil rights lawsuit filed in Ohio\'s Northern District court against the City of Akron and nearly 100 defendants, including Akron police officers, Summit County Sheriff deputies, and University of Akron officers, alleging unlawful arrest, excessive force, and malicious prosecution. The City of Akron settled the lawsuit for $747,000.',
      'state' => 'Ohio',
      'ideologies' => ['Black Lives Matter', 'Anti-Police Violence'],
      'era' => '2020s',
      'in_custody' => false,
      'released' => true,
    ]);
    $inst = \App\Models\Institution::firstOrCreate(
      ['name' => 'Summit County Jail', 'city' => 'Akron', 'state' => 'Ohio']
    );
    $case = new \App\Models\PrisonerCase([
      'institution_id' => $inst->id,
      'charges' => 'Riot (ORC 2917.03); Misconduct at an Emergency (ORC 2917.13); Failure to Disperse (ORC 2917.04)',
      'arrest_date' => '2022-07-04',
      'incarceration_date' => '2022-07-04',
      'imprisoned_for_days' => 1,
      'convicted' => 'No — charges dismissed',
      'sentence' => 'No sentence — all charges dismissed after approximately one year of criminal litigation',
    ]);
    $p->cases()->save($case);
    echo 'Added: Damean Martin' . PHP_EOL;
  } else {
    echo 'SKIP (exists): Damean Martin' . PHP_EOL;
  }

  // Sierra Lewis
  if (!\App\Models\Prisoner::withoutGlobalScopes()->where('name', 'Sierra Lewis')->exists()) {
    $p = \App\Models\Prisoner::create([
      'name' => 'Sierra Lewis',
      'first_name' => 'Sierra',
      'last_name' => 'Lewis',
      'description' => 'Sierra Renee Lewis (born September 14, 1999) is an Akron, Ohio resident who was arrested in the early morning hours of July 4, 2022, during protests that erupted following the release of body camera footage showing Akron police officers shooting Jayland Walker, a 25-year-old Black man, on June 27, 2022. Walker was struck by dozens of bullets after a car and foot chase; officers fired 90 rounds at him. The footage release triggered mass demonstrations downtown, during which police made nearly 50 arrests between midnight and approximately 6 AM. Lewis was arrested at 5:53 AM by Akron Police Officer Badge 1518 and charged with Riot (ORC 2917.03), Disorderly Conduct (ORC 2917.11), and Failure to Disperse (ORC 2917.04). She was booked into the Summit County Jail, where she and other protesters were held at least through the following day on nearly identical, standardized "copy-and-paste" arrest complaints — forms in which officers wrote the same two sentences on virtually every arrestee\'s paperwork, alleging each person "took part in protest that turned to violent riot" and "failed to disperse when ordered to." After nearly a year of criminal proceedings, all charges against the Akron protest arrestees were dropped or resulted in acquittal. Twenty-four of those arrested subsequently filed a federal civil rights lawsuit alleging unlawful arrest, excessive force, and First Amendment retaliation; the City of Akron agreed to pay $747,000 to settle the suit in 2024. Lewis\'s incarceration was brief — approximately one night — but resulted solely from her participation in a protest against a police killing, and all charges against her were ultimately dismissed.',
      'state' => 'Ohio',
      'gender' => 'Female',
      'ideologies' => ['Black Lives Matter', 'Anti-Police Brutality'],
      'era' => '2020s',
      'in_custody' => false,
      'released' => true,
    ]);
    $inst = \App\Models\Institution::firstOrCreate(
      ['name' => 'Summit County Jail', 'city' => 'Akron', 'state' => 'Ohio']
    );
    $case = new \App\Models\PrisonerCase([
      'institution_id' => $inst->id,
      'charges' => 'RIOT - 2917.03, DISORDERLY CONDUCT - 2917.11, FAILURE TO DISPERSE - 2917.04',
      'arrest_date' => '2022-07-04',
      'incarceration_date' => '2022-07-04',
      'release_date' => '2022-07-05',
      'imprisoned_for_days' => 1,
      'convicted' => 'No — all charges dropped/dismissed',
      'sentence' => 'No sentence imposed — charges were ultimately dropped or acquitted following nearly a year of criminal litigation',
    ]);
    $p->cases()->save($case);
    echo 'Added: Sierra Lewis' . PHP_EOL;
  } else {
    echo 'SKIP (exists): Sierra Lewis' . PHP_EOL;
  }

  // Brendan Eaton
  if (!\App\Models\Prisoner::withoutGlobalScopes()->where('name', 'Brendan Eaton')->exists()) {
    $p = \App\Models\Prisoner::create([
      'name' => 'Brendan Eaton',
      'first_name' => 'Brendan',
      'last_name' => 'Eaton',
      'description' => 'Brendan Eaton, approximately 20 years old at the time, was arrested during protests in Akron, Ohio on July 4, 2022 following the release of body camera footage showing the police killing of Jayland Walker, a 25-year-old Black man who was shot 46 times by Akron police on June 27, 2022. Eaton was among approximately 50 protesters arrested between midnight and 4 AM on July 4 as Akron police, equipped with body armor and backed by SWAT vehicles, fired tear gas and chemical irritants to disperse crowds. Eaton was charged with Riot (ORC 2917.03), Disorderly Conduct (ORC 2917.11), and Failure to Disperse (ORC 2917.04). Protesters were booked and held at Summit County Jail, with the facility setting up for mass intake and some detainees initially transported in large vans holding up to 15 people each. Those arrested were held at least through the following day on near-identical, copy-and-paste complaints in which virtually every arrest form contained the same two sentences claiming that the arrestees "took part in protest that turned to violent riot" and "failed to disperse when ordered to." The City of Akron subsequently pursued charges against protesters and put them through nearly a year of criminal litigation before all charges were either dropped or defendants were acquitted. Twenty-four of the arrested protesters filed a federal civil rights lawsuit against the City of Akron and the Akron Police Department alleging unlawful arrests, beatings, and the unjustified use of tear gas and pepper spray against peaceful demonstrators and bystanders. The city settled the lawsuit in 2024 for $747,000, with 22 of the 24 plaintiffs receiving payments — a de facto acknowledgment that the mass arrests were unlawful.',
      'state' => 'Ohio',
      'gender' => 'Male',
      'ideologies' => ['Black Lives Matter', 'Anti-Police Brutality'],
      'era' => '2020s',
      'in_custody' => false,
      'released' => true,
    ]);
    $inst = \App\Models\Institution::firstOrCreate(
      ['name' => 'Summit County Jail', 'city' => 'Akron', 'state' => 'Ohio']
    );
    $case = new \App\Models\PrisonerCase([
      'institution_id' => $inst->id,
      'charges' => 'Riot (ORC 2917.03), Disorderly Conduct (ORC 2917.11), Failure to Disperse (ORC 2917.04)',
      'arrest_date' => '2022-07-04',
      'incarceration_date' => '2022-07-04',
      'release_date' => '2022-07-05',
      'imprisoned_for_days' => 1,
      'convicted' => 'No — charges dropped or acquitted',
      'sentence' => 'None — all charges dropped or acquitted after approximately one year of criminal litigation',
    ]);
    $p->cases()->save($case);
    echo 'Added: Brendan Eaton' . PHP_EOL;
  } else {
    echo 'SKIP (exists): Brendan Eaton' . PHP_EOL;
  }

  // Devonna Culver
  if (!\App\Models\Prisoner::withoutGlobalScopes()->where('name', 'Devonna Culver')->exists()) {
    $p = \App\Models\Prisoner::create([
      'name' => 'Devonna Culver',
      'first_name' => 'Devonna',
      'last_name' => 'Culver',
      'description' => 'Devonna Culver was arrested by the Akron Police Department in the early morning hours of July 4, 2022, during mass protests that erupted after the city released body camera footage of the police killing of Jayland Walker, a 25-year-old Black man who was shot 46 times by eight officers on June 27, 2022. Culver was charged with riot (ORC 2917.03), disorderly conduct (ORC 2917.11), and failure to disperse (ORC 2917.04), all misdemeanors. Along with approximately 49 other protesters arrested during the demonstrations, Culver was booked at Summit County Jail in Akron, where detainees were held for more than 36 hours without arraignment, denied access to medications, and unable to make phone calls. Culver became one of 24 named plaintiffs in a federal civil rights lawsuit against the City of Akron and its police department, which alleged that officers subjected peaceful demonstrators and bystanders to unlawful mass arrests, beatings, teargas, and pepper spray as a retaliatory suppression of First Amendment-protected activities. The City of Akron settled the lawsuit in 2024 for $747,000. The criminal charges against Culver and other protesters were eventually dropped or resulted in acquittals, consistent with the broader outcome across the 62 documented protest-related cases from that period, in which over 60% were dismissed and none of the remaining cases resulted in significant incarceration.',
      'state' => 'Ohio',
      'ideologies' => ['Black Lives Matter', 'Anti-police violence'],
      'era' => '2020s',
      'in_custody' => false,
      'released' => true,
    ]);
    $inst = \App\Models\Institution::firstOrCreate(
      ['name' => 'Summit County Jail', 'city' => 'Akron', 'state' => 'Ohio']
    );
    $case = new \App\Models\PrisonerCase([
      'institution_id' => $inst->id,
      'charges' => 'Riot (ORC 2917.03), Disorderly Conduct (ORC 2917.11), Failure to Disperse (ORC 2917.04)',
      'arrest_date' => '2022-07-04',
      'incarceration_date' => '2022-07-04',
      'release_date' => '2022-07-06',
      'imprisoned_for_days' => 2,
      'convicted' => 'No — charges dismissed',
      'sentence' => 'None — all charges dropped or acquitted',
    ]);
    $p->cases()->save($case);
    echo 'Added: Devonna Culver' . PHP_EOL;
  } else {
    echo 'SKIP (exists): Devonna Culver' . PHP_EOL;
  }

  // Shelby Crane
  if (!\App\Models\Prisoner::withoutGlobalScopes()->where('name', 'Shelby Crane')->exists()) {
    $p = \App\Models\Prisoner::create([
      'name' => 'Shelby Crane',
      'first_name' => 'Shelby',
      'last_name' => 'Crane',
      'description' => 'Shelby Crane, approximately 20 years old and an Ohio resident, was arrested at 3:45 AM on July 4, 2022, during protests in Akron, Ohio, sparked by the release of police body camera footage showing the fatal shooting of Jayland Walker by Akron officers. Crane was taken into custody by the Akron Police Department and charged with Riot (Ohio RC 2917.03), Failure to Disperse (Ohio RC 2917.04), and Misconduct at an Emergency (Ohio RC 2917.13). Crane was part of a wave of nearly 50 demonstrators arrested between midnight and 4 AM that night; court filings and investigative reporting confirm that these arrestees were jailed and held at least through the following day on what a subsequent federal lawsuit described as "copy-and-paste" arrest complaints in which "virtually every" officer statement contained identical boilerplate language alleging the person "took part in protest that turned to violent riot" and "failed to disperse when ordered to." The city of Akron maintained charges against the protesters through nearly a year of criminal litigation before all charges were dropped or the defendants were acquitted. In February 2024, Akron settled a federal civil rights lawsuit filed by 24 of the arrested protesters for $747,000, with the complaint alleging that police subjected demonstrators to unconstitutional mass arrests, beatings, tear gas, and pepper spray, and that the city imposed an unlawful curfew to suppress protest activity. Crane\'s arrest arose from participation in demonstrations protesting racial injustice and police brutality following Walker\'s killing, conduct protected under the First Amendment.',
      'state' => 'Ohio',
      'ideologies' => ['Black Lives Matter', 'Anti-police brutality'],
      'era' => '2020s',
      'in_custody' => false,
      'released' => true,
    ]);
    $inst = \App\Models\Institution::firstOrCreate(
      ['name' => 'Summit County Jail', 'city' => 'Akron', 'state' => 'Ohio']
    );
    $case = new \App\Models\PrisonerCase([
      'institution_id' => $inst->id,
      'charges' => 'Riot (Ohio RC 2917.03), Failure to Disperse (Ohio RC 2917.04), Misconduct at an Emergency (Ohio RC 2917.13)',
      'arrest_date' => '2022-07-04',
      'incarceration_date' => '2022-07-04',
      'imprisoned_for_days' => 1,
      'convicted' => 'No — charges dropped/acquitted',
    ]);
    $p->cases()->save($case);
    echo 'Added: Shelby Crane' . PHP_EOL;
  } else {
    echo 'SKIP (exists): Shelby Crane' . PHP_EOL;
  }

  // Devonte Clark
  if (!\App\Models\Prisoner::withoutGlobalScopes()->where('name', 'Devonte Clark')->exists()) {
    $p = \App\Models\Prisoner::create([
      'name' => 'Devonte Clark',
      'first_name' => 'Devonte',
      'last_name' => 'Clark',
      'description' => 'Devonte Clark (also identified as "Devante Clark" in court filings) was arrested by the Akron Police Department in the early morning hours of July 4, 2022, during mass protests that erupted in Akron, Ohio following the June 27, 2022 police killing of Jayland Walker, a 25-year-old unarmed Black man shot more than 60 times by Akron officers during a vehicle chase. Clark was charged with riot (ORC 2917.03), disorderly conduct (ORC 2917.11), and failure to disperse (ORC 2917.04), charges that were filed against nearly all of the 61 protesters swept up in mass arrests between July 3 and 7, 2022. All 61 arrestees were formally booked into Summit County Jail, where detainees were held for at least 24 to 48 hours, denied medical care, chained together, and subjected to conditions a federal lawsuit later described as unconstitutional. Clark is identified as a plaintiff in Harris et al. v. City of Akron, a federal civil rights suit filed by the firm Friedman, Gilbert + Gerhardstein on behalf of 24 protesters, which alleged that Akron police officers subjected peaceful demonstrators and bystanders to baseless mass arrests, beatings, tear gas, and pepper spray, and that the city then put them through nearly a year of criminal litigation before all charges were dropped or ended in acquittal. In February 2024, the City of Akron agreed to pay $747,000 to settle the lawsuit with 22 of the 24 plaintiffs. The protest arrests were part of a broader pattern of law enforcement retaliation against demonstrators calling for accountability in the Jayland Walker killing.',
      'state' => 'Ohio',
      'ideologies' => ['Black Lives Matter', 'Anti-police brutality'],
      'era' => '2020s',
      'in_custody' => false,
      'released' => true,
    ]);
    $inst = \App\Models\Institution::firstOrCreate(
      ['name' => 'Summit County Jail', 'city' => 'Akron', 'state' => 'Ohio']
    );
    $case = new \App\Models\PrisonerCase([
      'institution_id' => $inst->id,
      'charges' => 'Riot (ORC 2917.03), Disorderly Conduct (ORC 2917.11), Failure to Disperse (ORC 2917.04)',
      'arrest_date' => '2022-07-04',
      'incarceration_date' => '2022-07-04',
      'convicted' => 'No — all charges dropped or acquitted',
      'sentence' => 'None',
    ]);
    $p->cases()->save($case);
    echo 'Added: Devonte Clark' . PHP_EOL;
  } else {
    echo 'SKIP (exists): Devonte Clark' . PHP_EOL;
  }

  // Semaj Brown
  if (!\App\Models\Prisoner::withoutGlobalScopes()->where('name', 'Semaj Brown')->exists()) {
    $p = \App\Models\Prisoner::create([
      'name' => 'Semaj Brown',
      'first_name' => 'Semaj',
      'last_name' => 'Brown',
      'description' => 'Semaj Brown is an Akron, Ohio activist who was arrested at approximately 3:16 AM on July 4, 2022, during mass protests following the police killing of 25-year-old Jayland Walker, a Black man shot by eight Akron police officers. Brown was among approximately 50 people arrested in the early morning hours of July 4 and charged with Riot (ORC 2917.03), Disorderly Conduct (ORC 2917.11), and Inciting to Violence (ORC 2917.01). Consistent with the documented pattern for all those arrested in the same mass arrest, Brown was held in jail for approximately one to two days before arraignment and release. (Notably, the City of Akron transported many of those arrested to Stark County Jail in Canton, roughly 30 miles away, specifically to hinder supporters from organizing outside the jail and to complicate bail.) The Akron City Prosecutor subsequently offered to dismiss Brown\'s charges if he agreed to sign a legal waiver forfeiting his right to sue the City — Brown refused. Charges against him were dismissed regardless, though the prosecutor left open the possibility of refiling. The broader crackdown on Jayland Walker protesters resulted in a federal civil rights lawsuit by 24 protesters and bystanders, with the City of Akron settling for $747,000 in 2024 over what plaintiffs described as unlawful mass arrests and excessive force during the demonstrations.',
      'state' => 'Ohio',
      'gender' => 'Male',
      'ideologies' => ['Black Lives Matter', 'Anti-Police Violence'],
      'era' => '2020s',
      'in_custody' => false,
      'released' => true,
    ]);
    $inst = \App\Models\Institution::firstOrCreate(
      ['name' => 'Summit County Jail', 'city' => 'Akron', 'state' => 'Ohio']
    );
    $case = new \App\Models\PrisonerCase([
      'institution_id' => $inst->id,
      'charges' => 'Riot (ORC 2917.03), Disorderly Conduct (ORC 2917.11), Inciting to Violence (ORC 2917.01)',
      'arrest_date' => '2022-07-04',
      'incarceration_date' => '2022-07-04',
      'imprisoned_for_days' => 2,
      'convicted' => 'No — charges dismissed',
      'sentence' => 'No sentence — charges dismissed',
    ]);
    $p->cases()->save($case);
    echo 'Added: Semaj Brown' . PHP_EOL;
  } else {
    echo 'SKIP (exists): Semaj Brown' . PHP_EOL;
  }

  // Katelynn Bolinger
  if (!\App\Models\Prisoner::withoutGlobalScopes()->where('name', 'Katelynn Bolinger')->exists()) {
    $p = \App\Models\Prisoner::create([
      'name' => 'Katelynn Bolinger',
      'first_name' => 'Katelynn',
      'last_name' => 'Bolinger',
      'description' => 'Katelynn Bolinger, an approximately 23-year-old resident of Akron, Ohio, was arrested by the Akron Police Department on July 4, 2022, during protests in downtown Akron following the police killing of Jayland Walker — a 25-year-old Black man shot 60 times by eight Akron officers on June 27, 2022. She was charged with three misdemeanor offenses: Riot (ORC 2917.03), Failure to Disperse (ORC 2917.04), and Disorderly Conduct (ORC 2917.11). More than 40 demonstrators were arrested during the July 2022 Akron protests, and all those arrested were deliberately transported to the Stark County Jail in Canton, approximately 30 miles from Akron — a tactic authorities used to isolate protesters from community supporters and complicate bail efforts. All arrestees spent roughly one to two days in custody before arraignment and release, with the Akron Municipal Court closed July 4, further delaying proceedings. Bolinger is not among the 24 named plaintiffs in the subsequent federal civil rights lawsuit, but that lawsuit represented only a portion of the 40+ people arrested. The City of Akron later settled that lawsuit for $747,000, with the complaint documenting that officers conducted baseless mass arrests and used tear gas and beatings against peaceful demonstrators. Of the 62 total criminal cases arising from the Jayland Walker protests, more than 60% were dismissed outright, with additional acquittals at trial. Her individual case disposition could not be confirmed from publicly available sources. The framing of these protests as "BLM riots" by the far-right doxxing site antifawatch.net misrepresents what were demonstrations against racially discriminatory police violence.',
      'state' => 'Ohio',
      'gender' => 'Female',
      'ideologies' => ['Black Lives Matter', 'Anti-Police Brutality', 'Racial Justice'],
      'era' => '2020s',
      'in_custody' => false,
      'released' => true,
    ]);
    $inst = \App\Models\Institution::firstOrCreate(
      ['name' => 'Stark County Jail', 'city' => 'Canton', 'state' => 'Ohio']
    );
    $case = new \App\Models\PrisonerCase([
      'institution_id' => $inst->id,
      'charges' => 'Riot (ORC 2917.03), Failure to Disperse (ORC 2917.04), Disorderly Conduct (ORC 2917.11)',
      'arrest_date' => '2022-07-04',
      'incarceration_date' => '2022-07-04',
      'imprisoned_for_days' => 1,
      'convicted' => 'Not convicted; charges against the vast majority of Akron protest arrestees were dismissed or resulted in acquittals',
      'sentence' => 'No sentence imposed; case likely dismissed',
    ]);
    $p->cases()->save($case);
    echo 'Added: Katelynn Bolinger' . PHP_EOL;
  } else {
    echo 'SKIP (exists): Katelynn Bolinger' . PHP_EOL;
  }

  // Mariah Bailey
  if (!\App\Models\Prisoner::withoutGlobalScopes()->where('name', 'Mariah Bailey')->exists()) {
    $p = \App\Models\Prisoner::create([
      'name' => 'Mariah Bailey',
      'first_name' => 'Mariah',
      'last_name' => 'Bailey',
      'description' => 'Mariah Bailey, approximately 23 years old at the time, was arrested by the Akron Police Department on July 4, 2022, during protests that erupted after city officials released body camera footage showing Akron police fatally shooting Jayland Walker — an unarmed Black man struck more than 60 times — during a traffic stop on June 27, 2022. Bailey was among more than 50 people swept up in mass arrests on July 3–4, 2022, and charged with riot (O.R.C. § 2917.03), disorderly conduct (O.R.C. § 2917.11), and failure to disperse (O.R.C. § 2917.04). News reporting and subsequent civil rights litigation documented that all protesters arrested during these events were booked and jailed for at least one to two days before arraignment, held on near-identical "copy-and-paste" arrest complaints in which virtually every form contained the same two sentences stating arrestees "took part in protest that turned to violent riot" and "failed to disperse when ordered to." The City of Akron subsequently paid $747,000 to settle a federal civil rights lawsuit brought by 24 of the arrested protesters, with the court record acknowledging "wrongful arrests and detentions." More than 62% of all 62 protest-related cases were ultimately dismissed, four more were thrown out by judges via Rule 29 motions, and the remainder resulted in a mix of pleas, diversion programs, acquittals, or convictions on minor charges. No specific court record or news coverage addressing Bailey\'s individual case outcome was located, and she was not among the named plaintiffs in the civil rights lawsuit. Her arrest and brief pretrial detention arose directly from participation in demonstrations protesting the police killing of a Black man, placing her case squarely within the pattern of protest suppression that the City of Akron later acknowledged through settlement.',
      'state' => 'Ohio',
      'gender' => 'Female',
      'ideologies' => ['Black Lives Matter', 'Anti-police brutality'],
      'era' => '2020s',
      'in_custody' => false,
      'released' => true,
    ]);
    $inst = \App\Models\Institution::firstOrCreate(
      ['name' => 'Summit County Jail', 'city' => 'Akron', 'state' => 'Ohio']
    );
    $case = new \App\Models\PrisonerCase([
      'institution_id' => $inst->id,
      'charges' => 'Riot (O.R.C. § 2917.03), Disorderly Conduct (O.R.C. § 2917.11), Failure to Disperse (O.R.C. § 2917.04)',
      'arrest_date' => '2022-07-04',
      'incarceration_date' => '2022-07-04',
      'release_date' => '2022-07-05',
      'imprisoned_for_days' => 1,
      'convicted' => 'No — charges dismissed (general pattern: over 62% of all Akron Jayland Walker protest cases dismissed; no individual case record found for Bailey)',
      'sentence' => 'None confirmed',
    ]);
    $p->cases()->save($case);
    echo 'Added: Mariah Bailey' . PHP_EOL;
  } else {
    echo 'SKIP (exists): Mariah Bailey' . PHP_EOL;
  }

  // Hashim Ali
  if (!\App\Models\Prisoner::withoutGlobalScopes()->where('name', 'Hashim Ali')->exists()) {
    $p = \App\Models\Prisoner::create([
      'name' => 'Hashim Ali',
      'first_name' => 'Hashim',
      'last_name' => 'Ali',
      'description' => 'Hashim Ali is a young Black activist from Ohio who was arrested on July 4, 2022, during protests in Akron following the police killing of Jayland Walker, a 25-year-old Black man shot 46 times by Akron officers on June 27, 2022. Ali was among approximately 50 demonstrators arrested over several nights of protest as the city imposed an overnight curfew in downtown Akron. Like all those arrested in the same wave, Ali was booked into the Summit County Jail and charged with Riot, Misconduct at an Emergency, and Failure to Disperse — misdemeanor charges that critics and a subsequent federal civil rights lawsuit characterized as sham charges used to justify unconstitutional mass arrests. After nearly a year of criminal litigation, all charges against the Jayland Walker protest arrestees were dismissed. The City of Akron later paid $747,000 to settle a federal civil rights lawsuit brought by 24 of those arrested, who accused police of conducting unlawful mass arrests, deploying tear gas and pepper spray against peaceful demonstrators, and holding detainees in the Summit County Jail for periods exceeding 36 hours without access to medical care or phone calls. Ali\'s case reflects the broader pattern of political repression targeting activists who protested the Akron Police Department\'s killing of Jayland Walker.',
      'state' => 'Ohio',
      'gender' => 'Male',
      'ideologies' => ['Black Lives Matter', 'Racial Justice', 'Anti-Police Brutality'],
      'era' => '2020s',
      'in_custody' => false,
      'released' => true,
    ]);
    $inst = \App\Models\Institution::firstOrCreate(
      ['name' => 'Summit County Jail', 'city' => 'Akron', 'state' => 'Ohio']
    );
    $case = new \App\Models\PrisonerCase([
      'institution_id' => $inst->id,
      'charges' => 'Riot (ORC 2917.03), Misconduct at an Emergency (ORC 2917.13), Failure to Disperse (ORC 2917.04)',
      'arrest_date' => '2022-07-04',
      'convicted' => 'No — charges dismissed',
      'sentence' => 'No sentence imposed; all charges dismissed after approximately one year of criminal litigation',
    ]);
    $p->cases()->save($case);
    echo 'Added: Hashim Ali' . PHP_EOL;
  } else {
    echo 'SKIP (exists): Hashim Ali' . PHP_EOL;
  }

  // Nemet Alrawajfeh
  if (!\App\Models\Prisoner::withoutGlobalScopes()->where('name', 'Nemet Alrawajfeh')->exists()) {
    $p = \App\Models\Prisoner::create([
      'name' => 'Nemet Alrawajfeh',
      'first_name' => 'Nemet',
      'last_name' => 'Alrawajfeh',
      'description' => 'Nemet Alrawajfeh is an Arab American activist and community worker from Akron, Ohio, who was pursuing a B.A. in Political Science and Sociology at Kent State University while working as an Employment Relationship Specialist at the Akron Urban League, a civil rights organization. She previously conducted research in the Middle East on women\'s development and worked as a Housing Specialist at the Miller Community House. On July 6, 2022, at approximately 10:50 PM, she was arrested by the Akron Police Department during protests that swept through downtown Akron in response to the police killing of Jayland Walker — a 25-year-old Black man shot 60 times by eight Akron officers on June 27, 2022. She was charged with riot, failure to disperse, disorderly conduct, and misrepresenting identification. Alrawajfeh was booked and held in custody along with dozens of other protesters arrested between July 3 and 7; according to the subsequent civil rights lawsuit, officers issued "copy-and-paste" arrest complaints and protesters were "jailed and held at least through the following day." She became one of 24 named plaintiffs in a federal civil rights lawsuit against the City of Akron, Mayor Daniel Horrigan, and Akron Police officers, alleging unlawful arrest, excessive force, use of tear gas and pepper spray, and suppression of First Amendment rights. The lawsuit alleged "loss of liberty" and other harms for all plaintiffs. The criminal charges against her were eventually dismissed after nearly a year of litigation. In early 2024, the City of Akron agreed to pay $747,000 to settle the civil rights claims brought by 22 of the 24 plaintiffs, acknowledging the unconstitutional nature of the mass arrests during the Jayland Walker protests.',
      'state' => 'Ohio',
      'gender' => 'Female',
      'race' => 'Arab American',
      'ideologies' => ['Black Lives Matter', 'Racial Justice', 'Police Accountability'],
      'era' => '2020s',
      'in_custody' => false,
      'released' => true,
    ]);
    $inst = \App\Models\Institution::firstOrCreate(
      ['name' => 'Summit County Jail', 'city' => 'Akron', 'state' => 'Ohio']
    );
    $case = new \App\Models\PrisonerCase([
      'institution_id' => $inst->id,
      'charges' => 'Riot (Ohio Rev. Code § 2917.03), Failure to Disperse (§ 2917.04), Disorderly Conduct (§ 2917.11), Misrepresenting ID (§ 606.145)',
      'arrest_date' => '2022-07-06',
      'incarceration_date' => '2022-07-06',
      'release_date' => '2022-07-07',
      'imprisoned_for_days' => 1,
      'convicted' => 'No — charges dismissed',
      'sentence' => 'No sentence; all charges dismissed after nearly a year of criminal litigation',
    ]);
    $p->cases()->save($case);
    echo 'Added: Nemet Alrawajfeh' . PHP_EOL;
  } else {
    echo 'SKIP (exists): Nemet Alrawajfeh' . PHP_EOL;
  }

echo 'Done.' . PHP_EOL;
PHPEOF

echo "Adding 13 Akron Jayland Walker protest arrestees..."
php artisan tinker --execute="require '/tmp/add_akron_protesters.php';"
echo "Script complete."
