<?php

namespace App\Domains;

use App\Enum\StripeDonationInterval;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

final class Stripe {
    private StripeClient $client;

    public function __construct() {
        $key = config('stripe.sk');
        if (empty($key)) {
            throw new \RuntimeException('Stripe secret key is not configured. Set STRIPE_SK in your .env file.');
        }
        $this->client = new StripeClient($key);
    }

    public function getSessionDetails(string $sessionId) {
        return $this->client->checkout->sessions->retrieve($sessionId, [
            'expand' => ['line_items'],
        ]);
    }

    /**
     * @throws ApiErrorException
     */
    public function createPaymentSession(StripeDonationInterval $interval, ?int $amount) {
        $mode = $interval === StripeDonationInterval::OneTime ? 'payment' : 'subscription';

        if ($amount) {
            $lineItems = [
                [
                    'quantity'   => 1,
                    'price_data' => [
                        'currency'     => 'usd',
                        'product_data' => ['name' => config('stripe.donation_name')],
                        'unit_amount'  => $amount,
                    ],
                ],
            ];

            if ($interval !== StripeDonationInterval::OneTime) {
                $lineItems[0]['price_data']['recurring'] = ['interval' => $interval->value];
            }
        } else {
            $lineItems = [
                [
                    'price'    => config('stripe.donation_price_id'),
                    'quantity' => 1,
                ],
            ];
        }

        return $this->client->checkout->sessions->create([
            'mode'        => $mode,
            'line_items'  => $lineItems,
            'success_url' => url('/donate-callback').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => url('/donate'),
        ]);
    }
}
