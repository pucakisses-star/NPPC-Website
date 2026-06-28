<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Publishes Kim Kelly's article "From Haymarket to Prairieland: How dissent has
 * unleashed the long arm of the law", credited to her author profile, in the
 * news category. Ensures the Kim Kelly author exists (so the byline resolves
 * even if authors:add-kim-kelly hasn't been run yet). Idempotent: matches the
 * article by slug/title.
 */
final class AddHaymarketPrairielandArticle extends Command
{
    protected $signature = 'articles:add-haymarket-prairieland';

    protected $description = 'Publish Kim Kelly\'s "From Haymarket to Prairieland" article';

    private const SLUG = 'from-haymarket-to-prairieland';

    public function handle(): int
    {
        $bio = 'Kim Kelly is a freelance journalist and author based in Philadelphia. Her writing can be '
            .'found in The Nation, In These Times, Teen Vogue, The Baffler, Playboy, Rolling Stone, and many '
            .'others. Her first book, FIGHT LIKE HELL: The Untold History of American Labor, was published in '
            .'2022, followed by a young readers edition in 2025. Find out more at kim-kelly.com.';

        $author = Author::firstOrCreate(['name' => 'Kim Kelly'], ['about' => $bio]);

        $title = 'From Haymarket to Prairieland: How dissent has unleashed the long arm of the law';

        $intro = 'Seven anti-ICE protesters and an alleged accomplice were handed a combined 450 years in '
            .'prison over a July 4, 2025 noise demonstration at Texas\'s Prairieland ICE Detention Center. '
            .'Kim Kelly traces how the state\'s vengeful response joins a long history of crushing dissent — '
            .'from the Haymarket martyrs to the first Red Scare to today.';

        $body = <<<'HTML'
<p>When Savanna Batten, Zachary Evetts, Autumn Hill, Meagan Morris, Maricela Rueda, Benjamin “Champagne” Song and Elizabeth Soto participated in a July 4, 2025, noise demonstration outside the Prairieland ICE Detention Center in Alvarado, Texas, it’s possible they thought they’d make history that day. And in the end, they did—by having the great misfortune of joining a long line of radicals who dared to push back against an oppressive system and were severely punished for it.</p>
<p>The Trump administration notched a critical victory in its self-styled war on “antifa” this week when seven demonstrators and one alleged accomplice were hit with a litany of terrorism charges by a Texas judge who told them quite clearly that “the state wants to send a message to anyone who shares a similar ideology.” He then handed down what amounted to a death sentence to each of them: a combined 450 years in prison. The eighth defendant— Daniel Rolando Sanchez Estrada—wasn’t even present at the protest, but still received 30 years for “conspiracy to conceal documents” after moving a box of zines to a friend’s house. Another 15 still face a dizzying array of state and federal charges that include accusations of rioting, conspiracy to use an explosive, providing material support for terrorism, and attempted murder of a federal employee.</p>
<p>The severity is incomprehensible: By declaring “antifa” a terrorist organization and pillorying the Prairieland defendants, the Trump administration has made it very clear that there is no acceptable form of protest left—and that this is just the beginning. But it’s also worth exploring the dark historical parallels, which show this isn’t the first time the US government has abused its powers to quash dissent.</p>
<p>The defendants were arrested after the July 4th noise demo at Prairieland in which the dozen or so participants shouted messages of support to detainees through a loudspeaker and set off fireworks. Nearby, a smaller group spray painted a few ICE vehicles. At one point a local police officer, Lt. Thomas Gross, was allegedly shot in the shoulder by one of the protestors, who stated that he’d seen the officer draw his weapon and feared that Gross was preparing to shoot one of the other people assembled there. Following the protest, the FBI and state law enforcement launched a series of raids on local activists’ homes and arrested 22 people they claimed were involved in the “antifa” plot.</p>
<p>Antifa is short for antifascism, a broad, leaderless political movement with deep historical roots around the globe. To be antifascist is to oppose white supremacy, authoritarianism, and far-right bigotry in whatever way possible and by any means necessary, whether that be a peaceful protest, a letter-writing campaign, or a militant street action.</p>
<p>During the first Trump regime, a wide variety of people who wouldn’t dream of donning all black or burning a flag—from normie Democrat “wine moms” to World War II veterans—could be seen proudly claiming the mantle of antifascism for themselves as a sign of resistance. In practice, it’s far more about direct action and mutual aid than social media posting, but given the circumstances, it was hard for even the most curmudgeonly comrades to find fault with everyday people loudly publicizing their own opposition to fascism. If there are no atheists in a foxhole, there was certainly no time for purity tests when the Nazis came marching in. “We are all antifa now,” as the media and artists said. And for a little while, we could be.</p>
<p>The Prairieland defendants were motivated by an opposition to ICE and the terrors they have and continue to unleash on communities; a position that, per a February 2026 poll, is held by a majority of Americans. How many people in your life feel the same way? And how about you—have you ever gone to an anti-ICE protest, complained about capitalism, or joined a neighborhood Signal chat? Congratulations. Definitionally, you’re an “antifa member”—and in the eyes of the US government, that makes you a terrorist.</p>
<p>To truly understand the gravity of this case and the vengeful precedent upon which it was built, let’s travel back to Chicago in 1886. Eight activists alleged to have been a part of “the Haymarket riot” stood accused of conspiracy to use explosives, left to the mercy of a hostile jury that found their political views repugnant and their identities suspect. All eight men were anarchists; many were also labor organizers, orators, and writers who had thrown themselves into the fight for the eight-hour workday. Some were immigrants of German extraction, while one was a former Confederate soldier from Alabama turned leftist wordsmith.</p>
<p>The activists had been hunted down and arrested after a rally of striking workers turned to chaos. Someone had thrown a bomb into the crowd, and the police started shooting. A number of workers were injured or killed in the resulting melee, as well as several policemen. Most of the accused had not been present when it happened, but were hauled to jail just the same; later, it was generally acknowledged that none of them had thrown the bomb, either.</p>
<p>Their friends and comrades tried desperately to win them their freedom by appeal, by pardon, by sheer force of will. Lucy Parsons, the iconic Black anarchist activist whose husband Albert was among the accused, criss-crossed the country drumming up support. But even her fierce dedication could not swing the odds in their favor.</p>
<p>Merely being associated with anarchism was enough to make a man seem guilty then, and ultimately, four of the eight men hung for it. Albert Parsons, August Spies, Adolph Fisher, and George Engel were executed on November 11, 1887. As the darkness closed in, Spies spoke the promise of his final words: “The day will come when our silence will be more powerful than the voices you strangle today!” Another, Louis Lingg, took his own life before the state got the chance. Three other defendants—Samuel Fielden, Oscar Neebe, and Michael Schwab—eventually had their sentences commuted after years of torment.</p>
<figure><img src="/storage/articles/haymarket-hanging-clipping.jpg" alt="Newspaper account of the hanging of the Chicago anarchists, with an engraving of August Spies in his cell" /><figcaption>“The Hanging of the Chicago Anarchists” — August Spies, the Chicago anarchist, as he appeared in his cell.</figcaption></figure>
<p>I thought about Spies and the other Haymarket martyrs as I read Benjamin “Champagne” Song’s closing statement from Prairieland. It, too, was a warning: “Whatever is taken from me is taken from you,” they said. “It may be these 22 strangers now, but it will be you tomorrow.” One can imagine the Haymarket martyrs stirring in their graves at the ugly familiarity of it all. Once again, the voices of dissent are being strangled. Once again, the silence of a living tomb—an endless prison sentence—threatens a movement. Another eight lives hang in terrible limbo, their families terrified of a rapidly darkening future.</p>
<figure><img src="/storage/articles/iww-deportation-1917.jpg" alt="Marching from Lowell, Arizona — deportation of Industrial Workers of the World, July 12, 1917" /><figcaption>Marching from Lowell, AZ, Deportation of Industrial Workers of the World, July 12, 1917</figcaption></figure>
<p>We unfortunately have a wealth of other historical parallels to choose from in our remembrances. We can look back to 1917, when the FBI raided the Industrial Workers of the World offices and hundreds of labor activists (including Eugene V. Debs and prominent Black organizer Ben Fletcher) were carted off to prison on bogus charges of treason; or the Bisbee Deportations, when 1,300 striking Mexican American coal miners were kidnapped and dumped in the New Mexico desert. We can also look to the First Red Scare, that nasty period between 1919 and 1920 when government suppression of left-wing organizations, trade unions, and anti-war activists hit a fever pitch and hundreds of socialists, anarchists, and communists (including the iconic Emma Goldman) were rounded up and deported.</p>
<p>Then there’s always the sad, infuriating tale of Nicola Sacco and Bartolomeo Vanzetti, Italian anarchists who were wrongfully arrested for murder, publicly castigated for their politics, then railroaded by a xenophobic jury who sent them to the gallows in 1927. Fifty years later, Massachusetts governor Michael Dukakis stuck a more penitent tone, noting the injustice of the trial and asking people "to reflect upon these tragic events, and draw from their historic lessons the resolve to prevent the forces of intolerance, fear, and hatred from ever again uniting to overcome the rationality, wisdom, and fairness to which our legal system aspires."</p>
<p>Recent history, too, has been filled with dissenters who’ve been knocked sideways by the long arm of the law for their trouble—particularly during the Trump era. In January 2017, following a mass protest during Trump’s first inauguration, 214 antifascist activists were arrested under the DC Riot Act and threatened with decades in prison; they were dragged through torturous and costly legal proceedings, only to see their charges dropped for lack of evidence. In 2020, during the nationwide Black Lives Matter protests, over 13,000 people were arrested, while multiple states enacted harsher laws aimed at suppressing protests and demonstrations. The ICE agents who gunned down Renee Good and Alex Pretti in Minneapolis earlier this year are still walking free and have faced no consequences for committing cold-blooded murder.</p>
<p>The trouble with being vocally in opposition to fascism under a fascist regime is that it automatically makes you a target. In September 2025, after years of urging from the bad actors, whiny fascists, and literal Nazis that populate the far-right mediasphere, the Trump administration made good on its threats to formally designate “antifa” a terrorist organization. Three days later, it released a memo titled Countering Domestic Terrorism and Organized Political Violence (known as NSPM-7) that further criminalized dissent as “domestic terrorism” and has poured government resources into identifying, investigating, and ultimately prosecuting groups and individuals who are caught within its web “before they result in violent political acts.” The fact that no such organization exists does not seem to matter.</p>
<p>The story of American dissent does not have to end here, though. Nearly 140 years since the noose tightened around the Chicago anarchists’ necks, we have an opportunity to reject their fate, to stand up for these 21st century dissidents and throw our backs into their defense. The case may well “disintegrate” once it breaks state containment, but we know that this will not be the last “antifa” trial of the Trump era. The regime is out for blood, and it’s far easier to trumpet its crackdown on “domestic terrorists” than to confront its own staggering failures.</p>
<p>As of this writing, fifteen people in Minnesota have been indicted on trumped-up federal charges of “conspiring to impede immigration agents” and are facing down the same anti-“antifa” playbook. We are all antifa now.</p>
<p>“I don’t fear for myself; I fear for all of you,” Song said in their statement this week. “What will you do in this time of great failures and great injustices? What will you do? How will you help each other? How will you help yourselves?”</p>
HTML;

        // Copy the article images (cover + the two historical photos embedded in
        // the body) onto the public disk where they are served from.
        $images = [
            'images/articles/from-haymarket-to-prairieland-cover.jpg' => 'articles/from-haymarket-to-prairieland-cover.jpg',
            'images/articles/haymarket-hanging-clipping.jpg' => 'articles/haymarket-hanging-clipping.jpg',
            'images/articles/iww-deportation-1917.jpg' => 'articles/iww-deportation-1917.jpg',
        ];
        foreach ($images as $src => $dest) {
            $path = public_path($src);
            if (is_file($path)) {
                Storage::disk('public')->put($dest, file_get_contents($path));
            } else {
                $this->warn('Missing image: public/'.$src);
            }
        }

        $category = Category::where('slug', 'news')->first();

        $attributes = [
            'title' => $title,
            'slug' => self::SLUG,
            'intro' => $intro,
            'body' => $body,
            'image' => 'articles/from-haymarket-to-prairieland-cover.jpg',
            'author_id' => $author->id,
            'published_at' => '2026-06-27 09:00:00',
        ];
        if ($category) {
            $attributes['category_id'] = $category->id;
        }

        $article = Article::where('slug', self::SLUG)->orWhere('title', $title)->first();
        if ($article) {
            $article->fill($attributes)->save();
            $this->info("Updated article: {$article->title}");
        } else {
            $article = Article::create($attributes);
            $this->info("Created article: {$article->title}");
        }

        $this->info("Credited to {$author->name}. View: {$article->url}");

        return self::SUCCESS;
    }
}
