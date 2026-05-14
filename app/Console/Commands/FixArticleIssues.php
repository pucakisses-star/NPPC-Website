<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;

/**
 * Applies a targeted set of fixes surfaced by the article-health
 * scan: unpublish three placeholder articles, correct title
 * typos, normalize "Fact Act" → "FACE Act" in body text.
 *
 * Dry-run by default; --apply writes.
 */
final class FixArticleIssues extends Command {
    protected $signature = 'archive:fix-article-issues {--apply : Actually write the changes}';
    protected $description = 'Fix specific article issues surfaced by the health scan';

    public function handle(): int {
        $apply = (bool) $this->option('apply');

        $changes = [];

        // -- Unpublish placeholder/empty articles --
        $unpublishSlugs = [
            'nppa-joins-amnesty-international-in-demanding-release-of-journalist-estefany-rodriguez',
            'super-bowl-halftime-performer-charged-months-after-holding-protest-flag-for-gaza',
            'trump-administration-creates-chilling-effect-on-free-speech-by-weaponizeing-immigration-enforcement-to-silences-political-opposition',
        ];
        foreach ($unpublishSlugs as $slug) {
            $a = Article::where('slug', $slug)->first();
            if (! $a) {
                $this->warn('MISS  '.$slug);
                continue;
            }
            if ($a->published_at !== null) {
                $changes[] = ['action' => 'unpublish', 'article' => $a];
            }
        }

        // -- Title fixes --
        $titleEdits = [
            // [slug, search, replace]
            [
                'trump-administration-creates-chilling-effect-on-free-speech-by-weaponizeing-immigration-enforcement-to-silences-political-opposition',
                ['weaponizeing', 'Silences', '  '],
                ['weaponizing', 'Silence', ' '],
            ],
            [
                'over-1300-student-visas-by-trump-admin-in-chilling-attack-on-civil-liberties',
                ['1300'],
                ['1,300'],
            ],
            [
                'third-circuit-ruling-narrows-habeas-pathway-in-mahmoud-khalil-case-raising-prospect-of-renewed-detention-by-national-political-prisoner-coalition',
                [' By National Political Prisoner Coalition', ' by National Political Prisoner Coalition'],
                ['', ''],
            ],
            [
                'nppa-joins-amnesty-international-in-demanding-release-of-journalist-estefany-rodriguez',
                ['NPPA'],
                ['NPPC'],
            ],
        ];
        foreach ($titleEdits as [$slug, $search, $replace]) {
            $a = Article::where('slug', $slug)->first();
            if (! $a) {
                continue;
            }
            $new = str_replace($search, $replace, (string) $a->title);
            if ($new !== $a->title) {
                $changes[] = ['action' => 'retitle', 'article' => $a, 'old' => $a->title, 'new' => $new];
            }
        }

        // -- Body: FACE Act normalization --
        $faceArticle = Article::where('slug', 'doj-working-group-determines-prosecutors')->first();
        if ($faceArticle) {
            $body = (string) $faceArticle->body;
            $newBody = preg_replace('/\bFact Act\b/u', 'FACE Act', $body);
            $newBody = preg_replace('/\bFace Act\b/u', 'FACE Act', $newBody);
            if ($newBody !== $body) {
                $changes[] = ['action' => 'rewrite_body', 'article' => $faceArticle, 'note' => 'Fact Act / Face Act → FACE Act'];
            }
        }

        // -- DOJ Working Group title — give it a verb --
        $dojTitle = Article::where('slug', 'doj-working-group-determines-prosecutors')->first();
        if ($dojTitle && stripos($dojTitle->title, 'targets prosecutors') === false && stripos($dojTitle->title, 'determines prosecutors') !== false) {
            $changes[] = [
                'action' => 'retitle',
                'article' => $dojTitle,
                'old' => $dojTitle->title,
                'new' => 'DOJ Working Group Targets Prosecutors Behind Biden FACE Act Cases',
            ];
        }

        // -- Print plan --
        $this->info('Planned changes: '.count($changes));
        foreach ($changes as $c) {
            $a = $c['article'];
            switch ($c['action']) {
                case 'unpublish':
                    $this->warn('UNPUBLISH  #'.$a->id.'  /news/'.$a->slug);
                    break;
                case 'retitle':
                    $this->info('RETITLE    #'.$a->id.'  /news/'.$a->slug);
                    $this->line('             OLD: '.$c['old']);
                    $this->line('             NEW: '.$c['new']);
                    break;
                case 'rewrite_body':
                    $this->info('BODY       #'.$a->id.'  /news/'.$a->slug.'  ('.$c['note'].')');
                    break;
            }
        }

        if (! $apply) {
            $this->info("\n(dry-run; re-run with --apply to write)");
            return self::SUCCESS;
        }

        foreach ($changes as $c) {
            $a = $c['article'];
            switch ($c['action']) {
                case 'unpublish':
                    $a->published_at = null;
                    $a->save();
                    break;
                case 'retitle':
                    $a->title = $c['new'];
                    $a->save();
                    break;
                case 'rewrite_body':
                    $body = (string) $a->body;
                    $body = preg_replace('/\bFact Act\b/u', 'FACE Act', $body);
                    $body = preg_replace('/\bFace Act\b/u', 'FACE Act', $body);
                    $a->body = $body;
                    $a->save();
                    break;
            }
        }
        $this->info('Applied '.count($changes).' changes.');

        return self::SUCCESS;
    }
}
