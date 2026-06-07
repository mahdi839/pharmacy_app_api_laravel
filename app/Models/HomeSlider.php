<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HomeSlider extends Model
{
    protected $fillable = [
        'name',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('home_slider_product.sort_order')
            ->orderBy('products.name');
    }

    public function apiPayload(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'products' => $this->products->map->apiPayload()->values(),
        ];
    }
}
