<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Attaches freely-licensed cover images to the 47 articles that have none.
 *
 * Every image is public-domain / CC0 / CC BY / CC BY-SA from Wikimedia
 * Commons, curated per-article for subject relevance and verified visually.
 * Attribution and licenses are recorded in
 * database/data/photos/CREDITS-article-covers.md, and each article's
 * image_caption is set to the credit line (only when the caption is empty).
 *
 * Three person-centric articles (Marius Mason, Eric King, Beto Coral) first
 * try to reuse the person's existing prisoner photo already in site storage,
 * falling back to the Commons image when the prisoner photo is missing.
 *
 * Images are downloaded from Commons at runtime (Special:FilePath, 1200px)
 * so no binaries are committed. Only fills articles that currently have NO
 * cover (pass --overwrite to replace). Missing articles are skipped with a
 * warning. Idempotent.
 */
final class AttachArticleCoverImages extends Command
{
    protected $signature = 'articles:attach-cover-images {--overwrite : Replace existing covers too} {--only= : Comma-separated slugs to limit to}';

    protected $description = 'Download and attach curated freely-licensed cover images for articles missing one';

    /** slug => [Commons file, prisoner-photo source or null, credit line]. */
    private array $map = [
        'aafia-siddiqui-2024-clemency-push-fmc-carswell' => ['FMCCarswelllargeimage.jpg', null, 'Photo: Federal Bureau of Prisons/Agencia Federal de Prisiones (public domain), via Wikimedia Commons'],
        'aaron-bushnell-self-immolation-feb-2024-fbi-investigation' => ['18.Rally.AaronBushnellVigil.WDC.26February2024 (53557121254).jpg', null, 'Photo: Elvert Barnes from Silver Spring MD, USA / CC BY-SA 2.0, via Wikimedia Commons'],
        'assange-plea-deal-saipan-release-june-2024' => ['RUEDA DE PRENSA CONJUNTA ENTRE CANCILLER RICARDO PATIÑO Y JULIAN ASSANGE (cropped).jpg', null, 'Photo: David G. Silvers, Cancillería del Ecuador / CC BY-SA 2.0, via Wikimedia Commons'],
        'assata-shakur-bla-exile-dies-in-havana-at-78' => ['Assata Shakur.jpg', null, 'Photo: dignidadrebelde / CC BY 2.0, via Wikimedia Commons'],
        'atlanta-solidarity-fund-organizers-2024-bail-fund-charges' => ['Atlanta Forest (52668033124).jpg', null, 'Photo: Chad Davis from Minneapolis, United States / CC BY 2.0, via Wikimedia Commons'],
        'beto-coral-ice-detention-arizona-june-2026' => ['Arizona Removal Operations Coordination Center.jpg', 'prisoner:beto-coral', 'Photo: OSC-Res1 / CC0, via Wikimedia Commons'],
        'beyond-bars' => ['Philadelphia County Prison (Moyamensing Prison) Philadelphia PA (7) 139920pu.jpg', null, 'Photo: Library of Congress (public domain), via Wikimedia Commons'],
        'broadview-six-ice-protest-case-collapses-2026' => ['Anti-ICE protest at the Broadview USCIS Processing Center 9 19 2025 20250919 0813 (54798312382).jpg', null, 'Photo: Paul Goyette from Chicago, USA / CC BY 4.0, via Wikimedia Commons'],
        'campus-encampment-mass-arrests-spring-2024' => ['LAPD arresting student protestors.jpg', null, 'Photo: Multiple authors: Darlene L, Matt Baretto / CC BY 4.0, via Wikimedia Commons'],
        'campus-repression-year-oct-2023-dec-2024-data' => ['Police following the arrests of Pro-Palestinian protesters at Columbia.jpg', null, 'Photo: Wm3214 / CC0, via Wikimedia Commons'],
        'casey-goonan-uc-berkeley-arson-2024-indictment' => ['Pro-Palestinian Protest in front of Sproul Hall at UC Berkeley.jpg', null, 'Photo: Kefr4000 / CC0, via Wikimedia Commons'],
        'daniel-hale-drone-whistleblower-2024-clemency-push' => ['Daniel Everette Hale (cropped).jpg', null, 'Photo: Stand with Daniel Hale / Bob Hayes / CC BY-SA 4.0, via Wikimedia Commons'],
        'detained-for-dissent' => ['Immigration Reform Leaders Arrested 14.jpg', null, 'Photo: Arasmus Photo / CC BY 2.0, via Wikimedia Commons'],
        'doj-fires-immigration-judges-who-ruled-for-palestine-activists-2026' => ['Robert F. Kennedy Department of Justice Building.jpg', null, 'Photo: APK / CC BY 4.0, via Wikimedia Commons'],
        'doj-indicts-39-cities-church-anti-ice-protesters-st-paul-2026' => ['01 19 22 Warren E. Burger Federal Building, St. Paul (51833331800).jpg', null, 'Photo: Chad Davis / CC BY 2.0, via Wikimedia Commons'],
        'domestic-terrorism-charging-escalation-2024-pattern' => ['Stop Cop City Jan 2023.jpg', null, 'Photo: Tatsoi / CC BY-SA 4.0, via Wikimedia Commons'],
        'eric-king-first-year-out-2024-post-adx-life' => ['Florence ADMAX.jpg', 'prisoner:eric-king', 'Photo: Federal Bureau of Prisons (public domain), via Wikimedia Commons'],
        'esqueda-joliet-whistleblower-charges-dropped-2024' => ['Joliet St south at US 30 east (Jefferson St) - Joliet, IL - September 2024.jpg', null, 'Photo: AlphaBeta135 / CC BY 4.0, via Wikimedia Commons'],
        'federal-grand-jury-wave-palestine-solidarity-2024' => ['National March on Washington FREE PALESTINE IMG 8566 (53430034196).jpg', null, 'Photo: Elvert Barnes from Silver Spring MD, USA / CC BY-SA 2.0, via Wikimedia Commons'],
        'federal-judge-orders-restoration-of-tufts-student-rumeysa-ozturks-sevis-record-clearing-the-way-for-campus-work' => ['Rümeysa Öztürk addresses reporters at press conference (2025).jpg', null, 'Photo: Office of Representative Ayanna Pressley (public domain), via Wikimedia Commons'],
        'forest-defender-domestic-terrorism-charging-precedents-2024' => ['Weelaunee Forest signs 2023.jpg', null, 'Photo: Tatsoi / CC BY-SA 4.0, via Wikimedia Commons'],
        'francesca-albanese-trump-sanctions-blocked-first-amendment-2026' => ['Francesca Albanese.jpg', null, 'Photo: Fotografía oficial de la Presidencia de Colombia - Andrea Puentes (public domain), via Wikimedia Commons'],
        'guidelines-on-the-definition-of-political-prisoners' => ['Attica, New York (Correctional Facility).jpg', null, 'Photo: Jayu from Harrisburg, PA, U.S.A. / CC BY-SA 2.0, via Wikimedia Commons'],
        'jessica-reznicek-mid-sentence-2024-terrorism-enhancement-appeal' => ['Protest against the Dakota Access Pipeline.jpg', null, 'Photo: Fibonacci Blue from Minnesota, USA / CC BY 2.0, via Wikimedia Commons'],
        'joshua-schulte-40-years-vault-7-sentencing-2024' => ['Aerial view of CIA headquarters, Langley, Virginia 14768v.jpg', null, 'Photo: Carol M. Highsmith (public domain), via Wikimedia Commons'],
        'justice-distorted-cldc-face-act-sentencing-2024' => ['May 2022 abortion protest at Foley Square 04.jpg', null, 'Photo: Legoktm / CC BY-SA 4.0, via Wikimedia Commons'],
        'leonard-peltier-parole-denied-july-2024' => ['Leonard Peltier Wet Plate Collodion Photograph by Shane Balkowitsch.jpg', null, 'Photo: Balkowitsch / CC BY 4.0, via Wikimedia Commons'],
        'maduros-new-york-federal-case-heads-toward-march-hearing-as-defense-signals-immunity-capture-challenges' => ['Nicolás Maduro on 12 November 2024.png', null, 'Photo: Presidency of Bolivarian Republic of Venezuela (public domain), via Wikimedia Commons'],
        'marius-mason-year-16-pre-release-ramp-2024' => ['Banner reading respect existence or expect restiance.jpg', 'prisoner:marius-mason', 'Photo: Infoletta Hambach / CC BY-SA 2.0, via Wikimedia Commons'],
        'merrimack-4-elbit-blockade-new-hampshire-defendants' => ['End the Siege Banner-11-9-23. Raytheon Protest, Goleta, CA.jpg', null, 'Photo: Marcywinograd / CC0, via Wikimedia Commons'],
        'move-9-2024-status-survivors' => ['MOVE members 1978.jpg', null, 'Photo: Paul Shane (public domain), via Wikimedia Commons'],
        'mumia-abu-jamal-43rd-year-pcra-brought-to-light-2024' => ['SupremeCourt protest for Mumia Abu-Jamal 2000.JPG', null, 'Photo: Carolmooredc / CC BY-SA 4.0, via Wikimedia Commons'],
        'my-name-is-mahmoud-khalil-and-i-am-a-political-prisoner' => ['Mahmoud Khalil (2025).jpg', null, 'Photo: Office of Representative Jim McGovern (public domain), via Wikimedia Commons'],
        'nppc-launches-political-prisoner-database-live-tracker' => ['Rolls of razor barbed wire fence at a prison correctional facility.jpg', null, 'Photo: Tony Webster / CC BY 2.0, via Wikimedia Commons'],
        'political-prisoner-2024-year-end-census' => ['Prison - Special Alternative Incarceration - Michigan Department of Corrections (52847289687).jpg', null, 'Photo: Tony Webster / CC BY 2.0, via Wikimedia Commons'],
        'political-trials-and-prisoners-in-the-united-states' => ['Chicago Eight circa 1968.jpg', null, 'Photo: unknown (public domain), via Wikimedia Commons'],
        'reality-winner-post-release-2024-supervised-release-restrictions' => ['Reality Winner 1130336.jpg', null, 'Photo: Sizzlipedia / CC BY-SA 4.0, via Wikimedia Commons'],
        'ruchell-cinque-magee-one-year-memoriam-2024' => ['Ruchell Cinque Magee.png', null, 'Photo: unknown / Copyrighted free use, via Wikimedia Commons'],
        'stop-cop-city-rico-pretrial-2024-defense-erosion' => ['Cop City (52668033304).jpg', null, 'Photo: Chad Davis from Minneapolis, United States / CC BY 2.0, via Wikimedia Commons'],
        'tampa-five-florida-prosecutors-drop-felony-charges-against-usf-anti-dei-protesters' => ['Marshallstudentcenter4 usf tampa.jpg', null, 'Photo: FightingRaven531 / CC BY-SA 3.0, via Wikimedia Commons'],
        'third-circuit-ruling-narrows-habeas-pathway-in-mahmoud-khalil-case-raising-prospect-of-renewed-detention-by-national-political-prisoner-coalition' => ['Mahmoud Khalil NYC detention protest 073.jpg', null, 'Photo: SWinxy / CC BY 4.0, via Wikimedia Commons'],
        'tortuguita-teran-one-year-civil-investigation-2024' => ['Tortuguita shrine Atlanta.jpg', null, 'Photo: Tatsoi / CC BY-SA 4.0, via Wikimedia Commons'],
        'trump-counterterrorism-strategy-classifies-antifa-major-terror-2026' => ['Black Lives Matter Protests, activist holding Antifa Enternasyonal flag (50115371632).jpg', null, 'Photo: Ivan Radic / CC BY 2.0, via Wikimedia Commons'],
        'under-cover-of-war' => ['March on Washington Free Palestine - 4.jpg', null, 'Photo: APK / CC BY 4.0, via Wikimedia Commons'],
        'weapons-manufacturer-blockaders-pittsburgh-holtec-boeing-2024' => ['PeaceMarchPgh 191005-50282 (48848347863).jpg', null, 'Photo: Mark Dixon from Pittsburgh, PA / CC BY 2.0, via Wikimedia Commons'],
        'wetsuweten-solidarity-defendants-2024-coastal-gaslink' => ['San Francisco Wetʼsuwetʼen solidarity rally February 7, 2020.jpg', null, 'Photo: Peg Hunter / CC BY-SA 2.0, via Wikimedia Commons'],
        'who-is-a-political-prisoner' => ['Attica Prison Riot, 1971.jpg', null, 'Photo: unknown (public domain), via Wikimedia Commons'],
    ];

