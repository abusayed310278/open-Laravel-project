<?php

namespace App\Models;

use App\Enums\PaymentRoute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'vendor_order_id',
        'product_id',
        'variant_id',
        'product_title',
        'product_grade',
        'product_condition',
        'sku',
        'quantity',
        'unit_price',
        'total_price',
        'payment_route',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'payment_route' => PaymentRoute::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vendorOrder(): BelongsTo
    {
        return $this->belongsTo(VendorOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function commissionRecord(): HasOne
    {
        return $this->hasOne(CommissionRecord::class);
    }
}
