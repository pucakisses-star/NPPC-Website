<?php

namespace App\Support;

use App\Models\AnnualReport;
use App\Models\ArchiveRecord;
use App\Models\Article;
use App\Models\Author;
use App\Models\CalendarEntry;
use App\Models\Event;
use App\Models\HistoryTopic;
use App\Models\Page;
use App\Models\Partner;
use App\Models\Petition;
use App\Models\PodcastEpisode;
use App\Models\Prisoner;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Staff;
use App\Models\Timeline;
use App\Models\Topic;

/**
 * Canonical list of every (model, field) tuple whose column stores a file
 * path on disk. Used by file-cleanup commands so all references are
 * protected from deletion and so dedupe runs can rewrite DB pointers
 * consistently when a redundant copy is removed.
 */
final class MediaFields {
    /** @return array<array{0: class-string, 1: string}> */
    public static function all(): array {
        return [
            [AnnualReport::class,    'file'],
            [AnnualReport::class,    'image'],
            [ArchiveRecord::class,   'file'],
            [ArchiveRecord::class,   'thumbnail'],
            [Article::class,         'image'],
            [Author::class,          'avatar'],
            [CalendarEntry::class,   'image'],
            [Event::class,           'image'],
            [HistoryTopic::class,    'image'],
            [Page::class,            'header_image'],
            [Partner::class,         'logo'],
            [Petition::class,        'image'],
            [PodcastEpisode::class,  'cover_image'],
            [Prisoner::class,        'photo'],
            [Product::class,         'image'],
            [Quote::class,           'author_image'],
            [Staff::class,           'image'],
            [Timeline::class,        'image'],
            [Topic::class,           'image'],
        ];
    }

    /**
     * Resolve a DB-stored path to an absolute filesystem path, trying both
     * possible locations (under public/ directly, or under storage/app/public/
     * via the disk('public') symlink). Returns the first existing path, or
     * null if neither exists.
     */
    public static function resolveAbsPath(string $path): ?string {
        foreach (self::candidatePaths($path) as $abs) {
            if (is_file($abs)) {
                return $abs;
            }
        }
        return null;
    }

    /**
     * Every absolute filesystem location a DB-stored path could refer to.
     *
     * @return string[]
     */
    public static function candidatePaths(string $path): array {
        $path = trim($path);
        if ($path === '' || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return [];
        }

        if (str_starts_with($path, '/')) {
            return [base_path('public'.$path)];
        }

        return [
            storage_path('app/public/'.$path),
            base_path('public/'.$path),
        ];
    }

    /**
     * Given a public/-relative path that DedupeFiles' walker produced
     * (e.g. "storage/quotes/x.jpg" or "pdfs/foo.pdf"), return every form a
     * DB row might use to reference that file. Used to look up rows that
     * point at a file being deleted.
     *
     * @return string[]
     */
    public static function dbPathForms(string $publicRelative): array {
        $publicRelative = ltrim($publicRelative, '/');
        $forms = [$publicRelative, '/'.$publicRelative];

        // Files under public/storage/ are also referenced as disk-relative
        // (e.g. DB stores "quotes/x.jpg" not "storage/quotes/x.jpg").
        if (str_starts_with($publicRelative, 'storage/')) {
            $disk = substr($publicRelative, strlen('storage/'));
            $forms[] = $disk;
            $forms[] = '/'.$disk;
        }

        return array_values(array_unique($forms));
    }

    /**
     * Inverse of dbPathForms: given the keeper's public/-relative path,
     * return the canonical disk-relative form a DB row should be updated to.
     */
    public static function dbPathFromPublicRelative(string $publicRelative): string {
        $publicRelative = ltrim($publicRelative, '/');
        if (str_starts_with($publicRelative, 'storage/')) {
            return substr($publicRelative, strlen('storage/'));
        }
        // Files under public/ directly (e.g. pdfs/foo.pdf) are stored with a
        // leading slash in DB (see ArchiveRecord::resolveAssetUrl).
        return '/'.$publicRelative;
    }
}
