<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;

/**
 * Sets the body of the Industrial Workers of the World topic to its overview
 * paragraph plus a chronological timeline of the union's key events, 1905–1919
 * (founding through the Centralia Massacre). Rendered in the /topics detail
 * panel; the timeline markup is styled by .tpx-timeline in the topics view.
 * Idempotent — rewrites the body on each run; no-op if the IWW topic is absent.
 */
final class AddIwwTimeline extends Command
{
    protected $signature = 'topics:add-iww-timeline';

    protected $description = 'Add a chronological timeline of key events to the Industrial Workers of the World topic';

    public function handle(): int
    {
        $iww = Topic::where('slug', 'industrial-workers-of-the-world')->first();
        if (! $iww) {
            $this->warn('The "industrial-workers-of-the-world" topic was not found — skipping (no-op).');

            return self::SUCCESS;
        }

        $events = [
            ['June 1905', 'Chicago, Illinois', 'Founding of the IWW', 'The "One Big Union" was founded by radicals including Big Bill Haywood, Eugene Debs, Lucy Parsons, and Mother Jones. It set out to organize all workers — especially those excluded by the craft unions — into a single industrial union fighting for better wages, hours, and conditions, and ultimately for worker control of industry.'],
            ['1906–1907', 'Goldfield, Nevada', 'Goldfield organizing drive', 'One of the IWW\'s first great western strongholds, especially among miners, it helped establish the Wobblies as a serious force in the West.'],
            ['1909', 'McKees Rocks, Pennsylvania', 'McKees Rocks / Pressed Steel Car Strike', 'One of the IWW\'s first nationally famous strikes. The dramatic walkout at the Pressed Steel Car Company brought the union national attention.'],
            ['1909', 'Missoula, Montana', 'Missoula Free Speech Fight', 'An early IWW free-speech fight against police suppression of street speaking. After roughly forty arrests, the authorities backed down and Wobblies resumed speaking and selling papers.'],
            ['1909–1910', 'Spokane, Washington', 'Spokane Free Speech Fight', 'A classic "fill the jails" campaign against a ban on street speaking. Elizabeth Gurley Flynn, then only 19, emerged as a major figure in the fight.'],
            ['1910–1911', 'Fresno, California', 'Fresno Free Speech Fight', 'Another major free-speech battle, defending soapboxing and street organizing against local ordinances and police repression.'],
            ['Jan.–Mar. 1912', 'Lawrence, Massachusetts', 'Lawrence Textile Strike ("Bread and Roses")', 'The IWW\'s most famous victory. Mostly immigrant textile workers — many of them women and children — struck against wage cuts and won increases. It remains one of the union\'s greatest successes.'],
            ['1912', 'San Diego, California', 'San Diego Free Speech Fight', 'One of the most violent free-speech fights: IWW members and allies were beaten, jailed, deported, and attacked by vigilantes — a precursor to the later wartime anti-IWW vigilantism.'],
            ['Feb.–July 1913', 'Paterson, New Jersey', 'Paterson Silk Strike', 'A huge IWW-led silk workers\' strike. Roughly 25,000 workers walked out for higher wages and the eight-hour day, effectively shutting down the industry.'],
            ['Aug. 3, 1913', 'Wheatland, California', 'Wheatland Hop Riot', 'A farm-labor strike at the Durst Ranch turned deadly, leaving four people dead. Authorities blamed the IWW; it became one of California\'s first major farm-labor confrontations and a major repression case.'],
            ['1914 – Nov. 19, 1915', 'Salt Lake City, Utah', 'Joe Hill case and execution', 'IWW songwriter and organizer Joe Hill was convicted of murder in a bitterly disputed case and executed by firing squad, becoming one of the union\'s great martyrs.'],
            ['Nov. 5, 1916', 'Everett, Washington', 'Everett Massacre', 'Armed deputies fired on Wobblies arriving by boat to support striking shingle workers and the free-speech fight. At least five Wobblies and two deputies were killed.'],
            ['July 12, 1917', 'Bisbee, Arizona', 'Bisbee Deportation', 'Armed vigilantes rounded up some 1,200 striking miners and supporters — many tied to the IWW — and deported them by train into the New Mexico desert.'],
            ['1917', 'Washington, Oregon, Idaho & Montana', 'Pacific Northwest Lumber Strike', 'A major IWW lumber strike that won the eight-hour day in the logging camps — one of the union\'s strongest wartime campaigns.'],
            ['1917–1918', 'Nationwide — Chicago, Wichita & Sacramento', 'Federal raids and mass IWW trials', 'Federal agents raided IWW halls across the country and prosecuted hundreds of members under wartime espionage and sedition laws. The union counted some "2,000 class-war prisoners."'],
            ['Nov. 11, 1919', 'Centralia, Washington', 'Centralia Massacre', 'American Legionnaires marched on the IWW hall and gunfire broke out. Wobbly Wesley Everest was tortured and lynched, and eight other members were sent to prison.'],
        ];

        $intro = '<p>The Industrial Workers of the World (IWW), whose members are known as "Wobblies," is a revolutionary industrial union founded in Chicago in 1905 to organize all workers into "One Big Union." Its militant free-speech fights and strikes drew ferocious repression: songwriter Joe Hill was executed in 1915, organizer Frank Little was lynched in 1917, and during World War I the federal government prosecuted more than a hundred IWW leaders — among them Big Bill Haywood — under the Espionage Act in one of the largest mass political trials in U.S. history. Many served long sentences at Leavenworth, and the union remains a touchstone of American labor radicalism.</p>';

        $items = '';
        foreach ($events as [$date, $loc, $title, $desc]) {
            $items .= '<li>'
                .'<div><span class="tpx-tl-date">'.e($date).'</span> · <span class="tpx-tl-loc">'.e($loc).'</span></div>'
                .'<div class="tpx-tl-desc"><span class="tpx-tl-title">'.e($title).'.</span> '.e($desc).'</div>'
                .'</li>';
        }

        $body = $intro
            .'<h3>Timeline of key events (1905–1919)</h3>'
            .'<ul class="tpx-timeline">'.$items.'</ul>';

        $iww->body = $body;
        $iww->published = true;
        $iww->save();

        $this->info('Set the IWW topic body with a '.count($events).'-event timeline.');

        return self::SUCCESS;
    }
}
