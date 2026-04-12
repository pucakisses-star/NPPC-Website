<x-filament-panels::page>
    {{-- Auto-refresh while running --}}
    @if($record->isPending() || $record->isRunning())
        <div wire:poll.3s="$refresh"></div>
    @endif

    {{-- Status Banner --}}
    <div class="rounded-lg p-4 mb-6 {{ match($record->status) {
        'pending' => 'bg-gray-500/10 border border-gray-500/20',
        'running' => 'bg-warning-500/10 border border-warning-500/20',
        'completed' => $record->isMerged() ? 'bg-success-500/10 border border-success-500/20' : ($record->isDiscarded() ? 'bg-danger-500/10 border border-danger-500/20' : 'bg-info-500/10 border border-info-500/20'),
        'failed' => 'bg-danger-500/10 border border-danger-500/20',
        default => 'bg-gray-500/10',
    } }}">
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-3">
                    @if($record->isRunning())
                        <svg class="animate-spin h-5 w-5 text-warning-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    @endif
                    <span class="text-lg font-bold">{{ $record->status_label }}</span>
                </div>
                @if($record->branch_name)
                    <div class="text-sm mt-1 opacity-70 font-mono">{{ $record->branch_name }}</div>
                @endif
            </div>
            <div class="text-sm opacity-70">
                {{ $record->created_at->diffForHumans() }} by {{ $record->created_by }}
            </div>
        </div>
    </div>

    {{-- Live Log (while running) --}}
    @if($record->isRunning() || $record->isPending())
        <x-filament::section heading="Live Log">
            @php $liveLog = $this->getLiveLog(); @endphp
            @if($liveLog)
                <pre class="whitespace-pre-wrap text-xs font-mono bg-gray-900 text-green-400 rounded-lg p-4 max-h-96 overflow-y-auto" id="live-log">{{ $liveLog }}</pre>
                <script>
                    // Auto-scroll to bottom
                    setTimeout(() => {
                        const el = document.getElementById('live-log');
                        if (el) el.scrollTop = el.scrollHeight;
                    }, 100);
                </script>
            @else
                <div class="text-sm text-gray-500 italic">Waiting for output...</div>
            @endif

            @if($record->output)
                <div class="mt-4">
                    <div class="text-sm font-semibold mb-2">Claude Output (live):</div>
                    <pre class="whitespace-pre-wrap text-xs font-mono bg-gray-900 text-gray-100 rounded-lg p-4 max-h-96 overflow-y-auto" id="claude-output">{{ $record->output }}</pre>
                    <script>
                        setTimeout(() => {
                            const el = document.getElementById('claude-output');
                            if (el) el.scrollTop = el.scrollHeight;
                        }, 100);
                    </script>
                </div>
            @endif
        </x-filament::section>
    @endif

    {{-- Prompt --}}
    <x-filament::section heading="Prompt" collapsed>
        <div class="whitespace-pre-wrap text-sm font-mono bg-gray-900 text-gray-100 rounded-lg p-4">{{ $record->prompt }}</div>
    </x-filament::section>

    {{-- Test Buttons --}}
    @if($record->isActive())
        <x-filament::section heading="Quick Actions">
            <div class="flex flex-wrap gap-2">
                <x-filament::button size="sm" color="gray" wire:click="runTest('test')" icon="heroicon-o-beaker">
                    Run Tests
                </x-filament::button>
                <x-filament::button size="sm" color="gray" wire:click="runTest('cache:clear')" icon="heroicon-o-trash">
                    Clear Cache
                </x-filament::button>
                <x-filament::button size="sm" color="gray" wire:click="runTest('config:clear')" icon="heroicon-o-cog-6-tooth">
                    Clear Config
                </x-filament::button>
                <x-filament::button size="sm" color="gray" wire:click="runTest('route:list')" icon="heroicon-o-map">
                    Route List
                </x-filament::button>
                <x-filament::button size="sm" color="gray" wire:click="runTest('migrate:status')" icon="heroicon-o-circle-stack">
                    Migration Status
                </x-filament::button>
                <x-filament::button size="sm" color="gray" wire:click="refreshDiff" icon="heroicon-o-arrow-path">
                    Refresh Diff
                </x-filament::button>
            </div>

            @if($testOutput)
                <div class="mt-4">
                    <div class="text-sm font-semibold mb-2">Output:</div>
                    <pre class="whitespace-pre-wrap text-xs font-mono bg-gray-900 text-green-400 rounded-lg p-4 max-h-96 overflow-y-auto">{{ $testOutput }}</pre>
                </div>
            @endif
        </x-filament::section>
    @endif

    {{-- Action Output --}}
    @if($actionOutput)
        <x-filament::section heading="Action Output">
            <pre class="whitespace-pre-wrap text-xs font-mono bg-gray-900 text-gray-100 rounded-lg p-4 max-h-64 overflow-y-auto">{{ $actionOutput }}</pre>
        </x-filament::section>
    @endif

    {{-- Files Changed --}}
    @if($record->files_changed && count($record->files_changed) > 0)
        <x-filament::section heading="Files Changed ({{ count($record->files_changed) }})">
            <div class="space-y-1">
                @foreach($record->files_changed as $file)
                    <div class="flex items-center gap-2 text-sm font-mono py-1 px-2 rounded hover:bg-gray-500/10">
                        <svg class="w-4 h-4 text-info-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        {{ $file }}
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    @endif

    {{-- Diff --}}
    @if($record->diff)
        <x-filament::section heading="Diff" collapsed>
            <pre class="whitespace-pre-wrap text-xs font-mono bg-gray-900 rounded-lg p-4 max-h-[600px] overflow-y-auto">{!! $this->formatDiff($record->diff) !!}</pre>
        </x-filament::section>
    @endif

    {{-- Claude Output --}}
    @if($record->output && !$record->isRunning())
        <x-filament::section heading="Claude Output">
            <pre class="whitespace-pre-wrap text-sm font-mono bg-gray-900 text-gray-100 rounded-lg p-4 max-h-[600px] overflow-y-auto">{{ $record->output }}</pre>
        </x-filament::section>
    @endif
</x-filament-panels::page>
