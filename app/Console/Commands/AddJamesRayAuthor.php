<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Author;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Create the author James Ray (bio + avatar fetched from Substack) and attribute
 * the "We're All Going to be Called Terrorists Eventually" essay to him.
 * Idempotent (updateOrCreate by name). Run after articles:add-terrorism-label so
 * the article exists to be linked.
 */
final class AddJamesRayAuthor extends Command
{
    protected $signature = 'authors:add-james-ray';

    protected $description = 'Add author James Ray (bio + photo) and attribute his article to him';

    private const AVATAR_URL = 'https://substackcdn.com/image/fetch/$s_!MRcO!,f_auto,q_auto:good,fl_progressive:steep/https%3A%2F%2Fsubstack-post-media.s3.amazonaws.com%2Fpublic%2Fimages%2F5962774f-d475-4c14-b9df-5e38b8c80c3d_662x662.jpeg';

    private const ARTICLE_SLUG = 'were-all-going-to-be-called-terrorists-eventually';

    public function handle(): int
    {
        $avatar = 'authors/james-ray.jpg';
        try {
            $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; NPPC-Archive/1.0)'])
                ->timeout(60)->get(self::AVATAR_URL);
            if ($resp->successful() && strlen($resp->body()) > 3000) {
                Storage::disk('public')->put($avatar, $resp->body());
                $this->info('Saved avatar to '.$avatar);
            } else {
                $avatar = self::AVATAR_URL;
                $this->warn('Avatar download failed — using remote URL.');
            }
        } catch (\Throwable $e) {
            $avatar = self::AVATAR_URL;
            $this->warn('Avatar fetch error: '.$e->getMessage());
        }

        $author = Author::updateOrCreate(
            ['name' => 'James Ray'],
            [
                'about' => "James Ray is an organizer, writer, and political commentator with a bachelor's degree in Economics and Political Science from Purdue University.",
                'avatar' => $avatar,
            ],
        );
        $this->info(($author->wasRecentlyCreated ? 'Created' : 'Updated')." author: {$author->name} (ID: {$author->id})");

        $article = Article::where('slug', self::ARTICLE_SLUG)->first();
        if ($article) {
            $article->author_id = $author->id;
            $article->save();
            $this->info('Attributed /news/'.self::ARTICLE_SLUG." to {$author->name}.");
        } else {
            $this->warn('Article '.self::ARTICLE_SLUG.' not found — run articles:add-terrorism-label first, then re-run this.');
        }

        return self::SUCCESS;
    }
}
