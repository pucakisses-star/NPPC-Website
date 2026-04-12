<?php

namespace App\Filament\Resources\ClaudeSessionResource\Pages;

use App\Filament\Resources\ClaudeSessionResource;
use App\Models\ClaudeSession;
use App\Services\ClaudeSessionService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class ViewClaudeSession extends Page {
    protected static string $resource = ClaudeSessionResource::class;
    protected static string $view = 'filament.pages.claude-view';

    public ClaudeSession $record;
    public string $testOutput = '';
    public string $actionOutput = '';
    public string $reply = '';

    public function mount(ClaudeSession $record): void {
        $this->record = $record;
    }

    public function getTitle(): string {
        return 'Claude Session';
    }

    public function getLiveLog(): string {
        $logFile = storage_path('logs/claude-sessions/'.$this->record->id.'.log');

        if (file_exists($logFile)) {
            return file_get_contents($logFile);
        }

        return '';
    }

    protected function getHeaderActions(): array {
        $actions = [];

        if ($this->record->isRunning() || $this->record->isPending()) {
            $actions[] = Actions\Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->action(fn () => $this->record->refresh());

            $actions[] = Actions\Action::make('mark_failed')
                ->label('Mark as Failed')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Mark Session as Failed')
                ->modalDescription('This will mark the stuck session as failed and clean up its worktree. Use this if a session has been stuck in "Running" for a long time.')
                ->action(function () {
                    $service = new ClaudeSessionService();
                    $service->discard($this->record);
                    $this->record->update([
                        'status' => 'failed',
                        'output' => $this->record->output."\n\nManually marked as failed by admin.",
                    ]);
                    $this->record->refresh();
                    Notification::make()->title('Session marked as failed')->success()->send();
                });
        }

        if ($this->record->isActive()) {
            $actions[] = Actions\Action::make('commit')
                ->label('Commit Changes')
                ->icon('heroicon-o-check')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Commit Changes')
                ->modalDescription('This will commit all changes in the worktree branch.')
                ->form([
                    Forms\Components\TextInput::make('message')
                        ->label('Commit Message')
                        ->default(fn () => 'Claude Code: '.substr($this->record->prompt, 0, 72))
                        ->required(),
                ])
                ->action(function (array $data) {
                    $service = new ClaudeSessionService();
                    $this->actionOutput = $service->commitChanges($this->record, $data['message']);
                    Notification::make()->title('Changes committed')->success()->send();
                });

            $actions[] = Actions\Action::make('push')
                ->label('Push Branch')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->requiresConfirmation()
                ->modalDescription("This will push branch '{$this->record->branch_name}' to the remote.")
                ->action(function () {
                    $service = new ClaudeSessionService();
                    $this->actionOutput = $service->pushBranch($this->record);
                    Notification::make()->title('Branch pushed')->success()->send();
                });

            $actions[] = Actions\Action::make('merge')
                ->label('Merge to Main')
                ->icon('heroicon-o-arrow-down-on-square-stack')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Merge to Production')
                ->modalDescription('This will commit all changes, merge the branch into main, and clean up the worktree. This affects the live site.')
                ->action(function () {
                    $service = new ClaudeSessionService();
                    $result = $service->mergeToMain($this->record);
                    $this->record->refresh();

                    if ($this->record->isMerged()) {
                        $this->actionOutput = $result;
                        Notification::make()->title('Merged to main successfully')->success()->send();
                    } else {
                        $this->actionOutput = $result;
                        Notification::make()->title('Merge failed')->body('Check the output below for details.')->danger()->send();
                    }
                });

            $actions[] = Actions\Action::make('discard')
                ->label('Discard')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Discard Session')
                ->modalDescription('This will delete the worktree and branch. All changes will be lost. This cannot be undone.')
                ->action(function () {
                    $service = new ClaudeSessionService();
                    $service->discard($this->record);
                    $this->record->refresh();
                    Notification::make()->title('Session discarded')->success()->send();
                });
        }

        return $actions;
    }

    public function runTest(string $command): void {
        if (! $this->record->isActive()) {
            return;
        }

        $service = new ClaudeSessionService();
        $this->testOutput = $service->runTest($this->record, $command);
    }

    public function sendReply(): void {
        $reply = trim($this->reply);
        if (! $reply) {
            return;
        }

        if ($this->record->isRunning()) {
            Notification::make()->title('Session is already running')->danger()->send();
            return;
        }

        // Build a prompt that includes the previous conversation for context
        $contextPrompt = "Here is the previous conversation in this session:\n\n"
            ."ORIGINAL PROMPT: {$this->record->prompt}\n\n"
            ."CLAUDE'S RESPONSE:\n{$this->record->output}\n\n"
            ."---\n\n"
            ."FOLLOW-UP INSTRUCTION: {$reply}";

        $this->record->update(['prompt' => $contextPrompt]);

        $artisan = base_path('artisan');
        $logFile = storage_path('logs/claude-sessions/'.$this->record->id.'.bg.log');
        $cmd = 'php '.escapeshellarg($artisan).' claude:run-session '.escapeshellarg($this->record->id)
            .' --continue > '.escapeshellarg($logFile).' 2>&1 &';
        exec($cmd);

        $this->reply = '';
        $this->record->refresh();
        Notification::make()->title('Follow-up sent')->success()->send();
    }

    public function refreshDiff(): void {
        $service = new ClaudeSessionService();
        $service->refreshDiff($this->record);
        $this->record->refresh();
        Notification::make()->title('Diff refreshed')->success()->send();
    }

    public function getViewData(): array {
        // Refresh from DB so poll cycles pick up status changes
        $this->record->refresh();

        return [
            'record' => $this->record,
        ];
    }

    public function formatDiff(string $diff): string {
        $lines = explode("\n", e($diff));
        $formatted = [];

        foreach ($lines as $line) {
            if (str_starts_with($line, '+') && ! str_starts_with($line, '+++')) {
                $formatted[] = '<span class="text-green-400">'.$line.'</span>';
            } elseif (str_starts_with($line, '-') && ! str_starts_with($line, '---')) {
                $formatted[] = '<span class="text-red-400">'.$line.'</span>';
            } elseif (str_starts_with($line, '@@')) {
                $formatted[] = '<span class="text-cyan-400">'.$line.'</span>';
            } elseif (str_starts_with($line, 'diff ') || str_starts_with($line, 'index ')) {
                $formatted[] = '<span class="text-yellow-400 font-bold">'.$line.'</span>';
            } else {
                $formatted[] = '<span class="text-gray-400">'.$line.'</span>';
            }
        }

        return implode("\n", $formatted);
    }
}
