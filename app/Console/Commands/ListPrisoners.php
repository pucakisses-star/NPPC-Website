<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

class ListPrisoners extends Command
{
    protected $signature = 'prisoners:list {--full : Include full description and case details}';
    protected $description = 'Print a compact list of every prisoner in the database.';

    public function handle(): int
    {
        $prisoners = Prisoner::with('cases.institution')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $this->info("Total prisoners: {$prisoners->count()}");
        $this->line(str_repeat('-', 80));

        foreach ($prisoners as $i => $p) {
            $n = $i + 1;
            $status = match (true) {
                (bool) $p->in_custody         => 'in custody',
                (bool) $p->released           => 'released',
                (bool) $p->currently_in_exile => 'in exile',
                (bool) $p->awaiting_trial     => 'awaiting trial',
                default                       => '—',
            };

            $birth = $p->birthdate?->format('Y') ?? '?';
            $death = $p->death_date?->format('Y') ?? '';
            $life  = $death ? "{$birth}–{$death}" : $birth;

            $descLen = strlen((string) $p->description);
            $sortOrder = $p->sort_order;
            $era = $p->era ?? '—';
            $caseCount = $p->cases->count();

            $this->line("{$n}. {$p->name}".($p->aka ? " (aka {$p->aka})" : ''));
            $this->line("   slug: {$p->slug} | sort: {$sortOrder} | era: {$era} | life: {$life} | {$status} | desc: {$descLen} chars | cases: {$caseCount}");

            if ($this->option('full')) {
                if ($p->description) {
                    $preview = substr((string) $p->description, 0, 300);
                    $this->line("   bio: {$preview}".(strlen($p->description) > 300 ? '…' : ''));
                }
                foreach ($p->cases as $j => $c) {
                    $inst = $c->institution?->name ?? '—';
                    $charges = $c->charges ? substr($c->charges, 0, 120) : '—';
                    $this->line("   case ".($j + 1).": {$inst} | {$charges}");
                }
                $this->line('');
            }
        }

        return self::SUCCESS;
    }
}
