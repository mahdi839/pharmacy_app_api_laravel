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
        'purchase_price',
        'sell_price',
        'mrp_rate',
        'price',
        'stock',
        'discount',
        'image',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'sell_price' => 'decimal:2',
            'mrp_rate' => 'decimal:2',
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

    public function scopeSearch(Builder $query, ?string $name, ?string $company, ?string $from = null, ?string $to = null): void
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
            })
            ->when($from, fn (Builder $query, string $from) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn (Builder $query, string $to) => $query->whereDate('created_at', '<=', $to));
    }

    public function stockValue(): float
    {
        return round((float) $this->purchase_price * $this->stock, 2);
    }

    public function effectiveSellPrice(): float
    {
        return (float) ($this->sell_price ?: $this->price);
    }

    public function effectiveMrpRate(): float
    {
        return (float) ($this->mrp_rate ?: $this->price ?: $this->effectiveSellPrice());
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
        if ($this->mrp_rate !== null) {
            return $this->effectiveSellPrice();
        }

        $price = $this->effectiveSellPrice();

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
            'purchase_price' => (float) $this->purchase_price,
            'mrp_rate' => $this->mrp_rate === null ? null : (float) $this->mrp_rate,
            'sell_price' => $this->effectiveSellPrice(),
            'price' => $this->effectiveMrpRate(),
            'discounted_price' => $this->discountedPrice(),
            'stock' => $this->stock,
            'stock_value' => $this->stockValue(),
            'discount' => $this->discount,
            'image' => $this->displayImage(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
