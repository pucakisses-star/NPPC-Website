<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Scans every ArchiveRecord for rows that don't contain any
 * clear US-political-prisoner signal in their title, description,
 * or subjects. Reports them grouped by collection so the user can
 * review and decide what to delete.
 *
 * Doesn't delete anything; report-only. Pass --by=title to group
 * by title prefix instead of collection.
 */
final class ScanOffTopicArchive extends Command {
    protected $signature = 'archive:scan-off-topic {--limit=500 : Max candidates to report} {--by=collection : Group by "collection" or "title"}';
    protected $description = 'Surface ArchiveRecord rows with no US-political-prisoner signal in title/description/subjects';

    public function handle(): int {
        $limit = (int) $this->option('limit');
        $by = $this->option('by');

        // Signal keywords. Any single hit qualifies a record as "on-topic".
        $signals = [
            // Generic prison / prisoner / movement
            'prisoner', 'prison', 'inmate', 'incarcerat', 'sentenc', 'parole', 'clemenc',
            'pardon', 'amnesty', 'defendant', 'indict', 'convict', 'trial', 'court',
            'jail', 'federal', 'penitentiary',
            'cointelpro', 'fbi', 'grand jury', 'repression', 'surveill', 'subpoena',
            'solidarity', 'support', 'free', 'freedom', 'defense', 'defence',
            'political prisoner', 'pp/pow', 'p.o.w', 'pow', 'p.p',
            // Named PPs (lowercase matching)
            'mumia', 'abu-jamal', 'abu jamal', 'peltier', 'assata', 'shakur', 'sundiata',
            'acoli', 'geronimo', 'pratt', 'dhoruba', 'bin wahad', 'safiya', 'bukhari',
            'angela davis', 'fred hampton', 'huey newton', 'stokely', 'carmichael',
            'kwame ture', 'mlk', 'martin luther king', 'malcolm x', 'kathy boudin',
            'jean seberg', 'george jackson', 'soledad', 'attica', 'pelican bay',
            'marion', 'lexington', 'control unit', 'csmu',
            'sacco', 'vanzetti', 'haymarket', 'palmer raid', 'espionage act',
            'eugene debs', 'emma goldman', 'alexander berkman', 'big bill haywood',
            'iww', 'industrial workers of the world', 'joe hill', 'centralia',
            'tom mooney', 'warren billings', 'scottsboro', 'rosenberg', 'gitlow',
            'whitney v. california', 'dennis v.', 'yates v.', 'brandenburg',
            'john brown', 'harpers ferry',
            'sla', 'symbionese', 'weather underground', 'wuo', 'm19', 'may 19',
            'united freedom front', 'uff', 'resistance conspiracy', 'rcc',
            'falN', 'puerto rican', 'oscar lópez', 'oscar lopez', 'cancel miranda',
            'lebrón', 'lebron', 'cordero', 'flores', 'collazo',
            'bla', 'black liberation army', 'black panther', 'bpp', 'panther 21',
            'panther 13', 'ny 3', 'ny3', 'ny 8', 'sf 8', 'sf8',
            'move 9', ' move ', 'move organization', 'mumia',
            'angola 3', 'angola three', 'woodfox', 'wallace', 'king kid',
            'kings bay plowshares', 'plowshares', 'transform now', 'y-12', 'oak ridge',
            'soa watch', 'school of the americas', 'kings bay',
            'eric mcdavid', 'green scare', 'operation backfire',
            'marius mason', 'daniel mcgowan', 'jessica reznicek',
            'leonard peltier', 'aim', 'american indian movement', 'wounded knee',
            'tinley park', 'nato 3', 'nato three', 'nato 5', 'occupy', 'j20',
            'stop cop city', 'atlanta forest', 'tortuguita', 'cop city',
            'j22nd', 'noche brava', 'eric king',
            'sean swain', 'malik smith', 'jaan laaman', 'tom manning',
            'levasseur', 'richard williams', 'patricia gros',
            'jamil al-amin', 'h. rap brown', 'rap brown',
            'casey goonan', 'elias rodriguez', 'aaron bushnell',
            'edward snowden', 'chelsea manning', 'reality winner',
            'daniel hale', 'kiriakou', 'jeremy hammond', 'aaron swartz',
            'merrimack 4', 'merrimack four', 'palestine action',
            'jericho movement', 'critical resistance',
            // Anarchist Black Cross / general support orgs
            'abc', 'black cross', 'anarchist black cross',
            // Misc movement
            'wobblies', 'communist party', 'cpusa', 'socialist workers',
            'red scare', 'sedition act', 'alien and sedition',
            'civil disobedience', 'antifascist', 'anti-fascist',
            'klan', 'kkk', 'liberation', 'revolutionary', 'communist', 'socialist',
            'anarchist', 'panthers', 'wounded knee',
            // Operational vocabulary that turns up in PP support
            'safer space', 'security culture', 'know your rights', 'no snitching',
            'mass defense', 'legal support', 'court support',
        ];

        // Lowercase, longest-first so we don't waste regex backtracking.
        $signals = array_values(array_unique(array_map('strtolower', $signals)));

        $records = ArchiveRecord::orderBy('collection')->get(['id', 'slug', 'title', 'description', 'collection', 'subjects']);
        $this->info('Scanning '.$records->count().' rows.');

        $offTopic = [];
        foreach ($records as $r) {
            $haystack = strtolower(implode(' ', [
                (string) $r->title,
                (string) $r->description,
                is_array($r->subjects) ? implode(' ', $r->subjects) : (string) $r->subjects,
            ]));
            $hit = false;
            foreach ($signals as $s) {
                if (str_contains($haystack, $s)) {
                    $hit = true;
                    break;
                }
            }
            if (! $hit) {
                $offTopic[] = $r;
            }
        }

        $this->info('Off-topic candidates: '.count($offTopic).' of '.$records->count());

        if ($by === 'title') {
            foreach (array_slice($offTopic, 0, $limit) as $r) {
                $this->line('  '.str_pad('['.($r->collection ?: '∅').']', 60).$r->slug.'  '.\Illuminate\Support\Str::limit($r->title, 80));
            }

            return self::SUCCESS;
        }

        // Group by collection
        $byColl = [];
        foreach ($offTopic as $r) {
            $key = $r->collection ?: '(no collection)';
            $byColl[$key][] = $r;
        }
        ksort($byColl);
        $count = 0;
        foreach ($byColl as $coll => $rows) {
            $this->line("\n— [".$coll.']  '.count($rows).' rows —');
            foreach ($rows as $r) {
                if ($count++ >= $limit) {
                    $this->info("\n(limit {$limit} reached; pass --limit=N to see more)");
                    break 2;
                }
                $this->line('  #'.$r->id.'  '.$r->slug.'  '.\Illuminate\Support\Str::limit($r->title, 80));
            }
        }

        return self::SUCCESS;
    }
}
