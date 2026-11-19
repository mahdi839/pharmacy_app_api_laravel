<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\DeviceToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceTokenTest extends TestCase
{
    use RefreshDatabase;

    private function registerCustomer(string $phone): string
    {
        return $this->postJson('/api/customer/register', [
            'name' => 'Token Customer',
            'phone' => $phone,
            'address' => 'Dhaka',
            'password' => 'secret123',
        ])->json('token');
    }

    public function test_guest_cannot_register_a_device_token(): void
    {
        $this->postJson('/api/device-tokens', ['token' => 'fcm-token-1'])->assertUnauthorized();

        $this->assertDatabaseCount('device_tokens', 0);
    }

    public function test_logged_in_customer_can_register_a_device_token(): void
    {
        $apiToken = $this->registerCustomer('01700000001');

        $this->withToken($apiToken)
            ->postJson('/api/device-tokens', ['token' => 'fcm-token-1', 'platform' => 'android'])
            ->assertOk();

        $deviceToken = DeviceToken::sole();
        $this->assertSame('fcm-token-1', $deviceToken->token);
        $this->assertSame(Customer::where('phone', '01700000001')->value('id'), $deviceToken->customer_id);
        $this->assertNotNull($deviceToken->last_seen_at);
    }

    public function test_registering_the_same_token_twice_does_not_duplicate_it(): void
    {
        $apiToken = $this->registerCustomer('01700000002');

        $this->withToken($apiToken)->postJson('/api/device-tokens', ['token' => 'fcm-token-2'])->assertOk();
        $this->withToken($apiToken)->postJson('/api/device-tokens', ['token' => 'fcm-token-2'])->assertOk();

        $this->assertDatabaseCount('device_tokens', 1);
    }

    public function test_token_moves_to_the_customer_who_logs_in_on_the_same_phone(): void
    {
        $firstApiToken = $this->registerCustomer('01700000003');
        $secondApiToken = $this->registerCustomer('01700000004');

        $this->withToken($firstApiToken)->postJson('/api/device-tokens', ['token' => 'shared-phone'])->assertOk();
        $this->withToken($secondApiToken)->postJson('/api/device-tokens', ['token' => 'shared-phone'])->assertOk();

        $this->assertDatabaseCount('device_tokens', 1);
        $this->assertSame(
            Customer::where('phone', '01700000004')->value('id'),
            DeviceToken::sole()->customer_id,
        );
    }

    public function test_customer_can_remove_their_device_token(): void
    {
        $apiToken = $this->registerCustomer('01700000005');

        $this->withToken($apiToken)->postJson('/api/device-tokens', ['token' => 'fcm-token-5'])->assertOk();
        $this->withToken($apiToken)->deleteJson('/api/device-tokens', ['token' => 'fcm-token-5'])->assertOk();

        $this->assertDatabaseCount('device_tokens', 0);
    }

    public function test_device_tokens_are_deleted_with_the_customer(): void
    {
        $apiToken = $this->registerCustomer('01700000006');

        $this->withToken($apiToken)->postJson('/api/device-tokens', ['token' => 'fcm-token-6'])->assertOk();

        Customer::where('phone', '01700000006')->first()->delete();

        $this->assertDatabaseCount('device_tokens', 0);
    }
}
