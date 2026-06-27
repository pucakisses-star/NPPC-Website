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
        $about = 'Kim Kelly is an independent journalist and author who covers labor, class, and social '
            .'movements. She is the author of Fight Like Hell: The Untold History of American Labor (2022), '
            .'a longtime labor columnist for Teen Vogue, and a labor reporter for In These Times. Her writing '
            .'has appeared in The New York Times, The Washington Post, The Nation, Rolling Stone, Esquire, and '
            .'The Baffler, among many others. A third-generation union member who began her career covering '
            .'heavy metal, she is a labor organizer based in Philadelphia.';

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
