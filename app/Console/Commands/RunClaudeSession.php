<?php

namespace App\Console\Commands;

use App\Models\ClaudeSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class RunClaudeSession extends Command {
    protected $signature = 'claude:run-session {session_id}';
    protected $description = 'Run a Claude Code session in the background';

    public function handle(): int {
        $session = ClaudeSession::findOrFail($this->argument('session_id'));

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

            $this->log($logFile, "Creating worktree: {$branchName}");

            $result = Process::path($repoPath)
                ->timeout(30)
                ->run('git worktree add -b '.escapeshellarg($branchName).' '.escapeshellarg($worktreePath).' HEAD 2>&1');

            if (! $result->successful()) {
                throw new \RuntimeException('Failed to create worktree: '.$result->output().$result->errorOutput());
            }

            $this->log($logFile, "Worktree created at: {$worktreePath}");
            $this->log($logFile, "Running Claude Code...");
            $this->log($logFile, "Prompt: {$session->prompt}");
            $this->log($logFile, str_repeat('─', 60));

            // Run Claude Code — stream output to log file
            // --yes auto-approves tool permissions since we can't interact
            // with the CLI in background mode. This is safe because it runs
            // in an isolated worktree — nothing touches production.
            $claudeLog = $logFile.'.claude';
            $cmd = escapeshellarg($claudeBinary).' -p '.escapeshellarg($session->prompt).' --yes > '.escapeshellarg($claudeLog).' 2>&1';

            $home = config('claude.home', '/root');

            $process = Process::path($worktreePath)
                ->timeout(1500)
                ->env([
                    'HOME' => $home,
                    'PATH' => '/usr/local/bin:/usr/bin:/bin:'.($home.'/.local/bin'),
                ])
                ->start($cmd);

            // Poll the log file and update the session while Claude runs
            $lastSize = 0;
            while ($process->running()) {
                sleep(5);

                if (file_exists($claudeLog)) {
                    clearstatcache(true, $claudeLog);
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
            $output = file_exists($claudeLog) ? file_get_contents($claudeLog) : $processResult->output();

            $this->log($logFile, str_repeat('─', 60));
            $this->log($logFile, "Claude Code finished. Capturing changes...");

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
            $this->log($logFile, count($filesChanged).' file(s) changed.');

            $session->update([
                'status'        => 'completed',
                'output'        => $output,
                'diff'          => $diff,
                'files_changed' => $filesChanged,
            ]);

            @unlink($claudeLog);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->log($logFile, "ERROR: ".$e->getMessage());

            $claudeLog = $logFile.'.claude';
            $partialOutput = file_exists($claudeLog) ? file_get_contents($claudeLog) : '';

            $session->update([
                'status' => 'failed',
                'output' => $partialOutput."\n\nERROR: ".$e->getMessage(),
            ]);

            $this->cleanupWorktree($repoPath, $worktreePath, $branchName);
            @unlink($claudeLog);

            return self::FAILURE;
        }
    }

    private function log(string $logFile, string $message): void {
        $timestamp = now()->format('H:i:s');
        file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND);
    }

    private function cleanupWorktree(string $repoPath, string $worktreePath, string $branchName): void {
        if (is_dir($worktreePath)) {
            Process::path($repoPath)->timeout(30)->run('git worktree remove --force '.escapeshellarg($worktreePath));
        }
        Process::path($repoPath)->timeout(30)->run('git branch -D '.escapeshellarg($branchName));
    }
}
