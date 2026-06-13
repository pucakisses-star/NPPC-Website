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

    /**
     * Create a hosted Checkout session for a store order. Line items are built
     * from the cart (see CartController), each with explicit price_data so we
     * never depend on pre-created Stripe prices.
     *
     * @param  array<int, array<string, mixed>>  $lineItems
     *
     * @throws ApiErrorException
     */
    public function createProductCheckoutSession(array $lineItems, string $orderId) {
        return $this->client->checkout->sessions->create([
            'mode'                        => 'payment',
            'line_items'                  => $lineItems,
            'shipping_address_collection' => ['allowed_countries' => ['US', 'CA']],
            'client_reference_id'         => $orderId,
            'metadata'                    => ['order_id' => $orderId],
            'success_url'                 => url('/checkout/success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'                  => url('/cart'),
        ]);
    }
}
