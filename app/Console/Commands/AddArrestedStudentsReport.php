<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Publishes the report "Documents Prove The Trump Administration Arrested
 * Students for Criticizing Israel" (republished from Mother Jones / the Center
 * for Investigative Reporting), filed under the "Reports" category and dated
 * 2026-01-23. Copies the committed cover image to the public disk and sets it
 * on the article. Idempotent: matches by slug (or title) and updates in place.
 */
final class AddArrestedStudentsReport extends Command
{
    protected $signature = 'articles:add-arrested-students-report';

    protected $description = 'Publish the "Documents Prove The Trump Administration Arrested Students for Criticizing Israel" report';

    private const SLUG = 'documents-prove-the-trump-administration-arrested-students-for-criticizing-israel';

    private const SOURCE_IMAGE = 'images/articles/documents-prove-trump-administration-arrested-students.jpg';

    private const IMAGE = 'articles/documents-prove-trump-administration-arrested-students.jpg';

    public function handle(): int
    {
        $author = Author::firstOrCreate(
            ['name' => 'Mother Jones'],
            ['about' => 'Mother Jones is a nonprofit news organization published by the Center for Investigative Reporting.']
        );

        $category = Category::firstOrCreate(['slug' => 'reports'], ['title' => 'Reports']);

        // Copy the committed cover image to the public disk.
        $source = public_path(self::SOURCE_IMAGE);
        if (is_file($source)) {
            Storage::disk('public')->put(self::IMAGE, file_get_contents($source));
            $this->info('Copied cover image to public disk: '.self::IMAGE);
        } else {
            $this->warn('Source image not found: public/'.self::SOURCE_IMAGE.' — article will be created without it.');
        }

        $title = 'Documents Prove The Trump Administration Arrested Students for Criticizing Israel';

        $intro = 'Newly unsealed court records illustrate how the US government specifically targeted students for pro-Palestine speech.';

        $body = <<<'HTML'
<p>Documents unsealed by a federal judge this week <a href="https://www.courtlistener.com/docket/69784731/american-association-of-university-professors-v-rubio/" target="_blank" rel="noopener">confirm</a> the federal government&rsquo;s attempts to target, arrest, and deport students for pro-Palestine speech on college campuses last year. The court records also make clear the methods of investigation. The government looked to unverified accounts shared on social media and utilized <a href="https://www.motherjones.com/politics/2025/07/canary-mission-israel-palestine-blacklist-university-trump-deportation-ozturk-khalil/" target="_blank" rel="noopener">Canary Mission</a>&mdash;a shadowy online blacklist created by anonymous authors to smear pro-Palestine activists&mdash;to gather evidence against student protestors.</p>

<p>The documents were unsealed only after sustained pressure from journalists and press-freedom groups. News organizations, including the Center for Investigative Reporting (the parent organization of Mother Jones and the <a href="https://revealnews.org/" target="_blank" rel="noopener">Reveal</a> radio show and podcast), challenged the government&rsquo;s efforts to keep large portions of the record secret, arguing that the public had a right to understand how speech was being scrutinized and punished. In unsealing the documents, US District Judge William G. Young sharply rebuked the Trump administration and called the government&rsquo;s actions against pro-Palestinian speech an unconstitutional attempt to twist laws to intimidate students.</p>

<p>The new materials <a href="https://www.motherjones.com/politics/2025/07/canary-mission-israel-palestine-blacklist-university-trump-deportation-ozturk-khalil/" target="_blank" rel="noopener">confirm previous accounts and reporting</a> about the Department of Homeland Security&rsquo;s targeting of students. In 2025, after <a href="https://www.motherjones.com/politics/2025/03/mahmoud-khalil-student-protester-palestine-trump-deportation-columbia-shai-davidai/" target="_blank" rel="noopener">Mahmoud Khalil</a> and <a href="https://revealnews.org/podcast/trump-deportations-venezuela-el-salvador-students-gaza-mahmoud-khalil-rumeysa-ozturk/" target="_blank" rel="noopener">R&uuml;meysa &Ouml;zt&uuml;rk</a> were taken into custody by Immigration and Customs Enforcement officials, speculation spread quickly among advocacy groups that government officials were collecting names by looking at pro-Israel monitoring websites like Canary Mission.</p>

<p>The documents unsealed provide the clearest timeline of how this happened. And they make clear how quickly a case escalated, with Canary Mission&rsquo;s help. &Ouml;zt&uuml;rk&rsquo;s case is indicative. In March of 2024, &Ouml;zt&uuml;rk was one of four names published <a href="https://www.tuftsdaily.com/article/2024/03/4ftk27sm6jkj" target="_blank" rel="noopener">as part of a campus op-ed</a> that criticized the Tufts University administration for failing to honor <a href="https://www.tuftsdaily.com/article/2024/03/tcu-senate-passes-3-of-4-resolutions-seeking-university-accountability-for-ties-to-israel" target="_blank" rel="noopener">three student-led resolutions that had recently passed</a>, including one calling for recognition of genocide in Gaza and another for divestment from the state of Israel.</p>

<p>Almost a year later, a profile of R&uuml;meysa &Ouml;zt&uuml;rk <a href="https://canarymission.org/individual/Rumeysa_Ozturk" target="_blank" rel="noopener">appeared on Canary Mission</a>. A month after that, according to the documents, government officials compiled a report on &Ouml;zt&uuml;rk. A week later, on March 25, 2025, <a href="https://www.motherjones.com/politics/2025/05/rumeysa-ozturk-tufts-phd-student-released-ice-detention-deportation-trump/" target="_blank" rel="noopener">&Ouml;zt&uuml;rk was arrested</a> by United States Immigration and Customs Enforcement officials.</p>

<p>The new records make clear what happened: &Ouml;zt&uuml;rk&rsquo;s participation in the op-ed <a href="https://storage.courtlistener.com/recap/gov.uscourts.mad.282460/gov.uscourts.mad.282460.315.18.pdf" target="_blank" rel="noopener">was cited as the cause for her removal</a>. (DHS and ICE did not show &Ouml;zt&uuml;rk had participated in any antisemitic activity.)</p>

<p><em>Related:</em> <a href="https://www.motherjones.com/politics/2025/07/canary-mission-israel-palestine-blacklist-university-trump-deportation-ozturk-khalil/" target="_blank" rel="noopener">How a Shadowy Online Blacklist Became a Legal Threat to Pro-Palestinian Activists</a></p>

<p>The documents show that federal agencies, such as Homeland Security Investigations (HSI) within the Department of Homeland Security, relied on &ldquo;publicly available information,&rdquo; including social media posts and third-party websites, to assess students&rsquo; eligibility for visas and residency.</p>

<p>And they confirm previous public testimony. In July 2025, Peter Hatch, an ICE official who was part of HSI&rsquo;s division that compiled background reports on students, <a href="https://knightcolumbia.org/documents/nuqkur34f7" target="_blank" rel="noopener">testified during the lawsuit&rsquo;s hearings that</a> &ldquo;the direction [for his team] was to look at the website [Canary Mission].&rdquo; Hatch says his team compiled more than 100 reports from a list of 5,000 names.</p>

<blockquote><p>&ldquo;Many of us have long been trying to raise alarm bells about the dangers of privately-funded, hate groups such as Canary Mission,&rdquo; said Nadia Abu El Hajj, an anthropology professor at Barnard and Columbia University. &ldquo;As testimony at the trial and the trove of newly released documents clearly demonstrate, Canary Mission&rsquo;s blacklist has serious, material consequences: they have played a central role in providing names of Palestinian and pro-Palestinian students to the federal government, calling for their deportation.&rdquo;</p></blockquote>

<p>Internal reports also show that social posts; news articles from sources like the New York Post; and unverified information from Canary Mission were used to justify the deportation of Khalil, &Ouml;zt&uuml;rk, and a slew of others, including Mohsen Mahdawi, Badar Khan Suri, and Yunseo Chung. The <a href="https://storage.courtlistener.com/recap/gov.uscourts.mad.282460/gov.uscourts.mad.282460.315.15.pdf" target="_blank" rel="noopener">files for Khalil, &Ouml;zt&uuml;rk, and Mahdawi</a> all specifically cite Canary Mission. The reports also include posts from X accounts like <a href="https://x.com/CampusJewHate" target="_blank" rel="noopener">@CampusJewHate</a>, which describes itself as an account to &ldquo;put pressure on academic institutions to oppose Jew-hatred by exposing toxic anti-Israel climate on their campuses.&rdquo;</p>

<blockquote><p>&ldquo;Secretaries Noem and Rubio and their several agents and subordinates acted in concert to misuse the sweeping powers of their respective offices to target non-citizen pro-Palestinians for deportation primarily on account of their First Amendment-protected political speech,&rdquo; wrote Judge Young <a href="https://storage.courtlistener.com/recap/gov.uscourts.mad.282460/gov.uscourts.mad.282460.314.0.pdf" target="_blank" rel="noopener">in his court order</a>. &ldquo;Moreover, the effect of these targeted deportation proceedings continues unconstitutionally to chill freedom of speech to this day.&rdquo;</p></blockquote>

<p>The State Department, in a statement, was unapologetic. &ldquo;The Trump Administration is using every tool available to get terrorist-supporting aliens out of our country,&rdquo; a spokesperson said. &ldquo;A visa is a privilege, not a right. We abide by all applicable laws to ensure the United States does not harbor aliens who pose a threat to our national security.&rdquo;</p>

<p>The documents have been released as the US pushes once again to deport Khalil. Earlier this month, a US Appeals court <a href="https://www2.ca3.uscourts.gov/opinarch/252162p.pdf" target="_blank" rel="noopener">overturned</a> a lower court decision that blocked the Columbia former graduate student&rsquo;s deportation. Following that ruling, a DHS Assistant Secretary Tricia McLaughlin went <a href="https://www.newsnationnow.com/katie-pavlich-tonight/" target="_blank" rel="noopener">on NewsNation</a> and promised to send Khalil to Algeria.</p>

<p>In a statement, McLaughlin told the Center for Investigative Reporting that &ldquo;there is no room in the United States for the rest of the world&rsquo;s terrorist sympathizers, and we are under no obligation to admit them or let them stay here. The framers of our Constitution and its Bill of Rights never contemplated a world where foreign citizens could come here as guests and hide behind the First Amendment to advocate for anti-American and anti-Semitic violence and terrorism.&rdquo;</p>
HTML;

        $attributes = [
            'title' => $title,
            'slug' => self::SLUG,
            'intro' => $intro,
            'body' => $body,
            'image' => self::IMAGE,
            'author_id' => $author->id,
            'category_id' => $category->id,
            'published_at' => '2026-01-23 09:00:00',
        ];

        $article = Article::where('slug', self::SLUG)->orWhere('title', $title)->first();

        if ($article) {
            $article->fill($attributes)->save();
            $this->info("Updated article: {$article->title}");
        } else {
            $article = Article::create($attributes);
            $this->info("Created article: {$article->title}");
        }

        $this->info("Filed under {$category->title}, dated 2026-01-23. View: {$article->url}");

        return self::SUCCESS;
    }
}
