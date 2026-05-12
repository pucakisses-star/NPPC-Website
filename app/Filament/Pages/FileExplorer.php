<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Livewire\WithFileUploads;

class FileExplorer extends Page {
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';
    protected static ?string $navigationLabel = 'File Explorer';
    protected static ?string $navigationGroup = 'Developer Tools';
    protected static ?int $navigationSort = 201;
    protected static string $view = 'filament.pages.file-explorer';

    public string $currentPath = '';
    public ?string $fileContent = null;
    public ?string $viewingFile = null;
    public string $searchQuery = '';
    public array $searchResults = [];
    public bool $isSearching = false;
    public int $searchPage = 1;
    public int $searchPerPage = 50;
    public string $newFolderName = '';
    public $uploadedFiles = [];
    public ?string $renamingFile = null;
    public string $newFileName = '';

    public function mount(): void {
        $this->currentPath = '';
    }

    public function navigateTo(string $path): void {
        $this->currentPath = $path;
        $this->fileContent = null;
        $this->viewingFile = null;
        $this->clearSearch();
    }

    public function goUp(): void {
        $this->currentPath = dirname($this->currentPath);
        if ($this->currentPath === '.') {
            $this->currentPath = '';
        }
        $this->fileContent = null;
        $this->viewingFile = null;
    }

    public function viewFile(string $path): void {
        // Always reset first so Livewire detects the change
        $this->fileContent = null;
        $this->viewingFile = null;

        $fullPath = $this->getFullPath($path);

        if (! is_file($fullPath) || ! is_readable($fullPath)) {
            $this->fileContent = '(Cannot read file)';
            $this->viewingFile = $path;

            return;
        }

        $size = filesize($fullPath);
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        // Images — always preview regardless of size
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico', 'bmp'])) {
            $this->fileContent = '__IMAGE__';
            $this->viewingFile = $path;

            return;
        }

        // PDFs — always preview regardless of size
        if ($ext === 'pdf') {
            $this->fileContent = '__PDF__';
            $this->viewingFile = $path;

            return;
        }

        // Videos — show info
        if (in_array($ext, ['mp4', 'webm', 'mov', 'avi'])) {
            $this->fileContent = '__VIDEO__';
            $this->viewingFile = $path;

            return;
        }

        // Text files — cap at 2MB
        if ($size > 2097152) {
            $this->fileContent = "(File too large to display: ".number_format($size / 1024, 1)." KB)";
            $this->viewingFile = $path;

            return;
        }

        // Other binary files
        if ($this->isBinaryFile($fullPath)) {
            $this->fileContent = "(Binary file: ".number_format($size / 1024, 1)." KB)";
            $this->viewingFile = $path;

            return;
        }

