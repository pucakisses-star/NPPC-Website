#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_face_act_prisoners.sh
# Adds 11 pro-life activists convicted under the FACE Act and conspiracy statutes.
# 10 from the October 22, 2020 Washington Surgi-Clinic blockade (DC);
# 1 (Heather Idoni) from a March 2021 Mount Juliet, Tennessee blockade.
set -e

echo "Adding Lauren Handy..."
php artisan prisoner:add '{
  "name": "Lauren Handy",
  "first_name": "Lauren",
  "last_name": "Handy",
  "description": "Lauren Handy is a pro-life activist and organizer from Alexandria, Virginia who organized and led the October 22, 2020 blockade of the Washington Surgi-Clinic in Washington, D.C. She obtained entry by making a fake patient appointment, then used \"lock and block\" tactics in which activists chained themselves together to obstruct clinic staff and patients. Handy and eight co-defendants were convicted in the U.S. District Court for the District of Columbia on charges of violating the Freedom of Access to Clinic Entrances (FACE) Act and conspiracy against rights. She received the harshest sentence of any defendant: 57 months in federal prison plus three years of supervised release, handed down by Judge Colleen Kollar-Kotelly in May 2024. Critics of the prosecution argued the Biden-era DOJ selectively enforced the FACE Act against pro-life demonstrators while declining to prosecute vandalism and arson attacks against pregnancy resource centers.",
  "state": "Virginia",
  "gender": "Female",
  "ideologies": ["Pro-life", "Anti-abortion", "Civil disobedience"],
  "era": "2020s",
  "in_custody": false,
  "released": false,
  "cases": [
    {
      "institution_name": "Federal Bureau of Prisons",
      "institution_state": "District of Columbia",
      "charges": "Violation of the Freedom of Access to Clinic Entrances (FACE) Act; conspiracy against rights; blockade of Washington Surgi-Clinic, October 22, 2020",
      "arrest_date": "2020-10-22",
      "incarceration_date": "2024-05-14",
      "sentence": "57 months federal prison plus 3 years supervised release"
    }
  ]
}'

echo "Adding Jonathan Darnel..."
php artisan prisoner:add '{
  "name": "Jonathan Darnel",
  "first_name": "Jonathan",
  "last_name": "Darnel",
  "description": "Jonathan Darnel is an Evangelical Christian activist and former U.S. Army captain from Arlington, Virginia. On October 22, 2020, he participated in the blockade of the Washington Surgi-Clinic in Washington, D.C., livestreaming the action. He had a prior 2022 conviction for trespass during a separate Red Rose Rescue operation. Convicted of violating the Freedom of Access to Clinic Entrances (FACE) Act and conspiracy against rights, Darnel was sentenced in May 2024 by Judge Colleen Kollar-Kotelly to 34 months in federal prison plus three years of supervised release.",
  "state": "Virginia",
  "gender": "Male",
  "ideologies": ["Pro-life", "Anti-abortion", "Civil disobedience", "Evangelical Christian"],
  "era": "2020s",
  "in_custody": false,
  "released": false,
  "cases": [
    {
      "institution_name": "Federal Bureau of Prisons",
      "institution_state": "District of Columbia",
      "charges": "Violation of the FACE Act; conspiracy against rights; blockade of Washington Surgi-Clinic, October 22, 2020",
      "arrest_date": "2020-10-22",
      "incarceration_date": "2024-05-14",
      "sentence": "34 months federal prison plus 3 years supervised release"
    }
  ]
}'

echo "Adding Joan Andrews Bell..."
php artisan prisoner:add '{
  "name": "Joan Andrews Bell",
  "first_name": "Joan",
  "last_name": "Andrews Bell",
  "description": "Joan Andrews Bell, 76, is a veteran pro-life activist from Montague, New Jersey who has been engaged in anti-abortion civil disobedience since Roe v. Wade was decided in 1973. A longtime participant in Operation Rescue and similar groups, she has prior convictions in Baltimore, St. Louis, Pittsburgh, and Florida related to abortion clinic protests spanning decades. On October 22, 2020, she participated in the blockade of the Washington Surgi-Clinic in Washington, D.C. Convicted of violating the Freedom of Access to Clinic Entrances (FACE) Act and conspiracy against rights, she was sentenced in May 2024 by Judge Colleen Kollar-Kotelly to 27 months in federal prison plus three years of supervised release.",
  "state": "New Jersey",
  "gender": "Female",
  "ideologies": ["Pro-life", "Anti-abortion", "Civil disobedience", "Catholic social teaching"],
  "era": "2020s",
  "in_custody": false,
  "released": false,
  "cases": [
    {
      "institution_name": "Federal Bureau of Prisons",
      "institution_state": "District of Columbia",
      "charges": "Violation of the FACE Act; conspiracy against rights; blockade of Washington Surgi-Clinic, October 22, 2020",
      "arrest_date": "2020-10-22",
      "incarceration_date": "2024-05-14",
      "sentence": "27 months federal prison plus 3 years supervised release"
    }
  ]
}'

