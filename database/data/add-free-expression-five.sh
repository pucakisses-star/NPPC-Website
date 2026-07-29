#!/usr/bin/env bash
#
# Add five missing figures from the free-expression chronology:
#
#   James Franklin      1722  Boston printer jailed for the New-England
#                             Courant, refused to name his author
#   John Peter Zenger   1734  seditious libel, eight months awaiting the
#                             trial that established truth as a defence
#   William F. Davis    1887  street preacher jailed over the Boston
#                             Common permit ordinance
#   Ralph Ginzburg      1972  Eros, convicted on the pandering theory
#   Judith Miller       2005  civil contempt, refused to name a source
#
# WHAT IS NOT HERE. Four more names from the same list -- Clarence H.
# Gilbert, Conway Craig, Bob McCracken and Tom Mulvaney -- are left out
# on purpose. None could be identified from the available sources, and
# a record invented around a plausible-sounding identity is worse than
# no record. Note in particular that the WWI-era Minnesota sedition
# defendant of Gilbert v. Minnesota, 254 U.S. 325 (1920), was JOSEPH
# Gilbert, manager of the Nonpartisan League organization department --
# a different first name, and not assumed to be the same man.
#
# CUSTODY, case by case:
#
#   FRANKLIN   Jailed by order of the Massachusetts General Court after
#              the June 4-11, 1722 Courant suggested the authorities
#              were slack in chasing a pirate off the coast; he refused
#              to name the writer. Accounts differ on the length -- two
#              weeks, three weeks, a month, four weeks -- and agree only
#              that he went in during June and was out by the July 2-9
#              issue. MONTH PRECISION is used for both ends rather than
#              inventing the days: June 1722 to July 1722.
#
#   ZENGER     Arrested November 17, 1734; bail was set at 400 pounds
#              when he swore his whole estate outside his tools and
#              clothes was under 40, so he stayed in. Acquitted and
#              freed August 5, 1735 = 261 days, which matches the
#              "more than eight months" of the standard accounts.
#
#   DAVIS      Two cases. The 1887 imprisonment is the only documented
#              custody: he refused to pay the accumulated fines and the
#              state jailed him, and he wrote Christian Liberties in
#              Boston while inside. NO SOURCE IN HAND GIVES THE LENGTH,
#              so the incarceration is recorded at year precision with
#              NO RELEASE DATE -- the counter stays empty by design
#              (the Andrew Lawrence rule). The 1894 conviction that
#              became Davis v. Massachusetts is a second, DATELESS case:
#              it produced a fine, and a fine is not custody (the
#              Garrett rule).
#
#   GINZBURG   Entered federal custody February 17, 1972, held at the
#              federal prison camp at Allenwood, Pennsylvania, paroled
#              October 11, 1972 = 237 days, the eight months the
#              obituaries describe. The five-year sentence had been cut
#              to three shortly before he reported.
#
#   MILLER     Jailed July 6, 2005, released September 29, 2005 = 85
#              days, which is exactly the figure every account gives
#              and which is why July 6 is used: some sources say July 7,
#              but that would make it 84. The variant is noted on the
#              case.
#
# LIFE DATES are set only where documented. Davis has neither: no birth
# or death date was located, so both stay unset rather than guessed.
#
# The payloads use quoted heredocs so the prose can carry real
# apostrophes without fighting the shell. Each add is allowed to fail
# without aborting the script, so a partial re-run finishes the rest --
# prisoner:add refuses duplicates by name.
#
# Run from the repo root:
#   bash database/data/add-free-expression-five.sh
#
# Afterwards, place the new records in the sort order:
#   php artisan prisoners:place-zero-sort-by-year --apply

set -euo pipefail
cd "$(dirname "$0")/../.."

add() {
    php artisan prisoner:add "$1" || echo "  (skipped -- already exists)"
    echo
}

