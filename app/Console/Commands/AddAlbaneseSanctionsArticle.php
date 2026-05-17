<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Publish article on the May 14, 2026 federal court preliminary
 * injunction blocking Trump's IEEPA sanctions against UN Special
 * Rapporteur on the occupied Palestinian Territory Francesca
 * Albanese. Judge Richard Leon (D.D.C.) ruled the sanctions
 * targeted protected speech in violation of the First Amendment.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddAlbaneseSanctionsArticle extends Command {
    protected $signature = 'articles:add-albanese-sanctions-blocked';
    protected $description = 'Publish article on the May 2026 court ruling blocking Trump sanctions on UN Rapporteur Albanese';

    private const SLUG     = 'francesca-albanese-trump-sanctions-blocked-first-amendment-2026';
    private const PUB_DATE = '2026-05-14 08:46:28';

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'Repression'], ['slug' => 'repression']);
        $author   = Author::firstOrCreate(['name' => 'NPPC Editorial']);

        $body = <<<'BODY'
<p><em>A federal judge in Washington has temporarily blocked the Trump administration's IEEPA sanctions against the United Nations Special Rapporteur on the occupied Palestinian Territory — sanctions that for nearly a year cut Francesca Albanese off from U.S. banking, travel, and any American entity willing to do business with her. The judge: "Albanese has done nothing more than speak."</em></p>

<p>On <strong>May 13, 2026</strong>, U.S. District Judge <strong>Richard J. Leon</strong> of the District of Columbia issued a preliminary injunction blocking the United States from enforcing the executive-order sanctions the Trump administration imposed in July 2025 on <strong>Francesca Albanese</strong>, the United Nations Human Rights Council Special Rapporteur on the situation of human rights in the occupied Palestinian Territory.</p>

<p>The ruling is, in his own framing, narrow: a preliminary injunction, not a final order; addressed to the First Amendment harm to one specific named person and her family, not a global ruling on the underlying ICC-related sanctions regime. It is also, by every measure that matters to the civil-liberties bar, <strong>a major precedential breakage</strong> in the executive branch's recent practice of using the International Emergency Economic Powers Act (IEEPA) and the so-called ICC executive orders to punish international human-rights workers for the substance of their reporting.</p>

<h2>What the sanctions did</h2>

<p>Trump's July 2025 sanctions designated Albanese under the IEEPA/ICC executive-order regime, characterising her work as "biased and malicious activities" against the United States and Israel. The practical effect was a near-comprehensive financial and travel exclusion: she was barred from entering the United States, cut off from U.S. banking and payment systems, and any American entity — bank, university, publisher, NGO, employer — was prohibited from doing business with her. Her family's court papers described it bluntly as <strong>"effectively debanking her and making it nearly impossible to meet the needs of her daily life."</strong></p>

<p>The trigger was substance, not procedure. As Special Rapporteur, Albanese had publicly criticised U.S. policy toward Israel's military operations in Gaza, named the conduct she observed as genocide consistent with the Genocide Convention, and recommended that the <strong>International Criminal Court pursue war crimes prosecutions against named Israeli and American officials</strong>. The Trump administration's sanctions followed within weeks.</p>

<h2>Why her family sued</h2>

<p>Because the sanctions barred Albanese from the United States entirely, she could not litigate the case here in her own name without coming within reach of enforcement. The plaintiffs of record are instead her <strong>Italian husband and her U.S.-citizen daughter</strong>, who filed the action in February 2026 — the daughter's American citizenship anchoring jurisdiction, and the family's documented harm (frozen accounts, blocked transactions, inability to send their mother basic financial support from inside the U.S.) supplying the injury-in-fact element.</p>

<p>The legal team's framing, adopted by Judge Leon, was that the sanctions were a viewpoint-discriminatory censorship instrument operated through the back door of an emergency-economic-powers statute. The court agreed.</p>

<h2>What Judge Leon held</h2>

<p>Judge Leon's memorandum opinion makes the First Amendment finding the load-bearing wall of the injunction. The key holdings:</p>

<ul>
  <li>The Trump administration sanctioned Albanese <strong>because of the "idea or message expressed"</strong> in her UN reporting and ICC recommendations, not because of any independent conduct. That makes the sanctions content-based and viewpoint-discriminatory.</li>
  <li>Her recommendations to the ICC carry <strong>"no binding effect"</strong> and are "nothing more than her opinion" — they are speech, not action.</li>
  <li><strong>"Albanese has done nothing more than speak"</strong> — the line that has now circulated widely as the case's signature finding.</li>
  <li>"Protecting the freedom of speech is 'always' in the public interest" — supplying the public-interest prong of the preliminary-injunction test.</li>
</ul>

<p>The injunction is preliminary. The government has not yet announced whether it will appeal to the D.C. Circuit. Final judgment is still to come, on what is now likely to be a substantially developed evidentiary record.</p>

<h2>Albanese's response</h2>