echo "Adding William Goodman..."
php artisan prisoner:add '{
  "name": "William Goodman",
  "first_name": "William",
  "last_name": "Goodman",
  "description": "William Goodman, 54, is a pro-life activist from the Bronx, New York who participated in the October 22, 2020 blockade of the Washington Surgi-Clinic in Washington, D.C. He had a prior 2022 conviction for trespass during a Red Rose Rescue operation. Convicted of violating the Freedom of Access to Clinic Entrances (FACE) Act and conspiracy against rights, Goodman was sentenced in May 2024 by Judge Colleen Kollar-Kotelly to 27 months in federal prison plus three years of supervised release.",
  "state": "New York",
  "gender": "Male",
  "ideologies": ["Pro-life", "Anti-abortion", "Civil disobedience"],
  "era": "2020s",
  "in_custody": false,
  "released": false,
  "cases": [
    {
      "institution_name": "Federal Bureau of Prisons",
      "institution_state": "District of Columbia",
      "charges": "Violation of the FACE Act; conspiracy against rights; blockade of Washington Surgi-Clinic, October 22, 2020",
      "arrest_date": "2020-10-22",
      "incarceration_date": "2024-05-14",
      "sentence": "27 months federal prison plus 3 years supervised release"
    }
  ]
}'

echo "Adding Herb Geraghty..."
php artisan prisoner:add '{
  "name": "Herb Geraghty",
  "first_name": "Herb",
  "last_name": "Geraghty",
  "description": "Herb Geraghty, 27, is a pro-life activist from Pittsburgh, Pennsylvania and a board member of the Pro-Life Alliance of Gays and Lesbians. He participated in the October 22, 2020 blockade of the Washington Surgi-Clinic in Washington, D.C. Convicted of violating the Freedom of Access to Clinic Entrances (FACE) Act and conspiracy against rights, Geraghty was sentenced in May 2024 by Judge Colleen Kollar-Kotelly to 27 months in federal prison plus three years of supervised release.",
  "state": "Pennsylvania",
  "gender": "Male",
  "ideologies": ["Pro-life", "Anti-abortion", "Civil disobedience", "LGBTQ rights"],
  "era": "2020s",
  "in_custody": false,
  "released": false,
  "cases": [
    {
      "institution_name": "Federal Bureau of Prisons",
      "institution_state": "District of Columbia",
      "charges": "Violation of the FACE Act; conspiracy against rights; blockade of Washington Surgi-Clinic, October 22, 2020",
      "arrest_date": "2020-10-22",
      "incarceration_date": "2024-05-14",
      "sentence": "27 months federal prison plus 3 years supervised release"
    }
  ]
}'

echo "Adding Jean Marshall..."
php artisan prisoner:add '{
  "name": "Jean Marshall",
  "first_name": "Jean",
  "last_name": "Marshall",
  "description": "Jean Marshall, 73, is a Secular Franciscan and pro-life activist from Kingston, Massachusetts. She participated in the October 22, 2020 blockade of the Washington Surgi-Clinic in Washington, D.C. alongside her sister Paula \"Paulette\" Harlow, who received an identical sentence. Convicted of violating the Freedom of Access to Clinic Entrances (FACE) Act and conspiracy against rights, Marshall was sentenced in May 2024 by Judge Colleen Kollar-Kotelly to 24 months in federal prison plus three years of supervised release.",
  "state": "Massachusetts",
  "gender": "Female",
  "ideologies": ["Pro-life", "Anti-abortion", "Civil disobedience", "Catholic social teaching"],
  "era": "2020s",
  "in_custody": false,
  "released": false,
  "cases": [
    {
      "institution_name": "Federal Bureau of Prisons",
      "institution_state": "District of Columbia",
      "charges": "Violation of the FACE Act; conspiracy against rights; blockade of Washington Surgi-Clinic, October 22, 2020",
      "arrest_date": "2020-10-22",
      "incarceration_date": "2024-05-14",
      "sentence": "24 months federal prison plus 3 years supervised release"
    }
  ]
}'

