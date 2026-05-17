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
 * Republish the Abolition Media memorial for revolutionary and
 * former political prisoner Ed Mead (1941–2023), who died at
 * home on his 82nd birthday after a decade-long battle with lung
 * cancer. Original by Abolition Media, November 26, 2023.
 * Surfaced via @ADayIn1920 share, November 27, 2023.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddEdMeadMemorialArticle extends Command {
    protected $signature = 'articles:add-ed-mead-memorial';
    protected $description = 'Repost Abolition Media memorial for Ed Mead (1941–2023)';

    private const SLUG      = 'remembering-ed-mead-george-jackson-brigade-political-prisoner-memorial';
    private const IMAGE_URL = 'https://pbs.twimg.com/media/F_5jSuIXIAAvfL4.jpg';
    private const PUB_DATE  = '2023-11-26 00:00:00';

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'Memorial'], ['slug' => 'memorial']);
        $author   = Author::firstOrCreate(['name' => 'Abolition Media (republished)']);

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
<p><em>The following memorial was originally published by <a href="https://abolitionmedia.noblogs.org/post/2023/11/26/remembering-the-life-and-times-of-revolutionary-and-former-political-prisoner-ed-mead/">Abolition Media</a> on November 26, 2023, and is republished here in full with attribution. The original surfaced via <a href="https://x.com/ADayIn1920/status/1728928797377077726">@ADayIn1920</a>.</em></p>

<hr>

<p>A memorial and look-back at the life of former political prisoner and activist, Ed Mead.</p>

<blockquote>
<p>"Regardless of when a general change in political consciousness may come to the U.S., the fact remains that the march of history and the forces of progress are on our side. Through the process of our struggle we will make important changes right now, changes that will also help to propel that much needed rise in consciousness right here in the belly of the beast." — Ed Mead (1941–2023)</p>
</blockquote>

<p>On November 6, 2023, lifelong abolitionist, writer, fighter, and former political prisoner Ed Mead joined the ancestors. Ed died at home, on his 82nd birthday, after almost a decade of battling late stage lung cancer. Born in 1941, in Santa Monica, California, to Ramona (Ona) Irene Mead and Edward Leo Mead, Ed was the second oldest of six siblings.</p>

<p>Ed Mead did not live a conventional life. As his lifelong friend and comrade, Mark Cook, is fond of saying, Ed spent his life "kicking ass for the working class." After spending much of his youth in reform "schools" and detention centers along the Pacific coast, Ed became politicized in prison in the 1960s. He was a founding member of the <strong>George Jackson Brigade</strong>, a revolutionary guerilla underground organization based in the Pacific Northwest in the mid-to-late 1970s. Ed spent 35 years of his life in prisons, 18 of which were for his political actions as a member of the George Jackson Brigade.</p>

<p>A brief bio for an essay Ed wrote in the 2024 Certain Days: Freedom for Political Prisoners calendar reads:</p>

<blockquote>
<p>"I was once a young man doing life on the installment plan, well on my way to becoming just another crime statistic. Then something changed, I became rights conscious. I no longer identified as a criminal, instead I came to identify as a prisoner rights activist. With the passage of time and a lot of effort, I morphed again; I became class conscious—I became a communist. These changes were not sudden, they involved years of struggle and difficult study. The one thread throughout the years of change was political struggle on the inside and studying the writings of early revolutionaries. This is the path for those of you who will no longer accept the things you cannot change and are instead changing the things you cannot accept."</p>
</blockquote>

<p>While in prison for his part in armed struggle, Ed helped to form <strong>Men Against Sexism (MAS)</strong> at Walla Walla State Penitentiary in Washington. With other comrades, Ed helped to put an end to prisoner-on-prisoner sexual assault and other forms of abuse at Walla Walla. He also helped to form the Committee to Safeguard Prisoners' Rights at Arizona State Prison. He was a seasoned jailhouse lawyer and a committed organizer within the prison walls.</p>

<p>While imprisoned, Ed was a prodigious journalist. He co-founded and wrote for the <em>Red Dragon</em> in the 1970s, <em>The Abolitionist</em> in the 1980s (different from the contemporary newspaper of that name), and <strong>Prison Legal News</strong>, which still exists and is the longest running newspaper produced by and for current and former prisoners in the United States.</p>

<p>Once released from prison in 1993, Ed worked tirelessly with revolutionary organizations and prisoner support groups, including but not limited to the Prairie Fire Organizing Committee, the Attica Brothers Legal Defense Committee, the Seattle chapter of the National Jericho Movement, All of Us or None, and the National Lawyers Guild. Ed created the <strong>Free Mark Cook Organizing Committee</strong> and worked relentlessly to free his comrade Mark Cook, who was finally released in 2000.</p>

