<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * Session-backed shopping cart. Stores only the minimal {product_id, size,
 * quantity} per line; product names and prices are always hydrated live from
 * the database so a stale or tampered client value can never set the price.
 */
final class CartService {
    private const KEY = 'cart';

    public static function lineKey(string $productId, ?string $size): string {
        return md5($productId.'|'.((string) $size));
    }

    public function add(Product $product, int $quantity = 1, ?string $size = null): void {
        $quantity = max(1, $quantity);
        $size = $size !== null && $size !== '' ? $size : null;
        $lines = $this->lines();
        $key = self::lineKey($product->id, $size);

        if (isset($lines[$key])) {
            $lines[$key]['quantity'] += $quantity;
        } else {
            $lines[$key] = [
                'product_id' => $product->id,
                'size'       => $size,
                'quantity'   => $quantity,
            ];
        }
        $lines[$key]['quantity'] = min(99, $lines[$key]['quantity']);
        $this->save($lines);
    }

    public function update(string $key, int $quantity): void {
        $lines = $this->lines();
        if (! isset($lines[$key])) {
            return;
        }
        if ($quantity <= 0) {
            unset($lines[$key]);
        } else {
            $lines[$key]['quantity'] = min(99, $quantity);
        }
        $this->save($lines);
    }

    public function remove(string $key): void {
        $lines = $this->lines();
        unset($lines[$key]);
        $this->save($lines);
    }

    public function clear(): void {
        Session::forget(self::KEY);
    }

    /**
     * Cart lines hydrated with live product data. Lines whose product no longer
     * exists or is unpublished are silently dropped.
     *
     * @return Collection<int, object>
     */
    public function items(): Collection {
        $lines = $this->lines();
        if (empty($lines)) {
            return collect();
        }

        $ids = collect($lines)->pluck('product_id')->unique()->all();
        $products = Product::published()->findMany($ids)->keyBy('id');

        $items = collect();
        $changed = false;
        foreach ($lines as $key => $line) {
            $product = $products->get($line['product_id']);
            if (! $product) {
                unset($lines[$key]);
                $changed = true;

                continue;
            }
            $price = (float) $product->price;
            $qty = (int) $line['quantity'];
            $items->push((object) [
                'key'        => $key,
                'product'    => $product,
                'name'       => $product->name,
                'slug'       => $product->slug,
                'image_url'  => $product->image_url,
                'size'       => $line['size'],
                'price'      => $price,
                'quantity'   => $qty,
                'line_total' => $price * $qty,
            ]);
        }
        if ($changed) {
            $this->save($lines);
        }

        return $items;
    }

    public function count(): int {
        return (int) collect($this->lines())->sum('quantity');
    }

    public function subtotal(): float {
        return (float) $this->items()->sum('line_total');
    }

    public function isEmpty(): bool {
        return empty($this->lines());
    }

    /** @return array<string, array{product_id:string, size:?string, quantity:int}> */
    private function lines(): array {
        return Session::get(self::KEY, []);
    }

    private function save(array $lines): void {
        Session::put(self::KEY, $lines);
    }
}
