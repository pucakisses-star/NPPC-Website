<?php

namespace App\Console\Commands;

use App\Models\Quote;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Adds quotes specifically about political imprisonment — being jailed for one's
 * beliefs, the injustice of imprisoning dissenters, and solidarity with
 * prisoners of conscience — to the homepage quote carousel, each with a
 * public-domain author portrait (the carousel only shows quotes that have an
 * author_image).
 *
 * All quotes are sourced and correctly attributed. Idempotent: matched by text,
 * so re-runs update rather than duplicate. An earlier, more general set
 * (Douglass / Luxemburg / Mother Jones / Gandhi's "non-cooperation") is removed
 * here in favour of these prison-specific ones.
 */
final class AddJusticeQuotes extends Command
{
    protected $signature = 'quotes:add-justice-quotes';

    protected $description = 'Add political-imprisonment quotes (with portraits) to the carousel';

    /** Earlier quotes that were too general — delete them if present. */
    private const REMOVE = [
        'Power concedes nothing without a demand. It never did and it never will.',
        'Freedom is always the freedom of those who think differently.',
        'Pray for the dead and fight like hell for the living.',
        'Non-cooperation with evil is as much a duty as is cooperation with good.',
    ];

    /** Their portrait files on the public disk, no longer used. */
    private const REMOVE_IMAGES = [
        'quotes/douglass.jpg',
        'quotes/luxemburg.jpg',
        'quotes/jones.jpg',
        // Old rectangular portraits, replaced by transparent PNG cutouts.
        'quotes/thoreau.jpg',
        'quotes/debs.jpg',
        'quotes/gandhi.jpg',
        'quotes/vanzetti.jpg',
        'quotes/solzhenitsyn.jpg',
    ];

    /** @var list<array{key:string,text:string,author:string}> */
    private const QUOTES = [
        [
            'key' => 'thoreau',
            'text' => 'Under a government which imprisons any unjustly, the true place for a just man is also a prison.',
            'author' => 'Henry David Thoreau',
        ],
        [
            'key' => 'debs',
            'text' => 'While there is a lower class, I am in it; while there is a criminal element, I am of it; while there is a soul in prison, I am not free.',
            'author' => 'Eugene V. Debs, Labor Leader & Political Prisoner',
        ],
        [
            'key' => 'gandhi',
            'text' => 'I am here, therefore, to invite and submit cheerfully to the highest penalty that can be inflicted upon me for what in law is a deliberate crime, and what appears to me to be the highest duty of a citizen.',
            'author' => 'Mahatma Gandhi, at the Great Trial (1922)',
        ],
        [
            'key' => 'vanzetti',
            'text' => 'I am suffering because I am a radical, and indeed I am a radical; I have suffered because I was an Italian, and indeed I am an Italian.',
            'author' => 'Bartolomeo Vanzetti, Anarchist (Sacco & Vanzetti)',
        ],
        [
            'key' => 'solzhenitsyn',
            'text' => 'Bless you, prison, bless you for being in my life. For there, lying upon the rotting prison straw, I came to realize that the object of life is not prosperity, as we are made to believe, but the maturity of the human soul.',
            'author' => 'Aleksandr Solzhenitsyn, Gulag Survivor',
        ],
    ];

    public function handle(): int
    {
        $removed = 0;
        foreach (self::REMOVE as $text) {
            $removed += Quote::where('text', $text)->delete();
        }
        foreach (self::REMOVE_IMAGES as $img) {
            if (Storage::disk('public')->exists($img)) {
                Storage::disk('public')->delete($img);
            }
        }
        if ($removed) {
            $this->info("Removed {$removed} earlier (too-general) quote(s).");
        }

        $created = 0;
        $updated = 0;
        foreach (self::QUOTES as $q) {
            $source = public_path('images/quote-authors/'.$q['key'].'.png');
            $stored = 'quotes/'.$q['key'].'.png';
            if (is_file($source)) {
                Storage::disk('public')->put($stored, file_get_contents($source));
            } else {
                $this->warn('Portrait missing: public/images/quote-authors/'.$q['key'].'.png');
            }

            $attributes = ['author_name' => $q['author'], 'author_image' => $stored];
            $quote = Quote::where('text', $q['text'])->first();
            if ($quote) {
                $quote->fill($attributes)->save();
                $updated++;
                $this->info('Updated quote: '.$q['author']);
            } else {
                Quote::create($attributes + ['text' => $q['text']]);
                $created++;
                $this->info('Created quote: '.$q['author']);
            }
        }

        $this->newLine();
        $this->info("Done. Created {$created}, updated {$updated}, removed {$removed}.");

        return self::SUCCESS;
    }
}
