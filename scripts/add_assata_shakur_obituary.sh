#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_assata_shakur_obituary.sh
#
# Adds a News article marking Assata Shakurs death in Havana, Cuba on
# September 25, 2025 at age 78. The article summarizes publicly
# documented facts about her life and case in original prose.
#
# Sources used to verify facts (cited in article citations_json):
#   - Cuban Ministry of Foreign Affairs announcement (Sept 26, 2025)
#   - Associated Press (Sept 26, 2025)
#   - State of New Jersey v. Joanne Chesimard, 1977 conviction record
#   - Assata: An Autobiography (1987)
#   - FBI Most Wanted Terrorists listing (added May 2, 2013)
set -e

php artisan tinker --execute='
$author = App\Models\Author::firstOrCreate(["name" => "NPPC Editorial"]);

$category = App\Models\Category::firstOrCreate(
    ["slug" => "news"],
    ["title" => "News"]
);

$title = "Assata Shakur, BLA exile, dies in Havana at 78";

if (App\Models\Article::where("title", $title)->exists()) {
    echo "Article already exists; skipping.\n";
    exit(0);
}

$intro = "Assata Shakur, the former Black Panther Party and Black Liberation Army member who lived in Cuban political asylum for the last 41 years of her life, has died in Havana at the age of 78. The Cuban Ministry of Foreign Affairs confirmed her death on September 26, 2025, attributing it to natural causes related to her age and to longstanding health conditions.";

$body = <<<HTML
<p>Born JoAnne Deborah Byron in Queens, New York on July 16, 1947, Shakur came of age politically through the Black student movement at Manhattan Community College and at the City College of New York in the late 1960s. She joined the Harlem chapter of the Black Panther Party in 1970 and shortly afterward affiliated with the Black Liberation Army, the underground formation made up largely of Panthers who had concluded that the partys above-ground organizing could not survive the FBIs COINTELPRO program of surveillance, disruption, and assassination.</p>

<p>On the night of May 2, 1973, a New Jersey state trooper pulled over a vehicle on the New Jersey Turnpike carrying Shakur, her BLA comrade Zayd Malik Shakur, and Sundiata Acoli (Clark Squire). In the gunfight that followed, Trooper Werner Foerster was killed, Trooper James Harper was wounded, Zayd Malik Shakur was killed, and Assata Shakur was shot multiple times and arrested. After a series of mistrials and changes of venue, she was convicted in 1977 in Middlesex County (NJ) Superior Court of first-degree murder and a number of related charges and was sentenced to life plus 33 years. Her supporters and many subsequent legal scholars have argued that the prosecutions evidence was inconsistent with the medical evidence — in particular that Shakur had been shot with her hands raised and was incapable of having fired a weapon — and that the verdict reflected jury bias compounded by years of prejudicial pretrial publicity.</p>

<p>On November 2, 1979, BLA comrades posing as visitors entered the Clinton Correctional Facility for Women in Hunterdon County, New Jersey, took two corrections officers hostage, and freed Shakur. She lived underground in the United States for approximately five years before being granted political asylum in Cuba by the government of Fidel Castro in 1984. She remained there for the rest of her life, raising a daughter, writing, and occasionally speaking with foreign journalists.</p>

<p>Her 1987 memoir, "Assata: An Autobiography," became a foundational text of Black liberation literature. Successive U.S. administrations sought her extradition; Cuba refused. In 1998 the New Jersey State Police placed a one-million-dollar bounty on her, and in 2013 — on the fortieth anniversary of the New Jersey Turnpike shootout — the FBI added her to its Most Wanted Terrorists list and doubled the bounty to two million dollars, making her the first woman and the first African American woman to appear on that list.</p>

<p>Shakur is survived by her daughter, Kakuya Shakur, by her godson the rapper Tupac Shakur (who predeceased her in 1996), and by an extended community of organizers, scholars, and political prisoners for whom her writing and her example remained central. The Cuban Foreign Ministry, in its announcement, said she had been treated with the dignity owed a freedom fighter granted asylum, and that she had asked to be remembered by her people in struggle.</p>

<p>The National Political Prisoner Coalition expresses its condolences to her family and to the international community of organizers who knew her. Her case file remains <a href="/prisoner/assata-shakur">on this site</a>.</p>
HTML;

$citations = [
    [
        "title"  => "Cuba confirms death of US fugitive Assata Shakur in Havana",
        "source" => "Associated Press",
        "date"   => "2025-09-26",
    ],
    [
        "title"  => "Anuncio del Ministerio de Relaciones Exteriores de Cuba",
        "source" => "Ministerio de Relaciones Exteriores de Cuba",
        "date"   => "2025-09-26",
    ],
    [
        "title"  => "Assata: An Autobiography",
        "author" => "Assata Shakur",
        "source" => "Lawrence Hill Books",
        "date"   => "1987",
    ],
    [
        "title"  => "FBI Most Wanted Terrorists",
        "source" => "Federal Bureau of Investigation",
        "date"   => "2013-05-02",
    ],
];

App\Models\Article::create([
    "title"          => $title,
    "intro"          => $intro,
    "body"           => $body,
    "author_id"      => $author->id,
    "category_id"    => $category->id,
    "published_at"   => "2025-09-26 06:00:00",
    "citations_json" => json_encode($citations),
]);

echo "Created article: {$title}\n";
'

echo
echo "Assata Shakur obituary article add complete."
