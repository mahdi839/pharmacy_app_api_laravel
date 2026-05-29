<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProductStore
{
    private string $path;

    public function __construct()
    {
        $this->path = storage_path('app/products.json');
    }

    public function all(): Collection
    {
        $this->ensureFileExists();

        return collect(json_decode(File::get($this->path), true) ?: [])
            ->sortByDesc('created_at')
            ->values();
    }

    public function search(?string $name, ?string $company): Collection
    {
        $name = Str::lower(trim((string) $name));
        $company = Str::lower(trim((string) $company));

        return $this->all()
            ->filter(function (array $product) use ($name, $company): bool {
                $matchesName = $name === '' || Str::contains(Str::lower($product['name']), $name);
                $matchesCompany = $company === '' || Str::contains(Str::lower($product['company']), $company);

                return $matchesName && $matchesCompany;
            })
            ->values();
    }

    public function create(array $data): array
    {
        $products = $this->all();
        $product = [
            'id' => (string) Str::uuid(),
            'name' => $data['name'],
            'company' => $data['company'],
            'strength' => $data['strength'],
            'form' => $data['form'],
            'price' => (float) $data['price'],
            'stock' => (int) $data['stock'],
            'discount' => (int) ($data['discount'] ?? 0),
            'image' => $data['image'] ?: 'https://placehold.co/300x220/e8f5ef/1a7f5a.png?text='.urlencode($data['name']),
            'created_at' => now()->toIso8601String(),
        ];

        $products->prepend($product);
        File::put($this->path, json_encode($products->values()->all(), JSON_PRETTY_PRINT));

        return $product;
    }

    private function ensureFileExists(): void
    {
        if (! File::exists(dirname($this->path))) {
            File::makeDirectory(dirname($this->path), 0755, true);
        }

        if (! File::exists($this->path)) {
            File::put($this->path, json_encode($this->seedProducts(), JSON_PRETTY_PRINT));
        }
    }

    private function seedProducts(): array
    {
        return [
            [
                'id' => (string) Str::uuid(),
                'name' => 'Napa Extra',
                'company' => 'Beximco Pharma',
                'strength' => '500mg',
                'form' => 'Tablet',
                'price' => 25,
                'stock' => 120,
                'discount' => 8,
                'image' => 'https://placehold.co/300x220/e8f5ef/1a7f5a.png?text=Napa+Extra',
                'created_at' => now()->subMinutes(5)->toIso8601String(),
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Seclo',
                'company' => 'Square Pharmaceuticals',
                'strength' => '20mg',
                'form' => 'Capsule',
                'price' => 70,
                'stock' => 85,
                'discount' => 12,
                'image' => 'https://placehold.co/300x220/eef2ff/2654a7.png?text=Seclo',
                'created_at' => now()->subMinutes(4)->toIso8601String(),
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Maxpro',
                'company' => 'Renata Limited',
                'strength' => '20mg',
                'form' => 'Tablet',
                'price' => 80,
                'stock' => 64,
                'discount' => 10,
                'image' => 'https://placehold.co/300x220/f4f0ff/6546b8.png?text=Maxpro',
                'created_at' => now()->subMinutes(3)->toIso8601String(),
            ],
        ];
    }
}
