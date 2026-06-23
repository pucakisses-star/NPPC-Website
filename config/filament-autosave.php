<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin panel autosave (save-on-blur)
    |--------------------------------------------------------------------------
    |
    | When enabled, Filament resource Edit pages save automatically whenever a
    | field loses focus, so admins don't have to click the Save button. It is
    | disabled by default; set FILAMENT_AUTOSAVE=true in the environment to turn
    | it on, then run `php artisan config:clear`.
    |
    | Each autosave runs the exact same validated save() the Save button runs
    | (no redirect, no "Saved" toast). If the form is mid-edit and not yet valid
    | the autosave is skipped silently until it becomes valid; the edited field's
    | own validation errors still appear as usual.
    |
    */

    'enabled' => (bool) env('FILAMENT_AUTOSAVE', false),
];
