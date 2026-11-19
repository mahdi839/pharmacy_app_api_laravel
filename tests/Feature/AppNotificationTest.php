<?php

namespace Tests\Feature;

use App\Jobs\SendAppNotification;
use App\Models\Admin;
use App\Models\AppNotification;
use App\Models\Customer;
use App\Models\DeviceToken;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AppNotificationTest extends TestCase
{
    use RefreshDatabase;

    private string $credentialsPath;

    protected function setUp(): void
    {
        parent::setUp();

        // A throwaway service account key so the real JWT signing code runs.
        // Windows PHP builds need their bundled openssl.cnf to generate keys.
        $opensslOptions = array_filter([
            'config' => is_file($cnf = dirname(PHP_BINARY).'/extras/ssl/openssl.cnf') ? $cnf : null,
        ]);
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA] + $opensslOptions);
        openssl_pkey_export($key, $privateKey, null, $opensslOptions);

        $this->credentialsPath = tempnam(sys_get_temp_dir(), 'fcm');
        file_put_contents($this->credentialsPath, json_encode([
            'type' => 'service_account',
            'project_id' => 'test-project',
            'client_email' => 'test@test-project.iam.gserviceaccount.com',
            'private_key' => $privateKey,
            'token_uri' => 'https://oauth2.googleapis.com/token',
        ]));

        config(['services.fcm.credentials' => $this->credentialsPath]);
    }

    protected function tearDown(): void
    {
        @unlink($this->credentialsPath);

        parent::tearDown();
    }

    private function admin(): Admin
    {
        return Admin::create([
            'name' => 'Admin',
            'phone' => '01700000000',
            'gmail' => 'admin@example.com',
            'password' => 'password',
        ]);
    }

    private function customerWithToken(string $phone, ?string $deviceToken = null): array
    {
        $apiToken = $this->postJson('/api/customer/register', [
            'name' => 'Customer '.$phone,
            'phone' => $phone,
            'address' => 'Dhaka',
            'password' => 'secret123',
        ])->json('token');

        if ($deviceToken) {
            $this->withToken($apiToken)->postJson('/api/device-tokens', ['token' => $deviceToken])->assertOk();
        }

        return [$apiToken, Customer::where('phone', $phone)->first()];
    }

    private function product(): Product
    {
        return Product::create([
            'name' => 'Napa Extra',
            'company' => 'Test Pharma',
            'strength' => '500mg',
            'form' => 'Tablet',
            'purchase_price' => 5,
            'price' => 25,
            'mrp_rate' => 25,
            'sell_price' => 22,
            'stock' => 10,
            'discount' => 0,
        ]);
    }

    private function fakeFirebase(array $unregisteredTokens = []): void
    {
        Http::fake(function (HttpRequest $request) use ($unregisteredTokens) {
            if (str_contains($request->url(), 'oauth2.googleapis.com')) {
                return Http::response(['access_token' => 'test-access-token', 'expires_in' => 3600]);
            }

            if (in_array($request['message']['token'] ?? null, $unregisteredTokens, true)) {
                return Http::response([
                    'error' => ['status' => 'NOT_FOUND', 'details' => [['errorCode' => 'UNREGISTERED']]],
                ], 404);
            }

            return Http::response(['name' => 'projects/test-project/messages/1']);
        });
    }

    public function test_guest_cannot_open_notification_pages(): void
    {
        $this->get('/notifications')->assertRedirect('/admin/login');
        $this->get('/notifications/create')->assertRedirect('/admin/login');
        $this->post('/notifications', ['title' => 'x', 'body' => 'y'])->assertRedirect('/admin/login');
    }

    public function test_admin_pages_render(): void
    {
        $this->product();
        $admin = $this->admin();

        $this->actingAs($admin)->get('/notifications')->assertOk()->assertSee('Send Notification');
        $this->actingAs($admin)->get('/notifications/create')->assertOk()->assertSee('Napa Extra');
    }

    public function test_admin_sends_notification_with_product_to_all_logged_in_phones(): void
    {
        Storage::fake('public');
        $this->fakeFirebase();
        $product = $this->product();
        $this->customerWithToken('01700000001', 'phone-token-1');
        $this->customerWithToken('01700000002', 'phone-token-2');

        $this->actingAs($this->admin())->post('/notifications', [
            'title' => 'Napa Extra 10% off',
            'body' => 'Offer valid till Friday',
            'product_id' => $product->id,
            'image' => UploadedFile::fake()->image('offer.jpg', 1024, 512),
        ])->assertRedirect('/notifications');

        $notification = AppNotification::sole();
        Storage::disk('public')->assertExists($notification->image);
        $this->assertSame(AppNotification::STATUS_SENT, $notification->status);
        $this->assertSame(2, $notification->sent_count);
        $this->assertSame(0, $notification->failed_count);
        $this->assertNotNull($notification->sent_at);

        Http::assertSent(function (HttpRequest $request) use ($notification, $product): bool {
            if (! str_contains($request->url(), 'fcm.googleapis.com/v1/projects/test-project/messages:send')) {
                return false;
            }

            $message = $request['message'];

            return $request->hasHeader('Authorization', 'Bearer test-access-token')
                && $message['token'] === 'phone-token-1'
                && $message['notification']['title'] === 'Napa Extra 10% off'
                && $message['notification']['body'] === 'Offer valid till Friday'
                && str_ends_with($message['notification']['image'], $notification->image)
                && $message['data'] === ['notification_id' => (string) $notification->id, 'product_id' => (string) $product->id]
                && $message['android']['notification']['channel_id'] === 'default';
        });
    }

    public function test_notification_without_product_or_image_sends_only_notification_id(): void
    {
        $this->fakeFirebase();
        $this->customerWithToken('01700000003', 'phone-token-3');

        $this->actingAs($this->admin())->post('/notifications', [
            'title' => 'Hello',
            'body' => 'New stock arrived',
        ])->assertRedirect('/notifications');

        $notification = AppNotification::sole();

        Http::assertSent(fn (HttpRequest $request): bool => str_contains($request->url(), 'fcm.googleapis.com')
            && ! isset($request['message']['notification']['image'])
            && $request['message']['data'] === ['notification_id' => (string) $notification->id]);
    }

    public function test_uninstalled_app_tokens_are_removed_after_sending(): void
    {
        $this->fakeFirebase(['dead-token']);
        $this->customerWithToken('01700000004', 'live-token');
        $this->customerWithToken('01700000005', 'dead-token');

        $notification = AppNotification::create(['title' => 'Hi', 'body' => 'There']);
        SendAppNotification::dispatchSync($notification);

        $notification->refresh();
        $this->assertSame(1, $notification->sent_count);
        $this->assertSame(1, $notification->failed_count);
        $this->assertSame(['live-token'], DeviceToken::pluck('token')->all());
    }

    public function test_sending_is_marked_failed_when_firebase_key_is_missing(): void
    {
        config(['services.fcm.credentials' => '/missing/firebase.json']);
        Http::fake();
        $this->customerWithToken('01700000006', 'phone-token-6');

        $notification = AppNotification::create(['title' => 'Hi', 'body' => 'There']);
        SendAppNotification::dispatchSync($notification);

        $this->assertSame(AppNotification::STATUS_FAILED, $notification->fresh()->status);
        Http::assertNothingSent();
    }

    public function test_customer_inbox_lists_notifications_and_unread_count(): void
    {
        [$apiToken, $customer] = $this->customerWithToken('01700000007');
        $customer->forceFill(['created_at' => now()->subDay()])->save();
        $product = $this->product();

        AppNotification::create(['title' => 'First', 'body' => 'One']);
        AppNotification::create(['title' => 'Second', 'body' => 'Two', 'product_id' => $product->id]);

        $this->getJson('/api/notifications')->assertUnauthorized();

        $this->withToken($apiToken)->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('unread_count', 2)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'Second')
            ->assertJsonPath('data.0.product_id', $product->id)
            ->assertJsonPath('data.0.is_unread', true);

        $this->withToken($apiToken)->postJson('/api/notifications/read')->assertOk();

        $this->withToken($apiToken)->getJson('/api/notifications')
            ->assertJsonPath('unread_count', 0)
            ->assertJsonPath('data.0.is_unread', false);

        $this->travel(1)->minutes();
        AppNotification::create(['title' => 'Third', 'body' => 'Three']);

        $this->withToken($apiToken)->getJson('/api/notifications')
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('data.0.title', 'Third')
            ->assertJsonPath('data.0.is_unread', true);
    }

    public function test_new_customer_does_not_see_older_notifications_as_unread(): void
    {
        AppNotification::create(['title' => 'Old offer', 'body' => 'Before signup']);
        $this->travel(1)->minutes();

        [$apiToken] = $this->customerWithToken('01700000008');

        $this->withToken($apiToken)->getJson('/api/notifications')
            ->assertJsonPath('unread_count', 0)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.is_unread', false);
    }

    public function test_admin_can_delete_notification(): void
    {
        $notification = AppNotification::create(['title' => 'Hi', 'body' => 'There']);

        $this->actingAs($this->admin())
            ->delete('/notifications/'.$notification->id)
            ->assertRedirect('/notifications');

        $this->assertDatabaseCount('app_notifications', 0);
    }

    public function test_single_product_api_returns_product(): void
    {
        $product = $this->product();

        $this->getJson('/api/products/'.$product->id)
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.name', 'Napa Extra');

        $this->getJson('/api/products/999999')->assertNotFound();
    }
}