echo "Adding Paula Harlow..."
php artisan prisoner:add '{
  "name": "Paula Harlow",
  "first_name": "Paula",
  "last_name": "Harlow",
  "description": "Paula \"Paulette\" Harlow, 75, is a Catholic grandmother and Secular Franciscan from Kingston, Massachusetts who uses a wheelchair due to illness. She participated in the October 22, 2020 blockade of the Washington Surgi-Clinic in Washington, D.C. alongside her sister Jean Marshall. Court documents described her chaining herself to co-defendants using a bike lock around her neck and blocking the clinic'\''s main entrance. She was the 10th and final defendant sentenced in the DC blockade case. Convicted of violating the Freedom of Access to Clinic Entrances (FACE) Act and conspiracy against rights, Harlow was sentenced on May 31, 2024 by Judge Colleen Kollar-Kotelly to 24 months in federal prison plus three years of supervised release.",
  "state": "Massachusetts",
  "gender": "Female",
  "ideologies": ["Pro-life", "Anti-abortion", "Civil disobedience", "Catholic social teaching"],
  "era": "2020s",
  "in_custody": false,
  "released": false,
  "cases": [
    {
      "institution_name": "Federal Bureau of Prisons",
      "institution_state": "District of Columbia",
      "charges": "Violation of the FACE Act; conspiracy against rights; blockade of Washington Surgi-Clinic, October 22, 2020",
      "arrest_date": "2020-10-22",
      "incarceration_date": "2024-05-31",
      "sentence": "24 months federal prison plus 3 years supervised release"
    }
  ]
}'

echo "Adding John Hinshaw..."
php artisan prisoner:add '{
  "name": "John Hinshaw",
  "first_name": "John",
  "last_name": "Hinshaw",
  "description": "John Hinshaw, 69, is a pro-life activist from Levittown, New York who participated in the first March for Life in 1974 and has been active in anti-abortion civil disobedience for decades, including Red Rose Rescue operations. He participated in the October 22, 2020 blockade of the Washington Surgi-Clinic in Washington, D.C. Convicted of violating the Freedom of Access to Clinic Entrances (FACE) Act and conspiracy against rights, Hinshaw was sentenced in May 2024 by Judge Colleen Kollar-Kotelly to 21 months in federal prison plus three years of supervised release.",
  "state": "New York",
  "gender": "Male",
  "ideologies": ["Pro-life", "Anti-abortion", "Civil disobedience", "Catholic social teaching"],
  "era": "2020s",
  "in_custody": false,
  "released": false,
  "cases": [
    {
      "institution_name": "Federal Bureau of Prisons",
      "institution_state": "District of Columbia",
      "charges": "Violation of the FACE Act; conspiracy against rights; blockade of Washington Surgi-Clinic, October 22, 2020",
      "arrest_date": "2020-10-22",
      "incarceration_date": "2024-05-15",
      "sentence": "21 months federal prison plus 3 years supervised release"
    }
  ]
}'

echo "Adding Jay Smith..."
php artisan prisoner:add '{
  "name": "Jay Smith",
  "first_name": "Jay",
  "last_name": "Smith",
  "description": "Jay Smith, 33, is a pro-life activist from Freeport, New York who participated in the October 22, 2020 blockade of the Washington Surgi-Clinic in Washington, D.C. He pleaded guilty and was the first defendant in the case to be sentenced. In March 2023, he received a 10-month federal prison sentence for his role in the blockade, which violated the Freedom of Access to Clinic Entrances (FACE) Act.",
  "state": "New York",
  "gender": "Male",
  "ideologies": ["Pro-life", "Anti-abortion", "Civil disobedience"],
  "era": "2020s",
  "in_custody": false,
  "released": true,
  "cases": [
    {
      "institution_name": "Federal Bureau of Prisons",
      "institution_state": "District of Columbia",
      "charges": "Violation of the FACE Act; guilty plea; blockade of Washington Surgi-Clinic, October 22, 2020",
      "arrest_date": "2020-10-22",
      "incarceration_date": "2023-03-01",
      "sentence": "10 months federal prison",
      "imprisoned_for_days": 304
    }
  ]
}'

echo "Adding Heather Idoni..."
php artisan prisoner:add '{
  "name": "Heather Idoni",
  "first_name": "Heather",
  "last_name": "Idoni",
  "description": "Heather Idoni, 62, is a pro-life activist from Linden, Michigan who was convicted in two separate federal FACE Act cases. She participated in the October 22, 2020 blockade of the Washington Surgi-Clinic in Washington, D.C. as well as a March 2021 blockade at a reproductive health clinic in Mount Juliet, Tennessee. Idoni has been incarcerated since August 2023. Convicted on charges of violating the Freedom of Access to Clinic Entrances (FACE) Act and conspiracy against rights in both cases, she was sentenced to 24 months in federal prison in connection with the Tennessee case. Reports from supporters documented mistreatment by U.S. Marshals during her incarceration.",
  "state": "Michigan",
  "gender": "Female",
  "ideologies": ["Pro-life", "Anti-abortion", "Civil disobedience"],
  "era": "2020s",
  "in_custody": true,
  "released": false,
  "cases": [
    {
      "institution_name": "Federal Bureau of Prisons",
      "institution_state": "District of Columbia",
      "charges": "Violation of the FACE Act; conspiracy against rights; blockade of Washington Surgi-Clinic, October 22, 2020",
      "arrest_date": "2020-10-22",
      "incarceration_date": "2023-08-01",
      "sentence": "24 months federal prison (Tennessee case)"
    }
  ]
}'

echo "All 11 FACE Act prisoners added."