<p>He also founded Prison Art, a nonprofit website that provided a platform for prisoners to sell their crafts and artwork. And he continued to write about prison conditions and prisoner resistance. He wrote for California Prison Focus, founded <em>The Rock</em> to support California prisoners on hunger strike, co-created the prison newsletter <em>The Kite</em>, and the <em>Prison Covid</em> newsletter to track the pandemic in prison in 2020–2021. Ed believed changing prisons will come from the prisoners themselves. This belief motivated his work on publications featuring prisoner journalism and communications.</p>

<p>In 2016, Mead donated his papers to the University of Washington Libraries to be accessed by researchers, students, activists, and others. The collection, which forms the basis of something now called the <strong>Washington Prison History Project</strong>, includes several prisoner-run newsletters and lawsuits that Mead participated in. It also included the programming code for the Warden Game, a computer game Ed designed in prison in the mid-1980s after the Washington Department of Corrections introduced computers on a limited capacity in prisons. (A playable version of the game, based on Ed's original code, is on the WPHP site.) Ed was later able to use the computer skills he taught himself inside to gain employment as a technical engineer for several different agencies.</p>

<p>Ed published the zine <em>The Theory and Practice of Armed Struggle in the Northwest: A Historical Analysis</em> (Kersplebedeb, 2007), and the book <em>Lumpen: The Autobiography of Ed Mead</em> (Kersplebedeb, 2015). Some of his organizing in Washington prisons is also captured in the books <em>Concrete Mama: Prison Profiles from Walla Walla</em> (originally published by University of Missouri Press, 1981), <em>Guerrilla USA: The George Jackson Brigade and the Anticapitalist Underground of the 1970s</em> (University of California Press, 2010), and <em>Creating a Movement with Teeth: A Documentary History of the George Jackson Brigade</em> (PM Press, 2010), as well as in dozens of talks and interviews he conducted over the years. He can be seen in the film <em>The Gentleman Bank Robber: The Story of Butch Lesbian Freedom Fighter rita bo brown</em> (2017). Along with Mark Cook, Ed also has an interview in the forthcoming book <em>Rattling the Cages: Oral Histories of North American Political Prisoners</em> (AK Press, due out in December 2023).</p>

<p>In the Postscript to <em>Lumpen</em>, Ed wrote, "Let me tell you what my mama told me. She said the Earth should be a better place to live as a result of you having passed through. It took me a long while to internalize that message, although I do think the world is a slightly better place as a result of my having been here." We agree with Ed—the world is a better place because of his lifetime of struggle and sacrifice.</p>

<p><strong>Ed Mead Presente!</strong></p>

<hr>

<p><em>Originally published by Abolition Media at <a href="https://abolitionmedia.noblogs.org/post/2023/11/26/remembering-the-life-and-times-of-revolutionary-and-former-political-prisoner-ed-mead/">abolitionmedia.noblogs.org</a>, November 26, 2023.</em></p>
BODY;

        $data = [
            'title'        => 'Remembering Ed Mead (1941–2023): Revolutionary, George Jackson Brigade Member, Lifelong Abolitionist',
            'intro'        => "A memorial and look-back at the life of former political prisoner and George Jackson Brigade founding member Ed Mead, who died at home on his 82nd birthday, November 6, 2023, after a decade-long battle with lung cancer. Republished with attribution from Abolition Media.",
            'body'         => $body,
            'category_id'  => $category->id,
            'author_id'    => $author->id,
            'image'        => $imagePath,
            'published_at' => Carbon::parse(self::PUB_DATE),
            'citations_json' => [
                ['title' => 'Abolition Media — Remembering the Life and Times of Revolutionary and Former Political Prisoner, Ed Mead', 'url' => 'https://abolitionmedia.noblogs.org/post/2023/11/26/remembering-the-life-and-times-of-revolutionary-and-former-political-prisoner-ed-mead/'],
                ['title' => '@ADayIn1920 on X (original share)', 'url' => 'https://x.com/ADayIn1920/status/1728928797377077726'],
                ['title' => 'Lumpen: The Autobiography of Ed Mead (Kersplebedeb, 2015)', 'url' => 'https://www.leftwingbooks.net/book/content/lumpen-autobiography-ed-mead'],
                ['title' => 'Washington Prison History Project — Ed Mead Papers (UW Libraries)', 'url' => 'https://depts.washington.edu/labhist/'],
                ['title' => 'Prison Legal News', 'url' => 'https://www.prisonlegalnews.org/'],
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
