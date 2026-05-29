<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Restructures the Topics explorer into a faceted, drill-down layout
 * modeled on ECFR's "Mapping ..." explorers:
 *
 *   Introduction        (overview essay — the default landing view)
 *   Movements           -> Black Lives Matter, Environmental Justice, ...
 *   Eras                -> Civil Rights & Black Power, The Green Scare, ...
 *   Repressive Tools    -> COINTELPRO, Grand Juries, ...
 *
 * Existing top-level cause topics (Black Lives Matter, Environmental
 * Justice, Anti-War Activism) are reparented under "Movements". The
 * leftover "Categories" bucket is unpublished (not deleted). Bodies are
 * only filled when empty, so admin-authored content is never clobbered.
 * Safe to re-run.
 */
final class ReorganizeTopics extends Command {
    protected $signature = 'topics:reorganize';

    protected $description = 'Restructure Topics into Introduction / Movements / Eras / Repressive Tools with starter content';

    public function handle(): int {
        // --- Section roots (order drives the left nav; Introduction is first) ---
        $sections = [
            ['Introduction', 0, '<p>The National Political Prisoner Coalition documents people imprisoned in the United States for their political beliefs, associations, or activism. This explorer organizes those cases by the <strong>movements</strong> people acted within, the <strong>eras</strong> in which they were prosecuted, and the <strong>repressive tools</strong> the state has used against dissent.</p><p>Use the menu on the left to move between sections. Selecting a section reveals its sub-topics; choosing a sub-topic opens an overview alongside the related cases from our database.</p>'],
            ['Movements', 1, '<p>Political imprisonment in the United States has rarely targeted ideas in the abstract — it has targeted organized movements for social change. This section groups cases by the struggles people were part of, from the fight against racist policing to the defense of land and water.</p>'],
            ['Eras', 2, '<p>The targets and tactics of political repression shift over time. This section traces political imprisonment across distinct historical periods, from the Civil Rights and Black Power era to the post-9/11 War on Terror.</p>'],
            ['Repressive Tools', 3, '<p>The state has relied on a recurring toolkit to monitor, disrupt, and imprison dissidents. This section explains the mechanisms — from secret counterintelligence programs to charging strategies and conditions of confinement — that appear again and again across these cases.</p>'],
        ];

        $roots = [];
        foreach ($sections as [$title, $sort, $body]) {
            $roots[$title] = $this->upsert($title, null, $sort, $body);
        }

        // --- Sub-topics, grouped under each section ---
        $leaves = [
            'Movements' => [
                ['Black Lives Matter', '<p>The movement against police violence and for Black lives, which gained national prominence in the 2010s, has produced many arrests and prosecutions of organizers, livestreamers, and protesters. Charges have ranged from civil-disobedience citations to serious felony counts brought under rarely-used statutes.</p>'],
                ['Environmental Justice', '<p>Activists defending forests, waterways, and frontline communities have faced aggressive prosecution, including domestic-terrorism enhancements and conspiracy charges brought against direct-action and pipeline-resistance campaigns.</p>'],
                ['Anti-War Activism', '<p>From draft resistance to opposition to the post-9/11 wars, anti-war organizers have long been surveilled and prosecuted — among them people who protested at military installations, resisted recruitment, or exposed wartime abuses.</p>'],
                ['Black Liberation', '<p>Members of Black liberation organizations of the 1960s and 1970s, among them the Black Panther Party and the Black Liberation Army, were central targets of state counterintelligence. Some remain incarcerated decades later and are among the longest-held political prisoners in the country.</p>'],
                ['Indigenous Sovereignty', '<p>Indigenous activists asserting treaty rights and tribal sovereignty, including participants in the American Indian Movement, have faced prosecution and lengthy imprisonment. Their cases raise enduring questions about jurisdiction, surveillance, and the right to defend Native land.</p>'],
                ['Puerto Rican Independence', '<p>Advocates of Puerto Rican independence have been prosecuted under conspiracy and seditious-conspiracy statutes across the twentieth century. Several were granted clemency after years in prison, while debate over the island political status continues.</p>'],
            ],
            'Eras' => [
                ['Civil Rights & Black Power', '<p>The civil-rights and Black Power movements of the 1950s through the 1970s coincided with an unprecedented expansion of domestic surveillance. Many activists prosecuted in this period were later shown to have been targets of the FBI counterintelligence program known as COINTELPRO.</p>'],
                ['The Green Scare', '<p>Green Scare refers to the federal crackdown on radical environmental and animal-rights activists in the late 1990s and 2000s, in which property-destruction offenses were prosecuted as terrorism and often carried sharply enhanced sentences.</p>'],
                ['The War on Terror', '<p>After September 11, 2001, sweeping new surveillance and material-support statutes reshaped political prosecutions. Muslim communities in particular faced preemptive prosecutions, informant-driven sting operations, and confinement in restrictive units.</p>'],
            ],
            'Repressive Tools' => [
                ['COINTELPRO', '<p>COINTELPRO, the FBI Counter Intelligence Program exposed in 1971, was a covert effort to surveil, infiltrate, and disrupt domestic political organizations — especially Black, Indigenous, Puerto Rican, and New Left movements. Its tactics included forged documents, paid informants, and attempts to manufacture criminal cases against activists.</p>'],
                ['Grand Juries', '<p>Federal grand juries have been used not only to indict but to investigate movements. Activists subpoenaed to testify about their associates can be jailed for civil contempt if they refuse to cooperate — a practice known as grand-jury resistance.</p>'],
                ['Conspiracy & RICO Charges', '<p>Broad conspiracy statutes, including the Racketeer Influenced and Corrupt Organizations Act, let prosecutors tie individuals to the acts of others and secure long sentences without proving that the defendant personally committed an underlying crime.</p>'],
                ['Solitary Confinement & CMUs', '<p>Politically active prisoners have frequently been held in prolonged solitary confinement or in Communication Management Units — restrictive federal units that sharply limit contact with the outside world and have disproportionately held Muslim and politically active prisoners.</p>'],
                ['Surveillance & Informants', '<p>Infiltration by informants and undercover agents has shaped political prosecutions for decades, raising persistent questions of entrapment — particularly in post-9/11 sting operations where an informant supplied the means and the plan for an alleged plot.</p>'],
            ],
        ];

        foreach ($leaves as $sectionTitle => $items) {
            $parent = $roots[$sectionTitle];
            foreach ($items as $i => [$title, $body]) {
                $this->upsert($title, $parent->id, $i, $body);
            }
        }

        // --- Retire the leftover meta bucket (reversible: just unpublished) ---
        $categories = Topic::where('slug', 'categories')->orWhere('title', 'Categories')->first();
        if ($categories) {
            $categories->published = false;
            $categories->save();
            $this->warn('Unpublished the "Categories" bucket (data kept).');
        }

        $this->info('Topics reorganized. "Introduction" is now the default /topics landing.');

        return self::SUCCESS;
    }

    /**
     * Create the topic, or update an existing one matched by slug/title.
     * Sets parent, order and publish state; fills the body only when blank
     * so admin-authored content is preserved.
     */
    private function upsert(string $title, ?string $parentId, int $sort, string $body): Topic {
        $slug = Str::slug($title);
        $topic = Topic::where('slug', $slug)->first() ?? Topic::where('title', $title)->first();

        if ($topic) {
            $topic->parent_id = $parentId;
            $topic->sort_order = $sort;
            $topic->published = true;
            if (blank($topic->body)) {
                $topic->body = $body;
            }
            $topic->save();
            $this->line("  updated: {$title}");
        } else {
            $topic = Topic::create([
                'title' => $title,
                'parent_id' => $parentId,
                'sort_order' => $sort,
                'published' => true,
                'body' => $body,
            ]);
            $this->line("  created: {$title}");
        }

        return $topic;
    }
}
