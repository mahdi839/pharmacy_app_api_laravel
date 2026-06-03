<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'name',
        'company_id',
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

    public function companyInfo(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeSearch(Builder $query, ?string $name, ?string $company): void
    {
        $query
            ->when(trim((string) $name) !== '', function (Builder $query) use ($name): void {
                $query->where('name', 'like', '%'.trim((string) $name).'%');
            })
            ->when(trim((string) $company) !== '', function (Builder $query) use ($company): void {
                $query->where(function (Builder $query) use ($company): void {
                    $query
                        ->whereHas('companyInfo', function (Builder $query) use ($company): void {
                            $query->where('name', 'like', '%'.trim((string) $company).'%');
                        })
                        ->orWhere('company', 'like', '%'.trim((string) $company).'%');
                });
            });
    }

    public function displayImage(): string
    {
        if (! $this->image) {
            return 'https://placehold.co/300x220/e8f5ef/1a7f5a.png?text='.urlencode($this->name);
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return rtrim(request()->getSchemeAndHttpHost(), '/').Storage::url($this->image);
    }

    public function companyName(): string
    {
        return $this->companyInfo?->name ?? $this->getAttribute('company') ?? '';
    }

    public function discountedPrice(): float
    {
        $price = (float) $this->price;

        return round($price - ($price * ($this->discount / 100)), 2);
    }

    public function apiPayload(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'company_id' => $this->company_id,
            'company' => $this->companyName(),
            'strength' => $this->strength,
            'form' => $this->form,
            'price' => (float) $this->price,
            'discounted_price' => $this->discountedPrice(),
            'stock' => $this->stock,
            'discount' => $this->discount,
            'image' => $this->displayImage(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
