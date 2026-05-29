<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;

/**
 * Sets the body of the "Introduction" topic — the scene-setting overview
 * shown when the Topics explorer first loads. Overwrites any existing body.
 * Safe to re-run.
 */
final class SetIntroductionContent extends Command {
    protected $signature = 'topics:set-introduction';

    protected $description = 'Set the Introduction topic body for the Topics explorer';

    public function handle(): int {
        $body = <<<'HTML'
<p>This interactive resource maps the people, movements, and organizations at the heart of political imprisonment in the United States. Its goal is to provide an easy-to-use, scene-setting overview for researchers, journalists, advocates, and the families of the imprisoned — connecting individual cases to the broader movements, historical eras, and organizations from which they emerged.</p>

<p>The United States has a long history of imprisoning people for their political beliefs, associations, and activity. From the abolitionists and labor organizers of the nineteenth century to the Black liberation, Indigenous, Puerto Rican independence, anti-war, and environmental movements of the twentieth and twenty-first, successive generations of organizers have been surveilled, prosecuted, and incarcerated in connection with their work for social change.</p>

<p>Many of these cases are now decades old. A number of those imprisoned during the movements of the 1960s, 1970s, and 1980s remain behind bars today — repeatedly denied parole despite advanced age and failing health — while newer prosecutions, often brought under expansive conspiracy, material-support, and "terrorism enhancement" statutes, continue to produce lengthy sentences.</p>

<p>The ever-present backdrop to these cases is a long record of state surveillance and repression — most prominently the FBI's COINTELPRO operations, which sought to disrupt and discredit political movements, and the broader use of the criminal-legal system to weaken organized dissent. The result is a landscape in which the line between crime and political persecution is frequently contested, and in which the question of who should be recognized as a political prisoner remains the subject of debate.</p>

<p>This map charts those cases and the contexts around them — grouped by the movements people belonged to, the eras in which they were active, and the organizations with which they were associated — to help make sense of a history that is otherwise scattered and often deliberately obscured. It draws on publicly available records, court documents, movement histories, and scholarship. It aims to give a big-picture view and should not be considered comprehensive or exhaustive. The resource is periodically updated and expanded as new cases and information come to light.</p>
HTML;

        $intro = Topic::where('slug', 'introduction')->orWhere('title', 'Introduction')->first();

        if ($intro) {
            $intro->body = $body;
            $intro->published = true;
            $intro->save();
            $this->info("Updated the Introduction topic body ({$intro->slug}).");
        } else {
            $intro = Topic::create([
                'title' => 'Introduction',
                'sort_order' => 0,
                'published' => true,
                'body' => $body,
            ]);
            $this->info("Created the Introduction topic ({$intro->slug}).");
        }

        return self::SUCCESS;
    }
}
