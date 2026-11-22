<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_root_displays_public_download_page(): void
    {
        $response = $this->get('/');

        $response->assertOk()->assertSee('Download for Android');
    }

    public function test_public_homepage_does_not_link_to_admin_login(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('Admin login')
            ->assertDontSee(route('login'), false);
    }

    public function test_dashboard_requires_admin_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/admin/login');
    }
}
