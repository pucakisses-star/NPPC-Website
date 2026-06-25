<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Publish the essay "We're All Going to be Called Terrorists Eventually"
 * (Dec 17, 2025) with the rabbit-rescue cover image fetched from Substack.
 * No byline was supplied, so author_id is left null. Idempotent (updateOrCreate
 * by slug). A second inline image referenced in the piece (a Philadelphia
 * pro-Palestine protest) was not provided, so only the cover is attached.
 */
final class AddTerrorismLabelArticle extends Command
{
    protected $signature = 'articles:add-terrorism-label';

    protected $description = 'Publish the "We\'re All Going to be Called Terrorists Eventually" essay';

    private const SLUG = 'were-all-going-to-be-called-terrorists-eventually';

    private const IMAGE_URL = 'https://substackcdn.com/image/fetch/$s_!aA4b!,f_auto,q_auto:good,fl_progressive:steep/https%3A%2F%2Fsubstack-post-media.s3.amazonaws.com%2Fpublic%2Fimages%2F93f042f3-7c78-473a-b4ac-14a3963f0ea8_1200x750.jpeg';

    public function handle(): int
    {
        $category = Category::firstOrCreate(['title' => 'News'], ['slug' => 'news']);

        // Fetch the cover image to the public disk; fall back to the remote URL.
        $image = 'articles/'.self::SLUG.'.jpg';
        try {
            $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; NPPC-Archive/1.0)'])
                ->timeout(60)->get(self::IMAGE_URL);
            if ($resp->successful() && strlen($resp->body()) > 5000) {
                Storage::disk('public')->put($image, $resp->body());
                $this->info('Saved cover image to '.$image);
            } else {
                $image = self::IMAGE_URL;
                $this->warn('Image download failed — using remote URL.');
            }
        } catch (\Throwable $e) {
            $image = self::IMAGE_URL;
            $this->warn('Image fetch error: '.$e->getMessage());
        }

