<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUSES = [
        'pending',
        'processing',
        'on delivery',
        'delivered',
        'completed',
    ];

    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_address',
        'status',
        'subtotal',
        'discount_total',
        'total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function apiPayload(): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'customer_address' => $this->customer_address,
            'status' => $this->status,
            'subtotal' => (float) $this->subtotal,
            'discount_total' => (float) $this->discount_total,
            'total' => (float) $this->total,
            'notes' => $this->notes,
            'items' => $this->items->map->apiPayload()->values(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
