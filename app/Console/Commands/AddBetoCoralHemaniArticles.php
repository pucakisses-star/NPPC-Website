<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Publishes two standalone News articles for cases also tracked on the
 * dashboard: the ICE detention of Colombian activist Beto Coral, and the
 * Supreme Court's decision in United States v. Hemani. Keyed by slug so re-runs
 * update in place. Idempotent.
 */
final class AddBetoCoralHemaniArticles extends Command
{
    protected $signature = 'articles:add-beto-coral-hemani';

    protected $description = 'Publish News articles for the Beto Coral ICE case and United States v. Hemani';

    public function handle(): int
    {
        $category = Category::firstOrCreate(['title' => 'News'], ['slug' => 'news']);
        $author = Author::firstOrCreate(['name' => 'NPPC Editorial']);

        $created = 0;
        $updated = 0;
        foreach ([$this->betoCoral(), $this->hemani()] as $a) {
            $payload = [
                'title' => $a['title'],
                'slug' => $a['slug'],
                'intro' => $a['intro'],
                'body' => $a['body'],
                'image' => '',
                'category_id' => $category->id,
                'author_id' => $author->id,
                'published_at' => Carbon::parse($a['published_at']),
                'citations_json' => $a['citations'],
            ];

            $existing = Article::where('slug', $a['slug'])->first();
            if ($existing) {
                $existing->update($payload);
                $updated++;
                $this->line("Updated: {$a['title']}");
            } else {
                Article::create($payload);
                $created++;
                $this->info("Added: {$a['title']}");
            }
        }

        $this->info("\nDone. created={$created} updated={$updated}");

        return self::SUCCESS;
    }

    private function betoCoral(): array
    {
        return [
            'slug' => 'beto-coral-ice-detention-arizona-june-2026',
            'title' => 'ICE Detains Colombian Activist Beto Coral as His Allies Allege Political Retaliation',
            'intro' => 'Franklin "Beto" Coral Garrido — a Colombian attorney, former congressional candidate, and one of the most visible online supporters of President Gustavo Petro — was arrested by U.S. Immigration and Customs Enforcement in Arizona on June 16, 2026 and now faces deportation. His detention came the same day Secretary of State Marco Rubio reportedly signed a memo deeming him removable, and Petro and Coral\'s supporters have framed the arrest as retaliation for his criticism of a Trump-backed Colombian candidate.',
            'published_at' => '2026-06-18 09:00:00',
            'body' => <<<'BODY'
<p>Franklin Humberto Coral Garrido, the Colombian progressive activist known online as <strong>Beto Coral</strong>, was taken into custody by U.S. Immigration and Customs Enforcement in Arizona on June 16, 2026. An attorney and former candidate for the Colombian Congress, Coral built a large following as a defender of Colombian President Gustavo Petro, a leftist who has repeatedly clashed with the Trump administration. He now sits in immigration detention facing removal from the United States.</p>

<h2>How he was detained</h2>

<p>By the account of multiple outlets, Coral entered the United States in December 2015 on a B1/B2 visitor visa that authorized a six-month stay, and remained in the country after it expired — an overstay of roughly ten years. He had been living in Arizona, where reporting says he drove for a rideshare service, and had recently traveled to Miami. Federal agents arrested him on June 16; he was processed for deportation to Colombia on the basis of his immigration status.</p>

<h2>The political backdrop</h2>

<p>What has turned an immigration case into an international incident is its timing and political context. According to Colombian journalist Daniel Coronell and subsequent U.S. reporting, Coral was detained on the same day Secretary of State Marco Rubio issued a memorandum determining that he was deportable — an unusually senior intervention in a routine removal matter. Coral had been a sharp public critic of Abelardo De La Espriella, a right-wing Colombian presidential candidate viewed as aligned with the Trump administration.</p>

<p>President Petro publicly denounced the arrest as political persecution of one of his most prominent supporters and demanded an explanation from Washington. The episode unfolded amid broader tensions between the Petro and Trump governments and drew coverage framing it as an instance of U.S. immigration enforcement reaching a foreign government\'s domestic politics.</p>

<h2>Why the NPPC is tracking it</h2>

<p>The use of immigration detention against a noncitizen for political speech — here, criticism of a foreign candidate favored by the U.S. government — fits a pattern the National Political Prisoner Coalition documents: the deployment of administrative and carceral machinery against expressive and movement activity. Whatever the ultimate disposition of Coral\'s removal case, the public record already shows a senior State Department memo and a same-day arrest of a vocal political critic. NPPC will continue to follow the case.</p>
BODY,
            'citations' => [
                ['title' => 'Newsweek — ICE Detains Petro Ally as Trump Accused of Interfering in Colombia Election', 'url' => 'https://www.newsweek.com/trump-admin-responds-ice-detains-beto-coral-colombian-president-ally-12087542'],
                ['title' => 'Infobae — Quién es Beto Coral, el activista colombiano detenido por Homeland Security en Arizona', 'url' => 'https://www.infobae.com/colombia/2026/06/17/quien-es-beto-coral-el-activista-colombiano-detenido-por-ice-en-arizona-estados-unidos/'],
                ['title' => 'Colombia One — Colombian Activist and Former Congressional Candidate Beto Coral Detained by ICE', 'url' => 'https://colombiaone.com/2026/06/17/colombia-activist-beto-coral-detained-ice/'],
                ['title' => 'Colombia One — Claims in Colombia Link Marco Rubio to Beto Coral\'s US Arrest', 'url' => 'https://colombiaone.com/2026/06/18/colombia-marco-rubio-reportedly-behind-arrest-pro-petro-activist-us/'],
            ],
        ];
    }

