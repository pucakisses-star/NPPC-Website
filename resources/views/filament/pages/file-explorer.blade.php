<x-filament-panels::page>
    <link rel="stylesheet" href="/style/file-explorer.css">
    {{-- Top Bar: Breadcrumbs + Search + Actions --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 16px; flex-wrap: wrap;">
        {{-- Breadcrumbs --}}
        <div class="fe-breadcrumb">
            @foreach($breadcrumbs as $crumb)
                @if(!$loop->last)
                    <span class="fe-breadcrumb-item" wire:click="navigateTo('{{ $crumb['path'] }}')">
                        @if($loop->first)
                            <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:2px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                        @endif
                        {{ $crumb['name'] }}
                    </span>
                    <span class="fe-breadcrumb-sep">/</span>
                @else
                    <span class="fe-breadcrumb-active">{{ $crumb['name'] }}</span>
                @endif
            @endforeach
        </div>

        {{-- Search --}}
        <div class="fe-toolbar">
            <form wire:submit="search" style="display: flex; gap: 6px;">
                <input type="text" wire:model="searchQuery" class="fe-search-input" placeholder="Search files... (e.g. .png or filename)">
                <button type="submit" class="fe-btn">Search</button>
                @if($isSearching)
                    <button type="button" wire:click="clearSearch" class="fe-btn">&times; Clear</button>
                @endif
            </form>
        </div>
    </div>

    {{-- Actions Bar: New Folder + Upload --}}
    <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 16px; flex-wrap: wrap;">
        <form wire:submit="createFolder" style="display: flex; gap: 6px; align-items: center;">
            <input type="text" wire:model="newFolderName" class="fe-small-input" placeholder="New folder name">
            <button type="submit" class="fe-btn">
                <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10.5v6m3-3H9m4.06-7.19l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" /></svg>
                Create Folder
            </button>
        </form>

        <label class="fe-btn" style="cursor: pointer;">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:4px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
            Choose Files
            <input type="file" wire:model="uploadedFiles" multiple style="display: none;">
        </label>

        @if($uploadedFiles)
            <button wire:click="uploadFiles" class="fe-btn fe-btn-primary">
                Upload {{ count($uploadedFiles) }} file{{ count($uploadedFiles) > 1 ? 's' : '' }}
            </button>
        @endif

        <span style="font-size: 12px; color: rgba(255,255,255,0.3);">Current: /{{ $currentPath ?: 'project root' }}</span>
    </div>

    {{-- Drag & Drop Zone --}}
    <div id="fe-dropzone"
         style="display: none; border: 2px dashed #6366f1; border-radius: 12px; padding: 40px; text-align: center; margin-bottom: 16px; background: rgba(99,102,241,0.05);">
        <svg xmlns="http://www.w3.org/2000/svg" style="width:48px;height:48px;margin:0 auto 12px;color:#6366f1;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
        <div style="font-size: 18px; font-weight: 600; color: #6366f1;">Drop files here to upload</div>
        <div style="font-size: 13px; color: rgba(255,255,255,0.4); margin-top: 4px;">to /{{ $currentPath ?: 'project root' }}</div>
    </div>

    {{-- Search Results --}}
    @if($isSearching)
        <div style="display: flex; gap: 24px;">
        <div style="flex: 1; min-width: 0;">
        <x-filament::section heading="Search Results ({{ count($searchResults) }}{{ count($searchResults) >= 100 ? '+' : '' }})">
            @if(empty($searchResults))
                <div style="text-align: center; color: rgba(255,255,255,0.4); padding: 24px;">No results found for "{{ $searchQuery }}"</div>
            @else
                <table class="fe-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th style="width: 200px;">Location</th>
                            <th style="width: 100px; text-align: right;">Size</th>
                            <th style="width: 90px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($searchResults as $result)
                            <tr class="fe-row"
                                data-ctx-path="{{ $result['path'] }}"
                                data-ctx-name="{{ $result['name'] }}"
                                data-ctx-dir="{{ $result['is_dir'] ? '1' : '0' }}"
                                data-ctx-folder="{{ $result['dir'] }}"
                            >
                                <td @if($result['is_dir']) wire:click="navigateTo('{{ $result['path'] }}')" @else wire:click="viewFile('{{ $result['path'] }}')" @endif>
                                    @if($result['is_dir'])
                                        <svg xmlns="http://www.w3.org/2000/svg" class="fe-icon fe-folder" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" /></svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="fe-icon fe-{{ $this->getFileIcon($result['ext'] ?? '') }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                    @endif
                                    {{ $result['name'] }}
                                </td>
                                <td style="font-size: 12px; color: rgba(255,255,255,0.4); font-family: monospace;">{{ $result['dir'] }}</td>
                                <td style="text-align: right; color: rgba(255,255,255,0.4); font-size: 13px;">
                                    {{ $result['is_dir'] ? '-' : $this->formatSize($result['size']) }}
                                </td>
                                <td style="text-align: right;">
                                    <span style="display: inline-flex; gap: 4px;">
                                        <button wire:click="navigateTo('{{ $result['dir'] === '/' ? '' : $result['dir'] }}')" class="fe-action-btn" title="Open folder location">
                                            <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" /></svg>
                                        </button>
                                        <button wire:click="startRename('{{ $result['path'] }}')" class="fe-action-btn" title="Rename">
                                            <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                                        </button>
                                        <button wire:click="deleteFile('{{ $result['path'] }}')" wire:confirm="Delete '{{ $result['name'] }}'? This cannot be undone." class="fe-action-btn fe-action-delete" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                        </button>
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </x-filament::section>
        </div>

        {{-- Preview Panel (in search mode) --}}
        @if($fileContent !== null)
            <div style="flex: 1; min-width: 0; position: sticky; top: 80px; align-self: flex-start;">
                <x-filament::section>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; margin-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                        <span class="font-mono" style="font-size: 13px; color: rgba(255,255,255,0.8);">{{ basename($viewingFile) }}</span>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <button wire:click="startRename('{{ $viewingFile }}')" class="fe-btn" style="padding: 4px 10px; font-size: 12px;">Rename</button>
                            <button wire:click="closeFile" style="color: rgba(255,255,255,0.5); cursor: pointer; background: none; border: none; font-size: 20px; line-height: 1;">&times;</button>
                        </div>
                    </div>

                    @if($fileContent === '__IMAGE__' && $imageUrl)
                        <div style="text-align: center; padding: 16px; background: rgba(0,0,0,0.3); border-radius: 8px;">
                            <img src="{{ $imageUrl }}" style="max-width: 100%; max-height: 500px; border-radius: 4px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                        </div>
                    @elseif($fileContent === '__PDF__' && $imageUrl)
                        <div style="padding: 8px; background: rgba(0,0,0,0.3); border-radius: 8px;">
                            <iframe src="{{ $imageUrl }}" style="width: 100%; height: 600px; border: none; border-radius: 4px;"></iframe>
                        </div>
                    @elseif($fileContent === '__VIDEO__' && $imageUrl)
                        <div style="text-align: center; padding: 16px; background: rgba(0,0,0,0.3); border-radius: 8px;">
                            <video controls style="max-width: 100%; max-height: 500px; border-radius: 4px;">
                                <source src="{{ $imageUrl }}">
                            </video>
                        </div>
                    @else
                        <div style="max-height: 600px; overflow: auto;">
                            <pre class="whitespace-pre-wrap text-xs font-mono bg-gray-900 text-gray-100 p-4" style="margin: 0; border-radius: 6px;">{{ $fileContent }}</pre>
                        </div>
                    @endif
                    @if($imageMeta)
                        <div style="display: flex; gap: 16px; justify-content: center; margin-top: 12px; font-size: 12px; color: rgba(255,255,255,0.5);">
                            @if($imageMeta['width'] && $imageMeta['height'])
                                <span>{{ $imageMeta['width'] }} &times; {{ $imageMeta['height'] }} px</span>
                            @endif
                            <span>{{ $imageMeta['size'] }}</span>
                            @if($imageMeta['type'])
                                <span>{{ $imageMeta['type'] }}</span>
                            @endif
                        </div>
                    @endif
                    <div style="margin-top: 8px; font-size: 11px; color: rgba(255,255,255,0.3); text-align: center; font-family: monospace;">{{ $viewingFile }}</div>
                </x-filament::section>
            </div>
        @endif
        </div>
    @endif

    {{-- Rename Modal --}}
    @if($renamingFile)
        <div style="position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 999; display: flex; align-items: center; justify-content: center;">
            <div style="background: #1e1e2e; border: 1px solid rgba(255,255,255,0.15); border-radius: 12px; padding: 24px; width: 420px; max-width: 90vw;">
                <div style="font-size: 16px; font-weight: 600; margin-bottom: 16px;">Rename</div>
                <div style="font-size: 12px; color: rgba(255,255,255,0.4); margin-bottom: 12px; font-family: monospace;">{{ $renamingFile }}</div>
                <form wire:submit="renameFile">
                    <input type="text" wire:model="newFileName" class="fe-search-input" style="width: 100%; margin-bottom: 16px;" autofocus>
                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                        <button type="button" wire:click="cancelRename" class="fe-btn">Cancel</button>
                        <button type="submit" class="fe-btn fe-btn-primary">Rename</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Main Content --}}
    @if(!$isSearching)
        <div style="display: flex; gap: 24px;">
            {{-- File List --}}
            <div style="flex: 1; min-width: 0;">
                <x-filament::section>
                    @if($currentPath)
                        <div class="fe-row" wire:click="goUp" style="padding: 8px 12px; border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;color:#fbbf24;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" /></svg>
                            <span style="color: rgba(255,255,255,0.6); font-size: 14px;">..</span>
                        </div>
                    @endif

                    <table class="fe-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th style="width: 100px; text-align: right;">Size</th>
                                <th style="width: 160px; text-align: right;">Modified</th>
                                <th style="width: 90px; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr class="fe-row"
                                    data-ctx-path="{{ $item['path'] }}"
                                    data-ctx-name="{{ $item['name'] }}"
                                    data-ctx-dir="{{ $item['is_dir'] ? '1' : '0' }}"
                                    @if(!$item['is_dir']) draggable="true" data-drag-path="{{ $item['path'] }}" @endif
                                    @if($item['is_dir']) data-drop-folder="{{ $item['path'] }}" @endif
                                >
                                    <td @if($item['is_dir']) wire:click="navigateTo('{{ $item['path'] }}')" @else wire:click="viewFile('{{ $item['path'] }}')" @endif>
                                        @if($item['is_dir'])
                                            <svg xmlns="http://www.w3.org/2000/svg" class="fe-icon fe-folder" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" /></svg>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="fe-icon fe-{{ $this->getFileIcon($item['ext'] ?? '') }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                        @endif
                                        <span style="{{ $item['is_dir'] ? 'font-weight: 600;' : '' }}">{{ $item['name'] }}</span>
                                    </td>
                                    <td style="text-align: right; color: rgba(255,255,255,0.4); font-size: 13px;">
                                        {{ $item['is_dir'] ? '-' : $this->formatSize($item['size']) }}
                                    </td>
                                    <td style="text-align: right; color: rgba(255,255,255,0.4); font-size: 13px;">
                                        {{ date('M j, Y g:ia', $item['modified']) }}
                                    </td>
                                    <td style="text-align: right;">
                                        <span style="display: inline-flex; gap: 4px;">
                                            <button wire:click="startRename('{{ $item['path'] }}')" class="fe-action-btn" title="Rename">
                                                <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                                            </button>
                                            <button wire:click="deleteFile('{{ $item['path'] }}')" wire:confirm="Delete '{{ $item['name'] }}'? This cannot be undone." class="fe-action-btn fe-action-delete" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                            </button>
                                        </span>
                                    </td>
                                </tr>
                            @endforeach

                            @if(empty($items))
                                <tr>
                                    <td colspan="4" style="text-align: center; color: rgba(255,255,255,0.4); padding: 24px;">Empty directory</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </x-filament::section>
            </div>

            {{-- File Viewer / Image Preview Panel --}}
            @if($fileContent !== null)
                <div style="flex: 1; min-width: 0; position: sticky; top: 80px; align-self: flex-start;">
                    <x-filament::section>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; margin-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                            <span class="font-mono" style="font-size: 13px; color: rgba(255,255,255,0.8);">{{ basename($viewingFile) }}</span>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <button wire:click="startRename('{{ $viewingFile }}')" class="fe-btn" style="padding: 4px 10px; font-size: 12px;">Rename</button>
                                <button wire:click="closeFile" style="color: rgba(255,255,255,0.5); cursor: pointer; background: none; border: none; font-size: 20px; line-height: 1;">&times;</button>
                            </div>
                        </div>

                        @if($fileContent === '__IMAGE__' && $imageUrl)
                            <div style="text-align: center; padding: 16px; background: rgba(0,0,0,0.3); border-radius: 8px;">
                                <img src="{{ $imageUrl }}" style="max-width: 100%; max-height: 500px; border-radius: 4px; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                            </div>
                        @elseif($fileContent === '__PDF__' && $imageUrl)
                            <div style="padding: 8px; background: rgba(0,0,0,0.3); border-radius: 8px;">
                                <iframe src="{{ $imageUrl }}" style="width: 100%; height: 600px; border: none; border-radius: 4px;"></iframe>
                            </div>
                        @elseif($fileContent === '__VIDEO__' && $imageUrl)
                            <div style="text-align: center; padding: 16px; background: rgba(0,0,0,0.3); border-radius: 8px;">
                                <video controls style="max-width: 100%; max-height: 500px; border-radius: 4px;">
                                    <source src="{{ $imageUrl }}">
                                </video>
                            </div>
                        @else
                            <div style="max-height: 600px; overflow: auto;">
                                <pre class="whitespace-pre-wrap text-xs font-mono bg-gray-900 text-gray-100 p-4" style="margin: 0; border-radius: 6px;">{{ $fileContent }}</pre>
                            </div>
                        @endif
                        @if($imageMeta)
                            <div style="display: flex; gap: 16px; justify-content: center; margin-top: 12px; font-size: 12px; color: rgba(255,255,255,0.5);">
                                @if($imageMeta['width'] && $imageMeta['height'])
                                    <span>{{ $imageMeta['width'] }} &times; {{ $imageMeta['height'] }} px</span>
                                @endif
                                <span>{{ $imageMeta['size'] }}</span>
                                @if($imageMeta['type'])
                                    <span>{{ $imageMeta['type'] }}</span>
                                @endif
                            </div>
                        @endif
                        <div style="margin-top: 8px; font-size: 11px; color: rgba(255,255,255,0.3); text-align: center; font-family: monospace;">{{ $viewingFile }}</div>
                    </x-filament::section>
                </div>
            @endif
        </div>
    @endif

    <script src="/js/file-explorer.js"></script>
</x-filament-panels::page>