        $body = <<<'BODY'
<p>The first time I began seriously questioning the usage of the term terrorism, it was amid a conversation being held between a small group of students in a “Terrorism and WMD Threat Assessment” and our professor. The class itself was fascinating, and one that was well out of my depth, as I had unknowingly signed up for a class comprised of graduate students as I pushed through my third year of undergraduate studies.</p>

<p>Our professor, an older, soft-spoken Jamaican man who had a background in security related to nuclear facilities, was parsing through the various definitions of “terrorism” held by various U.S. government entities. Unsurprisingly, these definitions varied amongst one another, oftentimes being tailored to the agencies themselves based on their relevant purviews. As he jumped from one definition to the next, he took note of this, stating that it was difficult at times to pin down a definitive, all-encompassing standard for what constituted an act of terror.</p>

<p>As he spoke on these intricacies and shortcomings of the label, he then shifted focus to something I had not come across in my studies thus far: the efforts by the U.S. to criminalize environmental activism through the labeling of the activities of groups engaged in direct action as terrorism. “Eco-Terrorism,” he stated, became a popular label for actions that to that point had been a nuisance to industries engaged in harmful environmental practices. As stated in FBI archival material:</p>

<blockquote><p>Since 1977, when disaffected members of the ecological preservation group Greenpeace formed the Sea Shepherd Conservation Society and attacked commercial fishing operations by cutting drift nets, acts of “eco-terrorism” have occurred around the globe. The FBI defines eco-terrorism as the use or threatened use of violence of a criminal nature against innocent victims or property by an environmentally-oriented, subnational group for environmental-political reasons, or aimed at an audience beyond the target, often of a symbolic nature.</p></blockquote>

<p>The FBI focus on “eco-terrorism” extended well beyond just Sea Shepherd and their actions against the fishing industry. Within these same documents, the Animal Liberation Front (ALF), Earth First (EF), the Coalition to Save the Preserves (CSP), and the Earth Liberation Front (ELF) are also mentioned. Their “acts of terror” were primarily related to monkeywrenching, or acts of sabotage that targeted industrial equipment of other infrastructure of what these groups deemed environmentally harmful companies and operations. These acts oftentimes took the form of “spiking,” in which organizers would hammer a metal rod, nail, or other materials into tree trunks to disrupt logging operations, the cutting of fishing nets, arson, the destruction of logging and construction equipment, and other acts of this nature.</p>

<p>As we dug into these acts, I couldn’t help but think that none of these actions fit squarely in my contemporary understanding of terrorism. The word invoked images of bombs detonating in busy streets, planes flying into buildings, mass shootings, and other similarly violent acts. An environmental collective sabotaging logging equipment, on the other hand, seemed like a fairly understandable, even defensible, instance of direct action. After all, these protestors had oftentimes tried other methods before these actions, and had seen corporate interests triumph over the health of the forests and waterways they cared so much about. What was the FBI doing focusing on a damaged fishing net?</p>

<p>The answer became somewhat clear as I continued reading through the agency’s archival materials, which went to great lengths to emphasize the economic damages of these actions. Our professor made sure to note that dynamic as well, noting that the economic impact drove focus onto these groups, who happened to stand in the way of profits of the logging and fisheries industries, among others. The label, it seemed, was borne out of an effort to protect corporate interests against those who might stand against their destructive efforts.</p>

<p>It was a lesson that challenged me to reassess my understanding of not just terrorism, but why the label existed in the first place. It did not take long once the seeds were planted to begin reevaluating U.S. foreign policy, particularly how we ascribed the label of “terrorist” to some groups but not others, as well as calling the acts of some nations “acts of terror” as other nations committing similar acts were labelled as “having acted in self-defense” (conveniently the latter were nearly universally our allies, their actions advancing U.S. interests).</p>

<p>By this time, I had met Palestinians who themselves were often labelled as terrorist threats by their colonial occupier simply for existing on their ancestral land. The Israeli military would regularly bomb Gaza in lethal operations they called “mowing the grass” and face little to no condemnations, whereas Palestinians engaging in armed resistance immediately found themselves labelled as terrorists, thus justifying more violence by Israeli Occupational Forces. A friend I had made from the West Bank had described his run-ins with the Israeli military and the harm they had done to him and those close to him. Was he a terrorist if he stood up against them? It seemed an absurd and contradictory situation.</p>

<p>What seemed like a contradiction, however, was just a failure to reexamine my premises. As I grew older, I saw example after example of protestors in the United States, armed insurgents and revolutionaries, and anyone who stood in the way of the status quo being labelled terrorists. The word, it seemed, had no actual functional meaning, but certainly did have a political use. Want to delegitimize a movement or its tactics? Call them terrorists. Want to justify a drone strike in a sovereign nation? Say that your targets were terrorists. Want to destroy a hospital in Palestine or a pharmaceutical factory in Sudan? Call it terrorist infrastructure.</p>

<p>What’s more, as the years go by, the term is seemingly expanding to encompass larger and increasingly vague groups of people within the United States and across the world. The recent announcement of Donald Trump’s NSPM-7 national security directive set the stage for the crackdown and targeting of groups and individuals nationwide who fit this criteria:</p>

<ul>
<li>Anti-Americanism</li>
<li>Anti-capitalism</li>
<li>Anti-Christianity</li>
<li>Support for the overthrow of the United States Government,</li>
<li>Extremism on migration</li>
<li>Extremism on race</li>
<li>Extremism on gender</li>
<li>Hostility towards those who hold traditional American views on family</li>
<li>Hostility towards those who hold traditional American views on religion</li>
<li>Hostility towards those who hold traditional American views on morality.</li>
</ul>

<p>It is not hard to see how this could apply to vast swaths of individuals, particularly those on the organized “Left”. The vague standards outlined in NSPM-7 give federal agencies the ability to interpret a target’s positions on various issues as strictly or loosely as they need to justify their operations. This did not start with Trump either. Successive administrations from Bush to Biden have all played a role in giving U.S. agencies broad leeway to target “subversives” and those they may even deem “terrorist threats”. The goal is clear: stifle dissent and crack down on individuals and groups that challenge the status quo.</p>

<p>As these vague standards continue to be applied to organizers across the country, including those engaged in direct action against arms manufacturing facilities in support of Palestine today, the stage is being set for widespread designation of organizations and individuals as “terrorists” in an effort to delegitimize and criminalize them. Organizations like Samidoun, a pro-Palestine prisoner solidarity organization, have already been targeted in such a way, and they will not be the last.</p>

<p>Movement crackdowns are sure to continue, and we are likely to see not just further weaponization of the term terrorist, but also defensive movements from above-ground political organizations as they try to avoid such a designation for as long as they can. This could in itself have damaging effects as movement organizations attempt to defang themselves in an effort to stave off what very well may be inevitable, to the detriment of the movement.</p>

<p>The reality of the crisis that we face is decades in the making, constructed in myriad ways that have helped deepen the resources of state intelligence and security apparatuses and cast a wide enough net to justify state violence against those the state deems opposition. We face a future where we all may be deemed terrorists eventually, at least those doing work that challenges the state of things. As this reality draws nearer, we must reexamine our relationship to the term and push back on its usage against groups at home and abroad. We cannot hope to dull the sting of the term against ourselves if we allow it to be weaponized against those abroad.</p>

<p>“Terrorism” is ultimately a near-meaningless term with more political than functional use. It will be used to delegitimize us and crack down on our organizations and networks. We must push back and acknowledge the label for what it is.</p>
BODY;

        $data = [
            'title' => "We're All Going to be Called Terrorists Eventually",
            'intro' => 'The term "terrorism" has been weaponized for years by the United States to delegitimize and criminalize those it deems oppositional to its interests. It\'s only going to get worse.',
            'body' => $body,
            'image' => $image,
            'image_caption' => 'Two animal liberation activists in balaclavas, each holding a rescued white rabbit.',
            'category_id' => $category->id,
            'published_at' => Carbon::parse('2025-12-17'),
        ];

        $article = Article::updateOrCreate(['slug' => self::SLUG], $data);

        $this->info(($article->wasRecentlyCreated ? 'Created' : 'Updated').": {$article->title}");
        $this->info('View: /news/'.self::SLUG);

        return self::SUCCESS;
    }
}
