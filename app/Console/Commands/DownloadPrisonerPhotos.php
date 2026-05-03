<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DownloadPrisonerPhotos extends Command
{
    protected $signature = 'prisoners:download-wiki-photos {--overwrite : Replace photo even if one is already set}';

    protected $description = 'Download photos for prisoners from Wikipedia, restricted to Wikimedia Commons (free-license) images only.';

    /**
     * Map of prisoner slug → English Wikipedia article title.
     *
     * Only includes people for whom Wikipedia's main article image is
     * almost certainly hosted on Wikimedia Commons (free license).
     * Photos hosted only on en.wikipedia.org under fair use are
     * filtered out at runtime — we never download those.
     */
    private const PAGES = [
        // Pre-1900
        'john-brown'                => 'John Brown (abolitionist)',
        'aaron-stevens'             => 'Aaron Dwight Stevens',
        'john-e-cook'               => 'John E. Cook (raider)',
        'john-anthony-copeland-jr'  => 'John Anthony Copeland Jr.',
        'shields-green'             => 'Shields Green',
        'edwin-coppoc'              => 'Edwin Coppoc',
        'albert-hazlett'            => 'Albert Hazlett',
        'clement-vallandigham'      => 'Clement Vallandigham',
        'august-spies'              => 'August Spies',
        'albert-parsons'            => 'Albert Parsons',
        'adolph-fischer'            => 'Adolph Fischer',
        'george-engel'              => 'George Engel',
        'louis-lingg'               => 'Louis Lingg',
        'samuel-fielden'            => 'Samuel Fielden',
        'michael-schwab'            => 'Michael Schwab',
        'oscar-neebe'               => 'Oscar Neebe',

        // IWW / anarchist / WWI era
        'joe-hill'                  => 'Joe Hill',
        'emma-goldman'              => 'Emma Goldman',
        'alexander-berkman'         => 'Alexander Berkman',
        'roger-baldwin'             => 'Roger Nash Baldwin',
        'mary-harris-jones'         => 'Mary Harris Jones',

        // Puerto Rican independence
        'pedro-albizu-campos'       => 'Pedro Albizu Campos',
        'lolita-lebron'             => 'Lolita Lebrón',
        'rafael-cancel-miranda'     => 'Rafael Cancel Miranda',
        'andres-figueroa-cordero'   => 'Andrés Figueroa Cordero',
        'irvin-flores-rodriguez'    => 'Irvin Flores Rodríguez',

        // Catholic Worker
        'dorothy-day'               => 'Dorothy Day',

        // Black Power / Chicago 7-8 / AIM / SNCC
        'abbie-hoffman'             => 'Abbie Hoffman',
        'jerry-rubin'               => 'Jerry Rubin',
        'tom-hayden'                => 'Tom Hayden',
        'rennie-davis'              => 'Rennie Davis',
        'david-dellinger'           => 'David Dellinger',
        'john-froines'              => 'John Froines',
        'lee-weiner'                => 'Lee Weiner',
        'bobby-seale'               => 'Bobby Seale',
        'huey-p-newton'             => 'Huey P. Newton',
        'eldridge-cleaver'          => 'Eldridge Cleaver',
        'russell-means'             => 'Russell Means',
        'dennis-banks'              => 'Dennis Banks',
        'john-trudell'              => 'John Trudell',
        'anna-mae-aquash'           => 'Anna Mae Aquash',
        'stokely-carmichael'        => 'Stokely Carmichael',
        'bob-moses'                 => 'Bob Moses (activist)',
        'james-forman'              => 'James Forman',
        'diane-nash'                => 'Diane Nash',
        'james-lawson-jr'           => 'James Lawson (activist)',

        // Recent
        'anthony-russo'             => 'Anthony Russo (whistleblower)',
        'ramona-africa'             => 'Ramona Africa',
        'camilo-mejia'              => 'Camilo Mejía',

        // Reuben Crandall and Keith McHenry exist already; only fetch
        // if no photo is set.
        'reuben-crandall'           => 'Reuben Crandall',
        'keith-mchenry'             => 'Keith McHenry',
    ];

    public function handle(): int
    {
        $downloaded = 0;
        $skippedExisting = 0;
        $skippedNotFound = 0;
        $skippedNonFree = 0;
        $skippedNoArticleImage = 0;
        $skippedNoPrisoner = 0;

        Storage::disk('public')->makeDirectory('prisoners');

        foreach (self::PAGES as $slug => $title) {
            $prisoner = Prisoner::where('slug', $slug)->first();

            if (! $prisoner) {
                $this->warn("  no prisoner row for slug {$slug}");
                $skippedNoPrisoner++;
                continue;
            }

            if ($prisoner->photo && ! $this->option('overwrite')) {
                $this->line("  {$slug}: already has photo, skipping");
                $skippedExisting++;
                continue;
            }

            try {
                $thumbUrl = $this->resolveCommonsThumbnail($title);
            } catch (\Throwable $e) {
                $this->error("  {$slug}: {$e->getMessage()}");
                $skippedNotFound++;
                continue;
            }

            if ($thumbUrl === null) {
                $this->warn("  {$slug}: no article-image returned by Wikipedia");
                $skippedNoArticleImage++;
                continue;
            }

            // Only accept images hosted in Wikimedia Commons. Fair-use
            // images on en.wikipedia live under /wikipedia/en/ and are
            // not free to redistribute.
            if (! str_contains($thumbUrl, '/wikipedia/commons/')) {
                $this->warn("  {$slug}: Wikipedia main image is not on Commons (likely fair-use); skipping");
                $skippedNonFree++;
                continue;
            }

            try {
                $bytes = Http::timeout(30)
                    ->withHeaders(['User-Agent' => 'NPPC-Website/1.0 (prisoner photo importer)'])
                    ->get($thumbUrl)
                    ->throw()
                    ->body();
            } catch (\Throwable $e) {
                $this->error("  {$slug}: download failed — {$e->getMessage()}");
                $skippedNotFound++;
                continue;
            }

            $extension = strtolower(pathinfo(parse_url($thumbUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION)) ?: 'jpg';
            if (! in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                $extension = 'jpg';
            }

            $path = "prisoners/{$slug}.{$extension}";
            Storage::disk('public')->put($path, $bytes);

            $prisoner->photo = $path;
            $prisoner->save();

            $this->info("  {$slug}: downloaded ".strlen($bytes)." bytes -> {$path}");
            $downloaded++;
        }

        $this->line('');
        $this->info("Done.");
        $this->line("  downloaded:                 {$downloaded}");
        $this->line("  skipped (already had one):  {$skippedExisting}");
        $this->line("  skipped (no prisoner):      {$skippedNoPrisoner}");
        $this->line("  skipped (no article image): {$skippedNoArticleImage}");
        $this->line("  skipped (non-free image):   {$skippedNonFree}");
        $this->line("  skipped (download error):   {$skippedNotFound}");

        return self::SUCCESS;
    }

    /**
     * Ask the Wikipedia API for the article's main page-image
     * thumbnail at ~600px. Returns the thumbnail URL or null.
     */
    private function resolveCommonsThumbnail(string $title): ?string
    {
        $response = Http::timeout(20)
            ->withHeaders(['User-Agent' => 'NPPC-Website/1.0 (prisoner photo importer)'])
            ->get('https://en.wikipedia.org/w/api.php', [
                'action'      => 'query',
                'titles'      => $title,
                'prop'        => 'pageimages',
                'pithumbsize' => 600,
                'redirects'   => 1,
                'format'      => 'json',
                'formatversion' => 2,
            ])
            ->throw();

        $pages = $response->json('query.pages') ?? [];
        $page = $pages[0] ?? null;
        if (! $page || ($page['missing'] ?? false)) {
            throw new \RuntimeException("Wikipedia article '{$title}' not found");
        }

        return $page['thumbnail']['source'] ?? null;
    }
}
