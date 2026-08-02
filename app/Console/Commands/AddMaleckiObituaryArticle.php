<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Prisoner;
use Carbon\Carbon;
use Illuminate\Console\Command;

final class AddMaleckiObituaryArticle extends Command {
    protected $signature = 'articles:add-malecki-obituary';
    protected $description = 'Publish the obituary for Robert Malecki (1942-2024)';

    private const SLUG     = 'robert-malecki-obituary-1942-2024';
    private const PUB_DATE = '2026-08-02 12:00:00';

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'News'], ['slug' => 'news']);
        $author   = Author::firstOrCreate(['name' => 'NPPC Editorial']);

        // Reuse his database portrait as the article image when present.
        $prisoner = Prisoner::withUnderReview()->where('slug', 'robert-malecki')->first();
        $image    = $prisoner?->photo ?: '';

        $body = <<<'BODY'
<p><em>Robert Malecki, the American anti-war activist who destroyed tens of thousands of draft cards during the Vietnam War and then spent more than half a century in exile in Sweden, died on September 24, 2024. He was 81.</em></p>

<p>Malecki was born on October 27, 1942. Between 1968 and 1972, at the height of the American war in Vietnam, he carried out one of the most sustained direct-action campaigns of the draft-resistance movement, destroying what he described as "tens of thousands" of draft cards — the Selective Service records through which young Americans were conscripted into the war. He also took responsibility for the destruction of the international computer network of the Dow Chemical Corporation in Washington, D.C. — the company then infamous as the manufacturer of the napalm being dropped on Vietnam.</p>

<p>For these acts he served time in federal prison. Shortly after his release, he was charged again — this time with conspiracy to bomb public buildings and power plants. Facing a new prosecution and the prospect of decades more behind bars, Malecki made the decision that would define the rest of his life: with funds raised by fellow anti-war protesters, he fled the United States in June 1972 and sought refuge in Sweden.</p>

<h2>Fifty-two years of exile</h2>

<p>He never came home. Sweden — which through the war years had become a haven for hundreds of American deserters and resisters — remained his country for the rest of his life. From June 1972 until his death in September 2024, Malecki lived in exile for more than fifty-two years, one of the longest political exiles of any American of the Vietnam generation. The charges that drove him abroad were never resolved, and he remained beyond the reach of the prosecution that prompted his flight until the end.</p>

<p>Malecki died in Sweden on September 24, 2024, a month short of his eighty-second birthday.</p>

<h2>His place in this coalition's record</h2>

<p>The National Political Prisoner Coalition records Malecki among the Americans whose resistance to the Vietnam War cost them their country. His entry in our database — the prison time for the draft-card destruction and the Dow Chemical action, the bomb-conspiracy charge that followed, and the exile that ran from June 1972 to his death — stands as one of the clearest illustrations in our archive of how far the state was willing to go against the anti-war movement, and how much of a life resistance could cost. Fifty-two years away is not a footnote. It was the price he paid, in full.</p>
BODY;

        $data = [
            'title'        => 'Robert Malecki, Draft Resister Who Spent Fifty-Two Years in Swedish Exile, Dies at 81',
            'intro'        => 'Robert Malecki, the American anti-war activist who destroyed tens of thousands of draft cards during the Vietnam War and then spent more than half a century in exile in Sweden, died on September 24, 2024. He was 81.',
            'body'         => $body,
            'image'        => $image,
            'category_id'  => $category->id,
            'author_id'    => $author->id,
            'published_at' => Carbon::parse(self::PUB_DATE),
        ];

        $existing = Article::where('slug', self::SLUG)->first();
        if ($existing) {
            $existing->update($data);
            $this->info('Updated article: '.$data['title']);
        } else {
            Article::create(['slug' => self::SLUG] + $data);
            $this->info('Created article: '.$data['title']);
        }

        $this->line('Live at /news/'.self::SLUG);

        return self::SUCCESS;
    }
}
