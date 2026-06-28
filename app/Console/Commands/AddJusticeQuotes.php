<?php

namespace App\Console\Commands;

use App\Models\Quote;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Adds real, well-documented quotes on imprisonment, dissent, and freedom to
 * the homepage quote carousel, each with a public-domain author portrait (the
 * carousel only shows quotes that have an author_image). Portraits are copied
 * from public/images/quote-authors/ onto the public disk under quotes/.
 *
 * All quotes are sourced and correctly attributed (no paraphrases or
 * misattributions). Idempotent: matched by quote text — existing rows are
 * updated with the author/image rather than duplicated.
 */
final class AddJusticeQuotes extends Command
{
    protected $signature = 'quotes:add-justice-quotes';

    protected $description = 'Add real political-prisoner/justice quotes (with portraits) to the carousel';

    /** @var list<array{key:string,text:string,author:string}> */
    private const QUOTES = [
        [
            'key' => 'douglass',
            'text' => 'Power concedes nothing without a demand. It never did and it never will.',
            'author' => 'Frederick Douglass, Abolitionist',
        ],
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
            'key' => 'luxemburg',
            'text' => 'Freedom is always the freedom of those who think differently.',
            'author' => 'Rosa Luxemburg, Revolutionary',
        ],
        [
            'key' => 'gandhi',
            'text' => 'Non-cooperation with evil is as much a duty as is cooperation with good.',
            'author' => 'Mahatma Gandhi',
        ],
        [
            'key' => 'jones',
            'text' => 'Pray for the dead and fight like hell for the living.',
            'author' => 'Mary Harris "Mother" Jones, Labor Organizer',
        ],
    ];

    public function handle(): int
    {
        $created = 0;
        $updated = 0;

        foreach (self::QUOTES as $q) {
            $source = public_path('images/quote-authors/'.$q['key'].'.jpg');
            $stored = 'quotes/'.$q['key'].'.jpg';

            if (is_file($source)) {
                Storage::disk('public')->put($stored, file_get_contents($source));
            } else {
                $this->warn('Portrait missing: public/images/quote-authors/'.$q['key'].'.jpg');
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
        $this->info("Done. Created {$created}, updated {$updated}.");

        return self::SUCCESS;
    }
}
