<?php

namespace App\Jobs;

use App\Models\ClaudeSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;

class RunClaudeCode implements ShouldQueue {
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 1800;
    public int $tries   = 1;

    public function __construct(
        public string $sessionId,
    ) {}

    public function handle(): void {
        $session = ClaudeSession::findOrFail($this->sessionId);

        $repoPath = config('claude.repo_path', base_path());
        $worktreeBase = config('claude.worktree_base', '/tmp/claude-worktrees');
        $claudeBinary = config('claude.binary', 'claude');

        $branchName = 'claude/session-'.substr($session->id, 0, 8).'-'.time();
        $worktreePath = $worktreeBase.'/'.$branchName;

        // Log file for real-time output streaming
        $logDir = storage_path('logs/claude-sessions');
        if (! is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir.'/'.$session->id.'.log';

        $session->update([
            'status'        => 'running',
            'branch_name'   => $branchName,
            'worktree_path' => $worktreePath,
            'output'        => '',
        ]);

        try {
            if (! is_dir($worktreeBase)) {
                mkdir($worktreeBase, 0755, true);
            }

            // Log progress
            $this->log($logFile, $session, "Creating worktree: {$branchName}");

            $result = Process::path($repoPath)
                ->timeout(30)
                ->run('git worktree add -b '.escapeshellarg($branchName).' '.escapeshellarg($worktreePath).' HEAD 2>&1');

            if (! $result->successful()) {
                throw new \RuntimeException('Failed to create worktree: '.$result->output().$result->errorOutput());
            }

            $this->log($logFile, $session, "Worktree created at: {$worktreePath}");
            $this->log($logFile, $session, "Running Claude Code...");
            $this->log($logFile, $session, "Prompt: {$session->prompt}");
            $this->log($logFile, $session, str_repeat('─', 60));

            // Run Claude Code — stream output to log file
            $cmd = escapeshellarg($claudeBinary).' -p '.escapeshellarg($session->prompt).' > '.escapeshellarg($logFile.'.claude').' 2>&1';

            $process = Process::path($worktreePath)
                ->timeout(1500)
                ->env([
                    'HOME' => env('HOME', '/root'),
                    'PATH' => env('PATH', '/usr/local/bin:/usr/bin:/bin'),
                ])
                ->start($cmd);

            // Poll the log file and update the session while Claude runs
            $lastSize = 0;
            while ($process->running()) {
                sleep(5);

                // Read new output from Claude's log file
                $claudeLog = $logFile.'.claude';
                if (file_exists($claudeLog)) {
                    $currentSize = filesize($claudeLog);
                    if ($currentSize > $lastSize) {
                        $newContent = file_get_contents($claudeLog);
                        $session->update(['output' => $newContent]);
                        $lastSize = $currentSize;
                    }
                }
            }

            $processResult = $process->wait();

            // Read final output
            $claudeLog = $logFile.'.claude';
            $output = file_exists($claudeLog) ? file_get_contents($claudeLog) : $processResult->output();

            $this->log($logFile, $session, str_repeat('─', 60));
            $this->log($logFile, $session, "Claude Code finished. Capturing changes...");

            // Capture the diff
            $diffResult = Process::path($worktreePath)
                ->timeout(30)
                ->run('git diff HEAD');

            $diff = $diffResult->output();

            // Get changed files
            $statusResult = Process::path($worktreePath)
                ->timeout(30)
                ->run('git diff HEAD --name-only');

            $filesChanged = array_filter(explode("\n", trim($statusResult->output())));

            // Check for untracked files
            $untrackedResult = Process::path($worktreePath)
                ->timeout(30)
                ->run('git ls-files --others --exclude-standard');

            $untrackedFiles = array_filter(explode("\n", trim($untrackedResult->output())));

            if (! empty($untrackedFiles)) {
                Process::path($worktreePath)
                    ->timeout(30)
                    ->run('git add '.implode(' ', array_map('escapeshellarg', $untrackedFiles)));

                $diffResult = Process::path($worktreePath)
                    ->timeout(30)
                    ->run('git diff HEAD');

                $diff = $diffResult->output();
                $filesChanged = array_merge($filesChanged, $untrackedFiles);
            }

            $filesChanged = array_values(array_unique($filesChanged));
            $this->log($logFile, $session, count($filesChanged).' file(s) changed.');

            $session->update([
                'status'        => 'completed',
                'output'        => $output,
                'diff'          => $diff,
                'files_changed' => $filesChanged,
            ]);

            // Clean up temp log
            @unlink($claudeLog);
        } catch (\Throwable $e) {
            $this->log($logFile, $session, "ERROR: ".$e->getMessage());

            // Read whatever output we captured
            $claudeLog = $logFile.'.claude';
            $partialOutput = file_exists($claudeLog) ? file_get_contents($claudeLog) : '';

            $session->update([
                'status' => 'failed',
                'output' => $partialOutput."\n\nERROR: ".$e->getMessage(),
            ]);

            $this->cleanupWorktree($repoPath, $worktreePath, $branchName);
            @unlink($claudeLog);
        }
    }

    private function log(string $logFile, ClaudeSession $session, string $message): void {
        $timestamp = now()->format('H:i:s');
        $line = "[{$timestamp}] {$message}\n";
        file_put_contents($logFile, $line, FILE_APPEND);
    }

    private function cleanupWorktree(string $repoPath, string $worktreePath, string $branchName): void {
        if (is_dir($worktreePath)) {
            Process::path($repoPath)->timeout(30)->run('git worktree remove --force '.escapeshellarg($worktreePath));
        }
        Process::path($repoPath)->timeout(30)->run('git branch -D '.escapeshellarg($branchName));
    }
}
