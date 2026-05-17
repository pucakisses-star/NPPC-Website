<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Publish article on the December 16, 2024 dismissal of all four
 * felony official-misconduct charges against retired Joliet police
 * Sgt. Javier Esqueda, who leaked dashcam video of Eric Lurry's
 * January 2020 in-custody death and faced up to 20 years.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddEsquedaDroppedArticle extends Command {
    protected $signature = 'articles:add-esqueda-dropped';
    protected $description = 'Publish article on the Dec 16, 2024 dismissal of charges against Joliet whistleblower Javier Esqueda';

    private const SLUG     = 'esqueda-joliet-whistleblower-charges-dropped-2024';
    private const PUB_DATE = '2024-12-16 16:00:00';

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'News'], ['slug' => 'news']);
        $author   = Author::firstOrCreate(['name' => 'NPPC Editorial']);

        $body = <<<'BODY'
<p><em>Four years after he gave a local TV reporter dashcam footage showing officers slap, restrict the airway of, and shove a baton into the mouth of a handcuffed Black man hours before his death, retired Joliet police Sgt. Javier Esqueda watched the prosecutor who had charged him with four felonies stand up on the morning of trial and move to drop every count.</em></p>

<p>On Monday, December 16, 2024 — the day Sgt. <strong>Javier Esqueda</strong>'s two-day bench trial was set to begin — Kendall County State's Attorney <strong>Eric Weis</strong> filed a motion to dismiss all four counts of official misconduct against him. Kendall County Circuit Judge <strong>Jody Gleason</strong> granted the motion. Esqueda, a 29-year veteran of the Joliet Police Department who retired in July 2022 facing the prospect of <strong>20 years in prison, $400,000 in fines, and the loss of his pension</strong>, walked out of the courthouse with no conviction and no remaining state criminal exposure.</p>

<p>Weis's filing offered an unusually candid reason: while the case had been pending, "the People have received additional materials which were not originally tendered to the State's Attorney's Office, which have caused our office to reevaluate the evidence in this case." It did not detail what had been withheld, or by whom.</p>

<h2>What he leaked</h2>

<p>Esqueda's underlying conduct — the act for which the State of Illinois charged him with four felonies — was giving a Joliet television reporter dashcam footage from <strong>January 29, 2020</strong>. The video showed officers in custody of <strong>Eric Lurry, 37</strong>, a Black man who had been arrested in a drug-buy bust. Lurry was handcuffed in the back of a squad car when an officer slapped him across the face, called him a "bitch," held his head and restricted his airway, and pushed a police baton into his mouth. Lurry was then taken to St. Joseph Medical Center, where he died. The Will County coroner ruled the death an overdose.</p>

<p>The officers involved received six-day suspensions. The Joliet Police Department did not initially release the dashcam footage to the public. In July 2020, Esqueda gave it to WBBM-TV reporter Dana Kozlov, who aired it.</p>

<h2>What the city did to him</h2>

<p>Within three months of the broadcast, the Kendall County State's Attorney's office had indicted him for the leak. The Joliet Police Officer's Association — the local union — held a vote on whether to expel him. Members voted <strong>35 to 1</strong> to throw him out.</p>

<p>The case dragged for over four years. Esqueda's defense team, led by attorney <strong>Jeff Tomczak</strong>, mounted a public-interest defense: the video disclosed misconduct, the public had a right to see it, and the felony "official misconduct" theory the state was using turned every act of in-house whistleblowing into a crime carrying decades of prison. The defense did not get a chance to present that argument to a fact-finder. The state withdrew the case before opening statements.</p>

<h2>What broke loose, one day earlier</h2>

<p>The dismissal did not happen in a vacuum. One day before Weis filed his motion, the office of <strong>Illinois Attorney General Kwame Raoul</strong> released an investigatory report on the Joliet Police Department concluding that the department had engaged in a <strong>pattern of unreasonable force and a systemic failure to hold officers accountable for misconduct</strong>. The report cited the Lurry death prominently. Whether or not Weis's "additional materials" came from that investigation, the political and evidentiary ground under the prosecution had shifted overnight.</p>

<h2>What it cost him to win</h2>

<p>Esqueda is now suing the City of Joliet, the police department, and the union in federal court over the retaliation. His federal civil-rights complaint alleges that the criminal charges, the union expulsion, and the four-year campaign to break him were carried out in retaliation for protected First Amendment speech — disclosing police misconduct of public concern to a journalist. The federal action remains pending.</p>

<p>In the meantime, what Esqueda has — vindication on the criminal docket — came at the cost of his career, his union membership, four years of his life under indictment, and the long shadow of a prosecution that, until the morning of trial, the State of Illinois was prepared to pursue all the way to a jury.</p>

<p>The Lamplighter Project, which advocates for police whistleblowers, named him the inaugural recipient of its national award in 2022. The same group called Monday's dismissal <em>"justice at last."</em> They were not wrong. But it was a long arrival.</p>
BODY;

        $data = [
            'title'        => 'Justice, At Last: All Felony Charges Dropped Against Joliet Police Whistleblower Javier Esqueda',
            'intro'        => "On the morning his two-day trial was set to begin, the Kendall County State's Attorney moved to dismiss all four felony counts against retired Sgt. Javier Esqueda — the 29-year Joliet veteran who had given dashcam footage of Eric Lurry's January 2020 in-custody death to a TV reporter and faced 20 years in prison for the leak.",
            'body'         => $body,
            'category_id'  => $category->id,
            'author_id'    => $author->id,
            'published_at' => Carbon::parse(self::PUB_DATE),
            'citations_json' => [
                ['title' => 'CBS Chicago — Charges against whistleblower ex-Joliet sergeant dropped on day trial was to start', 'url' => 'https://www.cbsnews.com/chicago/news/charges-whistleblower-ex-joliet-illinois-police-sergeant-dropped/'],
                ['title' => 'Shaw Local — Kendall judge dismisses all felony charges against Joliet police whistleblower (Dec 17, 2024)', 'url' => 'https://www.shawlocal.com/the-herald-news/2024/12/17/kendall-judge-dismisses-all-felony-charges-against-joliet-police-whistleblower/'],
                ['title' => 'The Lamplighter Project — Justice at Last: Sgt. Javier Esqueda Cleared of Criminal Charges After Four-Year Battle', 'url' => 'https://www.thelamplighterproject.org/post/justice-at-last-lamplighter-award-recipient-sgt-javier-esqueda-cleared-of-criminal-charges-after'],
                ['title' => 'Joliet Patch — "Batman Don\'t Plead Guilty": Joliet Police Whistleblower Vindicated', 'url' => 'https://patch.com/illinois/joliet/batman-dont-plead-guilty-joliet-police-whistleblower-exonerated'],
                ['title' => 'USA TODAY — Whistleblower featured in "Behind the Blue Wall" series ousted from police union', 'url' => 'https://www.usatoday.com/story/news/investigations/2021/11/12/union-ousts-police-officer-featured-in-usa-today-behind-the-blue-wall-series/6396601001/'],
                ['title' => 'Scheer Post — Facing Felony Trial, Joliet Whistleblower Retires (July 2022)', 'url' => 'https://scheerpost.com/2022/07/26/facing-felony-trial-joliet-police-whistleblower-who-exposed-black-mans-death-retires/'],
                ['title' => 'CBS Chicago — Whistleblower ex-Joliet sergeant files retaliation lawsuit', 'url' => 'https://www.cbsnews.com/chicago/news/whistleblower-joliet-illinois-police-sergeant-javier-esqueda-lawsuit/'],
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
