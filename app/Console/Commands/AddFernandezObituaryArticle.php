<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Console\Command;

final class AddFernandezObituaryArticle extends Command {
    protected $signature = 'articles:add-fernandez-obituary';
    protected $description = 'Publish the obituary for historian Johanna Fernández (1970-2026)';

    private const SLUG     = 'johanna-fernandez-obituary-1970-2026';
    // Dated to her death, per the curator's convention for obituaries.
    private const PUB_DATE = '2026-07-30 12:00:00';

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'News'], ['slug' => 'news']);
        $author   = Author::firstOrCreate(['name' => 'NPPC Editorial']);

        $body = <<<'BODY'
<p><em>Johanna Fernández, the historian who gave the Young Lords their definitive history, pried a million pages of NYPD surveillance files out of the city's archives, and spent two decades fighting for the freedom of Mumia Abu-Jamal, died on July 30, 2026, in New York City. She was 55.</em></p>

<p>Fernández was born in New York on December 2, 1970, to Dominican parents who had fled the Dominican Republic. Her own political education began early: as a Brown University undergraduate in April 1992, she was a leader of Students for Aid and Minority Admissions and one of 253 people arrested in the occupation of University Hall demanding need-blind admissions. She went on to a doctorate in history at Columbia and became an associate professor of history at Baruch College, City University of New York.</p>

<h2>The historian of the Young Lords</h2>

<p>Her masterwork, <em>The Young Lords: A Radical History</em> (University of North Carolina Press, 2019), recovered the full story of the Puerto Rican radicals who occupied churches, seized an X-ray truck, and forced New York to collect the garbage in East Harlem — and placed them where they belonged, at the center of the era's freedom movements. She co-curated <em>¡Presente! The Young Lords in New York</em> (2015) and, in 2021, the inaugural exhibition of the National Museum of the American Latino.</p>

<p>The research itself became an act of accountability. In 2014, when the City of New York claimed it could not locate the NYPD's surveillance records of the movements she studied, Fernández sued — and the litigation surfaced more than one million pages of police surveillance documents spanning 1954 to 1972, the "lost" Handschu files, a trove that transformed the study of political policing in New York. It was among the largest recoveries of domestic surveillance records in the city's history, and every scholar of political repression works in its debt.</p>

<h2>The fight for Mumia</h2>

<p>Fernández was one of the most visible and rigorous advocates for Pennsylvania death-row journalist Mumia Abu-Jamal, a man whose case this coalition has tracked for decades. She described her first prison visits to him as transformative. She co-directed the documentary <em>Justice on Trial</em> (2010), co-edited a special issue of <em>Socialism and Democracy</em> with Abu-Jamal in 2014, and edited <em>Writing on the Wall</em> (2015), the collection that carried his prison writing to a new generation. She did the unglamorous work too — the court filings, the records requests, the press conferences on Philadelphia sidewalks — year after year.</p>

<h2>Her place in this coalition's record</h2>

<p>The National Political Prisoner Coalition's archive is built on the kind of history Fernández practiced: the conviction that the state's prisoners of politics deserve to have their stories recovered, documented, and told with precision. Few scholars did more to model that work. She documented the surveillance state's paper trail, told the stories of the criminalized, and fought for the imprisoned — as a historian, and as a participant. Her books remain; so does the archive she forced open.</p>
BODY;

        $data = [
            'title'        => 'Johanna Fernández, Historian of the Young Lords and Champion of Mumia Abu-Jamal, Dies at 55',
            'intro'        => "Johanna Fernández, the historian who gave the Young Lords their definitive history, pried a million pages of NYPD surveillance files out of the city's archives, and spent two decades fighting for the freedom of Mumia Abu-Jamal, died on July 30, 2026, in New York City. She was 55.",
            'body'         => $body,
            'category_id'  => $category->id,
            'author_id'    => $author->id,
            'published_at' => Carbon::parse(self::PUB_DATE),
            'citations_json' => [
                ['title' => 'Wikipedia — Johanna Fernández (historian)', 'url' => 'https://en.wikipedia.org/wiki/Johanna_Fern%C3%A1ndez_(historian)'],
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
