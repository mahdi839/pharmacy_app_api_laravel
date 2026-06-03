<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Admin::updateOrCreate(
            ['gmail' => 'admin@gmail.com'],
            [
                'name' => 'Pharmacy Admin',
                'phone' => '01700000000',
                'password' => 'password',
            ],
        );

        collect([
            [
                'name' => 'Napa Extra',
                'company' => 'Beximco Pharma',
                'strength' => '500mg',
                'form' => 'Tablet',
                'price' => 25,
                'stock' => 120,
                'discount' => 8,
                'image' => 'https://placehold.co/300x220/e8f5ef/1a7f5a.png?text=Napa+Extra',
            ],
            [
                'name' => 'Seclo',
                'company' => 'Square Pharmaceuticals',
                'strength' => '20mg',
                'form' => 'Capsule',
                'price' => 70,
                'stock' => 85,
                'discount' => 12,
                'image' => 'https://placehold.co/300x220/eef2ff/2654a7.png?text=Seclo',
            ],
            [
                'name' => 'Maxpro',
                'company' => 'Renata Limited',
                'strength' => '20mg',
                'form' => 'Tablet',
                'price' => 80,
                'stock' => 64,
                'discount' => 10,
                'image' => 'https://placehold.co/300x220/f4f0ff/6546b8.png?text=Maxpro',
            ],
        ])->each(fn (array $product): Product => Product::updateOrCreate(
            ['name' => $product['name'], 'company' => $product['company'], 'strength' => $product['strength']],
            $product,
        ));
    }
}
