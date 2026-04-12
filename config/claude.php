<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Claude Code Binary Path
    |--------------------------------------------------------------------------
    |
    | The path to the Claude Code CLI binary. If Claude Code is installed
    | globally, just 'claude' should work. Otherwise, provide the full path.
    |
    */
    'binary' => env('CLAUDE_BINARY', 'claude'),

    /*
    |--------------------------------------------------------------------------
    | Repository Path
    |--------------------------------------------------------------------------
    |
    | The path to the main git repository. Worktrees are created from this
    | repo. Defaults to the Laravel application base path.
    |
    */
    'repo_path' => env('CLAUDE_REPO_PATH', base_path()),

    /*
    |--------------------------------------------------------------------------
    | Worktree Base Directory
    |--------------------------------------------------------------------------
    |
    | Directory where git worktrees are created for isolated Claude sessions.
    | Each session gets its own subdirectory. This should be outside the
    | main repo and writable by the web server user.
    |
    */
    'worktree_base' => env('CLAUDE_WORKTREE_BASE', '/tmp/claude-worktrees'),

    /*
    |--------------------------------------------------------------------------
    | Max Concurrent Sessions
    |--------------------------------------------------------------------------
    |
    | Maximum number of Claude sessions that can run simultaneously.
    | Set to 1 to prevent conflicts.
    |
    */
    'max_concurrent' => env('CLAUDE_MAX_CONCURRENT', 1),

    /*
    |--------------------------------------------------------------------------
    | Home Directory
    |--------------------------------------------------------------------------
    |
    | The home directory of the user that ran `claude login`. Claude stores
    | its auth tokens in ~/.claude/. When the web server runs as a different
    | user (e.g. www-data), this must point to the home directory of the
    | user who authenticated the CLI (typically /root).
    |
    */
    'home' => env('CLAUDE_HOME', '/root'),
];
