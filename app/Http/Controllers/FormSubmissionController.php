<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

final class FormSubmissionController extends Controller {
    public function submit(Request $request, string $form) {
        // Validate the form type against an allowed list
        $allowedForms = ['contact', 'volunteer'];
        if (! in_array($form, $allowedForms, true)) {
            abort(404);
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
            if (in_array($k, ['_token', 'g-recaptcha-response'], true)) {
                continue;
            }
            $data[$k] = $v;
        }

        FormSubmission::create([
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

        try {
            Mail::raw($formattedData, function ($message) use ($subject) {
                $message->to('info@nationalpoliticalprisonercoalition.org')
                    ->subject($subject);
            });
        } catch (\Exception $e) {
            // Email may fail if mail isn't configured — submission is already saved
        }

        return redirect("/{$form}?form_submitted=true");
    }
}