# ---------------------------------------------------------------- Franklin
read -r -d '' FRANKLIN <<'JSON' || true
{
  "name": "James Franklin",
  "first_name": "James",
  "last_name": "Franklin",
  "description": "James Franklin was the Boston printer who founded The New-England Courant in August 1721, the first newspaper in the American colonies published without the authorities' licence and the first to make a habit of mocking them. In June 1722 the Courant printed a note suggesting that the colonial government was in no hurry to chase a pirate vessel then working the New England coast. The Massachusetts General Court took the piece as a scandalous libel and ordered Franklin jailed; he refused to identify the author and was held roughly four weeks, coming out in early July. His sixteen-year-old apprentice and brother, Benjamin Franklin, brought the paper out in his absence — the same year Benjamin had been slipping the Silence Dogood letters under the print-shop door. Franklin defended himself by citing Chapter 29 of Magna Carta, arguing that no free man could be imprisoned by the magistrates without the lawful judgment of his peers or the law of the land. In January 1723 the General Court forbade him to publish the Courant unless each issue was first submitted for official approval; rather than submit, the paper reappeared under Benjamin's name, and the apprenticeship indentures were secretly cancelled to make the arrangement look lawful — the device that let Benjamin run away to Philadelphia that autumn. James Franklin moved to Newport in 1727 and in 1732 founded the Rhode Island Gazette, the colony's first newspaper. Born in Boston on February 4, 1697, he died in Newport on February 4, 1735, on his thirty-eighth birthday.",
  "state": "Massachusetts",
  "race": "White",
  "gender": "Male",
  "birthdate": "1697-02-04",
  "death_date": "1735-02-04",
  "ideologies": ["Press Freedom", "Civil Liberties"],
  "era": "1700s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "charges": "Scandalous libel — for a note in The New-England Courant of June 4–11, 1722 suggesting that the Massachusetts authorities were lax in pursuing a pirate ship off the coast. Franklin refused to name the writer, and the Massachusetts General Court ordered him imprisoned.",
      "sentence": "Imprisoned by order of the Massachusetts General Court in June 1722 and released in early July, before the July 2–9 issue of the Courant. Accounts of the length differ — two weeks, three weeks, a month, four weeks — so only the months are recorded here rather than invented days. In January 1723 the Court went further and forbade him to print the Courant without submitting each issue for prior approval; the paper continued under his brother Benjamin's name instead."
    }
  ]
}
JSON
add "$FRANKLIN"

# ------------------------------------------------------------------ Zenger
read -r -d '' ZENGER <<'JSON' || true
{
  "name": "John Peter Zenger",
  "first_name": "John",
  "middle_name": "Peter",
  "last_name": "Zenger",
  "description": "John Peter Zenger was the New York printer whose acquittal on a charge of seditious libel became the founding episode of the American free press. Born in the Rhenish Palatinate on October 26, 1697, he came to New York in 1710 as a boy among the Palatine refugees and was apprenticed to William Bradford, the colony's official printer. In November 1733 he began printing the New-York Weekly Journal, the paper of the faction opposing Governor William Cosby, which attacked the governor for removing a chief justice who had ruled against him and for rigging elections. Cosby could not persuade two successive grand juries to indict, so his attorney general, Richard Bradley, proceeded by information instead, and on November 17, 1734 the sheriff arrested Zenger and jailed him in the cells at New York's City Hall. Bail was fixed at 400 pounds; Zenger swore that his entire estate apart from his tools and his clothes came to less than 40, and so he stayed in prison for more than eight months awaiting trial. The Journal missed a single issue: his wife, Anna Catharina Maulin Zenger, kept it printing, taking his directions, by the standard account, through a hole in the door of his cell. His own lawyers, James Alexander and William Smith, were disbarred by Chief Justice James DeLancey for challenging the judges' commissions, and the aged Philadelphia lawyer Andrew Hamilton came north to take the case. Under the law as it stood, truth was no defence — the more truthful the libel, the greater the offence — and DeLancey refused to let Zenger prove that what he had printed was true. Hamilton conceded the printing and appealed over the judge's head to the jury, telling them the question was the liberty of exposing and opposing arbitrary power. The jury returned a verdict of not guilty in about ten minutes on August 5, 1735, and Zenger walked out after 261 days inside. The verdict changed no law, but no royal governor in America tried a printer for seditious libel with much confidence afterwards. Zenger later became public printer for the colonies of New York and New Jersey, and died in New York City on July 28, 1746.",
  "state": "New York",
  "race": "White",
  "gender": "Male",
  "birthdate": "1697-10-26",
  "death_date": "1746-07-28",
  "ideologies": ["Press Freedom", "Civil Liberties"],
  "era": "1700s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "New York City Hall Jail",
      "institution_city": "New York",
      "institution_state": "New York",
      "charges": "Seditious libel — for articles in the New-York Weekly Journal attacking Governor William Cosby. Two grand juries refused to indict, so Attorney General Richard Bradley proceeded by information.",
      "convicted": "No — acquitted by the jury on August 5, 1735, after about ten minutes, in the trial that began August 4 before Chief Justice James DeLancey.",
      "arrest_date": "1734-11-17",
      "incarceration_date": "1734-11-17",
      "release_date": "1735-08-05",
      "judge": "Chief Justice James DeLancey",
      "prosecutor": "Attorney General Richard Bradley",
      "sentence": "No sentence — acquitted. He was held from his arrest on November 17, 1734 until the verdict on August 5, 1735 because bail was set at 400 pounds and he swore his whole estate outside his tools and clothing was worth under 40. His counsel James Alexander and William Smith were disbarred before trial for challenging the judges' commissions; Andrew Hamilton of Philadelphia argued the case, conceding the printing and asking the jury to treat truth as a defence though the court had ruled it was not."
    }
  ]
}
JSON
add "$ZENGER"

