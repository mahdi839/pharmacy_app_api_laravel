<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_deletion_page_is_publicly_accessible(): void
    {
        $this->get('/account-deletion')
            ->assertOk()
            ->assertSee('Delete your account')
            ->assertSee('Data that will be deleted')
            ->assertSee('Data that may be kept');
    }

    public function test_valid_credentials_delete_account_and_personal_order_data(): void
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'gmail' => 'customer@example.com',
            'phone' => '01700000001',
            'password' => 'correct-password',
            'api_token' => 'test-token',
        ]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'order_number' => 'ORD-DELETE-1',
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'customer_address' => 'Private delivery address',
            'status' => 'pending',
            'subtotal' => 100,
            'discount_total' => 0,
            'total' => 100,
            'notes' => 'Private medical note',
        ]);

        $response = $this->post('/account-deletion', [
            'login' => $customer->phone,
            'password' => 'correct-password',
            'confirm_deletion' => '1',
        ]);

        $response
            ->assertRedirect('/account-deletion')
            ->assertSessionHas('deletion_status');

        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'customer_id' => null,
            'customer_name' => 'Deleted customer',
            'customer_phone' => 'Removed',
            'customer_address' => 'Removed following account deletion',
            'notes' => null,
        ]);
    }

    public function test_invalid_credentials_do_not_delete_account(): void
    {
        $customer = Customer::create([
            'name' => 'Test Customer',
            'gmail' => 'customer@example.com',
            'phone' => '01700000001',
            'password' => 'correct-password',
        ]);

        $this->post('/account-deletion', [
            'login' => $customer->phone,
            'password' => 'incorrect-password',
            'confirm_deletion' => '1',
        ])->assertSessionHasErrors('login');

        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }
}
