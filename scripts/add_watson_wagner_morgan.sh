#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_watson_wagner_morgan.sh
# Three activists imprisoned for actions connected to political uprisings:
#   Shabazz Akeem Isiah Watson — 5 years federal; arson during George Floyd uprising, Charleston SC, May 2020
#   Jamison Wagner — pretrial detention ~446 days; arson of Tesla showroom + GOP HQ, Albuquerque NM, 2025
#   Rebecca Morgan — 15 years federal; harbored Prairieland ICE shooting suspect, Dallas TX, 2025
set +e

cat > /tmp/add_watson_wagner_morgan.php << 'PHPEOF'
<?php

  // Shabazz Akeem Isiah Watson
  if (!\App\Models\Prisoner::withoutGlobalScopes()->where('name', 'Shabazz Akeem Isiah Watson')->exists()) {
    $p = \App\Models\Prisoner::create([
      'name' => 'Shabazz Akeem Isiah Watson',
      'first_name' => 'Shabazz',
      'last_name' => 'Watson',
      'description' => 'Shabazz Akeem Isiah Watson is a Black man from Saint Stephen (Berkeley County), South Carolina, who received a five-year federal prison sentence for arson committed during the George Floyd uprising in Charleston. On the night of May 30–31, 2020, in the hours after demonstrations erupted in downtown Charleston following the murder of George Floyd by Minneapolis police, Watson entered and set fire to four businesses: a wine bar on King Street, a retail clothing store on King Street, a paint store on Meeting Street, and a Family Dollar store at 478 Meeting Street, causing a total of $2,415,510.75 in damages — the majority from the Family Dollar fire, which gutted the building entirely. Watson was initially arrested on October 13, 2020 on state burglary and arson charges; he was later prosecuted federally. On March 20, 2023, he pleaded guilty in federal court to a single count of arson under 18 U.S.C. § 844, with three additional arson counts dismissed in exchange for the plea. On June 7, 2023, U.S. District Judge Richard Gergel sentenced him to five years in federal prison with no parole eligibility, followed by three years of supervised release, and ordered him to pay $2,415,510.75 in restitution. As of July 2026, Watson was still serving his sentence at an undisclosed federal facility.',
      'state' => 'South Carolina',
      'gender' => 'Male',
      'race' => 'Black',
      'ideologies' => ['Black Liberation', 'Anti-Police Brutality'],
      'era' => '2020s',
      'in_custody' => true,
      'released' => false,
    ]);
    $inst = \App\Models\Institution::firstOrCreate(
      ['name' => 'Federal Bureau of Prisons']
    );
    $case = new \App\Models\PrisonerCase([
      'institution_id' => $inst->id,
      'charges' => 'Arson (18 U.S.C. § 844); set fire to four businesses in downtown Charleston during the George Floyd uprising on the night of May 30–31, 2020; caused $2,415,510.75 in damages',
      'arrest_date' => '2020-10-13',
      'incarceration_date' => '2023-06-07',
      'convicted' => 'Yes — guilty plea (March 20, 2023)',
      'sentence' => '5 years federal prison; 3 years supervised release; $2,415,510.75 restitution',
    ]);
    $p->cases()->save($case);
    echo 'Added: Shabazz Akeem Isiah Watson' . PHP_EOL;
  } else {
    echo 'SKIP (exists): Shabazz Akeem Isiah Watson' . PHP_EOL;
  }

  // Jamison Wagner
  if (!\App\Models\Prisoner::withoutGlobalScopes()->where('name', 'Jamison Wagner')->exists()) {
    $p = \App\Models\Prisoner::create([
      'name' => 'Jamison Wagner',
      'first_name' => 'Jamison',
      'last_name' => 'Wagner',
      'description' => 'Jamison R. Wagner, 40, is a queer electrical engineer from Albuquerque, New Mexico, with a B.S. in electrical engineering and a partially completed master’s degree from the University of New Mexico, who previously worked at SolAero Technologies and Intel and co-authored research with Los Alamos National Laboratory. He was arrested on April 12, 2025 and charged with two federal counts of arson in connection with two property destruction actions in early 2025: on February 9, 2025, he allegedly set fire to the Tesla Albuquerque Showroom on the Santa Ana Pueblo in Sandoval County, leaving graffiti reading “Die Elon,” “Tesla Nazi Inc,” and swastika symbols, in apparent response to Elon Musk’s role in the Trump administration; and on March 30, 2025, he allegedly firebombed the Republican Party of New Mexico headquarters in Albuquerque, leaving graffiti reading “ICE=KKK.” FBI and ATF agents found eight assembled incendiary devices, improvised napalm materials, a stencil matching the GOP office graffiti, and a notebook containing plans for “violence against the regime” during a search of his home; DNA from a gin bottle left at the GOP office scene led to his identification. He was ordered held without bail and has remained in federal pretrial detention since April 2025. As of July 2026, he had not entered a plea, with trial scheduled for September 14, 2026 before District Judge Kea W. Riggs; he faces five to twenty years per count on two counts (up to forty years). FBI Director Kash Patel and Attorney General Pam Bondi characterized the case as “domestic terrorism.” Wagner identifies publicly as queer and bisexual and was listed in the “500 Queer Scientists” visibility campaign.',
      'state' => 'New Mexico',
      'gender' => 'Male',
      'ideologies' => ['Anti-fascism', 'Anti-ICE', 'LGBTQ+ liberation', 'Anti-capitalism'],
      'era' => '2020s',
      'in_custody' => true,
      'released' => false,
    ]);
    $inst = \App\Models\Institution::firstOrCreate(
      ['name' => 'Federal Pretrial Detention', 'city' => 'Albuquerque', 'state' => 'New Mexico']
    );
    $case = new \App\Models\PrisonerCase([
      'institution_id' => $inst->id,
      'charges' => 'Two counts of malicious damage or destruction of property by fire (18 U.S.C. § 844(i)); February 9, 2025 Tesla showroom arson (Santa Ana Pueblo, NM) and March 30, 2025 Republican Party of New Mexico headquarters arson (Albuquerque)',
      'arrest_date' => '2025-04-12',
      'incarceration_date' => '2025-04-14',
      'convicted' => 'No — awaiting trial (trial set September 14, 2026)',
      'sentence' => 'Not yet sentenced; faces 5–20 years per count on two counts',
    ]);
    $p->cases()->save($case);
    echo 'Added: Jamison Wagner' . PHP_EOL;
  } else {
    echo 'SKIP (exists): Jamison Wagner' . PHP_EOL;
  }

  // Rebecca Morgan
  if (!\App\Models\Prisoner::withoutGlobalScopes()->where('name', 'Rebecca Morgan')->exists()) {
    $p = \App\Models\Prisoner::create([
      'name' => 'Rebecca Morgan',
      'first_name' => 'Rebecca',
      'last_name' => 'Morgan',
      'description' => 'Rebecca Morgan, approximately 24 years old at the time of her arrest, is a Dallas, Texas resident with a degree in neurobiology who was arrested on July 15, 2025 — eleven days after a shooting attack on the Prairieland ICE Detention Center in Alvarado, Texas on July 4, 2025. Morgan was not present at the protest or shooting; she was charged with providing material support to terrorism for harboring Benjamin Hanil Song, the alleged gunman, at her Dallas apartment for approximately eleven days following the incident. She met Song and a co-defendant at a Dallas Home Depot the day after the shooting. Initially charged with hindering prosecution of terrorism and held at Johnson County Jail on a $2.5 million bond, Morgan was later transferred to Wichita County Detention Center. On November 24, 2025, she pleaded guilty in federal court in Fort Worth to one count of providing material support to terrorists (case no. 4:25-CR-00282-P), declining to cooperate with prosecutors against co-defendants. On July 1, 2026, she was sentenced to 180 months (fifteen years) in federal prison. The Prairieland prosecution was the Trump administration’s first federal terrorism case brought against alleged antifa members; the National Lawyers Guild warned the case could “result in people facing terrorism charges for doing very simple mainstream activism.” Supporters characterize Morgan and the other defendants as people who witnessed mass deportation and “decided to show up.” As of July 2026, she was serving her sentence at an undisclosed federal Bureau of Prisons facility.',
      'state' => 'Texas',
      'gender' => 'Female',
      'ideologies' => ['Anti-fascism', 'Anti-deportation', 'Anti-ICE'],
      'era' => '2020s',
      'in_custody' => true,
      'released' => false,
    ]);
    $inst = \App\Models\Institution::firstOrCreate(
      ['name' => 'Wichita County Detention Center', 'city' => 'Wichita Falls', 'state' => 'Texas']
    );
    $case = new \App\Models\PrisonerCase([
      'institution_id' => $inst->id,
      'charges' => 'Providing material support to terrorists (18 U.S.C. § 2339B); harbored alleged Prairieland ICE Detention Center gunman at her Dallas apartment for 11 days following July 4, 2025 shooting',
      'arrest_date' => '2025-07-15',
      'incarceration_date' => '2025-07-15',
      'imprisoned_for_days' => 351,
      'convicted' => 'Yes — guilty plea (November 24, 2025)',
      'sentence' => '180 months (15 years) federal prison',
    ]);
    $p->cases()->save($case);
    echo 'Added: Rebecca Morgan' . PHP_EOL;
  } else {
    echo 'SKIP (exists): Rebecca Morgan' . PHP_EOL;
  }

echo 'Done.' . PHP_EOL;
PHPEOF

echo "Adding Watson, Wagner, and Morgan via tinker..."
php artisan tinker --execute="require '/tmp/add_watson_wagner_morgan.php';"
echo "Script complete."
