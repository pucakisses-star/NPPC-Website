<?php

namespace App\Filament\Resources\ClaudeSessionResource\Pages;

use App\Filament\Resources\ClaudeSessionResource;
use App\Models\ClaudeSession;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class CreateClaudeSession extends Page implements HasForms {
    use InteractsWithForms;

    protected static string $resource = ClaudeSessionResource::class;
    protected static string $view = 'filament.pages.claude-create';
    protected static ?string $title = 'New Claude Session';

    public ?array $data = [];

    public function mount(): void {
        $this->form->fill();
    }

    public function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\Section::make('What should Claude do?')
                    ->description('Describe the task clearly. Claude will work on an isolated copy of the codebase — nothing touches production until you merge.')
                    ->schema([
                        Forms\Components\Textarea::make('prompt')
                            ->label('Prompt')
                            ->required()
                            ->rows(6)
                            ->placeholder("e.g. Add a contact form to the /contact page with name, email, and message fields. Store submissions in a new contact_submissions table.")
                            ->helperText('Be specific about what you want changed. Include file paths if you know them.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): mixed {
        $data = $this->form->getState();

        // Check for already-running sessions
        $maxConcurrent = config('claude.max_concurrent', 1);
        $running = ClaudeSession::where('status', 'running')->count();

        if ($running >= $maxConcurrent) {
            Notification::make()
                ->title('Session limit reached')
                ->body("There are already {$running} session(s) running. Wait for them to finish or discard them first.")
                ->danger()
                ->send();

            return null;
        }

        $session = ClaudeSession::create([
            'prompt'     => $data['prompt'],
            'status'     => 'pending',
            'created_by' => auth()->user()?->name ?? 'Unknown',
        ]);

        // Launch as a background process so the browser isn't blocked.
        // The sync queue driver would run the entire Claude session inside
        // this HTTP request, causing a 10+ minute hang with no response.
        $artisan = base_path('artisan');
        $logFile = storage_path('logs/claude-sessions/'.$session->id.'.bg.log');
        $cmd = 'php '.escapeshellarg($artisan).' claude:run-session '.escapeshellarg($session->id)
            .' > '.escapeshellarg($logFile).' 2>&1 &';
        exec($cmd);

        return redirect()->to(ClaudeSessionResource::getUrl('view', ['record' => $session]));
    }
}