# ------------------------------------------------------------------- Davis
read -r -d '' DAVIS <<'JSON' || true
{
  "name": "William F. Davis",
  "first_name": "William",
  "middle_name": "F.",
  "last_name": "Davis",
  "description": "William F. Davis was an itinerant street preacher who spent more than a decade challenging Boston's control of its public ground. A city ordinance of 1862 required the mayor's permit before anyone could deliver any sermon, lecture, address or discourse on city land. In 1884 Davis applied for permission to preach on Boston Common, was refused, preached anyway and was arrested. He went on preaching there for years, declining as a matter of principle to ask again, and the city fined him over and over; he would not pay, and in 1887 the state finally imprisoned him. He used the time to write Christian Liberties in Boston, a pamphlet which argued not only the freedom of religion he had claimed all along but something newer — that a permit system for open-air speaking was an offence against freedom of speech itself, at a time when almost no one framed the public square that way. Convicted again in 1894 for unlicensed preaching on the Common, he appealed to the Supreme Judicial Court of Massachusetts, where Justice Oliver Wendell Holmes Jr. dismissed the claim in 1895 with the observation that the legislature might lawfully forbid public speaking on the Common exactly as the owner of a private house might forbid it in his parlour. The Supreme Court of the United States affirmed unanimously in Davis v. Massachusetts, 167 U.S. 43, argued March 25 and decided May 10, 1897, adopting Holmes's reasoning wholesale. The Davis rule — that public parks and streets carry no right of assembly against the government that owns them — governed American law until Hague v. Committee for Industrial Organization overturned it in 1939.",
  "state": "Massachusetts",
  "race": "White",
  "gender": "Male",
  "ideologies": ["Religious Liberty", "Civil Liberties"],
  "era": "1800s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "charges": "Preaching on Boston Common without the mayor's permit, in violation of the Boston ordinance of 1862 requiring permission for any sermon, lecture, address or discourse on city ground. He refused to pay the accumulated fines and was imprisoned.",
      "incarceration_date": "1887-01-01",
      "sentence": "Imprisoned in 1887 after refusing to pay the fines that had accumulated since his first arrest in 1884. He wrote the pamphlet Christian Liberties in Boston while inside. NO SOURCE LOCATED GIVES THE LENGTH OF THE TERM OR THE JAIL, so the year is recorded without a release date and the imprisonment counter stays empty rather than carry an invented figure."
    },
    {
      "charges": "Preaching on Boston Common without a permit — the 1894 conviction that became Davis v. Massachusetts, 167 U.S. 43 (1897).",
      "convicted": "Yes — 1894. Affirmed by the Supreme Judicial Court of Massachusetts in 1895, Justice Oliver Wendell Holmes Jr. writing, and unanimously by the Supreme Court of the United States on May 10, 1897.",
      "judge": "Justice Oliver Wendell Holmes Jr. (Supreme Judicial Court of Massachusetts, 1895)",
      "sentence": "A fine. No custody is recorded for this case: the penalty under review in Davis v. Massachusetts was monetary, and a fine is not imprisonment. The case is listed without dates because it is the conviction that carried his argument to the Supreme Court, not a second term inside."
    }
  ]
}
JSON
add "$DAVIS"

