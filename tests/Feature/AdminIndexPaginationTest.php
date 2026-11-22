<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Product;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AdminIndexPaginationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Admin',
            'phone' => '01700000000',
            'gmail' => 'admin@example.com',
            'password' => 'password',
        ]);
    }

    private function product(string $name, int $stock = 10): Product
    {
        return Product::create([
            'name' => $name,
            'company' => 'Test Pharma',
            'strength' => '500mg',
            'form' => 'Tablet',
            'purchase_price' => 5,
            'price' => 25,
            'mrp_rate' => 25,
            'sell_price' => 22,
            'stock' => $stock,
            'discount' => 0,
        ]);
    }

    private function order(string $name, string $phone, float $total, string $address = 'Dhaka'): Order
    {
        static $n = 0;

        return Order::create([
            'order_number' => 'ORD-'.(++$n),
            'customer_name' => $name,
            'customer_phone' => $phone,
            'customer_address' => $address,
            'status' => 'pending',
            'subtotal' => $total,
            'discount_total' => 0,
            'total' => $total,
        ]);
    }

    public function test_products_index_is_paginated_with_totals_over_all_matches(): void
    {
        foreach (range(1, 25) as $i) {
            $this->product(sprintf('Medicine %02d', $i), 2);
        }

        $page1 = $this->actingAs($this->admin())->get('/products');
        $page1->assertOk()
            ->assertSee('Showing <strong>1</strong>&ndash;<strong>20</strong>', false)
            ->assertSee('25 medicines match the current filters.');
        $this->assertCount(20, $page1->viewData('products'));
        $this->assertSame(50, $page1->viewData('totals')['stock']);
        $this->assertEquals(250.0, $page1->viewData('totals')['stock_value']);

        $page2 = $this->get('/products?page=2');
        $page2->assertOk();
        $this->assertCount(5, $page2->viewData('products'));
    }

    public function test_products_pagination_keeps_filters_in_links(): void
    {
        foreach (range(1, 22) as $i) {
            $this->product("Napa {$i}");
        }

        $this->actingAs($this->admin())
            ->get('/products?name=napa')
            ->assertOk()
            ->assertSee('name=napa&amp;page=2', false);
    }

    public function test_customers_index_groups_by_phone_and_paginates(): void
    {
        $this->order('Old Name', '01811111111', 100, 'Old Address');
        $this->order('Rahim', '01811111111', 50, 'New Address');
        foreach (range(1, 21) as $i) {
            $this->order("Customer {$i}", '0190000'.str_pad((string) $i, 4, '0', STR_PAD_LEFT), 10);
        }

        $response = $this->actingAs($this->admin())->get('/customers');
        $response->assertOk()->assertSee('Repeat Customers');

        $stats = $response->viewData('stats');
        $this->assertSame(22, $stats['customers']);
        $this->assertSame(1, $stats['repeat_customers']);
        $this->assertSame(23, $stats['orders']);
        $this->assertEquals(360.0, $stats['revenue']);
        $this->assertCount(20, $response->viewData('customers'));

        $rahim = collect($this->get('/customers?search=Rahim')->viewData('customers')->items())->first();
        $this->assertSame('Rahim', $rahim['name']);
        $this->assertSame('New Address', $rahim['address']);
        $this->assertSame(1, $rahim['orders_count']);

        $repeat = collect($this->get('/customers?search=01811111111')->viewData('customers')->items())->first();
        $this->assertSame('Rahim', $repeat['name']);
        $this->assertSame(2, $repeat['orders_count']);
        $this->assertEquals(150.0, $repeat['total_spent']);

        $this->assertCount(2, $this->get('/customers?page=2')->viewData('customers'));
    }

    public function test_admin_seeder_replaces_old_admins_with_env_credentials(): void
    {
        $this->admin();
        putenv('ADMIN_EMAIL=new-admin@example.com');
        putenv('ADMIN_PASSWORD=Sup3r-Secret!');

        try {
            $this->seed(AdminSeeder::class);
        } finally {
            putenv('ADMIN_EMAIL');
            putenv('ADMIN_PASSWORD');
        }

        $this->assertSame(['new-admin@example.com'], Admin::pluck('gmail')->all());
        $this->assertTrue(Auth::validate(['gmail' => 'new-admin@example.com', 'password' => 'Sup3r-Secret!']));
        $this->assertFalse(Auth::validate(['gmail' => 'admin@example.com', 'password' => 'password']));
    }

    public function test_admin_seeder_without_credentials_leaves_admins_untouched(): void
    {
        $this->admin();

        $this->seed(AdminSeeder::class);

        $this->assertSame(['admin@example.com'], Admin::pluck('gmail')->all());
    }
}
