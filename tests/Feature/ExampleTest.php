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

    public function test_dashboard_requires_admin_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/admin/login');
    }
}
