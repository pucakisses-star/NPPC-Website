<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;

/**
 * Register Conrad Rockenhaus — U.S. Air Force veteran, longtime Tor
 * relay / exit-node operator, currently held at FCI Milan on a 2025
 * supervised-release revocation arising from a 2019 federal CFAA
 * conviction (E.D. Tex. Sherman Div., 4:19-cr-00181) for the 2014
 * shutdown of a former employer's Plano, TX servers. His wife
 * Adrienne Rockenhaus has publicly documented the FBI's interest in
 * his Tor exit nodes, alleged retaliation through the U.S. Probation
 * Office, and ongoing medical neglect / SHU placement at FCI Milan
 * (alleged chemical restraint with Zyprexa; allegations against named
 * officers; Sixth Circuit appeal pending).
 *
 * Idempotent — re-runs as no-op if Prisoner row already exists.
 */
final class AddConradRockenhausPrisoner extends Command {
    protected $signature = 'prisoner:add-conrad-rockenhaus';
    protected $description = 'Add Conrad Rockenhaus (Tor exit-node operator, FCI Milan) as a prisoner';

    public function handle(): int {
        if (Prisoner::where('name', 'Conrad Rockenhaus')->exists()) {
            $this->line('Conrad Rockenhaus already exists — no-op.');
            return self::SUCCESS;
        }

        $description = "U.S. Air Force veteran, longtime Tor relay and exit-node operator, and federal prisoner currently held at FCI Milan (Michigan). Rockenhaus was first federally charged in 2019 in the Eastern District of Texas (Sherman Division, case 4:19-cr-00181) under the Computer Fraud and Abuse Act, 18 U.S.C. § 1030(a)(5)(A) — \"intentional damage to a protected computer\" — for a November 11, 2014 incident in which prosecutors alleged he used residual VPN access to disconnect storage from servers at a former employer (an online travel-booking company headquartered in Plano, Texas), causing roughly \$564,000 in claimed damages. He was held approximately three years in pretrial detention in Texas before resolving the case by plea.\n\nThe Tor operation is central to how the case has been understood by movement digital-rights organizers. Rockenhaus had run Tor relays and exit nodes for years, and his wife, Adrienne Rockenhaus, has publicly documented that federal interest in his case intensified after he refused to assist the FBI with decryption / monitoring requests directed at traffic transiting his exit nodes. (Tor exit-node operators have no ability to decrypt end-to-end TLS traffic, but the demand and the refusal are part of his and his wife's public record.)\n\nIn 2025 the federal government moved against Rockenhaus a second time, this time through the U.S. Probation Office in the Eastern District of Michigan, where his supervised release had transferred. On September 4, 2025, a U.S. Marshals \"tactical breach\" raided the Rockenhaus home, and the government moved for supervised-release revocation on a stacked set of alleged technical violations (cannabis use, missed restitution, lost contact with probation, unauthorized credit lines, unauthorized phone, and use of SPICE remote-desktop software). His defense team has contested every allegation — Marinol is prescribed for service-connected PTSD; restitution receipts exist; phone and SPICE were authorized work / doctoral-studies tools; credit lines are alleged identity theft. He is currently detained at FCI Milan and his case is on appeal at the U.S. Court of Appeals for the Sixth Circuit.\n\nWhile at FCI Milan, Rockenhaus has been placed in the Special Housing Unit (SHU). In a January 4, 2026 call from the SHU his family released publicly, he identified Officers Crenshaw and Zielinski — previously reported to Magistrate Judge Jonathan J.C. Grey for allegedly displaying white-supremacist tattoos — as the staff physically abusing him, stated that BOP is denying his prescribed seizure medication and instead administering 10 mg of Zyprexa (an antipsychotic contraindicated with his trauma medication), and reported that he is now having seizures roughly weekly after being seizure-free for a year prior to incarceration. He is a documented traumatic-brain-injury veteran. His defense counsel of record is Kaycee Berente; Marc R. Lakin previously represented him. The Eastern District of Michigan district judge handling the revocation matter is Stephen J. Murphy III; the SAUSA prosecuting the revocation is Corinne Lambert; the U.S. Attorney for E.D. Mich. as of December 18, 2025 is Jerome F. Gorgon Jr.\n\nNPPC lists Rockenhaus on the U.S. political-prisoner roster on the documented record that federal interest in his case escalated through his operation of Tor exit nodes and his refusal of FBI decryption demands, and on the documented record of ongoing medical and physical mistreatment in BOP custody. Support and updates at rockenhaus.com.";

        $payload = [
            'name'          => 'Conrad Rockenhaus',
            'first_name'    => 'Conrad',
            'last_name'     => 'Rockenhaus',
            'description'   => $description,
            'state'         => 'Michigan',
            'gender'        => 'Male',
            'ideologies'    => ['Digital Privacy', 'Free Speech', 'Anti-Surveillance'],
            'affiliation'   => ['Tor exit-node operator', 'U.S. Air Force veteran'],
            'era'           => 'Contemporary',
            'in_custody'    => true,
            'released'      => false,
            'website'       => 'https://rockenhaus.com',
            'cases'         => [
                [
                    'institution_name'  => 'FCI Milan',
                    'institution_city'  => 'Milan',
                    'institution_state' => 'Michigan',
                    'charges'           => 'Supervised-release revocation arising from 2019 federal conviction under 18 U.S.C. § 1030(a)(5)(A) (Computer Fraud and Abuse Act — intentional damage to a protected computer), E.D. Tex. Sherman Div., No. 4:19-cr-00181, for a November 11, 2014 incident at a former employer in Plano, Texas. 2025 revocation alleged in E.D. Mich. on technical violations (cannabis, restitution, contact, credit lines, phone, SPICE remote-desktop software) — all contested by defense.',
                    'arrest_date'       => '2019-06-25',
                    'incarceration_date'=> '2025-09-04',
                    'convicted'         => 'Pled — 18 U.S.C. § 1030(a)(5)(A) (CFAA), E.D. Tex. No. 4:19-cr-00181',
                    'prosecutor'        => 'SAUSA Corinne Lambert (E.D. Mich. revocation); AUSA John Neal; U.S. Attorney Jerome F. Gorgon Jr. (confirmed Dec 18, 2025)',
                    'judge'             => 'Hon. Stephen J. Murphy III (E.D. Mich. revocation); Magistrate Judge Jonathan J.C. Grey',
                    'sentence'          => 'Supervised-release revocation pending; underlying 2019 CFAA conviction with restitution. Appeal pending in U.S. Court of Appeals for the Sixth Circuit.',
                ],
            ],
        ];

        $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);
        return $exit === 0 ? self::SUCCESS : self::FAILURE;
    }
}
