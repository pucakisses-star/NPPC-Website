<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;

/**
 * Adds four more organizations to the "Organizations" section of /topics:
 * the Brown Berets, the Socialist Workers Party, the African People's Socialist
 * Party / Uhuru Movement, and the Nation of Islam — each a group whose members
 * became U.S. political prisoners. New topics are appended after the existing
 * children by sort_order. Create-or-update by slug, so it is idempotent and
 * refreshes the body on re-run. No-op if the "organizations" parent is absent.
 */
final class AddMoreOrganizationTopics extends Command
{
    protected $signature = 'topics:add-more-organizations';

    protected $description = 'Add Brown Berets, Socialist Workers Party, African People\'s Socialist Party, and Nation of Islam to /topics';

    public function handle(): int
    {
        $parent = Topic::where('slug', 'organizations')->first();
        if (! $parent) {
            $this->warn('The "organizations" parent topic was not found — skipping (no-op).');

            return self::SUCCESS;
        }

        $order = (int) Topic::where('parent_id', $parent->id)->max('sort_order');
        $added = 0;
        $updated = 0;

        foreach ($this->records() as $r) {
            $existing = Topic::where('slug', $r['slug'])->first();

            if ($existing) {
                $existing->update([
                    'title' => $r['title'],
                    'parent_id' => $parent->id,
                    'body' => $r['body'],
                    'published' => true,
                ]);
                $updated++;
                $this->line("  updated: {$r['title']}");

                continue;
            }

            $order++;
            Topic::create([
                'title' => $r['title'],
                'slug' => $r['slug'],
                'parent_id' => $parent->id,
                'body' => $r['body'],
                'published' => true,
                'sort_order' => $order,
            ]);
            $added++;
            $this->info("  added: {$r['title']}");
        }

        $this->info("\nDone. Organizations — added: {$added}, updated: {$updated}.");

        return self::SUCCESS;
    }

    private function records(): array
    {
        return [
            [
                'title' => 'Brown Berets',
                'slug' => 'brown-berets',
                'body' => '<p>The Brown Berets were a Chicano militant organization founded in Los Angeles in 1967, modeled in part on the Black Panther Party. They organized against police brutality, for educational reform, and against the Vietnam War, playing a central role in the 1968 East L.A. school walkouts and the Chicano Moratorium movement. After the August 29, 1970 Chicano Moratorium — the massive antiwar march during which the journalist Rubén Salazar was killed by a sheriff\'s deputy — many members were arrested and prosecuted, and the group was a sustained target of police infiltration and surveillance.</p>',
            ],
            [
                'title' => 'Socialist Workers Party',
                'slug' => 'socialist-workers-party',
                'body' => '<p>The Socialist Workers Party, founded in 1938, is a Trotskyist organization long active in labor, antiwar, and civil-rights struggles. In 1941 its leaders and allied Minneapolis Teamsters from Local 544 became the first people prosecuted under the new Smith Act, convicted of "conspiring to advocate" the overthrow of the government; eighteen were imprisoned in a case widely condemned as an attack on political speech. The party was also a decades-long target of FBI surveillance and disruption, which it later exposed and curtailed in a landmark lawsuit against the federal government.</p>',
            ],
            [
                'title' => 'African People\'s Socialist Party',
                'slug' => 'african-peoples-socialist-party',
                'body' => '<p>The African People\'s Socialist Party, founded in 1972 and the political core of the Uhuru Movement, is a Black revolutionary socialist organization advancing Pan-Africanism, self-determination, and reparations. In 2022–2023, in a case that drew wide attention, the FBI raided the party\'s offices and federal prosecutors indicted its chairman, Omali Yeshitela, and others — the "Uhuru 3" — under the Foreign Agents Registration Act, accusing them of acting as unregistered agents of Russia. Supporters denounced the prosecution as an attack on Black political speech; in 2024 a jury acquitted the defendants of the central conspiracy charge.</p>',
            ],
            [
                'title' => 'Nation of Islam',
                'slug' => 'nation-of-islam',
                'body' => '<p>The Nation of Islam, founded in 1930, is a Black nationalist religious movement that became a major force in twentieth-century Black America, above all through its most famous minister, Malcolm X. Its members have repeatedly faced prosecution and imprisonment: founder Elijah Muhammad and scores of followers were jailed during World War II for sedition and draft refusal; Malcolm X was drawn to the movement during his own imprisonment; and Muhammad Ali was convicted of draft evasion in 1967 after refusing military induction on religious grounds — a conviction the Supreme Court overturned in 1971.</p>',
            ],
        ];
    }
}
