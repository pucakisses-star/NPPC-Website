<?php

namespace App\Console\Commands;

use App\Models\Petition;
use App\Models\PetitionSignature;
use Illuminate\Console\Command;

/**
 * Inspect and clean petition signatures (e.g. random-keyboard-mash
 * test entries left on a live petition).
 *
 *   php artisan petition:signature-purge {slug}
 *       List all NON-demo signatures with a gibberish-score column.
 *
 *   php artisan petition:signature-purge {slug} --gibberish
 *       Delete every signature flagged as gibberish.
 *
 *   php artisan petition:signature-purge {slug} --id=<uuid>
 *       Delete a specific signature by id.
 */
final class PetitionSignaturePurge extends Command {
    protected $signature = 'petition:signature-purge
        {slug : Petition slug (e.g. free-leonard-peltier)}
        {--id= : Delete signature with this id}
        {--gibberish : Delete every signature scored as gibberish}';
    protected $description = 'List or delete petition signatures (skips demo @nppc-demo.test rows)';

    public function handle(): int {
        $petition = Petition::where('slug', $this->argument('slug'))->first();
        if (! $petition) {
            $this->error('Petition not found.');
            return self::FAILURE;
        }

        if ($id = $this->option('id')) {
            $sig = PetitionSignature::where('petition_id', $petition->id)->where('id', $id)->first();
            if (! $sig) {
                $this->error('Signature not found.');
                return self::FAILURE;
            }
            $this->line("Deleting {$sig->first_name} {$sig->last_name} ({$sig->email})...");
            $sig->delete();
            $this->info('Deleted.');
            return self::SUCCESS;
        }

        $real = PetitionSignature::where('petition_id', $petition->id)
            ->where('email', 'not like', '%@nppc-demo.test')
            ->orderByDesc('created_at')
            ->get();

        if ($real->isEmpty()) {
            $this->info('No real (non-demo) signatures on this petition.');
            return self::SUCCESS;
        }

        $rows = [];
        $gibberishIds = [];
        foreach ($real as $sig) {
            $isGib = $this->isGibberish($sig->first_name) || $this->isGibberish($sig->last_name);
            if ($isGib) {
                $gibberishIds[] = $sig->id;
            }
            $rows[] = [
                $sig->id,
                $sig->first_name.' '.$sig->last_name,
                $sig->email,
                $sig->created_at?->format('Y-m-d H:i'),
                $isGib ? 'gibberish' : '',
            ];
        }
        $this->table(['id', 'name', 'email', 'created_at', 'flag'], $rows);

        if ($this->option('gibberish')) {
            if (! $gibberishIds) {
                $this->info('Nothing flagged as gibberish.');
                return self::SUCCESS;
            }
            PetitionSignature::whereIn('id', $gibberishIds)->delete();
            $this->info('Deleted '.count($gibberishIds).' gibberish signature(s).');
        }

        return self::SUCCESS;
    }

    /**
     * Heuristic: a "real" name has at least one vowel and isn't a
     * keyboard mash. Flag anything with no vowels, all-consonant
     * runs of 4+, no letters, or fewer than 2 characters.
     */
    private function isGibberish(?string $name): bool {
        $n = strtolower(trim((string) $name));
        if ($n === '' || strlen($n) < 2) return true;
        if (! preg_match('/[a-z]/', $n)) return true;
        if (! preg_match('/[aeiouy]/', $n)) return true;
        if (preg_match('/[bcdfghjklmnpqrstvwxz]{5,}/', $n)) return true;
        // Common keyboard rolls: qwerty / asdf / zxcv etc.
        $rolls = ['qwerty', 'asdf', 'zxcv', 'jkl', 'fjfj', 'asdfgh', 'qwertyui'];
        foreach ($rolls as $r) {
            if (str_contains($n, $r)) return true;
        }
        return false;
    }
}
