<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'company',
        'strength',
        'form',
        'price',
        'stock',
        'discount',
        'image',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'discount' => 'integer',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeSearch(Builder $query, ?string $name, ?string $company): void
    {
        $query
            ->when(trim((string) $name) !== '', function (Builder $query) use ($name): void {
                $query->where('name', 'like', '%'.trim((string) $name).'%');
            })
            ->when(trim((string) $company) !== '', function (Builder $query) use ($company): void {
                $query->where('company', 'like', '%'.trim((string) $company).'%');
            });
    }

    public function displayImage(): string
    {
        return $this->image ?: 'https://placehold.co/300x220/e8f5ef/1a7f5a.png?text='.urlencode($this->name);
    }

    public function apiPayload(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'company' => $this->company,
            'strength' => $this->strength,
            'form' => $this->form,
            'price' => (float) $this->price,
            'stock' => $this->stock,
            'discount' => $this->discount,
            'image' => $this->displayImage(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
