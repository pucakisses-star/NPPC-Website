#!/usr/bin/env bash
#
# Vieques 1999-2003 civil-disobedience wave — audit against the Wikipedia
# article "United States Navy in Vieques, Puerto Rico" (July 2026). The
# database's Vieques coverage is deep (219 records, including Rubén
# Berríos and Rev. Al Sharpton), but six of the prominent 2001
# trespass-jailing cases were missing. All were arrested at Camp García
# protesting the Navy's bombing exercises after David Sanes's death.
#
#  - Edward James Olmos: 20-day sentence, served 19 days.
#  - Robert F. Kennedy Jr.: 30 days; his son Aidan Caohman Vieques
#    Kennedy was born while he was jailed.
#  - Norma Burgos: PR senator; 40 days raised to 60 when she told the
#    judge the Navy should be on trial instead.
#  - Dennis Rivera: 1199/SEIU president; 30 days.
#  - Jacqueline Jackson: held 10 days refusing $3,000 bail.
#  - Rep. Luis Gutiérrez: arrested and mistreated in custody (a Navy
#    officer forced his face into the dirt with a foot on his neck);
#    later court outcome recorded as not located.
#
# Idempotent: prisoner:add refuses duplicates (|| true).
#
# Run from the repo root:  bash database/data/add-vieques-2001.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{"name":"Edward James Olmos","first_name":"Edward","middle_name":"James","last_name":"Olmos","description":"Edward James Olmos, the Mexican-American actor of Stand and Deliver and Battlestar Galactica, was arrested in April 2001 for trespassing at the U.S. Navy'"'"'s Camp García bombing range on Vieques, Puerto Rico, joining the mass civil disobedience that followed the April 1999 killing of civilian guard David Sanes by an errant Navy bomb. Sentenced to 20 days, he served 19 in a federal detention center in Puerto Rico in the summer of 2001 and returned to Vieques on his release to continue supporting the campaign, which won the Navy'"'"'s withdrawal from the island in May 2003.","state":"California","race":"Hispanic","gender":"Male","birthdate":"1947-02-24","ideologies":["Anti-militarism"],"era":"2000s","released":true,"cases":[{"charges":"Trespassing on U.S. Navy restricted lands at Camp García, Vieques, during the 2001 civil-disobedience campaign","arrest_date":"2001-04-29","convicted":"Yes","sentence":"20 days; served 19 in a federal detention center in Puerto Rico","imprisoned_for_days":19}]}' || true

php artisan prisoner:add '{"name":"Robert F. Kennedy Jr.","first_name":"Robert","middle_name":"F.","last_name":"Kennedy","aka":"RFK Jr.","description":"Robert F. Kennedy Jr., then an environmental lawyer with the Natural Resources Defense Council, was arrested in April 2001 for trespassing at the U.S. Navy'"'"'s Vieques bombing range in the civil-disobedience wave that followed the killing of David Sanes. In July 2001 a federal judge sentenced him to 30 days; he served the sentence in Puerto Rico, and while he was jailed his wife Mary gave birth to their son, whom they named Aidan Caohman Vieques Kennedy in honor of the island. This record concerns only his Vieques civil-disobedience jailing.","state":"New York","race":"White","gender":"Male","birthdate":"1954-01-17","ideologies":["Environmentalism","Anti-militarism"],"era":"2000s","released":true,"cases":[{"charges":"Trespassing on U.S. Navy restricted lands at Vieques during the 2001 civil-disobedience campaign","arrest_date":"2001-04-29","convicted":"Yes","sentence":"30 days (July 2001)","imprisoned_for_days":30}]}' || true

php artisan prisoner:add '{"name":"Norma Burgos","first_name":"Norma","last_name":"Burgos","description":"Norma Burgos, a sitting senator of Puerto Rico from the New Progressive Party, was among the protesters arrested for trespassing on the U.S. Navy'"'"'s Vieques bombing range during the 2001 civil-disobedience campaign. At sentencing in July 2001 the judge gave her 40 days; when she answered that it was the Navy, not the protesters, that should be on trial, the judge called her defiant and raised the term to 60 days. She was later indicted again over the May 2003 celebrations that followed the Navy'"'"'s withdrawal.","state":"Puerto Rico","gender":"Female","ideologies":["Anti-militarism"],"era":"2000s","released":true,"cases":[{"charges":"Trespassing on U.S. Navy restricted lands at Vieques during the 2001 civil-disobedience campaign","convicted":"Yes","sentence":"40 days, raised to 60 by the judge for courtroom defiance (July 2001)","imprisoned_for_days":60}]}' || true

php artisan prisoner:add '{"name":"Dennis Rivera","first_name":"Dennis","last_name":"Rivera","description":"Dennis Rivera, the Puerto Rico-born president of New York'"'"'s 1199/SEIU health-care workers union — then one of the most powerful labor leaders in the United States — was arrested for trespassing at the U.S. Navy'"'"'s Vieques bombing range during the 2001 civil-disobedience campaign and sentenced in July 2001 to 30 days in jail alongside Robert F. Kennedy Jr. and Senator Norma Burgos.","state":"New York","race":"Hispanic","gender":"Male","ideologies":["Labor","Anti-militarism"],"era":"2000s","released":true,"cases":[{"charges":"Trespassing on U.S. Navy restricted lands at Vieques during the 2001 civil-disobedience campaign","convicted":"Yes","sentence":"30 days (July 2001)","imprisoned_for_days":30}]}' || true

php artisan prisoner:add '{"name":"Jacqueline Jackson","first_name":"Jacqueline","last_name":"Jackson","description":"Jacqueline Jackson, civil-rights activist and wife of Rev. Jesse Jackson, was arrested with nine other activists for misdemeanor trespassing at the U.S. Navy'"'"'s Camp García bombing range on Vieques in June 2001. Refusing on principle to post the $3,000 bail, she was held for 10 days in jail in San Juan before her release on June 28, 2001.","state":"Illinois","race":"Black","gender":"Female","ideologies":["Civil rights","Anti-militarism"],"era":"2000s","released":true,"cases":[{"charges":"Misdemeanor trespassing on U.S. Navy restricted lands at Camp García, Vieques","release_date":"2001-06-28","convicted":"Held 10 days after refusing to post $3,000 bail","imprisoned_for_days":10}]}' || true

php artisan prisoner:add '{"name":"Luis Gutiérrez","first_name":"Luis","last_name":"Gutiérrez","description":"Luis Gutiérrez, then a Democratic U.S. representative from Illinois, was arrested in late April 2001 for trespassing at the U.S. Navy'"'"'s Vieques bombing range alongside Edward James Olmos and Robert F. Kennedy Jr. He charged publicly that Navy police mistreated him in custody — video showed him complying with orders while an officer forced his face into the dirt with a foot on his neck. The later disposition of his charge has not been located; this record documents the arrest and custody.","state":"Illinois","race":"Hispanic","gender":"Male","birthdate":"1953-12-10","ideologies":["Puerto Rican rights","Anti-militarism"],"era":"2000s","released":true,"cases":[{"charges":"Trespassing on U.S. Navy restricted lands at Vieques during the 2001 civil-disobedience campaign","arrest_date":"2001-04-29","convicted":"Disposition not located — arrested and detained; he charged mistreatment by Navy police in custody"}]}' || true

php artisan tinker --execute='
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Vieques 2001 additions applied."
