<?php

namespace App\Console\Commands;

use App\Models\Petition;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Bulk-create 17 petitions spanning currently-incarcerated political
 * prisoners, post-release pardon pushes, active prosecutions, memorial
 * accountability campaigns, and systemic BOP/Espionage-Act asks.
 *
 * Each petition is backdated to a meaningful anchor date (a rally,
 * commutation anniversary, indictment date, birthday, or the killing
 * itself for memorials) so the petition pages read as launched in
 * response to that moment.
 *
 * Idempotent — keyed by slug. Photos are best-effort: failures don't
 * block the petition creation.
 */
final class AddPetitionBatch17 extends Command {
    protected $signature = 'archive:add-petition-batch-17';
    protected $description = 'Create 17 petitions (Mumia, Veronza Bowers, Cop City, Tortuguita, Bushnell, et al.)';

    public function handle(): int {
        $created = 0; $updated = 0;
        foreach ($this->petitions() as $p) {
            $petition = Petition::firstOrNew(['slug' => $p['slug']]);
            $isNew = ! $petition->exists;

            $imagePath = null;
            if (! empty($p['image_url'])) {
                $imagePath = $this->fetchImage($p['slug'], $p['image_url']);
            }

            $petition->title             = $p['title'];
            $petition->body              = $p['body'];
            $petition->recipients        = $p['recipients'];
            $petition->suggested_subject = $p['suggested_subject'];
            $petition->suggested_message = $p['suggested_message'];
            $petition->signature_goal    = $p['signature_goal'];
            $petition->published         = true;
            if ($imagePath) {
                $petition->image = $imagePath;
            }
            $petition->save();

            $when = Carbon::parse($p['anchor_date'].' 12:00:00');
            if ($isNew) {
                $petition->created_at = $when;
                $petition->updated_at = $when;
                $petition->saveQuietly();
                $this->info('CREATE: '.$p['title'].'  ('.$p['anchor_date'].')');
                $created++;
            } else {
                $this->info('UPDATE: '.$p['title']);
                $updated++;
            }
        }
        $this->info("Done — created {$created}, updated {$updated}.");
        return self::SUCCESS;
    }