# ---------------------------------------------------------------- Ginzburg
read -r -d '' GINZBURG <<'JSON' || true
{
  "name": "Ralph Ginzburg",
  "first_name": "Ralph",
  "last_name": "Ginzburg",
  "description": "Ralph Ginzburg was the magazine publisher whose federal obscenity conviction produced one of the strangest doctrines in American First Amendment law. In 1962 he brought out Eros, a lavish hardbound quarterly on love and sex which ran for four issues; he also published the newsletter Liaison and The Housewife's Handbook on Selective Promiscuity. Indicted in Philadelphia in 1963, he was convicted on twenty-eight counts of violating the federal obscenity statute and sentenced to five years in prison and a $42,000 fine. The Supreme Court affirmed 5 to 4 on March 21, 1966 in Ginzburg v. United States, 383 U.S. 463. Justice Brennan's majority did not hold that the publications were themselves obscene; it held that the way Ginzburg had sold them — the leering advertising, the applications for mailing privileges from the Pennsylvania towns of Intercourse and Blue Ball — amounted to pandering, and that this could tip otherwise protected material over the line. Four justices dissented sharply, Justice Black observing that Ginzburg was going to prison for a crime he could not have known existed until the Court announced it. Appeals dragged for six more years, and the sentence was cut from five years to three shortly before he reported. He entered federal custody on February 17, 1972, served at the federal prison camp at Allenwood, Pennsylvania, and was paroled on October 11, 1972, after about eight months. Ginzburg had earlier published 100 Years of Lynchings (1962), a documentary collection of contemporary newspaper accounts of lynchings in America, and the magazine fact:, whose survey of psychiatrists on Barry Goldwater led to the libel judgment in Goldwater v. Ginzburg; he later published Avant Garde and Moneysworth and worked as a New York press photographer. Born in Brooklyn on October 28, 1929, he died of multiple myeloma in Riverdale, New York on July 6, 2006.",
  "state": "Pennsylvania",
  "race": "White",
  "gender": "Male",
  "birthdate": "1929-10-28",
  "death_date": "2006-07-06",
  "ideologies": ["Press Freedom", "Civil Liberties"],
  "era": "1970s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Federal Prison Camp, Allenwood",
      "institution_city": "Allenwood",
      "institution_state": "Pennsylvania",
      "charges": "Twenty-eight counts of violating the federal obscenity statute, for mailing Eros magazine, the newsletter Liaison and The Housewife's Handbook on Selective Promiscuity. Indicted in Philadelphia in 1963.",
      "convicted": "Yes — 1963. Affirmed 5–4 by the Supreme Court on March 21, 1966 in Ginzburg v. United States, 383 U.S. 463, on the ground that the pandering manner of the advertising, rather than the contents themselves, put the publications outside protection.",
      "incarceration_date": "1972-02-17",
      "release_date": "1972-10-11",
      "sentence": "Five years and a $42,000 fine, reduced to three years shortly before he surrendered. He entered custody on February 17, 1972, was held at the federal prison camp at Allenwood, Pennsylvania, and was paroled on October 11, 1972 after 237 days — the eight months usually cited. Nearly nine years passed between conviction and imprisonment while the appeals ran."
    }
  ]
}
JSON
add "$GINZBURG"

