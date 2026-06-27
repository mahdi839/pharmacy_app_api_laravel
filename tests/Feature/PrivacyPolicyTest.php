<?php

namespace Tests\Feature;

use Tests\TestCase;

class PrivacyPolicyTest extends TestCase
{
    public function test_privacy_policy_is_publicly_accessible(): void
    {
        $response = $this->get('/privacy-policy');

        $response
            ->assertOk()
            ->assertSee('Privacy Policy')
            ->assertSee('Account and data deletion')
            ->assertSee(config('app.privacy_email'));
    }
}