    private function hemani(): array
    {
        return [
            'slug' => 'united-states-v-hemani-scotus-drug-user-gun-ban-june-2026',
            'title' => 'United States v. Hemani: The Supreme Court Limits the Federal Drug-User Gun Ban',
            'intro' => 'On June 18, 2026, a unanimous Supreme Court held in United States v. Hemani that the government could not constitutionally prosecute Ali Danial Hemani under the federal ban on gun possession by unlawful drug users based on nothing more than his admitted, every-other-day marijuana use. The charge had grown out of a terrorism investigation of his family that turned up no terrorism case — only a gun he volunteered and a drug admission.',
            'published_at' => '2026-06-19 09:00:00',
            'body' => <<<'BODY'
<p>In <em>United States v. Hemani</em>, decided 9-0 on June 18, 2026, the Supreme Court ruled that the federal government violated the Second Amendment when it prosecuted Ali Danial Hemani under <strong>18 U.S.C. § 922(g)(3)</strong> — the statute barring firearm possession by an "unlawful user" of a controlled substance — on the strength of his marijuana use alone.</p>

<h2>How the case began</h2>

<p>Hemani is a U.S.-Pakistani dual citizen, born in Texas and long resident in the Dallas area. The government investigated him and his family over alleged terrorism ties and searched the family home in 2022. Hemani cooperated: he surrendered a firearm he kept in the house, pointed agents to marijuana on the property, and during a voluntary interview told them he used marijuana roughly every other day. No terrorism charge followed.</p>

<p>Instead, more than six months later, the government charged Hemani under § 922(g)(3) for possessing the gun while being an unlawful drug user — a prosecution that carried a potential sentence of up to fifteen years, resting entirely on his own admission of marijuana use.</p>

<h2>What the Court held</h2>

<p>Writing for seven justices, Justice Neil Gorsuch concluded that the government could not, consistent with the Second Amendment, automatically strip a person of the right to keep a firearm and prosecute him simply because he regularly uses a controlled substance, without any individualized showing that he is dangerous and without any pre-deprivation process. Gorsuch rejected the government\'s position that "anyone who regularly uses marijuana is categorically violent and dangerous." Justices Alito and Kagan concurred in the judgment by separate opinion, making the result unanimous.</p>

<p>Importantly, the Court did <em>not</em> strike down § 922(g)(3) itself. It left open prosecutions backed by individualized proof of dangerousness, narrowing the statute as applied rather than voiding it.</p>

<h2>Why it matters here</h2>

<p>The National Political Prisoner Coalition follows <em>Hemani</em> because of how the prosecution arose: a years-long terrorism investigation that produced no terrorism case was converted, after the fact, into a felony gun charge grounded solely in a cooperative defendant\'s admission. The pattern — surveillance of a community member, followed by a pretextual prosecution when the original theory collapses — is one this organization documents across movements. The Supreme Court\'s rejection of the charge is a meaningful limit on that tactic, even as the underlying statute survives.</p>
BODY,
            'citations' => [
                ['title' => 'The Trace — Supreme Court Limits the Federal Gun Ban on Drug Users', 'url' => 'https://www.thetrace.org/2026/06/hemani-supreme-court-gun-ban-drug-users/'],
                ['title' => 'CNBC — Supreme Court sides with marijuana user stripped of gun rights', 'url' => 'https://www.cnbc.com/2026/06/18/supreme-court-gun-ownership-drug-users-second-amendment-hunter-biden.html'],
                ['title' => 'U.S. Supreme Court — United States v. Hemani, No. 24-1234 (opinion)', 'url' => 'https://www.supremecourt.gov/opinions/25pdf/24-1234_g2bh.pdf'],
                ['title' => 'Wikipedia — United States v. Hemani', 'url' => 'https://en.wikipedia.org/wiki/United_States_v._Hemani'],
            ],
        ];
    }
}
