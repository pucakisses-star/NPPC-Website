<?php

namespace App\Http\Controllers;

use App\Domains\Stripe;
use App\DTOs\StripeCheckoutResponseDTO;
use App\Models\Donation;
use Illuminate\Http\Request;

final class DonateController extends Controller {
    public function callback(Request $request) {
        $sessionId = $request->get('session_id');

        if (! $sessionId) {
            abort(404);
        }

        try {
            $stripe  = new Stripe();
            $details = $stripe->getSessionDetails($sessionId);
        } catch (\Exception $e) {
            abort(404);
        }

        $DTO = new StripeCheckoutResponseDTO(
            id: $details->id,
            amount: $details->amount_total,
            status: $details->status,
            paymentStatus: $details->payment_status,
            customerEmail: $details->customer_details?->email,
            customerName: $details->customer_details?->name
        );

        // Record the donation so it shows up in the admin, not only in
        // Stripe. Store checkouts land on /checkout/success instead, but
        // guard on order metadata anyway. Recording must never break the
        // thank-you page.
        try {
            if (empty($details->metadata?->order_id)) {
                Donation::updateOrCreate(
                    ['stripe_session_id' => $details->id],
                    [
                        'amount'         => (int) $details->amount_total,
                        'currency'       => $details->currency ?? 'usd',
                        'mode'           => $details->mode ?? 'payment',
                        'status'         => (string) $details->status,
                        'payment_status' => (string) $details->payment_status,
                        'donor_name'     => $details->customer_details?->name,
                        'donor_email'    => $details->customer_details?->email,
                    ],
                );
            }
        } catch (\Exception $e) {
            report($e);
        }

        return view('pages.donate-callback', ['DTO' => $DTO]);
    }
}
