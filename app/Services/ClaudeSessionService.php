<?php

namespace App\Services;

use App\Models\ClaudeSession;
use Illuminate\Support\Facades\Process;

class ClaudeSessionService {
    private string $repoPath;

    public function __construct() {
        $this->repoPath = config('claude.repo_path', base_path());
    }

    /**
     * Commit all changes in the worktree with a descriptive message.
     */
    public function commitChanges(ClaudeSession $session, ?string $message = null): string {
        $message = $message ?: "Claude Code: {$session->prompt}";

        $result = Process::path($session->worktree_path)
            ->timeout(30)
            ->run('git add -A && git commit -m '.escapeshellarg($message));

        return $result->output().$result->errorOutput();
    }

    /**
     * Push the worktree branch to the remote.
     */
    public function pushBranch(ClaudeSession $session): string {
        $result = Process::path($session->worktree_path)
            ->timeout(60)
            ->run('git push -u origin '.escapeshellarg($session->branch_name));

        if (! $result->successful()) {
            return "PUSH FAILED:\n".$result->output().$result->errorOutput();
        }

        return $result->output().$result->errorOutput();
    }

    /**
     * Merge the worktree branch into main and update production.
     */
    public function mergeToMain(ClaudeSession $session): string {
        // First commit any uncommitted changes in the worktree
        $this->commitChanges($session);

        $output = '';

        // Switch to main in the main repo and merge
        $result = Process::path($this->repoPath)
            ->timeout(60)
            ->run('git merge '.escapeshellarg($session->branch_name).' --no-ff -m '.escapeshellarg("Merge Claude session: {$session->prompt}"));

        $output .= $result->output().$result->errorOutput();

        if (! $result->successful()) {
            return "MERGE FAILED:\n".$output;
        }

        // Clean up the worktree
        $this->cleanupWorktree($session);

        $session->update(['merged_at' => now()]);

        return $output;
    }

    /**
     * Discard all changes and clean up the worktree.
     */
    public function discard(ClaudeSession $session): string {
        $output = $this->cleanupWorktree($session);

        $session->update(['discarded_at' => now()]);

        return $output;
    }

    /**
     * Deploy changes: merge the worktree branch to main AND push to origin.
     * This is the one-click "make it live" action for non-technical admins.
     */
    public function deployChanges(ClaudeSession $session): string {
        $output = '';

        // Verify worktree still exists
        if (! $session->worktree_path || ! is_dir($session->worktree_path)) {
            return "ERROR: Worktree no longer exists at {$session->worktree_path}. This session cannot be deployed. The worktree was probably cleaned up. Try running the prompt in a new session.";
        }

        // Verify branch still exists
        $branchCheck = Process::path($this->repoPath)
            ->timeout(10)
            ->run('git show-ref --verify --quiet refs/heads/'.escapeshellarg($session->branch_name).' && echo exists || echo missing');
        if (str_contains($branchCheck->output(), 'missing')) {
            return "ERROR: Branch '{$session->branch_name}' no longer exists. This session cannot be deployed.";
        }

        // Make sure we're on main before merging
        $currentBranch = trim(Process::path($this->repoPath)->run('git branch --show-current')->output());
        $output .= "Current branch: {$currentBranch}\n";

        if ($currentBranch !== 'main') {
            $output .= "Switching to main...\n";
            $checkoutResult = Process::path($this->repoPath)
                ->timeout(30)
                ->run('git checkout main 2>&1');
            $output .= $checkoutResult->output().$checkoutResult->errorOutput()."\n";

            if (! $checkoutResult->successful()) {
                return $output."\nERROR: Could not switch to main branch. Deploy aborted.";
            }
        }

        $output .= "\n=== Merging to main ===\n";
        $output .= $this->mergeToMain($session);

        if (! $session->fresh()->isMerged()) {
            return $output."\n\nDEPLOY FAILED: Merge did not complete. No changes were deployed.";
        }

        $output .= "\n\n=== Pushing to GitHub ===\n";

        $result = Process::path($this->repoPath)
            ->timeout(60)
            ->run('git push origin main 2>&1');

        $output .= $result->output().$result->errorOutput();

        if (! $result->successful()) {
            $output .= "\n\nWARNING: Merge succeeded (changes are live on the server) but push to GitHub failed. You may need to run 'git push origin main' on the server manually.";
        } else {
            $output .= "\n\n✓ SUCCESS: Changes are now live on the site and synced with GitHub.";
        }

        return $output;
    }

    /**
     * Run artisan commands in the worktree for testing.
     */
    public function runTest(ClaudeSession $session, string $command = 'test'): string {
        $allowedCommands = [
            'test'           => 'php artisan test',
            'cache:clear'    => 'php artisan cache:clear',
            'config:clear'   => 'php artisan config:clear',
            'route:list'     => 'php artisan route:list',
            'migrate:status' => 'php artisan migrate:status',
            'pint'           => 'vendor/bin/pint --test',
        ];

        $cmd = $allowedCommands[$command] ?? null;

        if (! $cmd) {
            return "Unknown command: {$command}. Allowed: ".implode(', ', array_keys($allowedCommands));
        }

        $result = Process::path($session->worktree_path)
            ->timeout(120)
            ->run($cmd.' 2>&1');

        return $result->output();
    }

    /**
     * Refresh the diff (e.g. after Claude made more changes).
     */
    public function refreshDiff(ClaudeSession $session): void {
        $diffResult = Process::path($session->worktree_path)
            ->timeout(30)
            ->run('git diff HEAD');

        $statusResult = Process::path($session->worktree_path)
            ->timeout(30)
            ->run('git diff HEAD --name-only');

        $untrackedResult = Process::path($session->worktree_path)
            ->timeout(30)
            ->run('git ls-files --others --exclude-standard');

        $filesChanged = array_filter(explode("\n", trim($statusResult->output())));
        $untrackedFiles = array_filter(explode("\n", trim($untrackedResult->output())));

        $session->update([
            'diff'          => $diffResult->output(),
            'files_changed' => array_values(array_unique(array_merge($filesChanged, $untrackedFiles))),
        ]);
    }

    private function cleanupWorktree(ClaudeSession $session): string {
        $output = '';

        if ($session->worktree_path && is_dir($session->worktree_path)) {
            $result = Process::path($this->repoPath)
                ->timeout(30)
                ->run('git worktree remove --force '.escapeshellarg($session->worktree_path));

            $output .= $result->output().$result->errorOutput();
        }

        if ($session->branch_name) {
            $result = Process::path($this->repoPath)
                ->timeout(30)
                ->run('git branch -D '.escapeshellarg($session->branch_name));

            $output .= $result->output().$result->errorOutput();
        }

        return $output;
    }
}