<p>Upon learning of the ruling, Albanese posted to social media: <em>"Thanks to my daughter and my husband for stepping up to defend me, and everyone who has helped so far. Together we are One."</em></p>

<p>Greek economist and former finance minister Yanis Varoufakis framed the ruling as <em>"a small but significant victory in a massive battle to defend human rights and international law. Francesca has been vindicated even in the courts of the superpower that sanctioned her for doing her duty."</em> Italian journalist Stefania Maurizi pointed to the live <a href="https://petition.qomon.org/0b54a0b8-demand-justice-defend-the-defenders/">Courage Foundation petition</a> demanding the sanctions be permanently lifted: the preliminary injunction freezes enforcement; it does not yet make her whole, and it does not prevent the administration from designating other UN, ICC, or international human-rights workers under the same authority tomorrow.</p>

<h2>The wider pattern</h2>

<p>The Albanese ruling joins a recognizable run of First Amendment defeats the Trump-era Justice Department has absorbed in the federal courts over the past months: the four law-firm executive orders struck down on First Amendment grounds (Perkins Coie, WilmerHale, Jenner & Block, Susman Godfrey), the dismissal of the prosecution of veteran Jay Carey for burning a flag in Lafayette Park, the federal-court releases of Mahmoud Khalil, Mohsen Mahdawi, Rümeysa Öztürk, Yunseo Chung, and Leqaa Kordia from ICE detention under the Rubio "foreign policy deportability" memo, and the Vasquez Perdomo v. Mullin injunction protecting Pasadena lead plaintiff Isaac Villegas Molina from retaliatory re-detention.</p>

<p>Each of those rulings, like Albanese's, finds the same thing in different statutory clothing: the federal executive branch cannot use the leverage of its most powerful tools — IEEPA sanctions, ICE detention, security-clearance revocations, federal contracting bans, prosecutorial discretion — to punish people for the content of protected political speech. The fact that the executive branch keeps trying anyway, and that the courts keep ruling against it, is the story of the present moment in U.S. civil-liberties litigation.</p>

<p>For Albanese personally, the injunction means her U.S.-citizen family can begin to restore the financial infrastructure of her work. For the U.S. legal regime targeting ICC-cooperating international human-rights workers, it means the leading case has been decided against the government on First Amendment grounds. Both outcomes are significant. Neither is yet final.</p>
BODY;

        $data = [
            'title'        => '"Albanese Has Done Nothing More Than Speak": Federal Judge Blocks Trump\'s Sanctions on UN Rapporteur on the Occupied Palestinian Territory',
            'intro'        => "A federal judge in Washington has temporarily blocked the Trump administration's IEEPA sanctions against the United Nations Special Rapporteur on the occupied Palestinian Territory — sanctions that for nearly a year cut Francesca Albanese off from U.S. banking, travel, and any American entity willing to do business with her. The judge: \"Albanese has done nothing more than speak.\"",
            'body'         => $body,
            'category_id'  => $category->id,
            'author_id'    => $author->id,
            'published_at' => Carbon::parse(self::PUB_DATE),
            'citations_json' => [
                ['title' => '@yanisvaroufakis on X (origin tweet)', 'url' => 'https://x.com/yanisvaroufakis/status/2054845790678376864'],
                ['title' => '@SMaurizi on X (Courage Foundation petition)', 'url' => 'https://x.com/SMaurizi/status/2055218198765556019'],
                ['title' => 'The Guardian — Federal judge blocks Trump\'s sanctions on UN expert Francesca Albanese', 'url' => 'https://www.theguardian.com/us-news/2026/may/13/un-francesca-albanese-sanctions'],
                ['title' => 'Al Jazeera — Federal judge blocks US sanctions against UN rapporteur Francesca Albanese', 'url' => 'https://www.aljazeera.com/news/2026/5/14/federal-judge-blocks-us-sanctions-against-un-rapporteur-francesca-albanese'],
                ['title' => 'Common Dreams — "Albanese Has Done Nothing More Than Speak!" Judge Blocks Trump Sanctions on UN Palestine Expert', 'url' => 'https://www.commondreams.org/news/francesca-albanese-sanctions'],
                ['title' => 'Middle East Eye — US federal judge blocks US sanctions against UN\'s Francesca Albanese', 'url' => 'https://www.middleeasteye.net/news/us-federal-judge-blocks-us-sanctions-against-uns-francesca-albanese'],
                ['title' => 'Courage Foundation — Demand Justice / Defend the Defenders petition', 'url' => 'https://petition.qomon.org/0b54a0b8-demand-justice-defend-the-defenders/'],
            ],
        ];

        $existing = Article::where('slug', self::SLUG)->first();
        if ($existing) {
            $existing->update($data);
            $this->info('Updated article: '.$data['title']);
        } else {
            Article::create(['slug' => self::SLUG] + $data);
            $this->info('Created article: '.$data['title']);
        }

        return self::SUCCESS;
    }
}
