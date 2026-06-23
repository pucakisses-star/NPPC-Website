<?php

namespace App\Filament\Concerns;

use Illuminate\Validation\ValidationException;

/**
 * Saves a Filament resource Edit page automatically when a form field loses
 * focus, so admins don't have to click Save. Mix this into an EditRecord page.
 *
 * Combined with fields being live-on-blur (configured globally in
 * AppServiceProvider when config('filament-autosave.enabled') is true), Livewire
 * invokes updatedAutosavesOnBlur() on every field blur. That method name follows
 * Livewire's "updated{TraitBasename}" convention, so it is called as an additive
 * trait lifecycle hook — it does not override Filament's own form reactivity
 * (updatedInteractsWithForms), which still runs in the same request.
 *
 * Each autosave calls the page's normal save() (the same one the Save button
 * uses), minus the redirect and the "Saved" notification. Guards:
 *   - no-op entirely unless config('filament-autosave.enabled') is true;
 *   - only react to changes under the form's state path ("data.*"), not to
 *     unrelated Livewire properties;
 *   - a re-entrancy flag prevents a save-triggered update from looping;
 *   - ValidationException is swallowed so a mid-edit invalid form doesn't nag
 *     with a whole-form error (the edited field's own validation still shows);
 *     persistence simply waits until the form is valid.
 */
trait AutosavesOnBlur
{
    protected bool $isAutosaving = false;

    public function updatedAutosavesOnBlur(string $statePath, mixed $value): void
    {
        if (! config('filament-autosave.enabled', false)) {
            return;
        }

        $formPath = $this->getFormStatePath();
        if (! $formPath || ! str_starts_with($statePath, $formPath.'.')) {
            return;
        }

        if ($this->isAutosaving) {
            return;
        }

        $this->isAutosaving = true;

        try {
            $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
        } catch (ValidationException) {
            // Form is mid-edit / not yet valid: skip persisting for now.
        } finally {
            $this->isAutosaving = false;
        }
    }
}
