<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderItem extends Model {
    protected $casts = [
        'price'    => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function order(): BelongsTo {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }

    public function getLineTotalAttribute(): float {
        return (float) $this->price * $this->quantity;
    }
}
