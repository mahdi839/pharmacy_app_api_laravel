<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AppSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppUpdateTest extends TestCase
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

    public function test_app_version_api_returns_defaults(): void
    {
        $this->withHeader('X-App-Version', '1.1.0')
            ->getJson('/api/app-version')
            ->assertOk()
            ->assertJsonPath('data.latest_version', '1.1.0')
            ->assertJsonPath('data.minimum_version', '1.0.0')
            ->assertJsonPath('data.play_store_url', 'https://play.google.com/store/apps/details?id=com.medbangladesh.app');
    }

    public function test_admin_can_update_app_versions(): void
    {
        $this->actingAs($this->admin())
            ->put('/app-update', [
                'latest_version' => '1.2.0',
                'minimum_version' => '1.1.0',
                'message' => 'New offers screen!',
                'download_url' => 'https://example.com/app.apk',
            ])
            ->assertRedirect('/app-update');

        $this->withHeader('X-App-Version', '1.1.0')
            ->getJson('/api/app-version')
            ->assertJsonPath('data.latest_version', '1.2.0')
            ->assertJsonPath('data.minimum_version', '1.1.0')
            ->assertJsonPath('data.message', 'New offers screen!')
            ->assertJsonPath('data.download_url', 'https://example.com/app.apk');
    }

    public function test_minimum_version_cannot_be_above_latest(): void
    {
        $this->actingAs($this->admin())
            ->from('/app-update')
            ->put('/app-update', ['latest_version' => '1.1.0', 'minimum_version' => '1.2.0'])
            ->assertRedirect('/app-update')
            ->assertSessionHasErrors('minimum_version');

        $this->assertNull(AppSetting::getValue('app_latest_version'));
    }

    public function test_version_must_look_like_a_version_number(): void
    {
        $this->actingAs($this->admin())
            ->put('/app-update', ['latest_version' => 'latest', 'minimum_version' => '1.0.0'])
            ->assertSessionHasErrors('latest_version');
    }

    public function test_guest_cannot_open_app_update_page(): void
    {
        $this->get('/app-update')->assertRedirect('/admin/login');
        $this->actingAs($this->admin())->get('/app-update')->assertOk()->assertSee('Latest Version');
    }

    public function test_old_app_works_normally_while_blocking_is_off(): void
    {
        // Version 1.0.0 sends no X-App-Version header.
        $this->getJson('/api/products')->assertOk();
    }

    public function test_old_app_gets_update_message_when_blocking_is_on(): void
    {
        $this->actingAs($this->admin())->put('/app-update', [
            'latest_version' => '1.1.0',
            'minimum_version' => '1.0.0',
            'block_old_app' => '1',
        ]);

        $this->getJson('/api/products')
            ->assertStatus(426)
            ->assertJsonPath('message', 'A new version of Med Bangladesh is available. Please update the app from Google Play Store to continue.');

        $this->postJson('/api/customer/login', ['login' => 'x', 'password' => 'y'])->assertStatus(426);

        // New app versions send the header and keep working.
        $this->withHeader('X-App-Version', '1.1.0')->getJson('/api/products')->assertOk();
    }
}
