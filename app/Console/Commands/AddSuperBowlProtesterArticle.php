<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Publish article on Zul-Qarnain Kwame Nantambu, the Kendrick
 * Lamar Super Bowl LIX halftime dancer who unfurled a combined
 * Palestinian + Sudanese flag on the field on February 9, 2025
 * and was federally / state arrested four months later. Convicted
 * May 11, 2026 of resisting an officer (misdemeanor), acquitted
 * of disturbing the peace. Sentencing June 1, 2026.
 *
 * Replaces the dead placeholder article at
 *   /news/super-bowl-halftime-performer-charged-months-after-holding-protest-flag-for-gaza
 * (routes/web.php now 301-redirects that slug to this article).
 *
 * Idempotent — re-runs update by slug.
 */
final class AddSuperBowlProtesterArticle extends Command {
    protected $signature = 'articles:add-super-bowl-protester';
    protected $description = 'Publish article on Zul-Qarnain Nantambu — Super Bowl LIX Gaza/Sudan flag protester';

    private const SLUG     = 'zul-qarnain-nantambu-super-bowl-halftime-gaza-sudan-flag-conviction-2026';
    private const PUB_DATE = '2026-05-12 12:00:00';

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'Repression'], ['slug' => 'repression']);
        $author   = Author::firstOrCreate(['name' => 'NPPC Editorial']);

        $body = <<<'BODY'
<p><em>The New Orleans dancer who unfurled a stitched-together Palestinian and Sudanese flag bearing the words "Gaza" and "Sudan" on the field during Kendrick Lamar's Super Bowl LIX halftime performance — released that night without charges, then arrested four months later by state police — has been convicted of resisting an officer. The NFL has banned him from every league stadium and event for life.</em></p>

<p>On <strong>Monday, May 11, 2026</strong>, in Orleans Criminal District Court, Chief Judge <strong>Juana Marine-Lombard</strong> convicted <strong>Zul-Qarnain Kwame Nantambu</strong> of resisting an officer — a Louisiana misdemeanor — and acquitted him of disturbing the peace, the second charge the state had filed. Sentencing is set for <strong>June 1, 2026</strong>.</p>

<h2>The action</h2>

<p>Nantambu, a professional dancer based in New Orleans, was hired as part of the on-field performer corps for Kendrick Lamar's halftime show at <strong>Super Bowl LIX on February 9, 2025</strong> at Caesars Superdome. During the set, while standing on the 1987 Buick GNX prop used as a stage piece, he pulled from his costume a flag he had brought into the venue — a stitched-together Palestinian and Sudanese flag bearing the painted words "Gaza" and "Sudan" — held it aloft, and ran across the field before being tackled by NFL security personnel. The action was broadcast live to an audience of <strong>roughly 127 million U.S. viewers</strong> and an additional global audience of tens of millions more — the most watched television event of 2025.</p>

<p>In a television interview afterward, Nantambu explained that his goal was to "highlight the human suffering" in Gaza and Sudan — two ongoing wars he, like millions of others, had been watching unfold while the NFL declined to acknowledge either.</p>

<h2>Four months later, an arrest</h2>

<p>The New Orleans Police Department detained Nantambu in the moments after the halftime show ended and released him without charges that night. He went home.</p>

<p>The case did not stay closed. <strong>Louisiana State Police</strong> opened a new investigation — Sgt. Kate Stegall told reporters at the time it was "due to the nature of the incident, and the performer's access to a highly secured area." On <strong>June 26, 2025</strong>, more than four months after the protest itself, state troopers arrested Nantambu and booked him into the Orleans Parish Justice Center on two misdemeanor charges: resisting an officer and disturbing the peace by interruption of a lawful assembly.</p>

<h2>The verdict</h2>

<p>At the May 11, 2026 bench trial Judge Marine-Lombard split the case. The disturbing-the-peace count was dismissed on the merits — the judge declined to accept the state's theory that an NFL halftime show, broadcast to 100+ million people, was a "lawful assembly" Nantambu had unlawfully interrupted. The resisting-an-officer count survived on the basis of his contact with the NFL security personnel and police who tackled him on the field.</p>

