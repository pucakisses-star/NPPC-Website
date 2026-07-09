<?php

namespace App\Http\Controllers;

use App\Domains\Stripe;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class CartController extends Controller {
    public function index(CartService $cart) {
        return view('pages.cart', [
            'items'    => $cart->items(),
            'subtotal' => $cart->subtotal(),
        ]);
    }

    public function add(Request $request, CartService $cart) {
        $data = $request->validate([
            'product_id' => ['required', 'string'],
            'size'       => ['nullable', 'string', 'max:20'],
            'quantity'   => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::published()->findOrFail($data['product_id']);
        $qty = (int) ($data['quantity'] ?? 1);
        $cart->add($product, $qty, $data['size'] ?? null);

        // Fetch-driven adds (the slide-out cart drawer) get the cart back as
        // JSON; plain form posts keep the classic redirect to /cart.
        if ($request->wantsJson()) {
            return response()->json($this->payload($cart) + [
                'added' => ['name' => $product->name, 'quantity' => $qty, 'size' => $data['size'] ?? null],
            ]);
        }

        return redirect('/cart')->with('cart_status', $product->name.' was added to your cart.');
    }

    public function update(Request $request, CartService $cart) {
        $data = $request->validate([
            'key'      => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $cart->update($data['key'], (int) $data['quantity']);

        if ($request->wantsJson()) {
            return response()->json($this->payload($cart));
        }

        return redirect('/cart');
    }

    public function remove(Request $request, CartService $cart) {
        $data = $request->validate(['key' => ['required', 'string']]);
        $cart->remove($data['key']);

        if ($request->wantsJson()) {
            return response()->json($this->payload($cart));
        }

        return redirect('/cart');
    }

    /** Cart state as JSON for the slide-out drawer. */
    private function payload(CartService $cart): array {
        return [
            'items' => $cart->items()->map(fn ($i) => [
                'key'        => $i->key,
                'name'       => $i->name,
                'slug'       => $i->slug,
                'image_url'  => $i->image_url,
                'size'       => $i->size,
                'price'      => (float) $i->price,
                'quantity'   => (int) $i->quantity,
                'line_total' => (float) $i->line_total,
            ])->values()->all(),
            'subtotal' => (float) $cart->subtotal(),
            'count'    => $cart->count(),
        ];
    }

    public function checkout(CartService $cart) {
        $items = $cart->items();
        if ($items->isEmpty()) {
            return redirect('/cart')->with('cart_status', 'Your cart is empty.');
        }

        // Persist a pending order (prices come from the DB, never the client).
        $order = new Order();
        $order->reference = 'NPPC-'.strtoupper(Str::random(8));
        $order->status = 'pending';
        $order->currency = 'usd';
        $order->total = $items->sum('line_total');
        $order->save();

        foreach ($items as $item) {
            $order->items()->create([
                'product_id' => $item->product->id,
                'name'       => $item->name,
                'size'       => $item->size,
                'price'      => $item->price,
                'quantity'   => $item->quantity,
            ]);
        }

        $lineItems = $items->map(function ($item) {
            $name = $item->name.($item->size ? ' ('.$item->size.')' : '');

            return [
                'quantity'   => $item->quantity,
                'price_data' => [
                    'currency'     => 'usd',
                    'product_data' => ['name' => $name],
                    'unit_amount'  => (int) round($item->price * 100),
                ],
            ];
        })->values()->all();

        try {
            $session = (new Stripe())->createProductCheckoutSession($lineItems, $order->id);
        } catch (\Throwable $e) {
            report($e);
            $order->update(['status' => 'failed']);

            return redirect('/cart')->with('cart_status', 'We could not start checkout. Please try again, or email info@nationalpoliticalprisonercoalition.org.');
        }

        $order->update(['stripe_session_id' => $session->id]);

        return redirect()->away($session->url);
    }

    public function success(Request $request, CartService $cart) {
        $sessionId = $request->get('session_id');
        if (! $sessionId) {
            abort(404);
        }

        try {
            $details = (new Stripe())->getSessionDetails($sessionId);
        } catch (\Throwable $e) {
            abort(404);
        }

        $order = Order::where('stripe_session_id', $sessionId)->first()
            ?? Order::find($details->metadata->order_id ?? null);

        if ($order && $details->payment_status === 'paid' && ! $order->isPaid()) {
            $order->update([
                'status'           => 'paid',
                'payment_status'   => $details->payment_status,
                'customer_email'   => $details->customer_details->email ?? null,
                'customer_name'    => $details->customer_details->name ?? null,
                'shipping_address' => $this->formatShipping($details),
            ]);
            $cart->clear();
        }

        return view('pages.store-callback', [
            'order'         => $order,
            'paymentStatus' => $details->payment_status ?? null,
        ]);
    }

    private function formatShipping($details): ?string {
        $ship = $details->shipping_details ?? $details->customer_details ?? null;
        $addr = $ship->address ?? null;
        if (! $addr) {
            return null;
        }

        $parts = array_filter([
            $ship->name ?? null,
            $addr->line1 ?? null,
            $addr->line2 ?? null,
            trim(($addr->city ?? '').' '.($addr->state ?? '').' '.($addr->postal_code ?? '')),
            $addr->country ?? null,
        ]);

        return $parts ? implode("\n", $parts) : null;
    }
}
