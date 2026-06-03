<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'product_id',
        'product_name',
        'company',
        'strength',
        'form',
        'unit_price',
        'discount_percentage',
        'quantity',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'discount_percentage' => 'integer',
            'quantity' => 'integer',
            'line_total' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function apiPayload(): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'company' => $this->company,
            'strength' => $this->strength,
            'form' => $this->form,
            'unit_price' => (float) $this->unit_price,
            'discount_percentage' => $this->discount_percentage,
            'quantity' => $this->quantity,
            'line_total' => (float) $this->line_total,
        ];
    }
}
