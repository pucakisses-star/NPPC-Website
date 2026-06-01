<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

final class FormSubmissionController extends Controller {
    public function submit(Request $request, string $form) {
        // Validate the form type against an allowed list
        $allowedForms = ['contact', 'volunteer', 'prisoner-letter', 'contribution', 'article-submission'];
        if (! in_array($form, $allowedForms, true)) {
            abort(404);
        }

        // Honeypot: a hidden "website" field that real users never fill. If a
        // bot populates it, pretend the submission succeeded but store nothing.
        if (filled($request->input('website'))) {
            return $form === 'article-submission'
                ? redirect('/dashboard?form_submitted=true&form=article')
                : redirect()->back();
        }

        // reCAPTCHA validation (only if secret is configured)
        $secretKey = config('services.recaptcha.secret');
        if ($secretKey) {
            $recaptchaResponse = $request->input('g-recaptcha-response');
            if (! $recaptchaResponse) {
                return redirect()->back()->withErrors(['captcha' => 'ReCAPTCHA verification is required.'])->withInput();
            }

            $client   = new Client();
            $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
                'form_params' => ['secret' => $secretKey, 'response' => $recaptchaResponse, 'remoteip' => $request->ip()],
            ]);

            $body = json_decode((string) $response->getBody());

            if (! $body->success) {
                return redirect()->back()->withErrors(['captcha' => 'ReCAPTCHA validation failed.'])->withInput();
            }
        }

        $data = [];
        foreach ($request->all() as $k => $v) {
            if (in_array($k, ['_token', 'g-recaptcha-response', 'website'], true)) {
                continue;
            }
            $data[$k] = $v;
        }

        $submission = FormSubmission::create([
            'form_type' => $form,
            'data'      => $data,
            'status'    => 'new',
        ]);

        // Send email notification
        $formattedData = collect($data)->map(function ($value, $key) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            $formattedKey = ucwords(str_replace('_', ' ', $key));

            return "{$formattedKey}: {$value}";
        })->implode("\n");

        $subject = 'Form submission';
        if ($form === 'contact') {
            $subject = 'Contact form submission';
        }

        if ($form === 'volunteer') {
            $subject = 'Volunteer form submission';
        }

        if ($form === 'prisoner-letter') {
            $subject = 'Letter to a prisoner';
        }

        if ($form === 'contribution') {
            $subject = 'Database contribution';
        }

        if ($form === 'article-submission') {
            $subject = 'Article submitted for review';
        }

        // The submission is already persisted, so a mail failure can't lose
        // data — but it CAN silently strand notifications. Log loudly with
        // enough context to track it down (submission id + form + driver +
        // exception class + message).
        try {
            Mail::raw($formattedData, function ($message) use ($subject) {
                $message->to('info@nationalpoliticalprisonercoalition.org')
                    ->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::error('Form-submission notification email failed to send.', [
                'submission_id' => $submission->id,
                'form'          => $form,
                'subject'       => $subject,
                'mail_driver'   => config('mail.default'),
                'mail_host'     => config('mail.mailers.'.config('mail.default').'.host'),
                'exception'     => $e::class,
                'message'       => $e->getMessage(),
            ]);
        }

        $redirectPath = match ($form) {
            'prisoner-letter' => '/prisoner-outreach',
            'contribution' => '/topics/contributions',
            'article-submission' => '/dashboard',
            default => "/{$form}",
        };

        // The dashboard form distinguishes its thank-you via &form=article.
        $extra = $form === 'article-submission' ? '&form=article' : '';

        return redirect("{$redirectPath}?form_submitted=true{$extra}");
    }
}
