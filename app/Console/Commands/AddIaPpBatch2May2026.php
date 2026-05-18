<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Register a second batch of foundational U.S. political-prisoner
 * texts surfaced from Internet Archive in May 2026 (after the
 * #471 first batch). 17 PDFs, mirrored locally at
 * /pdfs/ia-pp-may-2026-batch2/.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddIaPpBatch2May2026 extends Command {
    protected $signature = 'archive:add-ia-pp-batch2-may-2026';
    protected $description = 'Add 17 additional IA political-prisoner records (Newton, Shakur, Weather Underground, BLA, Acoli, James, Estes, Mumia, Sacco-Vanzetti, Chicago Anarchists, etc.)';

    public function handle(): int {
        $records = [
            [
                'slug' => 'revolutionary-suicide-newton-1973',
                'title' => "Revolutionary Suicide",
                'description' => "Huey P. Newton's 1973 autobiography (Harcourt Brace Jovanovich), written in part from inside the Alameda County Jail. Co-founder of the Black Panther Party for Self-Defense (Oakland, October 1966), Newton wrote the book during and after his 1968–1970 imprisonment on charges arising from the October 28, 1967 shooting that killed Oakland police officer John Frey — for which Newton was convicted of voluntary manslaughter in 1968 (conviction reversed on appeal in 1970, ultimately dismissed in 1971). The book combines personal autobiography with Panther political philosophy, including the concept of 'revolutionary suicide' as opposed to 'reactionary suicide' — the willingness to risk death in confronting the conditions producing premature Black death. A central text of the Black Liberation tradition.",
                'record_type' => 'book',
                'source_format' => 'memoir',
                'file' => '/pdfs/ia-pp-may-2026-batch2/revolutionary-suicide-newton-1973.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'Huey P. Newton',
                'publisher' => 'Harcourt Brace Jovanovich',
                'year' => 1973,
                'date' => '1973-01-01',
                'subjects' => ['Black Panther Party', 'BPP', 'Huey P. Newton', 'Black Liberation', 'Political Prisoners', 'Oakland'],
            ],
            [
                'slug' => 'assata-an-autobiography-shakur-1987',
                'title' => "Assata: An Autobiography",
                'description' => "Assata Shakur's 1987 autobiography (Lawrence Hill Books / Zed). Black Liberation Army member, former Black Panther Party member, convicted in 1977 of the May 2, 1973 killing of New Jersey State Trooper Werner Foerster on the New Jersey Turnpike after a confrontation that left Assata wounded and fellow BLA member Zayd Malik Shakur dead. Sentenced to life in New Jersey state prison; escaped from the Clinton Correctional Facility for Women in November 1979 and surfaced in Cuba in 1984, where she received political asylum from Fidel Castro. The autobiography was written from Cuba, recounting her organizing life, the trial, her treatment in custody, and the political theory of the Black Liberation Army. Among the most widely-distributed prison-survivor autobiographies of the post-Civil Rights generation. Assata died in Cuba in September 2025; this autobiography is the canonical first-person account.",
                'record_type' => 'book',
                'source_format' => 'memoir',
                'file' => '/pdfs/ia-pp-may-2026-batch2/assata-an-autobiography-shakur-1987.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'Assata Shakur',
                'publisher' => 'Lawrence Hill Books / Zed Books',
                'year' => 1987,
                'date' => '1987-01-01',
                'subjects' => ['Assata Shakur', 'Black Liberation Army', 'BLA', 'Black Panther Party', 'Political Exile', 'Cuba'],
            ],
            [
                'slug' => 'prairie-fire-weather-underground-1974',
                'title' => "Prairie Fire: The Politics of Revolutionary Anti-Imperialism — The Political Statement of the Weather Underground",
                'description' => "1974 political statement of the Weather Underground Organization, produced clandestinely and distributed via the U.S. radical-left press during the group's underground period. The 156-page manifesto sets out the WUO's analysis of U.S. imperialism in the wake of Vietnam, the Watergate-era state-crisis, the Indochinese revolutions, the Black liberation movement, and the New Afrikan and Native American sovereignty movements. Co-authored by Bernardine Dohrn, Bill Ayers, Jeff Jones, and Celia Sojourn; the text was the foundation for the above-ground Prairie Fire Organizing Committee (PFOC) that emerged in 1975.",
                'record_type' => 'book',
                'source_format' => 'manifesto',
                'file' => '/pdfs/ia-pp-may-2026-batch2/prairie-fire-weather-underground-1974.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'Weather Underground Organization (Dohrn, Ayers, Jones, Sojourn)',
                'publisher' => 'Communications Co. (underground)',
                'year' => 1974,
                'date' => '1974-07-01',
                'subjects' => ['Weather Underground', 'WUO', 'Bernardine Dohrn', 'Bill Ayers', 'Anti-Imperialism', 'New Left'],
            ],
            [
                'slug' => 'collected-works-of-bla-vol-1',
                'title' => "Collected Works of the Black Liberation Army, Volume 1",
                'description' => "Anthology of communiques, theoretical statements, prison writings, and historical documents of the Black Liberation Army (BLA) — the armed Black liberation formation active in the United States from roughly 1971 through the early 1980s, which counted Assata Shakur, Mutulu Shakur, Sundiata Acoli, Sekou Odinga, Russell Maroon Shoatz, Dhoruba bin-Wahad, Jalil Muntaqim, and others among its named participants and political prisoners. Compiled by movement editors; circulating among prisoner-support and Black-radical-press networks. Volume 1.",
                'record_type' => 'book',
                'source_format' => 'anthology',
                'file' => '/pdfs/ia-pp-may-2026-batch2/collected-works-of-bla-vol-1.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'Black Liberation Army (collected)',
                'publisher' => 'movement-distribution edition',
                'subjects' => ['Black Liberation Army', 'BLA', 'Black Liberation', 'Assata Shakur', 'Mutulu Shakur', 'Sundiata Acoli', 'Political Prisoners'],
            ],
            [
                'slug' => 'imprisoned-intellectuals-james-2003',
                'title' => "Imprisoned Intellectuals: America's Political Prisoners Write on Life, Liberation, and Rebellion",
                'description' => "2003 anthology (Rowman & Littlefield) edited by political philosopher Joy James. Collects essays, letters, and theoretical writings from incarcerated U.S. political prisoners across the Black liberation, Puerto Rican independence, American Indian Movement, white anti-imperialist, and Black-feminist generations — including Mumia Abu-Jamal, Angela Davis, George Jackson, Assata Shakur, Leonard Peltier, Marilyn Buck, Dhoruba bin-Wahad, Susan Rosenberg, and others. One of the most cited academic-context anthologies of incarcerated political writing.",
                'record_type' => 'book',
                'source_format' => 'anthology',
                'file' => '/pdfs/ia-pp-may-2026-batch2/imprisoned-intellectuals-james-2003.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'Joy James (ed.)',
                'publisher' => 'Rowman & Littlefield',
                'year' => 2003,
                'date' => '2003-01-01',
                'subjects' => ['Political Prisoners', 'Joy James', 'Mumia Abu-Jamal', 'Angela Davis', 'George Jackson', 'Assata Shakur', 'Leonard Peltier', 'Marilyn Buck'],
            ],
            [
                'slug' => 'our-history-is-the-future-estes-2019',
                'title' => "Our History Is the Future: Standing Rock Versus the Dakota Access Pipeline, and the Long Tradition of Indigenous Resistance",
                'description' => "Nick Estes's 2019 Verso history of the Standing Rock Sioux Tribe's resistance to the Dakota Access Pipeline (DAPL) and the longer arc of Lakota / Dakota / Nakota sovereignty struggle. Combines reporting from the 2016–2017 #NoDAPL water-protector camps with deep historical narrative running from the 1851 Fort Laramie Treaty through Wounded Knee 1890, the 1973 Wounded Knee Occupation, the Black Hills, the Oahe Dam, and the contemporary pipeline-defense generation. Foundational reference for the Standing Rock / DAPL prisoner-and-water-protector record NPPC documents.",
                'record_type' => 'book',
                'source_format' => 'monograph',
                'file' => '/pdfs/ia-pp-may-2026-batch2/our-history-is-the-future-estes-2019.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'Nick Estes',
                'publisher' => 'Verso',
                'year' => 2019,
                'date' => '2019-03-05',
                'subjects' => ['Standing Rock', 'DAPL', 'Indigenous Resistance', 'Lakota', 'AIM', 'Wounded Knee', 'Water Protectors'],
            ],
            [
                'slug' => 'coming-of-age-new-afrikan-revolutionary',
                'title' => "Coming of Age: A New Afrikan Revolutionary",
                'description' => "Autobiographical and political writings of a New Afrikan revolutionary documenting the generation that came of age inside U.S. federal and state custody during the Black liberation prosecutions of the 1970s and 1980s. The text articulates the New Afrikan political identity — separate Black national consciousness, anti-imperialist commitment, prison-organizing practice — central to the Republic of New Afrika (RNA) tradition and the broader New Afrikan Prison Movement.",
                'record_type' => 'book',
                'source_format' => 'memoir',
                'file' => '/pdfs/ia-pp-may-2026-batch2/coming-of-age-new-afrikan-revolutionary.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'movement publication',
                'publisher' => 'movement publication',
                'subjects' => ['New Afrikan', 'Republic of New Afrika', 'RNA', 'New Afrikan Prison Movement', 'Black Liberation'],
            ],
            [
                'slug' => 'bits-n-pieces-sundiata-acoli',
                'title' => "Bits N Pieces, by Sundiata Acoli",
                'description' => "Collected shorter writings, letters, and political statements of Sundiata Acoli — Black Liberation Army member and longtime political prisoner held by New Jersey state corrections from 1973 to 2022. Acoli was convicted in 1974 alongside Assata Shakur for the May 1973 New Jersey Turnpike incident that killed State Trooper Werner Foerster. He was released on parole in May 2022 after 49 years inside; this collection brings together pieces written across that period — on the BLA, the New Afrikan struggle, prison-condition organizing, mathematics-teaching inside, and the broader history of Black resistance.",
                'record_type' => 'book',
                'source_format' => 'collected writings',
                'file' => '/pdfs/ia-pp-may-2026-batch2/bits-n-pieces-sundiata-acoli.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'Sundiata Acoli',
                'publisher' => 'movement publication',
                'subjects' => ['Sundiata Acoli', 'Black Liberation Army', 'BLA', 'New Afrikan', 'Political Prisoners'],
            ],
            [
                'slug' => 'new-afrikan-prison-struggle-acoli',
                'title' => "An Updated History of the New Afrikan Prison Struggle",
                'description' => "Sundiata Acoli's primary essay on the history of the New Afrikan Prison Movement — the political and organizational tradition of Black political prisoners in U.S. state and federal custody developed from the late 1960s onward. Covers the 1960s-1970s prison rebellions (Attica, San Quentin, Folsom), the BLA-era prisoner cohort, the founding of the Republic of New Afrika in 1968, the development of New Afrikan political identity inside, and the long-running campaign for recognition of New Afrikans as a colonized people. A canonical reference text for movement prisoner-support practice.",
                'record_type' => 'book',
                'source_format' => 'essay',
                'file' => '/pdfs/ia-pp-may-2026-batch2/new-afrikan-prison-struggle-acoli.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'Sundiata Acoli',
                'publisher' => 'movement publication',
                'subjects' => ['Sundiata Acoli', 'New Afrikan Prison Movement', 'Black Liberation', 'Attica', 'Republic of New Afrika'],
            ],
            [
                'slug' => 'sacco-vanzetti',
                'title' => "Sacco-Vanzetti",
                'description' => "Documentary collection on the case of Nicola Sacco and Bartolomeo Vanzetti — Italian-American anarchist immigrants convicted of the 1920 South Braintree, Massachusetts payroll robbery and murders, tried in 1921 in what became the most internationally-publicized political-prisoner case of the early-20th-century United States. Despite a worldwide defense campaign — including Albert Einstein, Felix Frankfurter, John Dos Passos, Bertrand Russell, Anatole France, and the labor and anarchist movements of multiple continents — Sacco and Vanzetti were executed by the Commonwealth of Massachusetts on August 23, 1927. The case established the modern U.S. anti-political-prosecution legal-defense tradition.",
                'record_type' => 'book',
                'source_format' => 'documents',
                'file' => '/pdfs/ia-pp-may-2026-batch2/sacco-vanzetti.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'Sacco-Vanzetti Defense Committee / contributors',
                'publisher' => 'Sacco-Vanzetti Defense Committee',
                'year' => 1927,
                'date' => '1927-01-01',
                'subjects' => ['Sacco and Vanzetti', 'Anarchism', 'Italian-American', 'Political Prisoners', 'Massachusetts', '1920s'],
            ],
            [
                'slug' => 'august-spies-autobiography-1887',
                'title' => "August Spies' Autobiography (1887)",
                'description' => "Autobiography of August Spies — German-American anarchist, labor organizer, editor of the Chicago Arbeiter-Zeitung, and one of the Haymarket Martyrs hanged by the State of Illinois on November 11, 1887 for the May 4, 1886 Haymarket Affair bombing. Spies wrote the autobiography in his cell at the Cook County jail in 1887 prior to his execution, alongside the autobiographies of his co-defendants Albert Parsons, Adolph Fischer, George Engel, and Louis Lingg. A founding document of the U.S. anarchist political-prisoner tradition and an account of the 1886 eight-hour-day labor movement that culminated at Haymarket.",
                'record_type' => 'book',
                'source_format' => 'memoir',
                'file' => '/pdfs/ia-pp-may-2026-batch2/august-spies-autobiography-1887.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'August Spies',
                'publisher' => 'Nina Van Zandt Spies / 1887 prison edition',
                'year' => 1887,
                'date' => '1887-01-01',
                'subjects' => ['Haymarket', 'August Spies', 'Anarchism', 'Chicago', 'Eight-Hour Day', '1880s Labor Movement'],
            ],
            [
                'slug' => 'jericho-manual',
                'title' => "The Jericho Movement Manual",
                'description' => "Organizing manual of the National Jericho Movement, the U.S. coalition formed in 1998 to demand amnesty and freedom for all U.S. political prisoners and prisoners of war. The manual lays out Jericho's founding principles, its political-prisoner roster, its mailing and visiting protocols, its national-and-international solidarity work, and its organizational structure. Reference text for any contemporary U.S. prisoner-support volunteer.",
                'record_type' => 'book',
                'source_format' => 'handbook',
                'file' => '/pdfs/ia-pp-may-2026-batch2/jericho-manual.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'National Jericho Movement',
                'publisher' => 'National Jericho Movement',
                'subjects' => ['Jericho Movement', 'Political Prisoners', 'Prisoner Support', 'Amnesty'],
            ],
            [
                'slug' => 'voice-of-the-voiceless-mumia-abu-jamal',
                'title' => "Voice of the Voiceless: The Case of Mumia Abu-Jamal",
                'description' => "Case-history and movement-statement collection on Mumia Abu-Jamal — journalist, former Black Panther Party member, longtime MOVE supporter, and U.S. political prisoner held by the Commonwealth of Pennsylvania since 1981 on a conviction arising from the December 9, 1981 killing of Philadelphia police officer Daniel Faulkner. Combines case documents, trial-record critique, Mumia's own commentaries, and statements from the international Free Mumia coalition. Foundational reference for the longest-running Black liberation political-prisoner case of the post-Civil Rights generation.",
                'record_type' => 'book',
                'source_format' => 'case file',
                'file' => '/pdfs/ia-pp-may-2026-batch2/voice-of-the-voiceless-mumia-abu-jamal.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'Free Mumia coalition / contributors',
                'publisher' => 'movement publication',
                'subjects' => ['Mumia Abu-Jamal', 'Black Panther Party', 'MOVE', 'Free Mumia', 'Philadelphia', 'Death Row'],
            ],
            [
                'slug' => 'chicago-anarchists-famous-speeches-1886',
                'title' => "The Accused, The Accusers: The Famous Speeches of the Chicago Anarchists in Court (1886)",
                'description' => "The closing courtroom statements of the eight Chicago anarchists convicted in connection with the May 4, 1886 Haymarket Affair: Albert Parsons, August Spies, Adolph Fischer, George Engel, Louis Lingg, Samuel Fielden, Michael Schwab, and Oscar Neebe. Delivered during the August–October 1886 trial before Judge Joseph Gary in the Cook County Criminal Court, and published the same year. The speeches — particularly Spies's, Parsons's, and Lingg's — became canonical political-prisoner texts of the late-19th-century U.S. labor and anarchist movements. Four of the eight were executed by hanging on November 11, 1887; Lingg died by suicide in his cell on November 10. The remaining three were pardoned by Illinois Governor John Peter Altgeld in 1893 on the explicit ground that the trial had been unjust.",
                'record_type' => 'book',
                'source_format' => 'speeches',
                'file' => '/pdfs/ia-pp-may-2026-batch2/chicago-anarchists-famous-speeches-1886.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'August Spies, Albert Parsons, et al.',
                'publisher' => 'Socialistic Publishing Society',
                'year' => 1886,
                'date' => '1886-12-01',
                'subjects' => ['Haymarket', 'Chicago Anarchists', 'Albert Parsons', 'August Spies', 'Louis Lingg', '1880s Labor Movement', 'Political Trials'],
            ],
            [
                'slug' => 'on-the-black-liberation-army-pdg',
                'title' => "On the Black Liberation Army",
                'description' => "Movement-published reader on the Black Liberation Army — historical and theoretical writings on the BLA's formation, its political program, the trial cohort of its prisoners, and the post-1981 BLA-era prosecutions including the Brink's-1981 case that landed Mutulu Shakur, Marilyn Buck, Sekou Odinga, and others in federal custody. Compiled and distributed by Prairie Fire Distribution Group and the broader anti-imperialist prisoner-support network.",
                'record_type' => 'book',
                'source_format' => 'reader',
                'file' => '/pdfs/ia-pp-may-2026-batch2/on-the-black-liberation-army-pdg.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'Prairie Fire Distribution Group / contributors',
                'publisher' => 'Prairie Fire Distribution Group',
                'subjects' => ['Black Liberation Army', 'BLA', 'Mutulu Shakur', 'Marilyn Buck', 'Sekou Odinga', 'Anti-Imperialism', 'Prairie Fire'],
            ],
            [
                'slug' => 'symbionese-liberation-army-1974',
                'title' => "The Symbionese Liberation Army: Documents and Communiques (1974)",
                'description' => "Collected communiques, taped statements, and historical documents of the Symbionese Liberation Army — the small Bay Area armed group founded in 1973 whose actions included the November 1973 killing of Oakland school superintendent Marcus Foster and the February 1974 kidnapping of Patricia Hearst. The collection includes the SLA's foundational political documents and the disputed Hearst tapes. Six SLA members died on May 17, 1974 in a Los Angeles police shootout; surviving members (Bill and Emily Harris, Russell Little, Joseph Remiro, James Kilgore, Sara Jane Olson) were prosecuted and served substantial prison sentences over the following two decades.",
                'record_type' => 'book',
                'source_format' => 'documents',
                'file' => '/pdfs/ia-pp-may-2026-batch2/symbionese-liberation-army-1974.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'Symbionese Liberation Army (collected)',
                'publisher' => 'movement publication',
                'year' => 1974,
                'date' => '1974-01-01',
                'subjects' => ['Symbionese Liberation Army', 'SLA', 'Patricia Hearst', 'Bay Area', '1970s Armed Struggle'],
            ],
            [
                'slug' => 'helen-keller-her-socialist-years-1967',
                'title' => "Helen Keller, Her Socialist Years: Writings and Speeches",
                'description' => "1967 International Publishers collection of Helen Keller's socialist writings, edited by Philip S. Foner. Reproduces the political speeches, articles, and letters Keller wrote across her four-decade career as a member of the Socialist Party of America (joined 1909) and later supporter of the Industrial Workers of the World — material systematically suppressed by mainstream-press hagiographies that portrayed her solely as a triumph-over-disability figure. Includes her 1916 essay 'Why I Became an IWW,' her defenses of Joe Hill (executed Utah 1915) and the IWW free-speech-fight prisoners, and her opposition to U.S. entry into World War I. A foundational early-20th-century political-prisoner-solidarity text from one of the country's most famous radicals-in-disguise.",
                'record_type' => 'book',
                'source_format' => 'collected writings',
                'file' => '/pdfs/ia-pp-may-2026-batch2/helen-keller-her-socialist-years-1967.pdf',
                'collection' => 'Movement Reference',
                'authors' => 'Helen Keller; Philip S. Foner (ed.)',
                'publisher' => 'International Publishers',
                'year' => 1967,
                'date' => '1967-01-01',
                'subjects' => ['Helen Keller', 'Socialist Party', 'IWW', 'Joe Hill', 'Early 20th Century Labor', 'Prisoner Solidarity'],
            ],
        ];

        $base = ['is_digitized' => true, 'published' => true];
        $added = 0; $updated = 0;
        foreach ($records as $r) {
            $slug = $r['slug']; unset($r['slug']);
            $payload = $r + $base;
            $existing = ArchiveRecord::where('slug', $slug)->first();
            if ($existing) { $existing->update($payload); $updated++; $this->info("Updated: {$payload['title']}"); }
            else { ArchiveRecord::create(['slug' => $slug] + $payload); $added++; $this->info("Added: {$payload['title']}"); }
        }
        $this->line("Done — added {$added}, updated {$updated}.");
        return self::SUCCESS;
    }
}
