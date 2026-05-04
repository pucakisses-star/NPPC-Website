<?php

namespace App\Console\Commands;

use App\Models\Faq;
use Illuminate\Console\Command;

class SeedFaqs extends Command
{
    protected $signature = 'faq:seed';
    protected $description = 'Seed the FAQ page with NPPC-themed questions and answers.';

    public function handle(): int
    {
        $faqs = [
            [
                'q' => 'What is the National Political Prisoner Coalition (NPPC)?',
                'a' => "The National Political Prisoner Coalition is a U.S.-based advocacy organization that documents, supports, and works to free political prisoners held in U.S. custody — past and present. We maintain a public database of cases, publish journalism on incarceration and dissent, coordinate letter-writing and commissary support, run a podcast, and organize public events to keep these stories visible. Our work is grounded in solidarity with the families and communities of the imprisoned and in the historical record of how the United States has used its criminal-legal system to neutralize political movements.",
            ],
            [
                'q' => 'Who do you consider a political prisoner?',
                'a' => "We use a working definition adapted from international human rights frameworks: a political prisoner is someone whose incarceration is connected to their political beliefs, organizing, identity, or speech — either because they were prosecuted directly for those activities, because the state used pretextual or fabricated criminal charges to remove them from political life, or because they were sentenced disproportionately compared to non-political defendants who committed similar acts.\n\nThis includes Black liberation prisoners from the COINTELPRO era, Indigenous activists targeted during the Pine Ridge period, Puerto Rican independentistas, anti-nuclear and anti-war resisters, environmental and animal-liberation defendants prosecuted under the AETA and FBI \"green scare\" framework, anti-fascist and Black Lives Matter defendants from the 2020 uprising, Muslim political prisoners caught in post-9/11 entrapment cases, whistleblowers prosecuted under the Espionage Act, and Palestine-solidarity organizers detained or deported in 2024–2025.\n\nWe do not require ideological alignment with a prisoner to include them — we require that the case meet the definition.",
            ],
            [
                'q' => 'How do I write to a political prisoner?',
                'a' => "Letters from the outside are one of the most concrete forms of solidarity you can offer. The basics:\n\n1. Use the prisoner's full legal name and ID number on the envelope (you'll find both on each prisoner's profile in our database).\n2. Address it to the correct facility — prisoners are transferred frequently, so check our records and the BOP/state inmate locator before mailing.\n3. Follow that facility's mail rules: no glitter, no foil, no card-stock thicker than 1/16\", no Polaroids, no stickers, no staples. White paper and a basic envelope are safest.\n4. Include a return address. If you don't want to use your home address, a P.O. box works.\n5. Don't discuss legal strategy, pending appeals, or unverified rumors — assume mail is read.\n6. Do write about your day, the weather, what you're reading, what's happening in your community. Mundane news is precious inside.\n\nWe sell a Political Prisoner Letter-Writing Kit in our store with paper, envelopes, stamps, an updated address list, and a one-page guide to prison mail rules.",
            ],
            [
                'q' => 'How can I send commissary money or books?',
                'a' => "Commissary funds let prisoners buy food, hygiene products, stamps, and phone time. For federal (BOP) prisoners, money is sent through MoneyGram to the BOP lockbox in Des Moines, IA — the prisoner's name and BOP register number must appear exactly. For state prisoners, each state has its own system (JPay, GTL/ConnectNetwork, Access Corrections, or money order). Each prisoner's profile lists the current method.\n\nBooks generally must come directly from a publisher or approved vendor (Amazon, Barnes & Noble, AK Press, PM Press, Burning Books, Left Bank Books). Used books and personally-mailed books are usually rejected. Always check the receiving facility's specific policy before ordering — rules change without notice.",
            ],
            [
                'q' => 'Can I visit a political prisoner?',
                'a' => "Possibly — but most facilities require the prisoner to add you to their approved visitor list first, and many require you to fill out a visitor application that the facility approves or denies. Some prisoners (particularly those held in Communications Management Units or supermax facilities like ADX Florence) have severely restricted visiting privileges. Reach out to the prisoner directly by mail and ask them to send you an application. Never travel to a facility without confirming an approved visit and the facility's current visiting hours.",
            ],
            [
                'q' => 'How is NPPC funded?',
                'a' => "NPPC is funded by individual donations, small recurring monthly supporters, store sales, and limited grant support. We don't accept funding from law-enforcement organizations, prison contractors, or state agencies. Our financials are reported in our Annual Report. Every dollar reduces our reliance on grant cycles and lets us spend more on direct prisoner support — commissary deposits, family travel funds for visits, legal-mail postage, and re-entry assistance for the recently released.",
            ],
            [
                'q' => 'Is my donation tax-deductible?',
                'a' => "Yes. NPPC is a 501(c)(3) nonprofit, and donations are tax-deductible to the extent permitted by U.S. law. You'll receive an email receipt at the time of donation; for cumulative annual gifts of \$250 or more we send a year-end acknowledgment letter for your tax records. Our EIN is listed in the footer of our Annual Report.",
            ],
            [
                'q' => 'Do you accept donations of stocks, crypto, or DAFs?',
                'a' => "Yes. We accept appreciated stock and other securities, donor-advised fund (DAF) recommendations, and several major cryptocurrencies (BTC, ETH, USDC). For stock or DAF gifts, contact us at donations@nppc.org for transfer instructions. Crypto donations can be made directly through our donate page.",
            ],
            [
                'q' => 'How do I volunteer with NPPC?',
                'a' => "We have a few standing volunteer roles: writing case profiles, transcribing podcast episodes, running letter-writing nights at local bookstores or community spaces, helping run our table at conferences and book fairs, photo research for prisoner profiles, and translation (Spanish, Arabic, French, Portuguese). Fill out our volunteer form on the Volunteer page and someone will follow up within two weeks. We also welcome attorneys, paralegals, and law students for case-research and FOIA work.",
            ],
            [
                'q' => 'I think someone should be added to your database. How do I submit a case?',
                'a' => "Use the contact form and select \"Submit a case.\" Include the person's full name and any aliases, location of incarceration and inmate number if known, the charges they were convicted of, your reason for considering this a political case, and any links to news coverage, court documents, or support sites. Our research team reviews every submission. We don't promise inclusion — we apply our working definition rigorously and try to verify court records before publishing — but we read every submission and write back.",
            ],
            [
                'q' => 'Why are there foreign nationals in your database?',
                'a' => "We focus on prisoners held in U.S. custody or whose imprisonment is the direct result of U.S. action. That includes foreign nationals extradited to the U.S. (Simon Trinidad, Alex Saab), people imprisoned overseas at U.S. request (Daniel Duggan in Australia awaiting U.S. extradition), people prosecuted in U.S. federal court for actions at U.S. military installations abroad (Helen John, the Trident Ploughshares defendants), and U.S. citizens or permanent residents in exile to escape U.S. prosecution (Assata Shakur, Edward Snowden, John Dougan). We do not generally cover foreign nationals incarcerated by their own governments for purely domestic reasons.",
            ],
            [
                'q' => 'What is COINTELPRO and why does it still matter?',
                'a' => "COINTELPRO (Counterintelligence Program) was an FBI initiative active from 1956 to 1971 that targeted civil-rights organizations, the Black Panther Party, the American Indian Movement, Puerto Rican independence groups, the New Left, and others. The program used illegal surveillance, infiltration, manufactured evidence, agents provocateurs, and coordinated prosecutions to neutralize political movements. After it was exposed in the 1971 Media, PA FBI office break-in and the 1975 Church Committee hearings, the FBI publicly disavowed it — but many of the convictions COINTELPRO produced are still being served. People like Mumia Abu-Jamal, Leonard Peltier, Sundiata Acoli, Mutulu Shakur, Veronza Bowers, and Russell Maroon Shoatz spent or are spending decades in prison on cases shaped by COINTELPRO tactics. Understanding COINTELPRO is the foundation of understanding most long-term U.S. political imprisonment.",
            ],
            [
                'q' => 'What is the difference between a federal and state political prisoner?',
                'a' => "Federal prisoners are held by the Federal Bureau of Prisons (BOP) and were convicted of crimes against the United States — federal terrorism, espionage, RICO, civil-rights violations, federal arson, drug-trafficking on federal land, etc. State prisoners are held by individual state Departments of Corrections after convictions in state court. The distinction matters in practice: BOP rules, transfer procedures, commissary systems, parole regimes, and political-prisoner support workflows are completely different from any state's. Federal sentences (post-1987) have no parole, only good-time credit; many state systems still have parole boards. Our database notes which system each prisoner is held in.",
            ],
            [
                'q' => 'What is a Communications Management Unit (CMU)?',
                'a' => "A Communications Management Unit is a special federal prison housing unit, currently operating at FCI Terre Haute (Indiana) and FCI Marion (Illinois), where prisoners' contact with the outside world is severely restricted. Mail is read and copied before delivery, phone calls are limited to a few minutes per week and conducted in English, and in-person visits happen behind glass with monitored phones. CMUs were created in 2006 without public notice or rulemaking. They disproportionately house Muslim prisoners and political prisoners — environmental activists, animal-rights defendants, and anti-imperialist prisoners. The Center for Constitutional Rights has litigated against them for over a decade.",
            ],
            [
                'q' => 'What is the AETA and what does it have to do with political prisoners?',
                'a' => "The Animal Enterprise Terrorism Act (AETA, 2006) is a federal law that turned property damage and protest activity targeting animal-research facilities, factory farms, and fur farms into federal terrorism offenses. It expanded the older Animal Enterprise Protection Act of 1992 to include actions that cause \"economic damage\" — including secondary protests at the homes of executives, websites that publish corporate addresses, and even some forms of legal undercover documentation. AETA prosecutions are central to the post-2000 \"Green Scare\" alongside Operation Backfire (2005–2006) which targeted the Earth Liberation Front and Animal Liberation Front. Marius Mason and several others in our database were prosecuted under this framework.",
            ],
            [
                'q' => 'Are there still political prisoners from the 1960s and 70s held in U.S. prisons?',
                'a' => "Yes. As of 2026, U.S. prisons still hold political prisoners from the Black Panther Party, the Black Liberation Army, the Republic of New Afrika, the American Indian Movement, the FALN, the Plowshares movement, Earth First!, MOVE, the New World Liberation Front, and others. Some have been imprisoned for over fifty years. Several have died in custody in recent years (Russell Maroon Shoatz, Mutulu Shakur, Romaine \"Chip\" Fitzgerald). Leonard Peltier was released to home confinement in February 2025 after 49 years in federal prison. Mumia Abu-Jamal has been incarcerated since December 1981.",
            ],
            [
                'q' => 'How can I keep up with what NPPC is doing?',
                'a' => "Sign up for our email list at the bottom of any page — we send a roughly-monthly newsletter with case updates, action calls, and event announcements, and an urgent action alert when something time-sensitive is happening (a sentencing, a parole hearing, a hunger-strike support call). We also post on our blog (the News page), publish the NPPC podcast, post events to our public Calendar, and cross-publish on Mastodon. We are not on X.",
            ],
            [
                'q' => 'Can I host an NPPC speaker or letter-writing night?',
                'a' => "Yes. We regularly send speakers to universities, community spaces, faith communities, and conferences, and we partner with bookstores, infoshops, and student groups to run letter-writing nights — we'll bring stamps, paper, address lists, and prisoner bios; you bring the room and the people. Email events@nppc.org with your city, date range, expected audience size, and any compensation or honoraria you can offer (we'll absolutely come for free if you can't, but it helps cover travel).",
            ],
            [
                'q' => 'Why don\'t you publish street addresses for prisoner support committees on the public site?',
                'a' => "Support committees frequently ask us not to publish their physical addresses, because mail to a known prisoner-support address can be delayed, opened, or used to identify supporters. Where a committee has a public P.O. box or an email contact, we list it on the prisoner's profile. Where they don't, we route inquiries through our contact form and forward them to the committee directly with the supporter's permission.",
            ],
            [
                'q' => 'I\'m formerly incarcerated and want to get involved. Where do I start?',
                'a' => "We work closely with formerly-incarcerated organizers, and several of our staff and board members have served time. The fastest entry points are: writing for our News page (we pay for original writing from formerly-incarcerated authors), recording for the podcast, helping run re-entry support workflows for recently-released political prisoners, and joining our prisoner-correspondence team. Email ric@nppc.org — that mailbox routes directly to our re-entry coordinator and a real person will write back.",
            ],
            [
                'q' => 'How do I correct an error on a prisoner\'s profile?',
                'a' => "Use the contact form and select \"Profile correction.\" Tell us the prisoner's name, the specific field that's wrong, the correct information, and (where possible) a source we can verify it against — court records, BOP locator, the prisoner's own correspondence, a reputable news article. We update the database within a few business days and we're grateful for the correction. Our research is human work and human work has errors.",
            ],
            [
                'q' => 'Are political prisoners eligible for clemency or compassionate release?',
                'a' => "Yes, but the path is steep. Federal prisoners can apply to the Office of the Pardon Attorney for clemency or to a sentencing court for compassionate release under 18 U.S.C. § 3582(c)(1)(A) (the latter expanded by the First Step Act, 2018). State prisoners apply through their state's parole board or governor's clemency process — every state is different. We support clemency campaigns for individuals we've worked with for years, but every case is its own multi-year effort. The most successful clemency campaigns combine documented age or medical decline, a clean recent disciplinary record, demonstrated post-release support and housing, and sustained organized public pressure.",
            ],
        ];

        $created = 0;
        $skipped = 0;
        $sortOrder = 0;

        foreach ($faqs as $entry) {
            $sortOrder += 10;
            $existing = Faq::where('question', $entry['q'])->where('type', 'faq')->first();
            if ($existing) {
                $this->line("Skipping (exists): {$entry['q']}");
                $skipped++;
                continue;
            }

            Faq::create([
                'question'   => $entry['q'],
                'answer'     => $entry['a'],
                'type'       => 'faq',
                'sort_order' => $sortOrder,
            ]);

            $this->info("Added: {$entry['q']}");
            $created++;
        }

        $this->info("\nDone. Created {$created}, skipped {$skipped}.");

        return self::SUCCESS;
    }
}
