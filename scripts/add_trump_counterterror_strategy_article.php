<?php

declare(strict_types=1);

/**
 * Add an NPPC news article on the May 7 2026 White House
 * counterterrorism strategy that classified Antifa and left-wing
 * networks among three "major" types of terror groups.
 *
 * Sources cross-referenced from the CNN piece and KRDO/ABC17News
 * syndications since CNN's wall blocked direct fetch. Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;

$slug = 'trump-counterterrorism-strategy-classifies-antifa-major-terror-2026';

if (Article::where('slug', $slug)->exists()) {
    echo "Article '{$slug}' already exists. Nothing to do.\n";
    return;
}

$category = Category::firstOrCreate(
    ['slug' => 'news'],
    ['name' => 'News']
);

$author = Author::firstOrCreate(
    ['name' => 'NPPC Editorial'],
);

$intro = "On May 7, 2026, the White House released the first counterterrorism strategy of President Trump's second term, classifying left-wing networks — including Antifa — among three 'major' types of terror groups the United States faces, alongside cartels and Islamist organizations.";

$body = <<<'HTML'
<p style="font-style: italic; opacity: 0.7; margin-bottom: 24px;">Reporting based on CNN's May 7, 2026 story and syndicated coverage at KRDO, ABC17News, and other outlets.</p>

<p>On May 7, 2026, the White House released the first counterterrorism strategy of President Trump's second term. The plan classifies left-wing networks — naming Antifa specifically — among three "major" types of terror groups the federal government has prioritized for counterterrorism resources, alongside transnational cartels and Islamist organizations.</p>

<p>The strategy describes its left-wing targets as "violent secular political groups whose ideology is anti-American, radically pro-transgender, and anarchist," and pledges that the federal government will "use all the tools constitutionally available to us to map them at home, identify their membership, map their ties to international organizations like Antifa." The framing treats Antifa as a coherent international organization with identifiable membership rolls — a characterization repeatedly contradicted by the federal law-enforcement officials charged with investigating it.</p>

<h2>An organization without organization</h2>

<p>The substantive problem with the new designation is that "Antifa" is not an organization in the conventional sense. It is a decentralized political tendency, a posture of resistance to fascist organizing, with no membership rolls, no leadership, no headquarters, no funding stream, and no legal entity that could be sanctioned, designated, or prosecuted as a unit.</p>

<p>This is not a partisan claim — it is the position of the FBI itself when pressed under oath. During a congressional hearing in late 2025, FBI counterterrorism official Michael Glasheen could not answer basic questions about Antifa's group size, location, organizational structure, or other details, while simultaneously declaring Antifa the FBI's "primary concern." The Bureau has labeled as its top counterterrorism priority a target it cannot define.</p>

<p>That gap between political designation and operational reality matters because the federal counterterrorism apparatus is built to produce arrests and prosecutions. When the apparatus is pointed at a category that does not legally exist, it tends to produce arrests and prosecutions of the people who happen to share the category's loose political markers — protesters at counter-demonstrations, organizers of mutual-aid networks, journalists who cover the left, and anyone the government's mapping effort connects to those targets.</p>

<h2>The asymmetry the strategy ignores</h2>

<p>Organized terrorism from left-wing groups in the United States is rare. The federal government's own threat assessments over the last two decades — across both parties — have consistently identified racially or ethnically motivated violent extremism, particularly white-supremacist violence, as the most lethal domestic terrorism threat. The Center for Strategic and International Studies, the Anti-Defamation League, the Government Accountability Office, and the FBI's own Joint Terrorism Task Force statistics have all reached the same conclusion: the lethal violence is overwhelmingly on the right.</p>

<p>The new strategy reverses that priority. It does not refute the data; it simply elevates a different target by political fiat.</p>

<h2>Why this matters for political-prisoner work</h2>

<p>The National Political Prisoner Coalition has tracked U.S. political prosecutions across more than a century — from Eugene Debs to the IWW Chicago trial, from the Smith Act defendants to COINTELPRO's wreckage of the Black Panther Party and AIM, through the Sanctuary movement, the Plowshares actions, the Animal Enterprise Terrorism Act prosecutions of ELF and ALF defendants, the NATO 5, the Cleveland 5, Stop Cop City, and the Operation Backfire wave that produced two-decade sentences for property crimes that injured no one. The pattern is consistent: when a federal counterterrorism apparatus is given an open-ended political target and the discretion to define it, the resulting prosecutions reach far beyond anyone who could plausibly be called a terrorist.</p>

<p>Designating "Antifa and left-wing networks" as one of three major terror priorities is not a prediction about violent acts. It is a procurement document — an instruction to U.S. attorneys' offices, FBI field offices, DHS fusion centers, and grand juries to find prosecutable cases inside that category. They will. The cases will land on people with political views, not necessarily on people who have done politically violent things.</p>

<p>NPPC will continue to document the prosecutions that follow, identify the defendants by name, track sentences and conditions of confinement, and publish what we can. Readers who want to support the legal-defense infrastructure that fights these cases should consider the Civil Liberties Defense Center, the Center for Constitutional Rights, the National Lawyers Guild, and the Tilted Scales Collective.</p>

<p style="opacity: 0.6; font-size: 14px; margin-top: 32px;">Sources: CNN Politics, May 7, 2026; KRDO and ABC17News syndicated coverage of the same date; congressional testimony of FBI counterterrorism official Michael Glasheen, late 2025; CSIS, ADL, and GAO domestic-terrorism threat assessments, 2017-2025.</p>
HTML;

$article = Article::create([
    'title'        => "Trump's New Counterterrorism Strategy Designates Antifa and Left-Wing Networks Among 'Major' Terror Groups",
    'slug'         => $slug,
    'author_id'    => $author->id,
    'category_id'  => $category->id,
    'intro'        => $intro,
    'body'         => $body,
    'published_at' => '2026-05-08 00:00:00',
]);

echo "[create] Article id={$article->id}, slug={$article->slug}\n";
echo "         Author: {$author->name} (id={$author->id})\n";
echo "         Category: {$category->name} (id={$category->id})\n";
echo "Done.\n";
