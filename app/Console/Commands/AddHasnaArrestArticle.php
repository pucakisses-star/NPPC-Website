<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Console\Command;

final class AddHasnaArrestArticle extends Command {
    protected $signature = 'articles:add-hasna-arrest';
    protected $description = 'Publish news entry on the UK arrest of Mohammad Yousef Hasna on US Hamas-financing charges';

    private const SLUG     = 'mohammad-yousef-hasna-uk-arrest-us-hamas-financing-charges';
    private const PUB_DATE = '2026-08-02 15:00:00';

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'News'], ['slug' => 'news']);
        $author   = Author::firstOrCreate(['name' => 'NPPC Editorial']);

        $body = <<<'BODY'
<p><em>A 45-year-old Turkish man from Istanbul was arrested in the United Kingdom on July 31, 2026, on American charges accusing him of financing Hamas through a charity — the latest in the expanding set of extraterritorial material-support prosecutions this coalition tracks.</em></p>

<p><strong>Mohammad Yousef Hasna</strong>, 45, of Istanbul, was arrested in the United Kingdom on Friday, July 31, 2026, and ordered held pending extradition proceedings on United States charges of <strong>conspiring to provide material support to Hamas, conspiring to finance terrorism, and financing terrorism</strong>. Each charge carries a maximum penalty of twenty years in prison.</p>

<p>The charges were announced by U.S. Attorney Jamie McDonald in Manhattan. Prosecutors allege that since at least 2023, Hasna worked closely with Hamas's senior leadership — including Ghazi Hamad, a member of the organization's governing body — funneling money and supplies through what they characterize as a sham global charity. "Hasna worked closely with Hamas' senior leadership to deliver supplies, food and funding to Hamas under the guise of humanitarian aid," McDonald said.</p>

<p>Hasna remains in British custody while the extradition request is litigated. The charges are accusations, and he has not been tried.</p>

<p>His case has been added to the coalition's <a href="/database">database</a>, where it will be tracked through the extradition proceedings and any U.S. prosecution that follows.</p>
BODY;

        $data = [
            'title'        => 'Turkish Man Arrested in U.K. on U.S. Charges of Financing Hamas Through a Charity',
            'intro'        => 'Mohammad Yousef Hasna, 45, of Istanbul, was arrested in the United Kingdom on July 31, 2026, and ordered held pending extradition on U.S. charges of conspiring to provide material support to Hamas and financing terrorism. His case has been added to the coalition database.',
            'body'         => $body,
            'category_id'  => $category->id,
            'author_id'    => $author->id,
            'published_at' => Carbon::parse(self::PUB_DATE),
            'citations_json' => [
                ['title' => 'ABC News (AP wire) — Turkish man arrested in UK on US charges accusing him of aiding Hamas', 'url' => 'https://abcnews.com/US/wireStory/turkish-man-arrested-uk-us-charges-accusing-aiding-135274598'],
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

        $this->line('Live at /news/'.self::SLUG);

        return self::SUCCESS;
    }
}
