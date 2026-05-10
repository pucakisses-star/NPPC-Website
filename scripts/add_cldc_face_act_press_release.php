<?php

declare(strict_types=1);

/**
 * Add CLDC Sept 12 2024 press release on Smith-Stewart / Rivera /
 * Freestone FACE Act sentencing as an Article in the
 * "Press Releases" category. Idempotent.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;

$slug = 'justice-distorted-cldc-face-act-sentencing-2024';

if (Article::where('slug', $slug)->exists()) {
    echo "Article '{$slug}' already exists. Nothing to do.\n";
    return;
}

$category = Category::firstOrCreate(
    ['slug' => 'press-releases'],
    ['name' => 'Press Releases']
);

$author = Author::firstOrCreate(
    ['name' => 'Civil Liberties Defense Center'],
);

$intro = "In a deeply troubling move, a federal court today sentenced pro-choice activists Amber Smith-Stewart and Annarella Rivera to 30 days in custody and 60 days of home confinement and Caleb Freestone to 1 year and 1 day in federal prison for Conspiracy, while dismissing two counts under the Freedom of Access to Clinic Entrances (FACE) Act.";

$body = <<<'HTML'
<p style="font-style: italic; opacity: 0.7; margin-bottom: 24px;">FOR IMMEDIATE RELEASE — September 12, 2024<br>
Contact: Lauren Regan, Director of Litigation &amp; Advocacy, Civil Liberties Defense Center — info@cldc.org | (541) 687-9180</p>

<h2>Justice Distorted: Activists Sentenced for Conspiracy, Avoid FACE Act Charges, in Unprecedented Attack on Pro-Choice Defenders</h2>

<p><strong>Tampa, FL</strong> — In a deeply troubling move, a federal court today sentenced pro-choice activists Amber Smith-Stewart and Annarella Rivera to 30 days in custody and 60 days of home confinement and Caleb Freestone to 1 year and 1 day in federal prison for Conspiracy, while dismissing two counts under the Freedom of Access to Clinic Entrances (FACE) Act, 18 U.S.C. §248. This unprecedented use of the FACE Act—originally designed to protect abortion clinics and their employees—has been twisted to prosecute activists protesting so-called crisis pregnancy centers (CPCs), which are in many cases religious-run facilities that actively work to manipulate and prevent people from accessing abortions. The crimes these defendants have admitted to and took responsibility for would normally result in a state or municipal court misdemeanor for graffiti. Caleb Freestone's longer sentence was attributed to minor activism he had been arrested for wheat-pasting flyers on private buildings—the charges were later dismissed but the court heavily punished him for it.</p>

<p>The Federal Judge from the Middle District of Florida, Virginia Hernandez Covington, at one point inconceivably argued that spray painting the outside of a closed fake clinic building was more threatening than fire bombing and causing a fire at an abortion clinic. Judge Hernandez Covington argued that the fear the crisis pregnancy center workers felt was in some way more serious than hurling two molotov cocktails at a legitimate health provider. Lawyer for Amber Smith-Stewart, Lauren Regan pushed back on this statement with the Judge eventually conceding, "They're both bad." This colloquy with the Court exposed the clear bias against the reproductive justice activists. Regan provided extensive evidence and argument regarding all of the FACE Act prosecutions and sentences around the country. "These reproductive justice activists are the first pro-choice people to be prosecuted under this statute after pressure was applied to the Department of Justice by Florida politicians. They are also the first to be prosecuted for minor property damage that occurred when there were no patients or workers around. Anti-abortion defendants that firebombed clinics or had repeated violations of blockading patients trying to enter Planned Parenthood Clinics got less punishment than our clients did today."</p>

<p>In the summer of 2022, Amber Smith-Stewart, Annarella Rivera, and Caleb Freestone spray painted three anti-abortion centers in Central and Southern Florida. The defendants previously admitted to tagging these facilities that Planned Parenthood calls "fake clinics," with the following messages: "If abortions aren't safe than niether [sic] are you." These protests came in the aftermath of the Supreme Court's Dobbs decision and were borne out of frustration and grief over the rollback of abortion rights in the United States in the wake of the overturned Roe v. Wade precedent. The defendants were arrested for spray painting messages on fake reproductive health facilities in Florida, which the government argued were "reproductive health service" providers under the FACE Act — a position highly questionable given that the centers involved in this case should never have been granted the protections of a law meant to safeguard true healthcare providers.</p>

<p>Rivera, a long-time advocate for reproductive justice, expressed disbelief at the extent of the government's response to the graffiti, stating "I've supported and raised two children by working in my chosen field of ob/gyn healthcare. When not working, I've spent countless hours escorting patients to and from clinical appointments for their safety and the safety of our workers. Simply put, women's health and the ability to choose their own path is my passion and calling. I never thought that the act of spray-painting a fake 'clinic' would result in the FBI violently raiding my house or that I would become a pawn in the fight for a person's right to bodily autonomy—something an overwhelming majority of citizens in the United States believe in."</p>

<p>Smith-Stewart added: "In hindsight, I understand that the slogans we spray painted in 2022 could have been taken as a threat which was not our intent. The real threat here remains the assault by a vocal minority against a person's right to choose. This is where the real danger lies. The Dobbs decision puts lives at risk. People seek abortions for a multitude of reasons, and the decision to terminate or carry a pregnancy to term should never be made by power brokers in Washington, D.C. In my case, my own life would be at risk if I was subjected to a forced pregnancy."</p>

<p>The use of the FACE Act to protect CPCs, which often operate with the intention of dissuading or manipulating people from obtaining abortions, is a gross distortion of the law. Defense counsel for Smith-Stewart, Lauren Regan, Director of Litigation and Advocacy at Civil Liberties Defense Center (CLDC), condemned today's ruling: "This case marks a dangerous shift in the misuse of the FACE Act, twisting it from a law designed to protect abortion providers into a tool for punishing those who stand up for reproductive rights. The government's claims are a gross overreach. Using a statute intended to safeguard access to abortion to criminalize protest against anti-abortion centers undermines justice and bodily autonomy. This should alarm anyone who believes in the right to choose and the fight for reproductive freedom."</p>

<p>The sentences handed down today send a chilling message to those standing up for reproductive rights and bodily autonomy but CLDC will have your back and we will continue to support Amber Smith-Stewart, Annarella Rivera, Caleb Freestone, and all those fighting for a better world. Thanks to Michael Maddux, attorney for Caleb Freestone, and Vanessa King, attorney for Annarella Rivera.</p>
HTML;

$article = Article::create([
    'title'        => 'Justice Distorted: Activists Sentenced for Conspiracy, Avoid FACE Act Charges, in Unprecedented Attack on Pro-Choice Defenders',
    'slug'         => $slug,
    'author_id'    => $author->id,
    'category_id'  => $category->id,
    'intro'        => $intro,
    'body'         => $body,
    'published_at' => '2024-09-12 00:00:00',
]);

echo "[create] Article id={$article->id}, slug={$article->slug}\n";
echo "         Author: {$author->name} (id={$author->id})\n";
echo "         Category: {$category->name} (id={$category->id})\n";
echo "Done.\n";
