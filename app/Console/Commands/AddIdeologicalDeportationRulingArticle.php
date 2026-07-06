<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Publish an article on the Trump administration's campaign to deport
 * university pro-Palestine activists and the September 30, 2025 federal ruling
 * (Judge William Young, AAUP v. Rubio) that found the "ideological deportation
 * policy" unconstitutional. Written from the facts of the reporting (The
 * Washington Post, Oct. 2, 2025) and the ruling; original prose, not a copy.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddIdeologicalDeportationRulingArticle extends Command
{
    protected $signature = 'articles:add-ideological-deportation-ruling';

    protected $description = 'Publish article on the Sept 2025 ruling against the deportation campaign targeting pro-Palestine student activists';

    private const SLUG = 'ideological-deportation-policy-unconstitutional-pro-palestine-students-2025';

    private const IMAGE_URL = 'https://upload.wikimedia.org/wikipedia/commons/9/98/Mahmoud_Khalil_%282025%29.jpg';

    private const PUB_DATE = '2025-10-02 09:00:00';

    public function handle(): int
    {
        $category = Category::firstOrCreate(['title' => 'News'], ['slug' => 'news']);
        $author = Author::firstOrCreate(['name' => 'NPPC Editorial']);

        $imagePath = 'articles/'.self::SLUG.'.jpg';
        try {
            if (! Storage::disk('public')->exists($imagePath)) {
                $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; NPPC-Archive/1.0)'])
                    ->timeout(60)
                    ->get(self::IMAGE_URL);
                if ($resp->successful() && strlen($resp->body()) > 5000) {
                    Storage::disk('public')->put($imagePath, $resp->body());
                    $this->info('Saved article image to '.$imagePath);
                } else {
                    $imagePath = self::IMAGE_URL;
                    $this->warn('Image download failed — using remote URL.');
                }
            }
        } catch (\Throwable $e) {
            $imagePath = self::IMAGE_URL;
            $this->warn('Image fetch error: '.$e->getMessage());
        }

        $body = <<<'BODY'
<p><em>A federal judge has ruled that the Trump administration's drive to deport foreign students and scholars for pro-Palestinian speech was unconstitutional — a rebuke delivered after a year in which the government pulled non-citizens out of their homes, shipped them to detention centers a thousand miles away, and stripped visas from hundreds of people whose only alleged offense was their politics.</em></p>

<p>On <strong>September 30, 2025</strong>, U.S. District Judge <strong>William G. Young</strong>, sitting in Boston, ruled that the administration's "ideological deportation policy" — a coordinated effort to expel non-citizen university activists who criticized Israel and supported Palestinians — violated the <strong>First Amendment</strong> and the Administrative Procedure Act. The case, brought by the American Association of University Professors and other academic groups against Secretary of State Marco Rubio and other officials, produced one of the clearest judicial statements yet on the rights of non-citizens.</p>

<p>"Non-citizens lawfully present here in the United States actually have the same free speech rights as the rest of us," Judge Young wrote. He found the policy "arbitrary or capricious," and noted that President Trump himself appeared to have approved it.</p>

<h2>How the campaign worked</h2>

<p>Beginning in early 2025, the State Department under Secretary Rubio moved to revoke the visas of student and academic activists, invoking a rarely-used provision of the Immigration and Nationality Act that lets the Secretary of State declare a non-citizen deportable on "foreign policy" grounds. The Department of Homeland Security, led by Secretary Kristi Noem, carried out the arrests. According to evidence in the case, investigators reviewed the names of roughly <strong>5,000 pro-Palestinian protesters</strong>, producing about <strong>200 "violation reports."</strong> By the spring of 2025, more than a thousand students had had their visas revoked or their status terminated, many of them later restored by courts.</p>

<h2>The people it targeted</h2>

<p>The government's arrests followed a pattern: plainclothes agents, no criminal charges, and transfer to immigration jails in Louisiana and Texas far from the detainees' homes, lawyers, and courts.</p>

<ul>
<li><strong>Mahmoud Khalil</strong>, a Columbia University graduate and lawful permanent resident, was taken from his New York apartment building by ICE agents on March 8, 2025 without an arrest warrant. He was held for <strong>104 days</strong> in Louisiana — including during the birth of his first child — before a federal court ordered his release. He was never charged with a crime.</li>
<li><strong>Rümeysa Öztürk</strong>, a Turkish doctoral student at Tufts University with a valid F-1 visa, was surrounded and arrested on a Somerville, Massachusetts street on March 25, 2025 and held for roughly <strong>six weeks</strong> in Louisiana — apparently over an op-ed she had co-written criticizing her university's response to the war in Gaza.</li>
<li><strong>Badar Khan Suri</strong>, a Georgetown University postdoctoral fellow, and <strong>Mohsen Mahdawi</strong>, a Columbia student arrested at what he believed was his citizenship interview, were likewise detained and later released by court order.</li>
<li><strong>Yunseo Chung</strong>, a Columbia undergraduate and longtime permanent resident, was sought by ICE and protected by a court; <strong>Momodou Taal</strong>, a Cornell doctoral student, left the country under threat of arrest; and <strong>Leqaa Kordia</strong> spent more than a year in ICE detention in Texas without ever being charged.</li>
</ul>

<h2>What comes next</h2>

<p>Judge Young scheduled a separate hearing on remedies, which is expected to include an order halting the ideological-deportation program. The administration is expected to appeal, and several of the individual cases are moving toward higher-court rulings that will set lasting precedent on whether the government may use immigration detention as an instrument of censorship against political speech.</p>

<p>For the coalition of students and scholars who were jailed, surveilled, and driven from the country, the ruling is a vindication — but it arrives after the fact. As the courts have repeatedly found no lawful basis for these detentions, the punishment had already been inflicted: months in distant cells, interrupted studies, and, for some, exile. The National Political Prisoner Coalition regards those targeted in this campaign as political prisoners — people deprived of their liberty by the state for their beliefs and their speech.</p>

<p><em>This article is based on reporting by The Washington Post (October 2, 2025) and on the text of Judge Young's September 30, 2025 ruling.</em></p>
BODY;

        $data = [
            'title' => 'Federal Judge Rules Trump Administration Unconstitutionally Targeted Pro-Palestine Student Activists for Deportation',
            'intro' => "A federal judge has ruled that the Trump administration's drive to deport foreign students and scholars for pro-Palestinian speech was unconstitutional — after a year in which the government pulled non-citizens from their homes, shipped them to distant detention centers, and stripped visas from hundreds of people whose only alleged offense was their politics.",
            'body' => $body,
            'category_id' => $category->id,
            'author_id' => $author->id,
            'image' => $imagePath,
            'image_caption' => 'Mahmoud Khalil, the Columbia University graduate and permanent resident held for 104 days in ICE detention. (Public domain, via Wikimedia Commons)',
            'published_at' => Carbon::parse(self::PUB_DATE),
            'citations_json' => [
                ['title' => 'The Washington Post — How the Trump administration went after university pro-Palestine activists (Oct. 2, 2025)', 'url' => 'https://www.washingtonpost.com/nation/2025/10/02/trump-administration-university-pro-palestine-activists/'],
                ['title' => 'First Amendment Center — Judge finds Trump administration unconstitutionally targeted non-citizens over Gaza war protests', 'url' => 'https://firstamendment.mtsu.edu/post/judge-finds-the-trump-administration-unconstitutionally-targeted-noncitizens-over-gaza-war-protests/'],
                ['title' => 'ABC News — What we know about the foreign college students targeted for deportation', 'url' => 'https://abcnews.go.com/Politics/foreign-college-students-targeted-deportation/story?id=120210587'],
                ['title' => 'Rolling Stone — Here Are the Students Trump Wants to Deport Over Support for Palestine', 'url' => 'https://www.rollingstone.com/politics/politics-features/trump-deport-student-speech-palestine-mahmoud-khalil-ozturk-1235305498/'],
                ['title' => 'Wikipedia — Activist deportations in the second Trump presidency', 'url' => 'https://en.wikipedia.org/wiki/Activist_deportations_in_the_second_Trump_presidency'],
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