<p>His attorney, <strong>Emily Posner</strong>, emphasized the acquittal in remarks after the verdict: "He did not disturb the peace at the 2025 Super Bowl…and her ruling assures that the NFL does not dictate how the criminal legal system works." The framing matters — the trial functioned in part as a test of whether a private-league entertainment property could route its security objections through the state's criminal-legal system to deter future protest. On the more dangerous of the two counts, it could not.</p>

<h2>The penalty besides the verdict</h2>

<p>Independent of the criminal case, the NFL has imposed a <strong>lifetime ban</strong> on Nantambu from all league stadiums and events — a private-actor sanction that does not require any criminal conviction and is not reviewable by any court. The misdemeanor sentencing range for resisting an officer in Louisiana caps at six months and a fine. The lifetime ban is, in practical terms, the larger penalty.</p>

<h2>The lineage</h2>

<p>Nantambu's case sits in a small but recognizable tradition of athletes and performers using mass-broadcast moments to draw attention to wars and political violence: Tommie Smith and John Carlos on the medal podium at the 1968 Mexico City Olympics; Toni Smith-Thompson turning her back on the flag during national anthems at Manhattanville College in 2003; Colin Kaepernick taking a knee in 2016. Each was punished — by selectors, by leagues, by employers — for using a platform built for one message to broadcast another. None was, in any honest reading, a criminal.</p>

<p>Whatever happens at sentencing on June 1, the case is closed enough to draw a few conclusions. The flag flew. 127 million people saw it. The NFL got a lifetime ban; the State of Louisiana got a misdemeanor. The point of the protest — that Gaza and Sudan are still on fire and that the people running the most-watched television event of the year have nothing to say about it — got across.</p>
BODY;

        $data = [
            'title'        => 'Super Bowl LIX Halftime Dancer Convicted of Misdemeanor Months After "Gaza-Sudan" Flag Protest',
            'intro'        => "The New Orleans dancer who unfurled a stitched-together Palestinian and Sudanese flag bearing the words \"Gaza\" and \"Sudan\" on the field during Kendrick Lamar's Super Bowl LIX halftime performance — released that night without charges, then arrested four months later by state police — has been convicted of resisting an officer. The NFL has banned him from every league stadium and event for life.",
            'body'         => $body,
            'category_id'  => $category->id,
            'author_id'    => $author->id,
            'published_at' => Carbon::parse(self::PUB_DATE),
            'citations_json' => [
                ['title' => 'NOLA.com — New Orleans dancer who protested at Super Bowl LIX halftime show found guilty of resisting officer', 'url' => 'https://www.nola.com/news/courts/super-bowl-lix-protestor/article_6e08e920-2194-4478-8d4e-3753bdc09c6f.html'],
                ['title' => 'Washington Post — Super Bowl halftime performer who ran with a protest flag is found guilty of resisting an officer', 'url' => 'https://www.washingtonpost.com/sports/2026/05/11/super-bowl-halftime-kendrick-lamar-disruptor/'],
                ['title' => 'CNN — Super Bowl halftime performer arrested after police say he held flag stating "Sudan and Free Gaza"', 'url' => 'https://www.cnn.com/2025/06/26/sport/super-bowl-halftime-performer-arrested-spt'],
                ['title' => 'NBC News — Super Bowl halftime show dancer displays Palestinian, Sudanese flags during Kendrick Lamar performance', 'url' => 'https://www.nbcnews.com/news/us-news/palestine-flag-super-bowl-halftime-performer-sudan-gaza-kendrick-lamar-rcna191411'],
                ['title' => 'Al Jazeera — Protester waves Sudan-Palestine flag during Kendrick Lamar Super Bowl show', 'url' => 'https://www.aljazeera.com/news/2025/2/10/protester-with-palestine-flag-interrupts-lamars-super-bowl-half-time-show'],
                ['title' => 'Rolling Stone — Kendrick Lamar Halftime Show Dancer Arrested Months After Gaza-Sudan Protest', 'url' => 'https://www.rollingstone.com/music/music-news/kendrick-lamar-super-bowl-dancer-arrested-gaza-protest-1235373344/'],
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
