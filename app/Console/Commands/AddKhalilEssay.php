<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddKhalilEssay extends Command
{
    protected $signature = 'articles:add-khalil-essay';
    protected $description = 'Add Mahmoud Khalil\'s essay "My Name is Mahmoud Khalil and I Am a Political Prisoner" — dictated by phone from ICE detention in Louisiana, March 2025.';

    public function handle(): int
    {
        DB::transaction(function () {
            $author = Author::firstOrCreate(['name' => 'Mahmoud Khalil']);

            $category = Category::firstOrCreate(
                ['slug' => 'news'],
                ['title' => 'News']
            );

            $title = 'My Name is Mahmoud Khalil and I Am a Political Prisoner';

            if (Article::where('title', $title)->exists()) {
                $this->warn('Article already exists; skipping.');

                return;
            }

            $intro = "Dictated by phone from an ICE detention facility in Jena, Louisiana, this is Mahmoud Khalil's first public statement after his March 8, 2025 arrest in New York. A Columbia University graduate and Palestinian student leader detained without charge for his pro-Palestine activism, Khalil writes: \"Who has the right to have rights?\"";

            $citations = [
                [
                    'title'  => 'Letter from a Palestinian Political Prisoner in Louisiana, Dictated Over the Phone From ICE Detention',
                    'author' => 'Mahmoud Khalil',
                    'source' => 'Center for Constitutional Rights',
                    'date'   => '2025-03-18',
                    'url'    => 'https://ccrjustice.org/home/press-center/press-releases/letter-palestinian-political-prisoner-louisiana-dictated-over-phone',
                ],
                [
                    'title'  => 'My Name is Mahmoud Khalil and I Am a Political Prisoner',
                    'author' => 'Mahmoud Khalil',
                    'source' => 'In These Times',
                    'date'   => '2025-03-18',
                    'url'    => 'https://inthesetimes.com/article/mahmoud-khalil-letter-gaza-israel-palestine-ice',
                ],
            ];

            Article::create([
                'title'          => $title,
                'intro'          => $intro,
                'body'           => $this->buildBody(),
                'author_id'      => $author->id,
                'category_id'    => $category->id,
                'published_at'   => Carbon::parse('2025-03-18 09:00:00 -0400'),
                'citations_json' => json_encode($citations),
            ]);

            $this->info("Created article: {$title}");
        });

        return self::SUCCESS;
    }

    private function buildBody(): string
    {
        return <<<'HTML'
<p>My name is Mahmoud Khalil and I am a political prisoner. I am writing to you from a detention facility in Louisiana where I wake to cold mornings and spend long days bearing witness to the quiet injustices underway against a great many people precluded from the protections of the law.</p>

<p>Who has the right to have rights? It is certainly not the humans crowded into the cells here. It isn't the Senegalese man I met who has been deprived of his liberty for a year, his legal situation in limbo and his family an ocean away. It isn't the 21-year-old detainee I met, who stepped foot in this country at age nine, only to be deported without so much as a hearing.</p>

<p>Justice escapes the contours of this nation's immigration facilities.</p>

<p>On March 8, I was taken by DHS agents who refused to provide a warrant, and accosted my wife and me as we returned from dinner. By now, the footage of that night has been made public. Before I knew what was happening, agents handcuffed and forced me into an unmarked car. At that moment, my only concern was for Noor's safety. I had no idea if she would be taken too, since the agents had threatened to arrest her for not leaving my side. DHS would not tell me anything for hours — I did not know the cause of my arrest or if I was facing immediate deportation. At 26 Federal Plaza, I slept on the cold floor. In the early morning hours, agents transported me to another facility in Elizabeth, New Jersey. There, I slept on the ground and was refused a blanket despite my request.</p>

<p>My arrest was a direct consequence of exercising my right to free speech as I advocated for a free Palestine and an end to the genocide in Gaza, which resumed in full force Monday night. With January's ceasefire now broken, parents in Gaza are once again cradling too-small shrouds, and families are forced to weigh starvation and displacement against bombs. It is our moral imperative to persist in the struggle for their complete freedom.</p>

<p>I was born in a Palestinian refugee camp in Syria to a family which has been displaced from their land since the 1948 Nakba. I spent my youth in proximity to yet distant from my homeland. But being Palestinian is an experience that transcends borders. I see in my circumstances similarities to Israel's use of administrative detention — imprisonment without trial or charge — to strip Palestinians of their rights. I think of our friend Omar Khatib, who was incarcerated without charge or trial by Israel as he returned home from travel. I think of Gaza hospital director and pediatrician Dr. Hussam Abu Safiya, who was taken captive by the Israeli military on December 27 and remains in an Israeli torture camp today. For Palestinians, imprisonment without due process is commonplace.</p>

<p>I have always believed that my duty is not only to liberate myself from the oppressor, but also to liberate my oppressors from their hatred and fear. My unjust detention is indicative of the anti-Palestinian racism that both the Biden and Trump administrations have demonstrated over the past 16 months as the U.S. has continued to supply Israel with weapons to kill Palestinians and prevented international intervention. For decades, anti-Palestinian racism has driven efforts to expand U.S. laws and practices that are used to violently repress Palestinians, Arab Americans, and other communities. That is precisely why I am being targeted.</p>

<p>While I await legal decisions that hold the futures of my wife and child in the balance, those who enabled my targeting remain comfortably at Columbia University. Presidents Shafik, Armstrong, and Dean Yarhi-Milo laid the groundwork for the U.S. government to target me by arbitrarily disciplining pro-Palestinian students and allowing viral doxing campaigns — based on racism and disinformation — to go unchecked.</p>

<p>Columbia targeted me for my activism, creating a new authoritarian disciplinary office to bypass due process and silence students criticizing Israel. Columbia surrendered to federal pressure by disclosing student records to Congress and yielding to the Trump administration's latest threats. My arrest, the expulsion or suspension of at least 22 Columbia students — some stripped of their B.A. degrees just weeks before graduation — and the expulsion of SWC President Grant Miner on the eve of contract negotiations, are clear examples.</p>

<p>If anything, my detention is a testament to the strength of the student movement in shifting public opinion toward Palestinian liberation. Students have long been at the forefront of change — leading the charge against the Vietnam War, standing on the frontlines of the Civil Rights Movement, and driving the struggle against apartheid in South Africa. Today, too, even if the public has yet to fully grasp it, it is students who steer us toward truth and justice.</p>

<p>The Trump administration is targeting me as part of a broader strategy to suppress dissent. Visa-holders, green-card carriers, and citizens alike will all be targeted for their political beliefs. In the weeks ahead, students, advocates, and elected officials must unite to defend the right to protest for Palestine. At stake are not just our voices, but the fundamental civil liberties of all.</p>

<p>Knowing fully that this moment transcends my individual circumstances, I hope nonetheless to be free to witness the birth of my first-born child.</p>

<hr>

<p><em>Mahmoud Khalil is a graduate of the School of International and Public Affairs at Columbia University. This letter was dictated by phone from ICE detention in Jena, Louisiana on March 18, 2025 and first released by the Center for Constitutional Rights.</em></p>
HTML;
    }
}
