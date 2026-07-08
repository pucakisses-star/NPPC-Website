<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;

/**
 * Adds an "Everett Massacre" topic page nested under the Industrial Workers of
 * the World topic in the /topics explorer. This is the first depth-2 (grand-
 * child) topic: its parent, IWW, is itself a sub-topic of the "Organizations"
 * section. The topics view surfaces a topic's own children in the detail panel
 * and keeps a sub-topic's related-case list even when it has children, so the
 * page renders and is reachable from the IWW page. Idempotent: updates the
 * existing topic if it is already present, and is a no-op if the IWW parent is
 * missing.
 */
final class AddEverettMassacreTopic extends Command
{
    protected $signature = 'topics:add-everett-massacre';

    protected $description = 'Add the Everett Massacre topic page under the Industrial Workers of the World topic';

    public function handle(): int
    {
        $iww = Topic::where('slug', 'industrial-workers-of-the-world')->first();
        if (! $iww) {
            $this->warn('The "industrial-workers-of-the-world" topic was not found — skipping (no-op).');

            return self::SUCCESS;
        }

        $body = <<<'HTML'
<p>The Everett Massacre — remembered by the Industrial Workers of the World as "Bloody Sunday" — was the deadliest labor confrontation in Pacific Northwest history. On November 5, 1916, some 250 Wobblies boarded the passenger steamer <em>Verona</em> in Seattle and sailed north to Everett, Washington, to assert their right to speak on the city's streets in support of striking shingle weavers. Waiting for them at the city dock were Snohomish County sheriff Donald McRae and roughly 200 armed citizen deputies. When the boat drew in, gunfire erupted; in the space of about ten minutes at least five Wobblies and two deputies were killed and more than thirty people were wounded.</p>

<p>The clash grew out of a long, bitter free-speech fight. Everett's shingle weavers had been on strike since the spring of 1916, and the IWW had come to the city to organize and to defend the right to soapbox on street corners — a right local authorities were determined to deny. Wobblies who traveled to Everett were repeatedly arrested, jailed, and run out of town. On October 30, 1916, a group of forty-one IWW members was seized, taken to Beverly Park on the edge of the city, and forced to run a gauntlet where deputies beat them with clubs and gun butts. The Sunday sailing of the <em>Verona</em> was the union's answer to that violence.</p>

<p>As the <em>Verona</em> came alongside the Everett dock, Sheriff McRae called out that the Wobblies could not land. A shot rang out — each side blamed the other — and the deputies opened fire on the crowded steamer. Panicked passengers rushed to the far rail, listing the boat so severely that men were thrown into the frigid water; an unknown number drowned and were never recovered. Five Wobblies were confirmed dead: Felix Baran, Hugo Gerlot, Gustav Johnson, John Looney, and Abraham Rabinowitz. Deputies Jefferson Beard and Charles Curtis were also killed, most likely by the crossfire of their own side. Dozens more, on the boat and on the dock, were wounded, among them men who would later appear in this database.</p>

<p>When the <em>Verona</em> limped back to Seattle it was met by police, and 74 IWW members were arrested and charged with the first-degree murder of deputy Jefferson Beard. Thomas H. Tracy was selected to be tried first, in a closely watched trial in Seattle. On May 5, 1917, the jury acquitted him, and the charges against the remaining Everett defendants were dropped. The men who had been held for months in the Snohomish County jail were freed. The IWW claimed the outcome as a vindication of the right to free speech, but the cost had been enormous, and the massacre became one of the defining martyrdom stories of the American labor movement — commemorated in Wobbly songs, pamphlets, and memory ever since.</p>
HTML;

        $existing = Topic::where('slug', 'everett-massacre')->first();

        if ($existing) {
            $existing->update([
                'title' => 'Everett Massacre',
                'parent_id' => $iww->id,
                'body' => $body,
                'published' => true,
            ]);
            $this->info('Updated the Everett Massacre topic under Industrial Workers of the World.');

            return self::SUCCESS;
        }

        $order = (int) Topic::where('parent_id', $iww->id)->max('sort_order') + 1;

        Topic::create([
            'title' => 'Everett Massacre',
            'slug' => 'everett-massacre',
            'parent_id' => $iww->id,
            'body' => $body,
            'published' => true,
            'sort_order' => $order,
        ]);

        $this->info('Added the Everett Massacre topic under Industrial Workers of the World.');

        return self::SUCCESS;
    }
}
