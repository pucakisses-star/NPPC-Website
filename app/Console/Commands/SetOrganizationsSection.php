<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Replaces the "Repressive Tools" section with an "Organizations" section.
 *
 * DESTRUCTIVE: "Repressive Tools" and all of its sub-topics are permanently
 * deleted (the topics.parent_id foreign key cascades on delete). A new
 * "Organizations" section is created in its place (sort_order 3) with a
 * drafted starter set of groups. Bodies are filled only when empty, so
 * admin-authored content on any matching topic is preserved. Safe to re-run.
 */
final class SetOrganizationsSection extends Command {
    protected $signature = 'topics:set-organizations';

    protected $description = 'Delete the Repressive Tools section and replace it with an Organizations section';

    public function handle(): int {
        // --- Remove "Repressive Tools" (cascades to its sub-topics) ---
        $tools = Topic::where('slug', 'repressive-tools')->orWhere('title', 'Repressive Tools')->first();
        if ($tools) {
            $childCount = Topic::where('parent_id', $tools->id)->count();
            $tools->delete();
            $this->warn("Deleted 'Repressive Tools' and {$childCount} sub-topic(s).");
        } else {
            $this->line("No 'Repressive Tools' section found — nothing to delete.");
        }

        // --- Create the "Organizations" section in its place ---
        $organizations = $this->upsert(
            'Organizations',
            null,
            3,
            '<p>Political prisoners are rarely lone actors — most were part of organized groups working for liberation, sovereignty, or social change. This section groups cases by the organizations people belonged to or were prosecuted in connection with, from the Black Panther Party to the Earth Liberation Front.</p>'
        );

        $orgs = [
            ['Black Panther Party', '<p>Founded in Oakland in 1966, the Black Panther Party organized armed self-defense against police violence alongside free-breakfast and community-health programs. It became the FBI primary COINTELPRO target; many members were imprisoned or killed, and several remain among the longest-held political prisoners in the United States.</p>'],
            ['Black Liberation Army', '<p>The Black Liberation Army emerged from the Black Panther Party in the early 1970s as a clandestine network. Its members were prosecuted in a series of high-profile cases, and a number remain incarcerated decades later.</p>'],
            ['American Indian Movement', '<p>Founded in 1968, the American Indian Movement organized for treaty rights and tribal sovereignty, drawing national attention at Wounded Knee in 1973. Its members faced extensive federal surveillance and prosecution; Leonard Peltier became one of the most internationally recognized political-prisoner cases.</p>'],
            ['FALN', '<p>The Fuerzas Armadas de Liberacion Nacional (FALN) was a clandestine organization fighting for Puerto Rican independence in the 1970s and 1980s. Members were prosecuted under seditious-conspiracy statutes; several had their sentences commuted in 1999.</p>'],
            ['Weather Underground', '<p>The Weather Underground grew out of the student movement against the Vietnam War and carried out symbolic bombings of government property in the early 1970s, issuing warnings to avoid casualties. Several people associated with the broader network were later imprisoned.</p>'],
            ['Earth Liberation Front', '<p>The Earth Liberation Front was a decentralized movement that used property destruction, particularly arson, against targets it viewed as ecologically destructive. Federal prosecutions in the 2000s, part of the so-called Green Scare, treated these acts as terrorism and produced lengthy sentences.</p>'],
            ['Animal Liberation Front', '<p>The Animal Liberation Front is a decentralized movement carrying out raids to release animals from laboratories and farms and to damage related property. Its activists have been prosecuted under the Animal Enterprise Terrorism Act and related statutes.</p>'],
            ['MOVE', '<p>MOVE is a Philadelphia-based Black liberation and naturalist organization. After a 1978 confrontation with police, nine members were convicted in the death of an officer; in 1985 police bombed the group home, killing eleven people, including five children. The imprisoned members became known as the MOVE 9.</p>'],
            ['Republic of New Afrika', '<p>The Provisional Government of the Republic of New Afrika advocated an independent Black nation in the southern United States. Its members were targets of state surveillance and prosecution, including the 1971 raid on its Jackson, Mississippi headquarters.</p>'],
        ];

        foreach ($orgs as $i => [$title, $body]) {
            $this->upsert($title, $organizations->id, $i, $body);
        }

        $this->info('Done. "Organizations" now occupies the fourth section slot.');

        return self::SUCCESS;
    }

    /**
     * Create the topic, or update an existing one matched by slug/title.
     * Fills the body only when blank so admin-authored content is preserved.
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