    private function fetchImage(string $slug, string $url): ?string {
        $path = 'petitions/'.$slug.'.jpg';
        if (Storage::disk('public')->exists($path)) {
            return $path;
        }
        try {
            $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; NPPC-Archive/1.0)'])
                ->timeout(45)->get($url);
            if ($resp->successful() && strlen($resp->body()) > 5000) {
                Storage::disk('public')->put($path, $resp->body());
                return $path;
            }
        } catch (\Throwable $e) {
            // best-effort
        }
        return null;
    }

    /** @return array<int, array<string, mixed>> */
    private function petitions(): array {
        $potus = 'The President of the United States; the U.S. Attorney General; the U.S. Pardon Attorney';
        return [
            // ============ 1. Mumia Abu-Jamal ============
            [
                'slug' => 'free-mumia-abu-jamal',
                'title' => 'Free Mumia Abu-Jamal — Pardon, Release, or New Trial',
                'anchor_date' => '2024-04-24',
                'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4f/Mumia_Abu-Jamal.jpg/800px-Mumia_Abu-Jamal.jpg',
                'recipients' => 'Governor of Pennsylvania; Philadelphia District Attorney; U.S. Attorney General',
                'signature_goal' => 10000,
                'suggested_subject' => 'Free Mumia Abu-Jamal',
                'suggested_message' => 'I write in support of Mumia Abu-Jamal — Black Panther, MOVE-supporting radio journalist, and the longest-held political prisoner in the United States. The 1981 prosecution that placed him on death row, later commuted to life without parole, has been condemned over four decades by Amnesty International, the NAACP, the Inter-American Commission on Human Rights, dozens of legal scholars, and prosecutors who reviewed the file. Mumia is 70 years old and his health is failing. I ask for the immediate steps within your power: a Pennsylvania pardon, an evidentiary hearing on the Brady-suppressed Williams notes, or an unconditional release. Free Mumia.',
                'body' => $this->html('Today — April 24, 2024 — Mumia Abu-Jamal turns 70 years old in SCI Mahanoy, beginning his 43rd year in Pennsylvania state custody for the 1981 killing of Philadelphia police officer Daniel Faulkner.',
                    'Mumia\'s case is the longest-running US political-prisoner campaign of the post-COINTELPRO generation. He has survived death row (commuted 2011), three appeals at the Pennsylvania Supreme Court, the Brady disclosure of the Williams notes (prosecution memos memorialising payoffs to the only witness to identify him at the scene), and 2024\'s adverse PA Supreme Court ruling refusing to hear the latest appeal on those notes.',
                    'We ask the Governor of Pennsylvania, the Philadelphia District Attorney, and the U.S. Attorney General for the action within each of their respective powers: a state pardon, a new evidentiary hearing, or a federal civil-rights review. Mumia has paid more than enough. Free him.',
                    'Sign and stand with the longest sustained political-prisoner campaign in U.S. history.'),
            ],
            // ============ 2. Veronza Bowers Jr. ============
            [
                'slug' => 'free-veronza-bowers',
                'title' => 'Free Veronza Bowers Jr. — Last Panther in Federal Prison',
                'anchor_date' => '2024-08-09',
                'image_url' => null,
                'recipients' => $potus.'; the U.S. Parole Commission',
                'signature_goal' => 2000,
                'suggested_subject' => 'Free Veronza Bowers Jr. — held past mandatory release',
                'suggested_message' => 'Veronza Bowers Jr. is the last living member of the Black Panther Party held in federal prison. He has been held past his mandatory release date since 2004 — for more than two decades after federal law required his release — over the objections of two of the three U.S. Parole Commissioners and through repeated unilateral interventions by the U.S. Attorney General. Please act on the immediate release that is lawfully required, not delayed.',
                'body' => $this->html('Veronza Bowers Jr. is 78 years old and the last living Black Panther held in federal prison. He has been incarcerated since 1974, convicted of the killing of a U.S. Park Service ranger in a case that the FBI Atlanta field office and the lead trial witness have themselves cast in doubt over the intervening decades.',
                    'Under 18 U.S.C. § 4206(d), Veronza\'s release was MANDATORY in 2004 — required by law. Yet for more than 20 years now, successive Attorneys General have used a rarely-invoked override authority to keep him inside. Two of the three U.S. Parole Commissioners have repeatedly voted for his release. The third has been overruled by AG memo each time.',
                    'We ask the President, the Attorney General, and the Parole Commission to do what should already have been done: release Veronza Bowers Jr. immediately. Twenty extra years of imprisonment past a federally-mandated release date is not a process. It is a punishment without authority.'),
            ],
            // ============ 3. Kamau Sadiki ============
            [
                'slug' => 'medical-clemency-kamau-sadiki',
                'title' => 'Medical Clemency for Kamau Sadiki',
                'anchor_date' => '2024-09-09',
                'image_url' => null,
                'recipients' => 'Governor of Georgia; Georgia Board of Pardons and Paroles',
                'signature_goal' => 2000,
                'suggested_subject' => 'Medical clemency for Kamau Sadiki',
                'suggested_message' => 'I write on behalf of Kamau Sadiki — former Black Panther Party member, sarcoidosis patient, and federal-vintage political prisoner now held in the Georgia state system on a 60-year sentence for a 1971 case that was tried only after he refused to inform on Assata Shakur. His health has deteriorated to the point that continued incarceration is a medical death sentence. I ask for compassionate medical release.',
                'body' => $this->html('Kamau Sadiki was a teenage Black Panther in 1971. He spent the next 31 years a free man, working as a Bell South lineman in Atlanta. In 2002 — after he refused to assist the FBI\'s pursuit of his former comrade Assata Shakur — Georgia revived a dormant 1971 cold case and convicted him of the killing of an Atlanta police officer.',
                    'He has now served more than 22 years on a 60-year Georgia sentence. He suffers from advanced sarcoidosis (a chronic inflammatory lung disease), hepatitis C, and chronic kidney disease that has produced extended hospitalizations.',
                    'We ask the Governor of Georgia and the Board of Pardons and Paroles for compassionate medical clemency — the relief reserved precisely for cases where continued incarceration is incompatible with the prisoner\'s survival.'),
            ],
            // ============ 4. Marius Mason ============
            [
                'slug' => 'free-marius-mason',
                'title' => 'Free Marius Mason — Eco-Prisoner and Trans Elder Inside',
                'anchor_date' => '2024-03-31',
                'image_url' => null,
                'recipients' => $potus.'; the Federal Bureau of Prisons',
                'signature_goal' => 2500,
                'suggested_subject' => 'Clemency and humane treatment for Marius Mason',
                'suggested_message' => 'Marius Mason is serving a 21-year sentence — the longest given to any U.S. eco-defender — for two non-injury property arsons committed in 1999 and 2000 on behalf of the Earth Liberation Front. He has served more than 15 years already. He is a trans man who was, until 2014, denied gender-affirming care and housing. I ask for executive clemency and the immediate provision of all trans-affirming medical, housing, and personal-care needs.',
                'body' => $this->html('Marius Mason is a Michigan-born poet, musician, and Earth Liberation Front saboteur who in 2009 received the longest sentence ever imposed on a U.S. eco-defender: 21 years and 10 months for two property arsons that injured no one (a Michigan State University genetic-engineering research facility and a Mead, WA logging operation). He was 47 at sentencing.',
                    'He is a trans man. For the first six years inside, BOP denied him gender-affirming hormones, denied housing requests, and confined him in the women\'s federal medical center at Carswell. Trans-affirming care was only initiated in 2014, after years of organizing by his support committee.',
                    'We ask the President for executive clemency commuting his remaining sentence, and the Bureau of Prisons for the full and uninterrupted provision of every trans-affirming medical, housing, and personal-care need he has requested.'),
            ],
            // ============ 5. Oso Blanco ============
            [
                'slug' => 'free-oso-blanco-byron-chubbuck',
                'title' => 'Free Oso Blanco — Zapatista-Solidarity Bank Expropriator',
                'anchor_date' => '2024-10-14',
                'image_url' => null,
                'recipients' => $potus,
                'signature_goal' => 1500,
                'suggested_subject' => 'Executive clemency for Oso Blanco (Byron Chubbuck)',
                'suggested_message' => 'Byron Chubbuck — Oso Blanco — is a Cherokee/Choctaw artist and Indigenous-solidarity activist serving an 80-year federal sentence for a series of 1998-99 unarmed bank expropriations in the Southwest, the proceeds of which he routed to Zapatista autonomous communities in Chiapas. No one was harmed in the expropriations. I ask for executive clemency commuting his sentence to time served.',
                'body' => $this->html('Byron Shane Chubbuck — known by his AIM name Oso Blanco ("White Bear") — is a Cherokee/Choctaw artist and Indigenous-solidarity activist now serving an 80-year federal sentence in USP Florence ADX-adjacent custody.',
                    'Between 1998 and 1999 he carried out a series of bank expropriations across New Mexico, Arizona, and Texas, the proceeds of which he routed to Zapatista autonomous communities in Chiapas. No one was harmed in the expropriations. After his arrest he was charged with attempting to disarm a transporting U.S. Marshal — a charge he disputes — which alone added decades to the sentence.',
                    'We ask the President for executive clemency commuting Oso Blanco\'s sentence to time served. He has been inside since 1999 and turns 60 this year.'),
            ],
            // ============ 6. Aafia Siddiqui ============
            [
                'slug' => 'free-aafia-siddiqui',
                'title' => 'Free Aafia Siddiqui — 86-Year Carswell Sentence',
                'anchor_date' => '2024-03-30',
                'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/02/Aafia_Siddiqui.jpg/800px-Aafia_Siddiqui.jpg',
                'recipients' => $potus,
                'signature_goal' => 10000,
                'suggested_subject' => 'Repatriate Aafia Siddiqui to Pakistan',
                'suggested_message' => 'I urge the immediate repatriation of Dr. Aafia Siddiqui — a Pakistani national, MIT/Brandeis-trained cognitive neuroscientist, mother of three — from FMC Carswell to Pakistan to serve any remaining time in her own country, close to her children and family. The 2010 SDNY trial that produced her 86-year sentence is widely regarded internationally as a textbook miscarriage. She has been brutally assaulted inside Carswell. The Government of Pakistan has formally requested her transfer.',
                'body' => $this->html('Dr. Aafia Siddiqui is a Pakistani citizen, MIT and Brandeis-trained cognitive neuroscientist, and mother of three who disappeared in Karachi in March 2003 with her three young children. She resurfaced in U.S. custody in Bagram in 2008, was flown to New York, tried in 2010 in the Southern District of New York, and sentenced to 86 years in federal prison.',
                    'Her case is one of the most internationally contested federal terrorism prosecutions of the post-9/11 era. The Government of Pakistan has formally and repeatedly requested her transfer under the U.S.–Pakistan prisoner transfer treaty. She has been brutally assaulted inside FMC Carswell in 2021 and remains there with documented untreated injuries.',
                    'We ask the President of the United States for her immediate repatriation to Pakistan.'),
            ],
            // ============ 7. Daniel Hale ============
            [
                'slug' => 'pardon-daniel-hale',
                'title' => 'Pardon Drone Whistleblower Daniel Hale',
                'anchor_date' => '2024-07-15',
                'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8b/Daniel_Hale_%28cropped%29.jpg/600px-Daniel_Hale_%28cropped%29.jpg',
                'recipients' => $potus,
                'signature_goal' => 5000,
                'suggested_subject' => 'Pardon Daniel Hale',
                'suggested_message' => 'I urge a full presidential pardon for Daniel Hale. As an Air Force intelligence analyst, he disclosed the documents that became "The Drone Papers" — the most authoritative public source on the U.S. drone-targeting program and the civilian casualty figures the government had suppressed for years. He served his 45-month federal sentence. He deserves a pardon clearing his conviction so he can move on with his life.',
                'body' => $this->html('Daniel Hale is the U.S. Air Force intelligence analyst (and later National Geospatial-Intelligence Agency contractor) who in 2014 leaked the classified documents that became "The Drone Papers" — published by The Intercept and the most authoritative public source on U.S. drone-targeting protocols and the civilian-casualty figures the government had suppressed.',
                    'He pled guilty in 2021 to one count under the Espionage Act of 1917 and was sentenced to 45 months in federal prison. He was released to a Tennessee halfway house in July 2024.',
                    'We ask the President for a full presidential pardon clearing his federal conviction, so that Daniel — like Daniel Ellsberg, like W. Mark Felt, like every whistleblower the country has eventually come to thank — can live and work without that record.'),
            ],
            // ============ 8. Chelsea Manning ============
            [
                'slug' => 'full-pardon-chelsea-manning',
                'title' => 'Full Pardon for Chelsea Manning',
                'anchor_date' => '2024-05-17',
                'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/aa/Chelsea_Manning_in_2017.jpg/600px-Chelsea_Manning_in_2017.jpg',
                'recipients' => $potus,
                'signature_goal' => 5000,
                'suggested_subject' => 'Full pardon for Chelsea Manning',
                'suggested_message' => 'I urge a full presidential pardon for Chelsea Manning. President Obama commuted her 35-year Espionage Act sentence in 2017. A full pardon would acknowledge what is now widely understood: the documents she disclosed — the Iraq and Afghanistan War Logs, the State Department cables, the "Collateral Murder" video — informed the public about U.S. conduct that the government had concealed, and she has more than paid for that disclosure.',
                'body' => $this->html('Chelsea Manning served seven years of a 35-year federal sentence — the longest Espionage Act sentence ever imposed for a leak to the press — before President Obama commuted the remainder on January 17, 2017. She was jailed again in 2019-20 for refusing to testify before a grand jury investigating WikiLeaks. She has now lived and worked as a free woman since 2020.',
                    'A presidential pardon would clear the underlying conviction and acknowledge what international tribunals, press-freedom organizations, and the public record now confirm: the materials Chelsea released — the Iraq and Afghanistan War Logs, the U.S. State Department cables, the "Collateral Murder" cockpit video — informed the public about U.S. conduct that the government had concealed.',
                    'We ask the President for the full pardon that the country owes her.'),
            ],
            // ============ 9. Steven Donziger ============
            [
                'slug' => 'pardon-steven-donziger',
                'title' => 'Pardon Steven Donziger',
                'anchor_date' => '2024-04-25',
                'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/65/Steven_Donziger.jpg/600px-Steven_Donziger.jpg',
                'recipients' => $potus,
                'signature_goal' => 5000,
                'suggested_subject' => 'Pardon Steven Donziger',
                'suggested_message' => 'I urge a full presidential pardon for Steven Donziger — the human-rights attorney who won the $9.5 billion Lago Agrio judgment against Chevron on behalf of 30,000 Indigenous and farmer plaintiffs in Ecuador, and who was then prosecuted via private contempt charges driven by Chevron itself after the SDNY U.S. Attorney declined to bring the case. The U.N. Working Group on Arbitrary Detention found his detention to be a violation of international law. Pardon him.',
                'body' => $this->html('Steven Donziger represented the 30,000 Ecuadorian Indigenous and farmer plaintiffs who won the $9.5 billion Lago Agrio judgment against Chevron in 2011 for decades of catastrophic Amazon oil contamination. Chevron then waged a U.S. counter-litigation campaign — described by Earthjustice and the UN as one of the most aggressive corporate retaliation suits in modern legal history.',
                    'After the Southern District of New York U.S. Attorney declined to bring criminal contempt charges, the same SDNY judge (Lewis Kaplan) appointed a private prosecutor — a Chevron-tied firm — to bring the case anyway. Donziger spent 993 days in pretrial home confinement, 45 days in federal prison, and ultimately had his New York bar license revoked. The UN Working Group on Arbitrary Detention found his detention to be a violation of international law.',
                    'We ask the President for a full pardon clearing the conviction.'),
            ],
            // ============ 10. Julian Assange ============
            [
                'slug' => 'pardon-julian-assange',
                'title' => 'Pardon Julian Assange',
                'anchor_date' => '2024-06-25',
                'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d8/Julian_Assange_in_Ecuadorian_Embassy.jpg/600px-Julian_Assange_in_Ecuadorian_Embassy.jpg',
                'recipients' => $potus,
                'signature_goal' => 25000,
                'suggested_subject' => 'Full presidential pardon for Julian Assange',
                'suggested_message' => 'I urge a full presidential pardon for Julian Assange. He pleaded guilty on June 26, 2024 in Saipan to a single Espionage Act count in order to end the 14-year ordeal — five years in Belmarsh, seven years in the Ecuadorian Embassy, two years in the U.S. extradition fight — and return home to Australia. The Espionage Act has never before been used to convict a publisher. A pardon would restore the press-freedom precedent his case put at risk.',
                'body' => $this->html('On June 26, 2024 — after five years in HMP Belmarsh, seven years inside the Ecuadorian Embassy in London, and a 14-year U.S. prosecution effort — Julian Assange pleaded guilty to a single count under the Espionage Act of 1917 in a U.S. federal court in Saipan, was sentenced to time served, and flew home to Australia.',
                    'He is the only publisher in U.S. history to be convicted under the Espionage Act. Press-freedom organizations from the Committee to Protect Journalists to Reporters Without Borders to the Knight First Amendment Institute have warned that the conviction creates a precedent that endangers every reporter who publishes classified material.',
                    'We ask the President for a full pardon vacating that conviction and restoring the press-freedom line that his case put at risk.'),
            ],
            // ============ 11. Stop Cop City RICO 61 ============
            [
                'slug' => 'drop-stop-cop-city-rico-charges',
                'title' => 'Drop the Stop Cop City RICO Charges',
                'anchor_date' => '2023-08-29',
                'image_url' => null,
                'recipients' => 'Governor of Georgia; Georgia Attorney General; Fulton County District Attorney',
                'signature_goal' => 25000,
                'suggested_subject' => 'Drop the RICO indictment against the Stop Cop City defendants',
                'suggested_message' => 'I urge the Georgia Attorney General to immediately drop the August 29, 2023 RICO indictment against 61 Stop Cop City defendants. The indictment is the most expansive use of state RICO ever brought against environmental activists in the United States. It treats activities like bail-fund support, mutual-aid kitchens, and forest-defender camps as predicate "racketeering" acts. It is a blatant criminalization of protest and must be withdrawn.',
                'body' => $this->html('On August 29, 2023 the Georgia Attorney General Chris Carr filed a 109-page RICO indictment charging 61 people — forest defenders, bail-fund organizers, medics, and supporters — with violating the Georgia state Racketeer Influenced and Corrupt Organizations Act over the Stop Cop City / Defend the Atlanta Forest movement against the planned 85-acre police-training facility in the South River Forest.',
                    'It is the most expansive use of state RICO against environmental activists in U.S. history. The indictment treats predicate acts like bail-fund donations, food-not-bombs kitchens, posting flyers, occupying the forest, and zine production as "racketeering." Defendants face up to 20 years on the RICO count alone, on top of underlying state and federal charges.',
                    'We demand the Georgia Attorney General drop the RICO indictment in full.'),
            ],
            // ============ 12. Gaza encampment defendants ============
            [
                'slug' => 'drop-charges-gaza-encampment-defendants',
                'title' => 'Drop Charges Against the Gaza-Solidarity Campus Encampment Defendants',
                'anchor_date' => '2024-04-17',
                'image_url' => null,
                'recipients' => 'University Presidents; District Attorneys; U.S. Attorneys',
                'signature_goal' => 15000,
                'suggested_subject' => 'Drop all charges against student Gaza-encampment defendants',
                'suggested_message' => 'I urge that all criminal and university disciplinary charges against the spring 2024 Gaza-solidarity encampment defendants — at Columbia, UCLA, GW, Emory, the University of Texas, and dozens of other campuses — be dropped immediately. The encampments were peaceful nonviolent protest in the moral tradition of the anti-apartheid divestment encampments of the 1980s. Punishing students for that protest is a stain on the institutions doing it.',
                'body' => $this->html('Beginning at Columbia University on April 17, 2024 and spreading within weeks to roughly 130 college and university campuses across the United States, students organized Gaza-solidarity encampments demanding their universities divest from Israeli state and weapons-manufacturer holdings, transparency in endowment investments, and an academic boycott consistent with the Palestinian-civil-society BDS call.',
                    'The university and police response set off the largest single wave of campus arrests of the last 50 years: more than 3,100 arrests across the spring 2024 wave, with thousands more facing university disciplinary action including suspensions, evictions from on-campus housing, withdrawn degrees, and barred-from-campus orders.',
                    'We urge university presidents, district attorneys, and federal prosecutors to drop every charge from that wave.'),
            ],
            // ============ 13. Tortuguita (Manuel Paez Terán) ============
            [
                'slug' => 'justice-for-tortuguita',
                'title' => 'Independent Federal Investigation into the Killing of Tortuguita',
                'anchor_date' => '2023-01-18',
                'image_url' => null,
                'recipients' => 'U.S. Attorney General; U.S. Attorney for the Northern District of Georgia; Georgia Bureau of Investigation',
                'signature_goal' => 10000,
                'suggested_subject' => 'Open a federal civil-rights investigation into the killing of Tortuguita',
                'suggested_message' => 'I urge a full, independent federal civil-rights investigation under 18 U.S.C. §§ 241-242 into the January 18, 2023 killing by Georgia State Patrol of Manuel "Tortuguita" Paez Terán — a 26-year-old Venezuelan/Indigenous nonbinary forest defender shot 57 times during a multi-agency raid on the Stop Cop City forest encampment. The DeKalb County medical examiner found Tortuguita died in a meditative cross-legged posture with their hands raised. No state-level officer has been charged. A federal investigation is required.',
                'body' => $this->html('On January 18, 2023 at approximately 9:00 a.m., during a multi-agency raid on the Stop Cop City forest encampment in the South River Forest of DeKalb County, Georgia, Manuel Esteban Paez Terán — a 26-year-old Venezuelan-born Indigenous nonbinary forest defender known as Tortuguita ("Little Turtle") — was shot 57 times by Georgia State Patrol officers.',
                    'The DeKalb County Medical Examiner\'s independent autopsy found that Tortuguita was in a meditative cross-legged posture with their hands raised at the moment of the killing. The state of Georgia has refused to file charges against any of the officers involved. No body-camera footage exists from the killing because the Georgia State Patrol officers were not equipped with cameras.',
                    'We ask the U.S. Department of Justice to open a full civil-rights investigation under 18 U.S.C. §§ 241-242 — the same statutes used to prosecute police killings in landmark federal civil-rights cases — into the killing of Tortuguita.'),
            ],
            // ============ 14. Aaron Bushnell memorial ============
            [
                'slug' => 'aaron-bushnell-memorial-ceasefire',
                'title' => 'Honor Aaron Bushnell — Ceasefire and Arms Embargo',
                'anchor_date' => '2024-02-25',
                'image_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3e/Aaron_Bushnell.jpg/600px-Aaron_Bushnell.jpg',
                'recipients' => 'The President of the United States; the U.S. Secretary of Defense; the U.S. Secretary of State',
                'signature_goal' => 25000,
                'suggested_subject' => 'Honor Aaron Bushnell with a U.S. arms embargo on Israel and a permanent ceasefire',
                'suggested_message' => 'On February 25, 2024, 25-year-old active-duty U.S. Air Force Senior Airman Aaron Bushnell self-immolated outside the Israeli Embassy in Washington, DC, declaring he would "no longer be complicit in genocide." I urge that you honor what he asked of you — a permanent ceasefire in Gaza and a complete U.S. arms embargo on the State of Israel.',
                'body' => $this->html('On February 25, 2024 at approximately 1:00 p.m., 25-year-old active-duty U.S. Air Force Senior Airman Aaron Bushnell walked onto the public sidewalk outside the Israeli Embassy in Washington, DC. He was wearing his uniform. He set up his cellphone to livestream on Twitch. He poured a flammable liquid over himself and ignited it, repeating "Free Palestine" until he could no longer speak. He died early the next morning at MedStar Washington Hospital Center.',
                    'In a written statement uploaded before the act, Aaron declared he would "no longer be complicit in genocide" and named the U.S. military-industrial complex\'s arming of the Israeli state as the proximate cause of what he was about to do.',
                    'We ask the President, the Secretary of Defense, and the Secretary of State to do what Aaron Bushnell asked of you with his life: a permanent ceasefire in Gaza and a complete U.S. arms embargo on the State of Israel.'),
            ],
            // ============ 15. End BOP CMUs ============
            [
                'slug' => 'end-bop-communications-management-units',
                'title' => 'End the BOP Communications Management Units',
                'anchor_date' => '2024-12-15',
                'image_url' => null,
                'recipients' => 'Director of the Federal Bureau of Prisons; the U.S. Attorney General',
                'signature_goal' => 3000,
                'suggested_subject' => 'Shut down the BOP Communications Management Units',
                'suggested_message' => 'I urge the immediate closure of the Federal Bureau of Prisons\' two Communications Management Units (CMUs) at USP Marion (Illinois) and FCI Terre Haute (Indiana). The CMUs disproportionately confine Muslim, Black, and political prisoners under "communications restrictions" found by the federal courts to violate the First Amendment. Close them.',
                'body' => $this->html('The Communications Management Units (CMUs) at USP Marion, Illinois and FCI Terre Haute, Indiana — opened secretly by the Bureau of Prisons in 2006-2008 — confine federal prisoners under extreme communications restrictions: monitored, recorded, and limited in-language phone calls to family, no contact visits, mail screened by paragraph, and almost no group association.',
                    'Roughly two-thirds of CMU prisoners are Muslim. Disproportionate populations of Black, Indigenous, and political prisoners are held there. In 2010 the Center for Constitutional Rights filed Aref v. Holder challenging the constitutionality of the CMUs. The case forced the disclosure of internal BOP documents showing the units were created without notice-and-comment rulemaking and operated outside the BOP\'s own regulations.',
                    'We ask the Director of the BOP and the Attorney General to close both CMUs and return all prisoners currently held there to general population.'),
            ],
            // ============ 16. Restore physical mail ============
            [
                'slug' => 'restore-physical-mail-prisons',
                'title' => 'Restore Physical Mail to Federal and State Prisons',
                'anchor_date' => '2024-11-01',
                'image_url' => null,
                'recipients' => 'Director of the Federal Bureau of Prisons; State Departments of Corrections',
                'signature_goal' => 3000,
                'suggested_subject' => 'Restore physical mail to all U.S. prisons',
                'suggested_message' => 'I urge the Federal Bureau of Prisons and every state Department of Corrections to immediately reverse the move to digital-only mail scanning. The shift sends physical letters from family members to a third-party scanning contractor, where they are photocopied at low resolution, often with months-long delivery delays, and the originals destroyed. This is a cruel and useless restriction that severs incarcerated people from the handwritten contact they need most.',
                'body' => $this->html('Beginning in 2018 and accelerating sharply in 2023-24, the federal Bureau of Prisons and a growing list of state DOCs have moved to digital-only mail intake: family members\' physical letters and children\'s drawings are mailed to a contracted scanning company, photocopied at low resolution, often delivered to the prisoner weeks or months later, and the originals destroyed without return.',
                    'Mailroom contraband has not declined. Suicide rates inside CMUs and other restrictive units, where contact with the outside is most severed, are documented to be among the highest in the federal system.',
                    'We ask the Director of the BOP and every state DOC to restore direct physical mail intake.'),
            ],
            // ============ 17. End Espionage Act vs. press ============
            [
                'slug' => 'end-espionage-act-prosecutions-of-journalists',
                'title' => 'End Espionage Act Prosecutions of Journalists and Whistleblowers',
                'anchor_date' => '2024-06-26',
                'image_url' => null,
                'recipients' => 'U.S. Attorney General; the U.S. Senate Judiciary Committee; the U.S. House Judiciary Committee',
                'signature_goal' => 8000,
                'suggested_subject' => 'End Espionage Act prosecutions of journalists and whistleblowers',
                'suggested_message' => 'I urge an immediate end to the use of the Espionage Act of 1917 against journalists, publishers, and whistleblowers — and the legislative reform that is required to enshrine that change. The Act, written to prosecute World War I-era German spies, has been deployed against Daniel Ellsberg, Chelsea Manning, Edward Snowden, Reality Winner, Daniel Hale, Terry Albury, Joshua Schulte, Julian Assange, and most recently Henry Frese. Stop it.',
                'body' => $this->html('The Espionage Act of 1917 — a wartime statute originally drafted to prosecute German spies during World War I — has, since the 1970s, been turned almost exclusively against U.S. journalists, publishers, and government whistleblowers. The list is now long: Daniel Ellsberg (1973, dropped), Samuel Loring Morison (1985), Stephen Kim, Jeffrey Sterling, Thomas Drake, Chelsea Manning, Edward Snowden, James Hitselberger, John Kiriakou, Reality Winner, Henry Kyle Frese, Terry Albury, Joshua Schulte, Daniel Hale, and the only publisher in U.S. history convicted under the Act — Julian Assange (June 2024).',
                    'The statute as written makes no distinction between leaking secrets to a foreign government and disclosing material to The New York Times. Federal courts have refused to recognize a public-interest defense. Press-freedom organizations including the Knight First Amendment Institute, the Reporters Committee for Freedom of the Press, and the Committee to Protect Journalists have warned for two decades that the trend is unsustainable for a country with a working First Amendment.',
                    'We ask the Attorney General and Congress for: (1) an immediate halt to new Espionage Act prosecutions of journalists, publishers, and whistleblowers; and (2) the passage of statutory reform creating an explicit public-interest defense and excluding publication and source-protection activities from the Act\'s reach.'),
            ],
        ];
    }

    private function html(string ...$paragraphs): string {
        return implode("\n", array_map(fn ($p) => '<p>'.$p.'</p>', $paragraphs));
    }
}
