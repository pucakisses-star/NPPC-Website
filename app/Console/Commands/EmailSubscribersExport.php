<?php

namespace App\Console\Commands;

use App\Models\EmailSubscriber;
use Illuminate\Console\Command;

/**
 * Exports the email-subscribers table as CSV — to STDOUT by default,
 * or to a file via --out=<path>. Useful for cron-driven sync to a
 * mailing platform (Mailchimp / Brevo / Resend bulk import), nightly
 * snapshots, or one-off campaign exports.
 *
 *   php artisan emails:export
 *   php artisan emails:export --status=active
 *   php artisan emails:export --since=2026-01-01 --out=/tmp/active.csv
 */
final class EmailSubscribersExport extends Command {
    protected $signature = 'emails:export
        {--status=active : active | unsubscribed | all}
        {--since= : Only rows created on or after YYYY-MM-DD}
        {--out= : Write to this path instead of STDOUT}';
    protected $description = 'Export email_subscribers table as CSV';

    public function handle(): int {
        $status = $this->option('status') ?: 'active';
        $since  = $this->option('since');
        $out    = $this->option('out');

        $query = EmailSubscriber::query()->orderBy('created_at');
        if (in_array($status, ['active', 'unsubscribed'], true)) {
            $query->where('status', $status);
        }
        if ($since) {
            $query->where('created_at', '>=', $since);
        }

        $stream = $out ? fopen($out, 'w') : fopen('php://stdout', 'w');
        if (! $stream) {
            $this->error('Failed to open output destination.');
            return self::FAILURE;
        }

        fputcsv($stream, ['email', 'status', 'subscribed_at']);
        $count = 0;
        $query->chunk(500, function ($rows) use ($stream, &$count) {
            foreach ($rows as $row) {
                fputcsv($stream, [
                    $row->email,
                    $row->status,
                    optional($row->created_at)->toIso8601String(),
                ]);
                $count++;
            }
        });
        fclose($stream);

        if ($out) {
            $this->info("Wrote {$count} subscriber row(s) to {$out}.");
        }

        return self::SUCCESS;
    }
}
