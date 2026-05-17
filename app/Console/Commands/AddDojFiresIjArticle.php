<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Publish article on the DOJ's April 10, 2026 firing of six
 * immigration judges — including the two who ruled in favor of
 * Rümeysa Öztürk and Mohsen Mahdawi in their pro-Palestine
 * deportation cases — under the March 2026 MSPB decision holding
 * that the attorney general may fire immigration judges at will.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddDojFiresIjArticle extends Command {
    protected $signature = 'articles:add-doj-fires-ij';
    protected $description = 'Publish article on the April 2026 DOJ firing of immigration judges who ruled for Palestine activists';

    private const SLUG     = 'doj-fires-immigration-judges-who-ruled-for-palestine-activists-2026';
    private const PUB_DATE = '2026-04-16 18:10:10';

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'Repression'], ['slug' => 'repression']);
        $author   = Author::firstOrCreate(['name' => 'NPPC Editorial']);

        $body = <<<'BODY'
<p><em>The Justice Department fires the immigration judges who ruled in favor of Rümeysa Öztürk and Mohsen Mahdawi, days after a federal merit-board ruling cleared the way for at-will dismissal of any administrative judge whose decisions the attorney general dislikes.</em></p>

<p>On the weekend of <strong>April 10, 2026</strong>, the U.S. Department of Justice fired <strong>six immigration judges</strong> — among them, the two whose rulings had blocked the Trump administration from deporting two of the most prominent pro-Palestine student-activist defendants in the country.</p>

<p>The two named in subsequent reporting are <strong>Judge Roopal Patel</strong>, who served on the Boston immigration court and had been on the bench for months on probationary status, and <strong>Judge Nina Froes</strong>. Patel had ruled in January 2026 that the Department of Homeland Security had failed to meet its burden to remove <strong>Rümeysa Öztürk</strong>, the Tufts University doctoral student whose F-1 visa was revoked after she co-authored an op-ed criticizing the university's handling of Israel-Palestine protests. Froes had dismissed deportation charges against <strong>Mohsen Mahdawi</strong>, the Columbia University student and Palestinian-American organizer arrested at a citizenship interview as part of the administration's wave of Palestine-protest detentions.</p>

<p>Both firings came under the legal cover of a <strong>Merit Systems Protection Board ruling issued in March 2026</strong> holding that the attorney general may constitutionally discharge immigration judges at will. The MSPB classified administrative judges as "inferior officers" within the executive branch, removable for any reason the AG considers consistent with executive enforcement of federal law. The decision swept aside decades of practice in which immigration judges, though technically DOJ employees, had functioned as independent adjudicators with significant insulation from political pressure on individual rulings.</p>

<p>Since the start of Donald Trump's second term in January 2025, the National Association of Immigration Judges reports that <strong>more than 100 immigration judges have been dismissed</strong> — a pace and total without recent precedent.</p>

<h2>The cases at issue</h2>

<p>The two firings most prominently reported sit on top of NPPC-database-eligible defendants who had won, at least temporarily, on the immigration-court record.</p>

<p>Rümeysa Öztürk was detained by masked agents on a Somerville, Massachusetts street in March 2025, transported across multiple states, and held in Louisiana ICE detention for more than six weeks before a federal judge in Vermont ordered her release. The State Department's grounds for revoking her visa, as in the cases of Mahmoud Khalil, Mohsen Mahdawi, and Yunseo Chung, rested on the rarely-invoked "foreign policy deportability" provision of the Immigration and Nationality Act and on Secretary of State Marco Rubio's personal determination that her continued presence would have "potentially serious adverse foreign policy consequences" — a determination based on her op-ed.</p>

<p>Judge Patel's January 2026 ruling did not vindicate Öztürk on the merits of the Rubio memo; it held more narrowly that DHS had not actually shown the evidence required to deport her. That was enough to keep her in the country. It was also, evidently, enough to get the judge fired.</p>

