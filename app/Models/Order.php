<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

final class Order extends Model {
    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function items(): HasMany {
        return $this->hasMany(OrderItem::class);
    }

    public function isPaid(): bool {
        return in_array($this->status, ['paid', 'fulfilled'], true);
    }
}
