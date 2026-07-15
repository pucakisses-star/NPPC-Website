<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Adds Matthew Wills's JSTOR Daily piece "How the Espionage Act Became a Tool
 * of Repression" to the article section, and creates the Matthew Wills author
 * profile (bio + avatar) to carry the byline.
 *
 * The body is a faithful, attributed editorial summary of Wills's article —
 * not a verbatim reproduction of the copyrighted JSTOR Daily text — with a
 * prominent link to the original and the underlying scholarly citation
 * (Geoffrey R. Stone). Dated to March 2026 per request. Directly relevant to
 * the WWI Espionage Act prisoners in the database (Rose Pastor Stokes, D. T.
 * Blodgett, Rev. Clarence Waldron all appear in the piece).
 *
 * Idempotent — updates the article by slug and the author by name; re-copies
 * the committed avatar to the public disk.
 */
final class AddEspionageActRepressionArticle extends Command
{
    protected $signature = 'articles:add-espionage-act-repression';

    protected $description = 'Add Matthew Wills\'s "How the Espionage Act Became a Tool of Repression" article + author profile';

    private const SLUG = 'how-the-espionage-act-became-a-tool-of-repression';

    private const PUB_DATE = '2026-03-12 10:00:00';

    private const AVATAR_SOURCE = 'images/authors/matthew-wills.jpg';

    private const AVATAR = 'authors/matthew-wills.jpg';

    private const SOURCE_URL = 'https://daily.jstor.org/how-the-espionage-act-became-a-tool-of-repression/';

    public function handle(): int
    {
        // Author: Matthew Wills (bio + avatar).
        $source = public_path(self::AVATAR_SOURCE);
        if (is_file($source)) {
            Storage::disk('public')->put(self::AVATAR, file_get_contents($source));
            $this->info('Avatar copied to public disk: '.self::AVATAR);
        } else {
            $this->warn('Author avatar not found: public/'.self::AVATAR_SOURCE);
        }

        $author = Author::firstOrNew(['name' => 'Matthew Wills']);
        $author->about = 'Matthew Wills has advanced degrees in library science and film studies and is lapsed in '
            .'both fields. He has published in Poetry, Huffington Post, and Nature Conservancy Magazine, among other '
            .'places, and blogs regularly about urban natural history at matthewwills.com.';
        $author->avatar = self::AVATAR;
        $author->save();
        $this->info(($author->wasRecentlyCreated ? 'Created' : 'Updated')." author: {$author->name}");

        $category = Category::firstOrCreate(['title' => 'Publications'], ['slug' => 'publications']);

        $intro = 'The Espionage Act of 1917 was written to protect military secrets in wartime — but within months '
            .'it had become the engine of one of the most repressive periods in American history, with more than 2,000 '
            .'dissenters prosecuted for their words. Matthew Wills, writing for JSTOR Daily, traces how a statute its own '
            .'authors barely understood was turned into a tool for punishing pacifists, socialists, and preachers.';

        $body = <<<'BODY'
<p><em>This is a summary of Matthew Wills's article <a href="https://daily.jstor.org/how-the-espionage-act-became-a-tool-of-repression/" target="_blank" rel="noopener">"How the Espionage Act Became a Tool of Repression,"</a> originally published by <strong>JSTOR Daily</strong>. Read the full piece at the link.</em></p>

<p>When Congress passed the Espionage Act in June 1917, two months after the United States entered the First World War, its stated purpose was narrow: to guard military secrets and protect the machinery of mobilization. What followed was something its authors had not intended and, by many accounts, did not foresee — one of the most repressive periods in American history. More than two thousand people were prosecuted for alleged disloyalty, most of them not for espionage in any ordinary sense but for what they said, wrote, or handed out.</p>

<h2>A law nobody agreed on</h2>

<p>As the legal scholar Geoffrey R. Stone has documented, the Act as passed was in fact narrower than what the Justice Department originally wanted — the administration's first proposal reached further, toward press censorship and the targeting of anarchists, and President Wilson considered the final version a disappointment. Yet the statute that emerged was so ambiguous that, as one senator remarked, he could not find two colleagues who agreed on what it meant. Into that ambiguity stepped federal judges, who read the law broadly and handed down strikingly harsh sentences.</p>

<h2>Ten years for a letter, twenty for a leaflet</h2>

<p>The results were severe and often grotesque. <strong>Rose Pastor Stokes</strong> was sentenced to ten years for writing that "no government which is for the profiteers can also be for the people" — a remark in a letter to a newspaper. <strong>D. T. Blodgett</strong> received twenty years for distributing a leaflet urging voters to reject congressmen who had supported conscription. The Reverend <strong>Clarence Waldron</strong> was convicted for circulating Christian-pacifist literature. The filmmaker <strong>Robert Goldstein</strong> was prosecuted over <em>The Spirit of '76</em>, a movie about the Revolutionary War, on the theory that depicting British soldiers unfavorably could undermine a wartime ally.</p>

<h2>Learned Hand's dissenting logic</h2>

<p>Against this current, Judge <strong>Learned Hand</strong> tried to draw a line. Ruling in the case of the radical journal <em>The Masses</em>, he distinguished ordinary political agitation — however heated — from "direct incitement to violent resistance," and held that only the latter could be punished. His decision was overturned on appeal, <em>The Masses</em> was suppressed, and Hand's career suffered for a time. But his reasoning endured. Decades later it helped shape the Supreme Court's 1969 decision in <em>Brandenburg v. Ohio</em>, which finally gave inflammatory political speech strong constitutional protection.</p>

<h2>Still on the books</h2>

<p>The Espionage Act was never repealed. It remains law, and it remains in use: in 2011 <strong>Chelsea Manning</strong> was charged under the same 1917 statute, drawing a 35-year sentence later commuted. The through-line Wills traces — from the pacifists and socialists of 1917 to the whistleblowers of the twenty-first century — is a reminder that a law's reach is set less by the intentions of the people who write it than by the willingness of the people who enforce it.</p>

<p><em>— Summarized from Matthew Wills, <a href="https://daily.jstor.org/how-the-espionage-act-became-a-tool-of-repression/" target="_blank" rel="noopener">"How the Espionage Act Became a Tool of Repression,"</a> JSTOR Daily. Drawing on Geoffrey R. Stone, "Judge Learned Hand and the Espionage Act of 1917: A Mystery Unraveled," University of Chicago Law Review 70, no. 1 (2003): 335–358.</em></p>
BODY;

        $data = [
            'title' => 'How the Espionage Act Became a Tool of Repression',
            'intro' => $intro,
            'body' => $body,
            'category_id' => $category->id,
            'author_id' => $author->id,
            'published_at' => Carbon::parse(self::PUB_DATE),
            'citations_json' => [
                ['title' => 'Matthew Wills, "How the Espionage Act Became a Tool of Repression" — JSTOR Daily', 'url' => self::SOURCE_URL],
                ['title' => 'Geoffrey R. Stone, "Judge Learned Hand and the Espionage Act of 1917: A Mystery Unraveled," University of Chicago Law Review 70, no. 1 (2003)', 'url' => 'https://www.jstor.org/stable/1600555'],
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
