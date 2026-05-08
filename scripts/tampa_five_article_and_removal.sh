#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/tampa_five_article_and_removal.sh
#
# Two tasks:
#   1. Add an article in /news about the Tampa Five — the five
#      Students for a Democratic Society protesters arrested at USF
#      on March 6, 2023 protesting Ron DeSantis's HB 999 anti-DEI
#      legislation; felony charges dropped Dec 5, 2023.
#   2. Remove the five prisoner records (Carpio, Davila, Kida,
#      Pineiro, Rodriguez) from the prisoners DB. Charges were
#      dropped via diversion so they are not political prisoners.
set -e

php artisan tinker --execute='
$author = App\Models\Author::firstOrCreate(["name" => "NPPC Editorial"]);

$category = App\Models\Category::firstOrCreate(
    ["slug" => "news"],
    ["title" => "News"]
);

$title = "Tampa Five: Florida prosecutors drop felony charges against USF anti-DEI protesters";

if (App\Models\Article::where("title", $title)->exists()) {
    echo "Article already exists; skipping create.\n";
} else {
    $intro = "Florida prosecutors agreed in December 2023 to drop felony charges against the Tampa Five — five Students for a Democratic Society organizers arrested March 6, 2023 at the University of South Florida while protesting Governor Ron DeSantiss anti-DEI legislation. Three faced up to fifteen years in prison.";

    $body = <<<HTML
<p>On March 6, 2023, members of the Tampa Bay chapter of Students for a Democratic Society (SDS) led a "Stop the Wave of Fascism" rally on the University of South Floridas Tampa campus to protest HB 999 — the bill, championed by Governor Ron DeSantis, that prohibited Florida public colleges from spending state funds on diversity, equity, and inclusion programs and gutted ethnic-studies curricula. Hundreds of students rallied at the Patel Center for Global Solutions and attempted to deliver a written demand to USF President Rhea Law that the university publicly oppose HB 999.</p>

<p>USF Police prevented the delegation from entering the building. In the ensuing confrontation, five protesters were arrested: Chrisley Marie Solidum Carpio, Gia Marie Davila, Jeanie Kida, Laura Raquel Rodriguez, and — turning herself in two months later on May 3, 2023 — Lauren Lynn Pineiro. The State Attorneys Office charged Carpio, Kida, and Rodriguez with two felony counts each of battery on a law-enforcement officer, a charge carrying up to fifteen years in state prison; Davila and Pineiro were charged with one felony battery count plus misdemeanor counts of resisting an officer without violence and disrupting a school function. Body-camera and bystander video later released by the defense showed officers tackling protesters from behind during arrests; defense attorneys argued the alleged "battery" was incidental contact during USF Police use of force against the demonstrators.</p>

<p>The case became a national flashpoint for the criminalization of protest in Florida. The Tampa Five spent months touring the country to build their defense — appearing at SDS conferences, on the Yale and Harvard campuses, and on local media — while organizers raised tens of thousands of dollars for legal defense. The Tampa Bay chapter of the Party for Socialism and Liberation, the National SDS, the National Lawyers Guild, the ACLU of Florida, the Council on American-Islamic Relations Florida, and dozens of student governments across the country issued statements demanding the charges be dropped.</p>

<p>On December 5, 2023, the Hillsborough County State Attorneys Office, under State Attorney Suzy Lopez, announced that all five would be permitted to enter a misdemeanor pretrial-intervention program: each defendant would complete 24 hours of community service, after which the felony charges would be formally dismissed. In a press conference outside the courthouse, the defendants and their attorneys called the outcome a vindication. "Theyve been lying from day one," Carpio told reporters, referring to USF Police statements that protesters had initiated the violence. "We didnt batter anyone. We were the ones being attacked."</p>

<p>The Tampa Five case was followed by HB 999s passage and signing into Florida law in May 2023, and by Governor DeSantiss subsequent rollback of state-funded DEI programs at every public university and college in Florida. Organizers in Tampa and across Florida have continued the fight against the law and against the criminalization of campus protest. The Tampa Five themselves remain active in SDS, the Tampa Bay chapter of the Party for Socialism and Liberation, and a range of campus and community organizing campaigns.</p>

<p>The National Political Prisoner Coalition recognizes the Tampa Five as among the wave of student-protest defendants prosecuted across the United States since 2023 — a wave that includes the Stop Cop City RICO defendants in Atlanta, the pro-Palestine campus encampment defendants of 2024, and many others. We salute the Tampa Five organizers and their supporters for the defense campaign that secured the dismissal of every felony charge.</p>
HTML;

    $citations = [
        [
            "title"  => "Felony charges dropped against USF protestors The Tampa Five",
            "source" => "WMNF 88.5 FM",
            "date"   => "2023-12-05",
        ],
        [
            "title"  => "Arrested USF protesters known as the Tampa 5 will have their charges dropped",
            "source" => "Axios Tampa Bay",
            "date"   => "2023-12-05",
        ],
        [
            "title"  => "USF protesters plead not guilty to charges of battery against police",
            "source" => "Tampa Bay Times",
            "date"   => "2023-05-17",
        ],
        [
            "title"  => "March 6, 2023: Tampa 5 Arrested During DeSantis Protest",
            "source" => "Zinn Education Project",
        ],
        [
            "title"  => "Tampa 5 on Police Violence, Prison for DeSantis Protest",
            "source" => "Refinery29",
            "date"   => "2023-09-13",
        ],
    ];

    App\Models\Article::create([
        "title"          => $title,
        "intro"          => $intro,
        "body"           => $body,
        "author_id"      => $author->id,
        "category_id"    => $category->id,
        "published_at"   => "2023-12-05 12:00:00",
        "citations_json" => json_encode($citations),
    ]);

    echo "Created article: {$title}\n";
}

// ---- Remove the Tampa Five prisoner records (charges dropped) ----
$names = [
    "Chrisley Marie Solidum Carpio",
    "Chrisley Carpio",
    "Gia Marie Davila",
    "Gia Davila",
    "Jeanie Kida",
    "Lauren Lynn Pineiro",
    "Lauren Pineiro",
    "Laura Raquel Rodriguez",
    "Laura Rodriguez",
];

$prisoners = App\Models\Prisoner::whereIn("name", $names)->get();
$nP = 0; $nC = 0;
foreach ($prisoners as $p) {
    $caseCount = $p->cases()->count();
    $p->cases()->delete();
    $p->delete();
    $nP++;
    $nC += $caseCount;
    echo "  Removed: {$p->name} (id={$p->id}, cases={$caseCount})\n";
}
echo "Tampa Five removal: prisoners={$nP}, cases={$nC}\n";
'

echo "Done."