        $this->fileContent = file_get_contents($fullPath);
        $this->viewingFile = $path;
    }

    public function closeFile(): void {
        $this->fileContent = null;
        $this->viewingFile = null;
    }

    // --- Search ---

    public function search(): void {
        $query = trim($this->searchQuery);
        if (! $query) {
            $this->clearSearch();

            return;
        }

        $this->isSearching = true;
        $this->searchResults = [];
        $this->searchPage = 1;
        $basePath = base_path();

        $this->searchDirectory($basePath, '', $query, 0);
    }

    public function clearSearch(): void {
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->isSearching = false;
        $this->searchPage = 1;
    }

    public function setSearchPage(int $page): void {
        $total = count($this->searchResults);
        $totalPages = max(1, (int) ceil($total / max(1, $this->searchPerPage)));
        $this->searchPage = max(1, min($page, $totalPages));
    }

    public function openFolderLocation(string $folder): void {
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->isSearching = false;
        $this->fileContent = null;
        $this->viewingFile = null;
        $this->currentPath = ($folder === '/' || $folder === '.') ? '' : $folder;
    }

    private function searchDirectory(string $basePath, string $relativePath, string $query, int $depth): void {
        if ($depth > 8 || count($this->searchResults) >= 500) {
            return;
        }

        $fullPath = $relativePath ? $basePath.DIRECTORY_SEPARATOR.$relativePath : $basePath;

        if (! is_dir($fullPath) || ! is_readable($fullPath)) {
            return;
        }

        $entries = @scandir($fullPath);
        if (! $entries) {
            return;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            if (in_array($entry, ['.git', 'node_modules', 'vendor'])) {
                continue;
            }

            $entryPath = $relativePath ? $relativePath.DIRECTORY_SEPARATOR.$entry : $entry;
            $entryFullPath = $basePath.DIRECTORY_SEPARATOR.$entryPath;
            $isDir = is_dir($entryFullPath);

            // Match by name or extension
            $matches = false;
            if (str_starts_with($query, '.')) {
                // Extension search: ".png" matches all png files
                $ext = '.'.strtolower(pathinfo($entry, PATHINFO_EXTENSION));
                $matches = ! $isDir && $ext === strtolower($query);
            } else {
                // Name search: case-insensitive substring
                $matches = stripos($entry, $query) !== false;
            }

            if ($matches) {
                $this->searchResults[] = [
                    'name'     => $entry,
                    'path'     => $entryPath,
                    'dir'      => $relativePath ?: '/',
                    'is_dir'   => $isDir,
                    'size'     => $isDir ? null : @filesize($entryFullPath),
                    'ext'      => $isDir ? null : strtolower(pathinfo($entry, PATHINFO_EXTENSION)),
                ];

                if (count($this->searchResults) >= 500) {
                    return;
                }
            }

            if ($isDir) {
                $this->searchDirectory($basePath, $entryPath, $query, $depth + 1);
            }
        }
    }

    // --- Create Folder ---

    public function createFolder(): void {
        $name = trim($this->newFolderName);
        if (! $name || ! preg_match('/^[a-zA-Z0-9_\-. ]+$/', $name)) {
            return;
        }

        $basePath = base_path();
        $targetPath = $this->currentPath
            ? $basePath.DIRECTORY_SEPARATOR.$this->currentPath.DIRECTORY_SEPARATOR.$name
            : $basePath.DIRECTORY_SEPARATOR.$name;

        if (! is_dir($targetPath)) {
            mkdir($targetPath, 0755, true);
        }

        $this->newFolderName = '';
    }

    // --- Upload Files ---

    public function uploadFiles(): void {
        if (empty($this->uploadedFiles)) {
            return;
        }

        $basePath = base_path();
        $targetDir = $this->currentPath
            ? $basePath.DIRECTORY_SEPARATOR.$this->currentPath
            : $basePath;

        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        foreach ($this->uploadedFiles as $file) {
            $filename = $file->getClientOriginalName();
            $destination = $targetDir.DIRECTORY_SEPARATOR.$filename;
            file_put_contents($destination, file_get_contents($file->getRealPath()));
        }

        $this->uploadedFiles = [];
    }

    // --- Rename ---

    public function startRename(string $path): void {
        $this->renamingFile = $path;
        $this->newFileName = basename($path);
    }

    public function cancelRename(): void {
        $this->renamingFile = null;
        $this->newFileName = '';
    }

    public function renameFile(): void {
        if (! $this->renamingFile || ! $this->newFileName) {
            return;
        }

        $name = trim($this->newFileName);
        if (! $name || ! preg_match('/^[a-zA-Z0-9_\-. ()]+$/', $name)) {
            return;
        }

        $basePath = base_path();
        $oldFullPath = $basePath.DIRECTORY_SEPARATOR.$this->renamingFile;
        $newFullPath = dirname($oldFullPath).DIRECTORY_SEPARATOR.$name;

        if (! file_exists($oldFullPath) || file_exists($newFullPath)) {
            return;
        }

        rename($oldFullPath, $newFullPath);

        // If we were viewing this file, update the viewer
        if ($this->viewingFile === $this->renamingFile) {
            $this->viewingFile = dirname($this->renamingFile).DIRECTORY_SEPARATOR.$name;
            if (str_starts_with($this->viewingFile, '.'.DIRECTORY_SEPARATOR)) {
                $this->viewingFile = substr($this->viewingFile, 2);
            }
        }

        $this->renamingFile = null;
        $this->newFileName = '';
    }

    // --- Move File ---

    public function moveFile(string $sourcePath, string $targetFolder): void {
        $basePath = base_path();
        $sourceFullPath = $basePath.DIRECTORY_SEPARATOR.$sourcePath;
        $targetDir = $basePath.DIRECTORY_SEPARATOR.$targetFolder;
        $filename = basename($sourcePath);
        $targetFullPath = $targetDir.DIRECTORY_SEPARATOR.$filename;

        if (! file_exists($sourceFullPath) || ! is_dir($targetDir) || file_exists($targetFullPath)) {
            return;
        }

        rename($sourceFullPath, $targetFullPath);
    }

    // --- Copy File ---

    public function copyFile(string $path): void {
        $basePath = base_path();
        $fullPath = $basePath.DIRECTORY_SEPARATOR.$path;

        if (! is_file($fullPath)) {
            return;
        }

        $dir = dirname($fullPath);
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $name = pathinfo($path, PATHINFO_FILENAME);
        $newName = $name.' - Copy'.($ext ? '.'.$ext : '');
        $newPath = $dir.DIRECTORY_SEPARATOR.$newName;

        $i = 2;
        while (file_exists($newPath)) {
            $newName = $name." - Copy ({$i})".($ext ? '.'.$ext : '');
            $newPath = $dir.DIRECTORY_SEPARATOR.$newName;
            $i++;
        }

        copy($fullPath, $newPath);
    }

    // --- Delete ---

    public function deleteFile(string $path): void {
        $basePath = base_path();
        $fullPath = $basePath.DIRECTORY_SEPARATOR.$path;

        if (is_file($fullPath)) {
            unlink($fullPath);
        } elseif (is_dir($fullPath)) {
            // Only delete empty directories for safety
            $contents = array_diff(scandir($fullPath), ['.', '..']);
            if (empty($contents)) {
                rmdir($fullPath);
            }
        }

        if ($this->viewingFile === $path) {
            $this->closeFile();
        }
    }

    // --- View Data ---

    public function getViewData(): array {
        $basePath = base_path();
        $fullPath = $this->currentPath ? $basePath.DIRECTORY_SEPARATOR.$this->currentPath : $basePath;

        $items = [];

        if (is_dir($fullPath)) {
            $entries = scandir($fullPath);

            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                if ($this->currentPath === '' && in_array($entry, ['node_modules', '.git'])) {
                    continue;
                }

                $entryPath = $this->currentPath ? $this->currentPath.DIRECTORY_SEPARATOR.$entry : $entry;
                $entryFullPath = $basePath.DIRECTORY_SEPARATOR.$entryPath;

                $isDir = is_dir($entryFullPath);
                $size = $isDir ? null : @filesize($entryFullPath);
                $modified = @filemtime($entryFullPath);

                $items[] = [
                    'name'     => $entry,
                    'path'     => $entryPath,
                    'is_dir'   => $isDir,
                    'size'     => $size,
                    'modified' => $modified,
                    'ext'      => $isDir ? null : strtolower(pathinfo($entry, PATHINFO_EXTENSION)),
                ];
            }

            usort($items, function ($a, $b) {
                if ($a['is_dir'] && ! $b['is_dir']) {
                    return -1;
                }
                if (! $a['is_dir'] && $b['is_dir']) {
                    return 1;
                }

                return strcasecmp($a['name'], $b['name']);
            });
        }

        $breadcrumbs = [['name' => 'Project Root', 'path' => '']];
        if ($this->currentPath) {
            $parts = explode(DIRECTORY_SEPARATOR, $this->currentPath);
            $accumulated = '';
            foreach ($parts as $part) {
                $accumulated = $accumulated ? $accumulated.DIRECTORY_SEPARATOR.$part : $part;
                $breadcrumbs[] = ['name' => $part, 'path' => $accumulated];
            }
        }

        // Get preview URL for images, PDFs, videos
        $previewUrl = null;
        if ($this->viewingFile && in_array($this->fileContent, ['__IMAGE__', '__PDF__', '__VIDEO__'])) {
            $previewUrl = $this->resolveImageUrl($this->viewingFile);
        }

        // Get file metadata
        $fileMeta = null;
        if ($previewUrl && $this->viewingFile) {
            $metaFullPath = $basePath.DIRECTORY_SEPARATOR.$this->viewingFile;
            if (is_file($metaFullPath)) {
                $size = @filesize($metaFullPath);
                $fileMeta = [
                    'size'   => $this->formatSize($size),
                    'width'  => null,
                    'height' => null,
                    'type'   => mime_content_type($metaFullPath) ?: null,
                ];

                if ($this->fileContent === '__IMAGE__') {
                    $dims = @getimagesize($metaFullPath);
                    if ($dims) {
                        $fileMeta['width'] = $dims[0];
                        $fileMeta['height'] = $dims[1];
                    }
                }
            }
        }

        return [
            'items'       => $items,
            'breadcrumbs' => $breadcrumbs,
            'imageUrl'    => $previewUrl, // kept as imageUrl for template compat
            'imageMeta'   => $fileMeta,
        ];
    }

    public function getThumbnailUrl(string $relativePath, string $ext): ?string {
        if (! in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
            return null;
        }

        return $this->resolveImageUrl($relativePath);
    }

    private function resolveImageUrl(string $relativePath): ?string {
        // storage/app/public/... -> served via /storage/...
        if (str_starts_with($relativePath, 'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR)) {
            $webPath = substr($relativePath, strlen('storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR));

            return asset('storage/'.$webPath);
        }

        // public/... -> served directly
        if (str_starts_with($relativePath, 'public'.DIRECTORY_SEPARATOR)) {
            return asset(substr($relativePath, strlen('public'.DIRECTORY_SEPARATOR)));
        }

        // Fallback: serve via a data URI route
        return route('filament.admin.pages.file-explorer').'?preview='.urlencode($relativePath);
    }

    public function formatSize(?int $bytes): string {
        if ($bytes === null) {
            return '-';
        }
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return number_format($bytes / 1024, 1).' KB';
        }

        return number_format($bytes / 1048576, 1).' MB';
    }

    public function getFileIcon(string $ext): string {
        return match ($ext) {
            'php'                                       => 'php',
            'js', 'ts'                                  => 'js',
            'vue'                                       => 'vue',
            'css', 'scss', 'sass'                       => 'css',
            'html'                                      => 'html',
            'json'                                      => 'json',
            'md'                                        => 'md',
            'yml', 'yaml'                               => 'yaml',
            'env'                                       => 'env',
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg' => 'img',
            'sql', 'sqlite'                             => 'db',
            'mp4', 'webm', 'mov'                        => 'video',
            'pdf'                                       => 'pdf',
            default                                     => 'file',
        };
    }

    private function getFullPath(string $path): string {
        $basePath = base_path();

        return $basePath.DIRECTORY_SEPARATOR.$path;
    }

    private function isBinaryFile(string $path): bool {
        $content = file_get_contents($path, false, null, 0, 8192);

        return str_contains($content, "\0");
    }
}