# ------------------------------------------------------------------ Miller
read -r -d '' MILLER <<'JSON' || true
{
  "name": "Judith Miller",
  "first_name": "Judith",
  "last_name": "Miller",
  "description": "Judith Miller was a New York Times reporter jailed for eighty-five days in 2005 for refusing to identify a confidential source to a federal grand jury. The grand jury, directed by special counsel Patrick Fitzgerald, was investigating who had disclosed that Valerie Plame worked for the Central Intelligence Agency. Miller had never written an article about Plame; she was subpoenaed over conversations she had had with a source, and she refused to testify, arguing that a promise of confidentiality does not lapse because a prosecutor finds it inconvenient. Judge Thomas F. Hogan of the federal district court in Washington held her in civil contempt on October 1, 2004; the Court of Appeals for the District of Columbia Circuit upheld the order, and the Supreme Court declined to hear the case in June 2005. She was jailed on July 6, 2005 at the Alexandria Detention Center in Virginia — civil contempt, so confinement was coercive rather than punitive, and would have ended the moment she agreed to testify. She was released on September 29, 2005 after her source, Vice President Cheney's chief of staff I. Lewis Libby, personally reaffirmed to her that he waived confidentiality; she testified the following day, and again at Libby's trial in January 2007. Her jailing became the central example in the campaign for a federal shield law, which has still not passed. Miller left the Times later in 2005 after her role in the paper's pre-war reporting on Iraqi weapons of mass destruction had come under sustained internal criticism, and afterwards wrote for other outlets and published a memoir. She was born in New York City on January 2, 1948.",
  "state": "Virginia",
  "race": "White",
  "gender": "Female",
  "birthdate": "1948-01-02",
  "ideologies": ["Press Freedom", "Civil Liberties"],
  "era": "2000s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Alexandria Detention Center",
      "institution_city": "Alexandria",
      "institution_state": "Virginia",
      "charges": "Civil contempt of court — refusing to testify before the federal grand jury investigating the disclosure of Valerie Plame's employment by the Central Intelligence Agency, and refusing to identify her confidential source.",
      "convicted": "Held in civil contempt by Judge Thomas F. Hogan on October 1, 2004. The D.C. Circuit affirmed and the Supreme Court declined review in June 2005.",
      "incarceration_date": "2005-07-06",
      "release_date": "2005-09-29",
      "judge": "Thomas F. Hogan",
      "prosecutor": "Special Counsel Patrick Fitzgerald",
      "sentence": "Coercive civil confinement rather than a criminal sentence: she could have ended it at any point by agreeing to testify, and it would in any event have expired with the grand jury. Eighty-five days, July 6 to September 29, 2005, at the Alexandria Detention Center. She was freed after I. Lewis Libby personally reaffirmed his waiver of confidentiality, and testified the next day. Some accounts date the jailing July 7; July 6 is used here because it is the date that yields the eighty-five days every account reports."
    }
  ]
}
JSON
add "$MILLER"

# --------------------------------------------------------------- precision
# prisoner:add writes date columns straight through, which cannot record
# that only part of a date is known. Franklin has months and no days;
# Davis has a year and nothing else.
php artisan tinker --execute='
use App\Models\Prisoner;

$franklin = Prisoner::withoutGlobalScopes()->where("slug", "james-franklin")->with("cases")->first();
if ($franklin && $case = $franklin->cases->first()) {
    $case->setPartialDate("incarceration_date", 1722, 6);
    $case->setPartialDate("release_date", 1722, 7);
    $case->save();
}

$davis = Prisoner::withoutGlobalScopes()->where("slug", "william-f-davis")->with("cases")->first();
if ($davis && $case = $davis->cases->first()) {
    $case->setPartialDate("incarceration_date", 1887);
    $case->save();
}

foreach (["james-franklin", "john-peter-zenger", "william-f-davis", "ralph-ginzburg", "judith-miller"] as $slug) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->with("cases")->first();
    if (! $p) {
        echo "MISSING: {$slug}\n";
        continue;
    }
    $total = 0;
    foreach ($p->cases as $c) { $total += (int) $c->imprisoned_for_days; }
    echo str_pad($p->name, 20)."[{$p->slug}]\n";
    echo "   born ".($p->formatPartialDate("birthdate") ?: "-")."   died ".($p->formatPartialDate("death_date") ?: "-")."   cases ".$p->cases->count()."\n";
    foreach ($p->cases as $c) {
        echo "   inc ".str_pad($c->formatPartialDate("incarceration_date") ?: "-", 14)
            ." rel ".str_pad($c->formatPartialDate("release_date") ?: "-", 14)
            ." days ".($c->imprisoned_for_days ?? "null")."\n";
    }
    echo "   TOTAL days = {$total}\n";
}
echo "\nExpected: Franklin 30 (month precision, about four weeks), Zenger 261, Davis 0 (length undocumented), Ginzburg 237, Miller 85.\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Now run: php artisan prisoners:place-zero-sort-by-year --apply"
