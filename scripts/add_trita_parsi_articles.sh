#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_trita_parsi_articles.sh
set -e

php artisan tinker --execute='
use App\Models\Article;
use App\Models\Author;
use App\Models\Category;

$cat = Category::firstOrCreate(["slug" => "news"], ["title" => "News"]);

// Article 1 — The New Republic
$author1 = Author::firstOrCreate(["name" => "The New Republic"]);
if (!Article::where("slug", "trump-investigates-deport-iran-war-critic-trita-parsi")->exists()) {
    Article::create([
        "title"        => "Trump Team Investigates How to Deport Major Iran War Critic",
        "body"         => "<p>The Trump administration is reportedly investigating Trita Parsi, a Swedish-born Iranian-American who holds U.S. permanent residency, with the aim of revoking his green card and deporting him. Parsi co-founded the National Iranian American Council and the Quincy Institute for Responsible Statecraft, a foreign policy think tank focused on diplomatic solutions.</p><p>Parsi faces scrutiny primarily for his vocal criticism of U.S. military intervention regarding Iran and his advocacy for diplomatic engagement with the Iranian government. The administration has previously used immigration enforcement against other critics, including college students protesting U.S. support for Israel’s military actions in Gaza and Columbia University graduate Mahmoud Khalil, whom the government is attempting to deport over campus protest participation.</p><p>Far-right influencer Laura Loomer has publicly attacked Parsi on social media, labeling him “a mouthpiece for the Iranian regime” and predicting his “days in our country are numbered.” Loomer has reportedly influenced White House decisions regarding immigration enforcement against political opponents.</p><p>The Quincy Institute has pledged to cover legal expenses should deportation proceedings commence. Critics argue such action would undermine First Amendment protections for permanent residents and chill political speech.</p>",
        "intro"        => "The Trump administration is investigating Iranian-American analyst and Quincy Institute co-founder Trita Parsi, reportedly seeking to revoke his green card and deport him for his outspoken criticism of U.S. military intervention against Iran.",
        "image"        => "https://images.newrepublic.com/4a47a0db6e60853dedfcfdf08a5ca249.png",
        "category_id"  => $cat->id,
        "author_id"    => $author1->id,
        "published_at" => "2026-06-11 11:30:00",
    ]);
    echo "Added: Trump Team Investigates How to Deport Major Iran War Critic\n";
} else {
    echo "Skipped (exists): New Republic Trita Parsi article\n";
}

// Article 2 — Democracy Now!
$author2 = Author::firstOrCreate(["name" => "Democracy Now!"]);
if (!Article::where("slug", "report-state-dept-opens-probe-of-iranian-born-trita-parsi-critic-of-trumps-war")->exists()) {
    Article::create([
        "title"        => "Report: State Dept. Opens Probe of Iranian-Born Trita Parsi, Critic of Trump’s War",
        "body"         => "<p>The State Department has launched an investigation into political analyst Trita Parsi, an Iranian-born individual holding permanent resident status who has resided in America for over two decades. According to reporting by The Free Press, this probe could potentially lead to deportation proceedings.</p><p>Parsi co-founded two prominent organizations: the Quincy Institute for Responsible Statecraft and the National Iranian American Council. In recent months, Parsi has been a leading critic of Trump’s war on Iran and has appeared on numerous media platforms, including Democracy Now!</p><p>The investigation represents a significant development given Parsi’s established status as a long-term U.S. resident and his visibility as a vocal commentator on American foreign policy regarding Iran.</p>",
        "intro"        => "The State Department has opened a probe into Iranian-born permanent resident Trita Parsi — co-founder of the Quincy Institute — that could lead to deportation, according to The Free Press.",
        "image"        => "https://www.democracynow.org/images/story/00/53553/thumb/DN2026-0612-11.jpg",
        "category_id"  => $cat->id,
        "author_id"    => $author2->id,
        "published_at" => "2026-06-12 00:00:00",
    ]);
    echo "Added: State Dept. Opens Probe of Trita Parsi (Democracy Now!)\n";
} else {
    echo "Skipped (exists): Democracy Now! Trita Parsi article\n";
}
'
