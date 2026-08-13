<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Banner;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MobileShoppingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_order_decreases_product_stock(): void
    {
        $product = Product::create([
            'name' => 'Stock Test Medicine',
            'company' => 'Test Pharma',
            'strength' => '500mg',
            'form' => 'Tablet',
            'price' => 20,
            'sell_price' => 18,
            'stock' => 10,
            'discount' => 10,
        ]);

        $token = $this->postJson('/api/customer/register', [
            'name' => 'Stock Customer',
            'gmail' => 'stock@example.com',
            'phone' => '01700000123',
            'address' => 'Dhaka',
            'password' => 'secret123',
        ])->json('token');

        $this->withToken($token)->postJson('/api/orders', [
            'customer_name' => 'Stock Customer',
            'customer_phone' => '01700000123',
            'customer_address' => 'Dhaka',
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
        ])->assertCreated();

        $this->assertSame(7, $product->fresh()->stock);
    }

    public function test_repeat_orders_are_combined_during_a_fixed_twelve_hour_window(): void
    {
        $product = Product::create([
            'name' => 'Repeat Order Medicine',
            'company' => 'Test Pharma',
            'strength' => '500mg',
            'form' => 'Tablet',
            'price' => 20,
            'mrp_rate' => 20,
            'sell_price' => 18,
            'stock' => 10,
            'discount' => 10,
        ]);

        $token = $this->postJson('/api/customer/register', [
            'name' => 'Repeat Customer',
            'gmail' => 'repeat@example.com',
            'phone' => '01700000789',
            'address' => 'Dhaka',
            'password' => 'secret123',
        ])->json('token');

        $payload = [
            'customer_name' => 'Repeat Customer',
            'customer_phone' => '01700000789',
            'customer_address' => 'Dhaka',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ];

        $firstResponse = $this->withToken($token)->postJson('/api/orders', $payload)->assertCreated();
        $firstOrderId = $firstResponse->json('data.id');

        $this->travel(11)->hours();

        $this->withToken($token)->postJson('/api/orders', [
            ...$payload,
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ])
            ->assertCreated()
            ->assertJsonPath('data.id', $firstOrderId)
            ->assertJsonPath('data.combined_order_count', 2)
            ->assertJsonPath('data.items.0.quantity', 3)
            ->assertJsonPath('data.total', 54);

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('orders', [
            'id' => $firstOrderId,
            'combined_order_count' => 2,
            'total' => 54,
        ]);
        $this->assertSame(7, $product->fresh()->stock);

        $combinedOrder = Order::with('items')->findOrFail($firstOrderId);
        $this->view('orders.invoice', ['order' => $combinedOrder])
            ->assertSee('Orders combined')
            ->assertSee('>2<', false);

        $this->travel(2)->hours();

        $this->withToken($token)->postJson('/api/orders', $payload)
            ->assertCreated()
            ->assertJsonPath('data.combined_order_count', 1);

        $this->assertDatabaseCount('orders', 2);
        $this->assertSame(6, $product->fresh()->stock);
    }

    public function test_a_repeat_order_does_not_change_an_order_already_being_processed(): void
    {
        $product = Product::create([
            'name' => 'Processing Order Medicine',
            'company' => 'Test Pharma',
            'strength' => '10mg',
            'form' => 'Tablet',
            'price' => 10,
            'sell_price' => 10,
            'stock' => 5,
            'discount' => 0,
        ]);

        $token = $this->postJson('/api/customer/register', [
            'name' => 'Processing Customer',
            'gmail' => 'processing@example.com',
            'phone' => '01700000987',
            'address' => 'Dhaka',
            'password' => 'secret123',
        ])->json('token');

        $payload = [
            'customer_name' => 'Processing Customer',
            'customer_phone' => '01700000987',
            'customer_address' => 'Dhaka',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ];

        $this->withToken($token)->postJson('/api/orders', $payload)->assertCreated();
        Order::firstOrFail()->update(['status' => 'processing']);

        $this->withToken($token)->postJson('/api/orders', $payload)
            ->assertCreated()
            ->assertJsonPath('data.combined_order_count', 1);

        $this->assertDatabaseCount('orders', 2);
        $this->assertDatabaseHas('orders', ['status' => 'processing', 'combined_order_count' => 1]);
        $this->assertDatabaseHas('orders', ['status' => 'pending', 'combined_order_count' => 1]);
    }

    public function test_mobile_order_cannot_exceed_available_stock(): void
    {
        $product = Product::create([
            'name' => 'Limited Medicine',
            'company' => 'Test Pharma',
            'strength' => '10mg',
            'form' => 'Tablet',
            'price' => 10,
            'sell_price' => 10,
            'stock' => 2,
            'discount' => 0,
        ]);

        $token = $this->postJson('/api/customer/register', [
            'name' => 'Limited Customer',
            'gmail' => 'limited@example.com',
            'phone' => '01700000456',
            'address' => 'Dhaka',
            'password' => 'secret123',
        ])->json('token');

        $this->withToken($token)->postJson('/api/orders', [
            'customer_name' => 'Limited Customer',
            'customer_phone' => '01700000456',
            'customer_address' => 'Dhaka',
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
        ])->assertUnprocessable()->assertJsonValidationErrors('items');

        $this->assertSame(2, $product->fresh()->stock);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_customer_registration_saves_and_returns_delivery_address(): void
    {
        $response = $this->postJson('/api/customer/register', [
            'name' => 'Mobile Customer',
            'gmail' => 'mobile@example.com',
            'phone' => '01700000099',
            'address' => '12 Test Road, Dhaka',
            'password' => 'secret123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('customer.address', '12 Test Road, Dhaka');

        $this->assertDatabaseHas('customers', [
            'phone' => '01700000099',
            'address' => '12 Test Road, Dhaka',
        ]);
    }

    public function test_banner_api_returns_mobile_banner_images(): void
    {
        Banner::create(['image' => 'banners/test-banner.jpg']);

        $this->getJson('/api/banners')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 1)
            ->assertJsonPath('data.0.image', url('/storage/banners/test-banner.jpg'));
    }

    public function test_admin_can_create_replace_and_delete_a_banner(): void
    {
        Storage::fake('public');
        $admin = Admin::create([
            'name' => 'Admin',
            'phone' => '01700000000',
            'gmail' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->actingAs($admin)->post('/banners', [
            'image' => UploadedFile::fake()->image('first.jpg', 1080, 360),
        ])->assertRedirect('/banners');

        $banner = Banner::firstOrFail();
        Storage::disk('public')->assertExists($banner->image);
        $oldImage = $banner->image;

        $this->actingAs($admin)->put("/banners/{$banner->id}", [
            'image' => UploadedFile::fake()->image('replacement.jpg', 1080, 360),
        ])->assertRedirect('/banners');

        $banner->refresh();
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($banner->image);

        $this->actingAs($admin)
            ->delete("/banners/{$banner->id}")
            ->assertRedirect('/banners');

        Storage::disk('public')->assertMissing($banner->image);
        $this->assertDatabaseMissing('banners', ['id' => $banner->id]);
    }

    public function test_invoice_is_formatted_for_an_80mm_thermal_printer(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-THERMAL-1',
            'customer_name' => 'Receipt Customer',
            'customer_phone' => '01700000001',
            'customer_address' => 'Dhaka',
            'status' => 'pending',
            'subtotal' => 100,
            'discount_total' => 10,
            'total' => 90,
        ]);

        $this->get("/orders/{$order->id}/invoice")
            ->assertRedirect('/admin/login');

        $view = $this->view('orders.invoice', [
            'order' => $order->load('items'),
        ]);

        $view
            ->assertSee('Print 58mm Receipt')
            ->assertSee('Print 80mm Receipt')
            ->assertSee('@page { size: 80mm', false);
    }
}
