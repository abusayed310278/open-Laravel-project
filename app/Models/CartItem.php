<?php

namespace App\Models;

use App\Enums\PaymentRoute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'variant_id',
        'quantity',
        'price',
        'payment_route',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'payment_route' => PaymentRoute::class,
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function lineTotal(): float
    {
        return (float) ($this->price * $this->quantity);
    }
}
