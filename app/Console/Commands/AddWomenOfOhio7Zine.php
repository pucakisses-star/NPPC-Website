<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Registers "The Women of the Ohio 7" — a zine of three statements from
 * Patricia Gros Levasseur, Carol Saucier Manning, and Barbara
 * Curzi-Laaman, drawn from their 1989 sedition trial in Springfield,
 * Massachusetts (the "Ohio 7" / United Freedom Front federal case).
 */
final class AddWomenOfOhio7Zine extends Command {
    protected $signature = 'archive:add-women-of-ohio-7-zine';
    protected $description = 'Register the Women of the Ohio 7 zine as an ArchiveRecord';

    public function handle(): int {
        $slug = 'women-of-the-ohio-7';
        $payload = [
            'title' => 'The Women of the Ohio 7',
            'description' => 'Zine of statements and autobiographical writings by Patricia Gros Levasseur, Carol Saucier Manning, and Barbara Curzi-Laaman — the three women among the seven United Freedom Front / Ohio 7 defendants tried in 1989 in Springfield, Massachusetts on federal sedition and RICO charges. The zine reproduces their pre-trial statements (including Manning\'s account of being held in the political prisoners\' Control Unit at Lexington, Kentucky before trial) and Pat Gros Levasseur\'s autobiographical narrative tracing her path from a working-class Maryland childhood through Civil Rights and anti-Vietnam organizing to the clandestine struggle. Opens with the labor hymn "Hearts starve as well as bodies, give us bread but give us roses too."',
            'record_type' => 'document',
            'source_format' => 'zine',
            'file' => '/pdfs/zines/women-of-ohio-7.pdf',
            'collection' => 'Movement Zines',
            'authors' => 'Patricia Gros Levasseur, Carol Saucier Manning, Barbara Curzi-Laaman',
            'subjects' => ['United Freedom Front', 'Ohio 7', 'Anti-imperialism', 'Political Prisoners', 'Women Political Prisoners'],
            'is_digitized' => true,
            'published' => true,
        ];

        $existing = ArchiveRecord::where('slug', $slug)->first();
        if ($existing) {
            $existing->update($payload);
            $this->info('RECORD updated: The Women of the Ohio 7.');
        } else {
            ArchiveRecord::create(['slug' => $slug] + $payload);
            $this->info('RECORD added: The Women of the Ohio 7.');
        }

        return self::SUCCESS;
    }
}
