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
 * Publish a commemoration article for the May 13, 2026 anniversary
 * of the 1985 Philadelphia police bombing of the MOVE row house at
 * 6221 Osage Avenue. Surfaced from a May 13, 2026 share by Kalonji
 * Changa (@Kalonjichanga) on X (status 2054591020176658925).
 *
 * Idempotent — re-runs update by slug.
 */
final class AddMoveBombingAnniversaryArticle extends Command {
    protected $signature = 'articles:add-move-bombing-anniversary';
    protected $description = 'Publish MOVE bombing anniversary commemoration article (May 13, 1985)';

    private const SLUG      = 'move-bombing-anniversary-philadelphia-1985-osage-avenue';
    private const IMAGE_URL = 'https://pbs.twimg.com/media/HINfpPAW4AA9G3w.jpg';
    private const PUB_DATE  = '2026-05-13 15:54:06';

    public function handle(): int {
        $category = Category::firstOrCreate(['title' => 'News'], ['slug' => 'news']);
        $author   = Author::firstOrCreate(['name' => 'NPPC Editorial']);

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
<p><em>Forty-one years ago today, on May 13, 1985, the Philadelphia Police Department dropped a satchel of military-grade C-4 explosive on the roof of a row house at 6221 Osage Avenue. Eleven people inside — six adults and five children — were killed. Sixty-one neighboring homes burned to the ground. No officer or city official was ever criminally charged.</em></p>

<p>On <strong>Wednesday, May 13, 2026</strong>, MOVE supporters, neighbors of the 6200 block of Osage Avenue, and Black-liberation organizers across the United States again mark the anniversary of the only documented instance in modern American history of a U.S. police department dropping a bomb from a helicopter onto a residential home in its own city. The day is observed every year by movement formations across Philadelphia and beyond — and is increasingly observed in the cities to which MOVE survivors and prisoners' relatives scattered after 1985.</p>

<h2>What happened</h2>

<p>By the morning of May 13, 1985, hundreds of Philadelphia police officers had cordoned off the 6200 block of Osage Avenue, in the West Philadelphia neighborhood of Cobbs Creek. Inside the row house at 6221 Osage were thirteen members of the MOVE Organization — a Black naturalist, back-to-the-land, anti-establishment commune founded in the early 1970s by John Africa (born Vincent Leaphart). MOVE members took the surname "Africa" and lived by an explicit critique of industrial society, animal cruelty, and police violence. The city's stated pretext for the cordon was outstanding warrants and complaints from neighbors about confrontations and amplified statements from the MOVE house.</p>

<p>What followed was not an arrest operation. Over the course of the day, Philadelphia police fired roughly <strong>10,000 rounds</strong> of ammunition into the house in 90 minutes. They poured tear gas. They pumped <strong>roughly a million gallons of water</strong> from deluge guns. Then, around 5:27 p.m., a Pennsylvania State Police helicopter dropped onto the rooftop a satchel containing two pounds of military-grade <strong>FBI-supplied Tovex/C-4 explosive</strong> — wrapped, in radio transmissions later recovered, as a "device" the police commissioner had approved that morning. The bunker on the roof, which had been the stated target, was effectively undamaged. The roof itself caught fire. Fire Commissioner William C. Richmond, on the orders of Police Commissioner Gregore J. Sambor and with the approval of Mayor W. Wilson Goode Sr., let the fire burn.</p>

<p>By the time it was extinguished, eleven people inside 6221 Osage Avenue were dead:</p>

<ul>
  <li><strong>John Africa</strong> (Vincent Leaphart) — MOVE founder</li>
  <li><strong>Rhonda Africa</strong></li>
  <li><strong>Theresa Africa</strong></li>
  <li><strong>Frank Africa</strong></li>
  <li><strong>Conrad Africa</strong></li>
  <li><strong>Raymond Africa</strong></li>
  <li><strong>Tomaso Africa</strong> — age 9</li>
  <li><strong>Little Phil Africa</strong> — age 11</li>
  <li><strong>Delisha Africa</strong> — age 12</li>
  <li><strong>Netta Africa</strong> — age 12</li>
  <li><strong>Tree Africa</strong> — age 14</li>
</ul>

<p>Two members survived the bombing: <strong>Ramona Africa</strong>, the only adult survivor, who escaped through the rear of the burning house carrying the second survivor, <strong>Birdie Africa</strong> (later Michael Ward), a 13-year-old boy. Ramona Africa, the surviving adult, was the only person charged in the aftermath. She was convicted of riot and conspiracy and served <strong>seven years</strong> in state prison.</p>

<p>The fire was permitted to spread. By morning, <strong>61 row houses</strong> on the 6200 block of Osage Avenue and the parallel 6200 block of Pine Street were destroyed. <strong>250 people</strong> were left homeless. Almost all of them — like the MOVE family itself — were Black.</p>

<h2>The official record</h2>

<p>A Philadelphia Special Investigation Commission (the "MOVE Commission"), convened by Mayor Goode himself in 1986, concluded in its formal report that the bombing was "<strong>unconscionable</strong>" and that allowing the fire to burn was "unconscionable." The commission found that the city had used "excessive force" and that "dropping a bomb on an occupied row house was unconscionable and should have been rejected out of hand." It made no criminal referrals.</p>

<p>A federal grand jury investigation in 1988 declined to indict any official. A 1996 federal civil-rights jury did find the city, the police commissioner, and the fire commissioner liable in a wrongful-death suit brought by Ramona Africa and the relatives of two of the children killed; the jury ordered the city to pay <strong>$1.5 million</strong>. No criminal charges were ever filed against any officer, official, or pilot.</p>

<p>In <strong>November 2020</strong>, the Philadelphia City Council formally apologized for the bombing — thirty-five years late — and designated May 13 as an annual day of "observation, reflection, and recommitment." In <strong>April 2021</strong>, it emerged that the University of Pennsylvania's Museum and Princeton University had been holding the partial remains of two of the children killed in the bombing as anthropological specimens, used for decades in osteology coursework. The institutions issued belated apologies; the remains were returned. The city's medical examiner was found in the same period to have <strong>cremated additional MOVE remains</strong> without notifying the families — an act for which Mayor Jim Kenney accepted personal responsibility and the health commissioner resigned.</p>

<h2>The MOVE 9</h2>

<p>The May 13, 1985 bombing did not occur in isolation. It came seven years after the <strong>August 8, 1978</strong> police siege of MOVE's earlier home in Powelton Village, in which Philadelphia police officer James Ramp was killed by a single bullet during a confrontation whose trajectory has been disputed by every independent ballistics analyst who has examined the evidence. Nine MOVE members — <strong>Chuck, Debbie, Delbert, Edward, Janet, Janine, Merle, Michael, and Phil Africa</strong> — were each sentenced to 30 to 100 years in Pennsylvania state prison for third-degree murder, despite the prosecution conceding it could not identify who fired the round. They are remembered as the <strong>MOVE 9</strong>.</p>

<p>Two of the MOVE 9 died in prison without ever being released: <strong>Merle Africa</strong> in 1998 and <strong>Phil Africa</strong> in 2015. The remaining seven were granted parole over the course of the 2018–2020 period after roughly four decades each in prison; <strong>Delbert Africa</strong>, paroled in January 2020, died less than six months after his release. The MOVE 9 case — its length, its frame-up character, and the deaths in custody — remains a touchstone for the U.S. political-prisoner support tradition that this archive documents.</p>

<h2>Why we observe May 13</h2>

<p>What was done at 6221 Osage Avenue was not done in a vacuum, and it was not done in the past. The federal government supplied the explosive. The city dropped it. The fire department let the block burn. The justice system declined to prosecute anyone. The universities took the bones. The political infrastructure that built and protected every one of those decisions is the same political infrastructure that built and protected the prosecution of the MOVE 9, the imprisonment of Mumia Abu-Jamal (whose support work was deeply intertwined with MOVE), the framing of Black-liberation prisoners across the BLA and Panther generations, and the post-September-11 use of "terrorism" charging against Black, Indigenous, and Palestinian organizing through to 2026.</p>

<p>NPPC marks May 13 every year. We mark it because the eleven people killed at 6221 Osage Avenue were killed by a U.S. police force using a military explosive on an occupied home in its own city, and because no one was ever criminally punished for it. We mark it because the political-prisoner case that came out of MOVE — the MOVE 9 — was held in U.S. prisons for four decades and two of its members died there. We mark it because the radical organizing tradition that John Africa founded refuses to be forgotten as long as someone is willing to say the names.</p>

<p>Today, we say them.</p>
BODY;

        $data = [
            'title'        => "Forty-One Years After Philadelphia Police Bombed Their Home, We Say the Names: MOVE, May 13, 1985",
            'intro'        => "Forty-one years ago today, on May 13, 1985, the Philadelphia Police Department dropped a satchel of military-grade C-4 explosive on the roof of a row house at 6221 Osage Avenue. Eleven people inside — six adults and five children — were killed. Sixty-one neighboring homes burned to the ground. No officer or city official was ever criminally charged.",
            'body'         => $body,
            'category_id'  => $category->id,
            'author_id'    => $author->id,
            'image'        => $imagePath,
            'published_at' => Carbon::parse(self::PUB_DATE),
            'citations_json' => [
                ['title' => '@Kalonjichanga on X (origin share, May 13 2026)', 'url' => 'https://x.com/Kalonjichanga/status/2054591020176658925'],
                ['title' => 'Philadelphia Special Investigation Commission (MOVE Commission) Report — 1986', 'url' => 'https://www.upenn.edu/about/move-commission'],
                ['title' => 'Africa v. City of Philadelphia — 1996 federal civil-rights verdict', 'url' => 'https://law.justia.com/cases/federal/district-courts/FSupp/938/1278/1493068/'],
                ['title' => 'Philadelphia City Council Resolution — Formal apology, Nov 12 2020', 'url' => 'https://phlcouncil.com/move-apology/'],
                ['title' => 'NYT — Penn Museum and Princeton held remains of MOVE bombing children, Apr 2021', 'url' => 'https://www.nytimes.com/2021/04/28/us/move-bombing-philadelphia-children-remains.html'],
                ['title' => 'On a MOVE: The Story of Mumia Abu-Jamal and the MOVE Organization (Terry Bisson)', 'url' => 'https://www.akpress.org/on-a-move.html'],
                ['title' => 'Let It Burn! The Philadelphia Tragedy (Michael Boyette)', 'url' => 'https://www.goodreads.com/book/show/2010657.Let_It_Burn'],
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