<p>Mohsen Mahdawi was detained in April 2025 when he reported for a routine immigration interview. A federal judge later ordered him released; Judge Froes's later dismissal of the underlying removal charges removed the immediate threat of deportation. Mahdawi has continued his organizing, with a security detail. Froes is now out of a job.</p>

<h2>What the message is</h2>

<p>The Freedom of the Press Foundation's chief of advocacy Seth Stern put the implication directly in <a href="https://www.theguardian.com/commentisfree/2026/apr/16/immigration-judges-fired-pro-palestinian-activists">an April 16, 2026 Guardian op-ed</a>: the DOJ is making every remaining immigration judge in the country choose between ruling on the law and First Amendment evidence in front of them, and keeping their job.</p>

<p>That is not a rhetorical flourish. The MSPB ruling means there is no formal procedural protection against retaliation for an adverse ruling. There is no inspector-general process. There is no judicial-conduct commission. There is the attorney general's discretion, and the discretion is exactly what was used.</p>

<p>Judge Patel, after her firing, told reporters the move reflects "a kind of political agenda to kind of reshape the immigration bench to reflect the policy agenda of the current administration to be one of mass deportations." She added that she did not believe a different ruling would have saved her job — the goal, she suggested, is the workforce restructure itself.</p>

<h2>For political defendants on the immigration docket</h2>

<p>The consequences for the people NPPC's database covers are not abstract. The administration has detained dozens of student visa-holders, lawful permanent residents, and other noncitizens for protected speech connected to Palestine solidarity organizing since the start of 2025. The immigration courts are the venue in which those cases were supposed to be heard on the law. After April 10, 2026, every judge sitting in that venue knows what a ruling in favor of a Palestine-activist defendant produced for Roopal Patel and Nina Froes.</p>

<p>The lawyers representing those defendants now have to argue knowing the bench has watched two colleagues lose their jobs for accepting their arguments. The defendants have to make their case knowing that the judge weighing it is sitting under an open warning. And the public-interest law firms that mount these defenses — many of them already on the receiving end of separate Trump executive orders aimed at their pro bono work — are watching the cumulative pressure mount.</p>

<p>None of this is a side effect. The MSPB ruling, the firings, the firm-targeting orders, the State Department visa revocations, and the Rubio-memo deportation theory all line up in one direction: stripping the procedural infrastructure that political defendants on the immigration docket need to keep from being deported for their speech.</p>
BODY;

        $data = [
            'title'        => 'DOJ Fires the Immigration Judges Who Ruled for Pro-Palestine Activists',
            'intro'        => "On April 10, 2026 the Justice Department fired six immigration judges — including the two whose rulings had blocked the Trump administration from deporting Rümeysa Öztürk and Mohsen Mahdawi — under the March 2026 Merit Systems Protection Board decision holding that the attorney general may discharge immigration judges at will.",
            'body'         => $body,
            'category_id'  => $category->id,
            'author_id'    => $author->id,
            'published_at' => Carbon::parse(self::PUB_DATE),
            'citations_json' => [
                ['title' => 'Seth Stern — Immigration judges are being fired for ruling in favor of pro-Palestinian activists (The Guardian, Apr 16, 2026)', 'url' => 'https://www.theguardian.com/commentisfree/2026/apr/16/immigration-judges-fired-pro-palestinian-activists'],
                ['title' => 'DOJ fires US immigration judges who ruled for pro-Palestine activists (JURIST, Apr 2026)', 'url' => 'https://www.jurist.org/news/2026/04/doj-fires-us-immigration-judges-who-ruled-for-pro-palestine-activists/'],
                ['title' => 'DOJ Fires Immigration Judges Amid 2026 Case Overhaul (VisaVerge)', 'url' => 'https://www.visaverge.com/news/justice-department-fires-3-immigration-judges-after-pro-palestine-rulings/'],
                ['title' => '@FreedomofPress on X (origin tweet)', 'url' => 'https://x.com/FreedomofPress/status/2044840789532922180'],
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
