<?php

namespace App\Console\Commands;

use App\Models\Author;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Creates (or updates) the Kim Kelly author profile — name, bio, and avatar
 * (cropped from her supplied photo) — for use as an article byline. Copies the
 * committed avatar to the public disk. Idempotent: matches the author by name.
 */
final class AddKimKellyAuthor extends Command
{
    protected $signature = 'authors:add-kim-kelly';

    protected $description = 'Add the Kim Kelly author profile (bio + avatar)';

    private const SOURCE = 'images/authors/kim-kelly.jpg';

    private const AVATAR = 'authors/kim-kelly.jpg';

    public function handle(): int
    {
        $about = 'Kim Kelly is a freelance journalist and author based in Philadelphia. Her writing can be '
            .'found in The Nation, In These Times, Teen Vogue, The Baffler, Playboy, Rolling Stone, and many '
            .'others. Her first book, FIGHT LIKE HELL: The Untold History of American Labor, was published in '
            .'2022, followed by a young readers edition in 2025. Find out more at kim-kelly.com.';

        // Copy the committed avatar onto the public disk where avatars are served.
        $source = public_path(self::SOURCE);
        if (is_file($source)) {
            Storage::disk('public')->put(self::AVATAR, file_get_contents($source));
            $this->info('Avatar copied to public disk: '.self::AVATAR);
        } else {
            $this->warn('Source image not found: public/'.self::SOURCE);
        }

        $author = Author::firstOrNew(['name' => 'Kim Kelly']);
        $author->about = $about;
        $author->avatar = self::AVATAR;
        $author->save();

        $this->info(($author->wasRecentlyCreated ? 'Created' : 'Updated')." author: {$author->name} (ID: {$author->id})");
        $this->info('Avatar URL: '.$author->avatar_url);

        return self::SUCCESS;
    }
}
