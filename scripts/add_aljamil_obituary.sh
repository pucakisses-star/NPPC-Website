#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_aljamil_obituary.sh
#
# Adds a News article marking the death of Imam Jamil Al-Amin
# (Hubert Gerold Brown / H. Rap Brown) in federal custody on
# November 23, 2025 at age 82. Article body is original NPPC
# editorial prose summarizing widely-documented public facts about
# his life. Cites the Scalawag piece "Remembering Imam Jamil
# al-Amin" by Tea Troutman and Da'Shaun L. Harrison (Dec 15, 2025)
# as a recommended read; downloads the lead photo from that piece
# into storage/app/public/articles/imam-jamil-al-amin.png and
# attaches it.
set -e

PHOTO_URL='https://i0.wp.com/scalawagmagazine.org/wp-content/uploads/2025/12/imam_jamil_al_amin_duaa_memento_mori_banner_1920x1080.png?fit=1920%2C1079&ssl=1'
PHOTO_REL='articles/imam-jamil-al-amin.png'
PHOTO_ABS="storage/app/public/${PHOTO_REL}"

mkdir -p "$(dirname "$PHOTO_ABS")"
echo "Downloading lead image..."
curl -fsSL -A 'Mozilla/5.0 NPPC-archive' -o "$PHOTO_ABS" "$PHOTO_URL" || {
    echo "WARNING: photo download failed; continuing without lead image."
    PHOTO_REL=""
}

php artisan tinker --execute='
$author = App\Models\Author::firstOrCreate(["name" => "NPPC Editorial"]);
$category = App\Models\Category::firstOrCreate(
    ["slug" => "news"],
    ["title" => "News"]
);

$title = "Remembering Imam Jamil Al-Amin";

if (App\Models\Article::where("title", $title)->exists()) {
    echo "Article already exists; skipping.\n";
    exit(0);
}

$intro = "Imam Jamil Al-Amin - born Hubert Gerold Brown and known to a generation as H. Rap Brown - died on November 23, 2025 in federal custody at age 82, while a federal court was still weighing the conviction-integrity review of the case that had held him for a quarter century. He died as he had lived for the last fifty years of his life: a Muslim, a community elder, and a political prisoner.";

$body = <<<HTML
<p>Hubert Gerold Brown was fifteen years old when he led a student walkout against segregated facilities in Baton Rouge, Louisiana, and not yet twenty-one when he turned up in Mississippi to register voters with the Student Nonviolent Coordinating Committee during the 1964 Freedom Summer. By the spring of 1967 he had been elected SNCCs national chairman, succeeding Kwame Ture (Stokely Carmichael), and pulled the organization openly into the politics of Black self-defense, anti-imperialism, and refusal of the war in Vietnam. As Minister of Justice of the Black Panther Party and author of the 1969 memoir <em>Die Nigger Die!</em>, he became one of the most surveilled, sought, and slandered figures of his generation. The FBI named him in COINTELPRO directives calling for his neutralization. The Marylands governor declared a riot in Cambridge after a 1967 speech that was, by later historians accounting, no riot at all.</p>

<p>He was first imprisoned in 1971 in connection with a New York City robbery and shootout case. Held at Rikers Island, then transferred to the federal system, he embraced Islam and took the name Jamil Abdullah Al-Amin. By the late 1970s he had been released, and he made his home in the West End of Atlanta - first as a corner-store proprietor, eventually as the founding imam of a Sunni Muslim community that would, over more than two decades, anchor a neighborhood notorious for street-level drug economies and police violence. In the West End he was known to neighbors of every faith as a man who walked the block, called the prayers, fed the hungry, and made it his business to know the names of everyone he passed.</p>

<p>On March 16, 2000, two Fulton County sheriffs deputies came to his West End store with a warrant, and one of them - Deputy Ricky Kinchen - was killed. Otis Jackson, who matched several elements of the eyewitness description, later confessed to the shooting; his confession was disregarded. Al-Amin was arrested four days later in Whitehall, Alabama, after a four-day FBI manhunt, and was tried in Fulton County Superior Court in 2002. The jury convicted him on March 9 of that year, and he was sentenced on March 13 to life without the possibility of parole. He maintained his innocence to the end of his life. Beginning in 2007 he was held in federal supermax custody at ADX Florence in Colorado, transferred to USP Tucson in his last years as his health declined, and finally to FMC Butner. His health failed in stages: blindness in one eye, a multiple-myeloma diagnosis, the slow incremental loss that supermax confinement cultivates by design.</p>

<p>He had outlived nearly all of his SNCC contemporaries. He was a witness to the long sweep of the era - from sit-ins to soul music to the consolidations and betrayals of the elected Black political class he had helped, in his way, to make possible. Tea Troutman and DaShaun L. Harrison, writing in <a href="https://scalawagmagazine.org/2025/12/remembering-imam-jamil-al-amin/" target="_blank" rel="noopener">Scalawag</a>, captured his last decades better than most: a man who, in the absence of any reasonable hope of personal release, did the patient inside work of remaining a teacher to those who wrote to him, of declining to disappear.</p>

<p>The National Political Prisoner Coalition extends its condolences to the Imams family, his wife Karima Al-Amin, the Community Mosque in Atlantas West End, and the network of organizers across four generations who knew him. We continue to call for the release of every elder still serving COINTELPRO-era sentences, and for a public reckoning with the cases - Al-Amins among them - that the Justice Department has spent the last quarter century declining to reopen.</p>
HTML;

$citations = [
    [
        "title"  => "Remembering Imam Jamil al-Amin",
        "author" => "Tea Troutman and DaShaun L. Harrison",
        "source" => "Scalawag",
        "date"   => "2025-12-15",
        "url"    => "https://scalawagmagazine.org/2025/12/remembering-imam-jamil-al-amin/",
    ],
    [
        "title"  => "Die Nigger Die! A Political Autobiography",
        "author" => "H. Rap Brown",
        "source" => "Dial Press",
        "date"   => "1969",
    ],
];

App\Models\Article::create([
    "title"          => $title,
    "intro"          => $intro,
    "body"           => $body,
    "image"          => "'"$PHOTO_REL"'",
    "image_caption"  => "Photograph: lead image from Scalawag, Remembering Imam Jamil al-Amin (Dec 15, 2025).",
    "author_id"      => $author->id,
    "category_id"    => $category->id,
    "published_at"   => "2025-12-16 09:00:00",
    "citations_json" => json_encode($citations),
]);

echo "Created article: {$title}\n";
'

echo
echo "Imam Jamil Al-Amin obituary article add complete."
