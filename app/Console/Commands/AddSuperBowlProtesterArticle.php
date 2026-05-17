<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Restore content at the original placeholder slug
 *   /news/super-bowl-halftime-performer-charged-months-after-holding-protest-flag-for-gaza
 * which previously had no body. Focuses on the June 26, 2025 Louisiana
 * State Police arrest of Zul-Qarnain Kwame Nantambu — four months after
 * his on-field Gaza/Sudan flag protest during Kendrick Lamar's Super
 * Bowl LIX halftime show on Feb 9, 2025. Dated to the charging.
 *
 * Also deletes the wrongly-created standalone slug
 *   zul-qarnain-nantambu-super-bowl-halftime-gaza-sudan-flag-conviction-2026
 * from any environment it was published to, so the placeholder slug is
 * the only canonical URL for this story.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddSuperBowlProtesterArticle extends Command {
    protected $signature = 'articles:add-super-bowl-protester';
    protected $description = 'Restore Super Bowl LIX Gaza/Sudan flag protester article at its original placeholder slug';

    private const SLUG          = 'super-bowl-halftime-performer-charged-months-after-holding-protest-flag-for-gaza';
    private const OBSOLETE_SLUG = 'zul-qarnain-nantambu-super-bowl-halftime-gaza-sudan-flag-conviction-2026';
    private const PUB_DATE      = '2025-06-26 18:00:00';

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'News'], ['slug' => 'news']);
        $author   = Author::firstOrCreate(['name' => 'NPPC Editorial']);

        // Remove the wrongly-created conviction-dated slug if present.
        $obsolete = Article::where('slug', self::OBSOLETE_SLUG)->first();
        if ($obsolete) {
            $obsolete->delete();
            $this->info('Deleted obsolete duplicate slug: '.self::OBSOLETE_SLUG);
        }

        $body = <<<'BODY'
<p><em>Four months after he unfurled a stitched-together Palestinian and Sudanese flag on the field during Kendrick Lamar's Super Bowl LIX halftime show, New Orleans dancer Zul-Qarnain Kwame Nantambu has been arrested and charged with two misdemeanors. The NFL released him from on-field security the night of the protest without charges; Louisiana State Police came back for him in late June.</em></p>

<p>On <strong>Thursday, June 26, 2025</strong>, <strong>Louisiana State Police</strong> arrested <strong>Zul-Qarnain Kwame Nantambu</strong> at his home in New Orleans and booked him into the Orleans Parish Justice Center on two misdemeanor counts: <strong>resisting an officer</strong> and <strong>disturbing the peace by interruption of a lawful assembly</strong>. The charges stem from his on-field protest during the Kendrick Lamar halftime show at Super Bowl LIX, more than four months earlier.</p>

<h2>The action</h2>

<p>Nantambu, a professional dancer based in New Orleans, was hired as part of the on-field performer corps for Kendrick Lamar's halftime show at <strong>Super Bowl LIX on February 9, 2025</strong> at Caesars Superdome. During the set, while standing on the 1987 Buick GNX prop used as a stage piece, he pulled from his costume a flag he had brought into the venue — a stitched-together Palestinian and Sudanese flag bearing the painted words "Gaza" and "Sudan" — held it aloft, and ran across the field before being tackled by NFL security personnel. The action was broadcast live to an audience of <strong>roughly 127 million U.S. viewers</strong> and an additional global audience of tens of millions more — the most watched television event of 2025.</p>

<p>In a television interview shortly afterward, Nantambu explained that his goal was to "highlight the human suffering" in Gaza and Sudan — two ongoing wars he, like millions of others, had been watching unfold while the NFL declined to acknowledge either.</p>

<h2>Released that night — arrested four months later</h2>

<p>The <strong>New Orleans Police Department</strong> detained Nantambu in the moments after the halftime show ended and released him without charges that night. He went home.</p>

<p>The case did not stay closed. <strong>Louisiana State Police</strong> opened a new investigation. Sgt. Kate Stegall, an LSP spokesperson, told reporters at the time of the June arrest that troopers reopened the matter "due to the nature of the incident, and the performer's access to a highly secured area." More than four months after the on-field protest itself, troopers came for him.</p>

<p>The charging theory is notable for what it tries to do. By alleging that an NFL halftime show — a private-league entertainment property broadcast to over a hundred million people — was a "lawful assembly" that Nantambu unlawfully interrupted, Louisiana prosecutors are effectively renting the state's criminal-legal system to enforce the league's security objections months after the fact. The "resisting" count tracks Nantambu's contact with the NFL security personnel and police who tackled him on the field.</p>

<h2>The penalty besides the charges</h2>

<p>Independent of the criminal case, the <strong>NFL has imposed a lifetime ban</strong> on Nantambu from all league stadiums and events — a private-actor sanction that does not require any criminal conviction and is not reviewable by any court. The misdemeanor charging range for resisting an officer in Louisiana caps at six months and a fine. The lifetime ban is, in practical terms, the larger penalty already imposed.</p>

<h2>The lineage</h2>

<p>Nantambu's case sits in a small but recognizable tradition of athletes and performers using mass-broadcast moments to draw attention to wars and political violence: Tommie Smith and John Carlos on the medal podium at the 1968 Mexico City Olympics; Toni Smith-Thompson turning her back on the flag during national anthems at Manhattanville College in 2003; Colin Kaepernick taking a knee in 2016. Each was punished — by selectors, by leagues, by employers — for using a platform built for one message to broadcast another. None was, in any honest reading, a criminal.</p>

<p>The case is set to proceed in Orleans Criminal District Court. NPPC will track its progress and post updates as the case moves through arraignment, motions, and trial. The flag flew. 127 million people saw it. Louisiana wants to make him pay for it anyway.</p>
BODY;

        $data = [
            'title'        => 'Super Bowl Halftime Performer Charged Months After Holding Protest Flag for Gaza',
            'intro'        => "Four months after he unfurled a stitched-together Palestinian and Sudanese flag on the field during Kendrick Lamar's Super Bowl LIX halftime show, New Orleans dancer Zul-Qarnain Kwame Nantambu has been arrested and charged with two misdemeanors. The NFL released him from on-field security the night of the protest without charges; Louisiana State Police came back for him in late June.",
            'body'         => $body,
            'category_id'  => $category->id,
            'author_id'    => $author->id,
            'published_at' => Carbon::parse(self::PUB_DATE),
            'citations_json' => [
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