    public function handle(): int
    {
        $overwrite = (bool) $this->option('overwrite');
        $only = array_filter(array_map('trim', explode(',', (string) $this->option('only'))));
        Storage::disk('public')->makeDirectory('articles/covers');
        $set = 0; $skip = 0; $fail = 0;

        foreach ($this->map as $slug => [$file, $prisonerSrc, $credit]) {
            if ($only && ! in_array($slug, $only, true)) {
                continue;
            }
            $article = Article::where('slug', $slug)->first();
            if (! $article) {
                $this->warn("Not found, skipping: {$slug}");
                $skip++;
                continue;
            }
            if (trim((string) $article->image) !== '' && ! $overwrite) {
                $this->line("Has cover, leaving: {$slug}");
                $skip++;
                continue;
            }

            [$data, $ext, $usedCredit] = $this->resolve($prisonerSrc, $file, $credit);
            if ($data === null) {
                $this->warn("Image unavailable: {$slug}");
                $fail++;
                continue;
            }

            $path = 'articles/covers/'.$slug.'.'.$ext;
            Storage::disk('public')->put($path, $data);
            $article->image = $path;
            if (trim((string) $article->image_caption) === '' && $usedCredit !== '') {
                $article->image_caption = $usedCredit;
            }
            $article->save();
            $this->info("Set cover: {$slug} -> {$path}");
            $set++;
        }

        $this->info("
Done. Set={$set}  Skipped={$skip}  Failed={$fail}");

        return self::SUCCESS;
    }

    /**
     * Resolve the image bytes: prisoner photo already in storage first (when
     * configured), then the Commons download.
     *
     * @return array{0:?string,1:string,2:string} [bytes|null, extension, credit]
     */
    private function resolve(?string $prisonerSrc, string $file, string $credit): array
    {
        if ($prisonerSrc !== null && str_starts_with($prisonerSrc, 'prisoner:')) {
            $slug = substr($prisonerSrc, strlen('prisoner:'));
            $prisoner = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            $photo = $prisoner?->photo;
            if ($photo && Storage::disk('public')->exists($photo)) {
                $ext = strtolower(pathinfo($photo, PATHINFO_EXTENSION) ?: 'jpg');
                // Reusing our own prisoner portrait — keep any caption empty
                // rather than mis-crediting it to Commons.
                return [Storage::disk('public')->get($photo), $ext, ''];
            }
            $this->line("  (no prisoner photo for {$slug}; using Commons fallback)");
        }

        $url = 'https://commons.wikimedia.org/wiki/Special:FilePath/'.rawurlencode($file).'?width=1200';
        [$data, $type] = $this->fetch($url);
        if ($data === null) {
            return [null, 'jpg', $credit];
        }
        $ext = match (true) {
            str_contains($type, 'png') => 'png',
            str_contains($type, 'gif') => 'gif',
            str_contains($type, 'webp') => 'webp',
            default => 'jpg',
        };

        return [$data, $ext, $credit];
    }

    /** @return array{0:?string,1:string} [binary data or null, content-type] */
    private function fetch(string $url): array
    {
        $ctx = stream_context_create(['http' => [
            'timeout' => 60,
            'follow_location' => 1,
            'header' => "User-Agent: NPPC-website/1.0 (article cover import; +https://nppc)
",
        ]]);
        for ($i = 0; $i < 4; $i++) {
            $data = @file_get_contents($url, false, $ctx);
            if ($data !== false && strlen($data) > 2500) {
                $type = '';
                foreach (($http_response_header ?? []) as $h) {
                    if (stripos($h, 'content-type:') === 0) {
                        $type = strtolower($h);
                    }
                }

                return [$data, $type];
            }
            sleep(1 + $i);
        }

        return [null, ''];
    }
}
