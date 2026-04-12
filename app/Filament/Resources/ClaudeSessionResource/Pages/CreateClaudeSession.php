<?php

namespace App\Filament\Resources\ClaudeSessionResource\Pages;

use App\Filament\Resources\ClaudeSessionResource;
use App\Jobs\RunClaudeCode;
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

    public function submit(): void {
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

            return;
        }

        $session = ClaudeSession::create([
            'prompt'     => $data['prompt'],
            'status'     => 'pending',
            'created_by' => auth()->user()?->name ?? 'Unknown',
        ]);

        RunClaudeCode::dispatch($session->id);

        Notification::make()
            ->title('Session started')
            ->body('Claude is working on your request. You\'ll see the results here shortly.')
            ->success()
            ->send();

        $this->redirect(ClaudeSessionResource::getUrl('view', ['record' => $session]), navigate: true);
    }
}
